<?php
class Transaksi {
    private $db;
    private $conn;
    private $barangModel; // Butuh akses ke model barang untuk update stok
    private $stmt;

    public function __construct(Database $db, Barang $barangModel) {
        $this->db = $db;
        $this->conn = $db->getConnection();
        $this->barangModel = $barangModel;
    }

    private function generateKodeTransaksi() {
        // Contoh format: INV-YYYYMMDD-NNN
        $tanggal = date('Ymd');
        $sql = "SELECT COUNT(*) as count FROM transaksi WHERE kode_transaksi LIKE ?";
        $prefix = "INV-" . $tanggal . "-%";
        if ($this->db->query($sql)) {
            $this->db->bind([$prefix]);
            $result = $this->db->single();
            $urutan = ($result['count'] ?? 0) + 1;
            return "INV-" . $tanggal . "-" . sprintf('%03d', $urutan);
        } else {
            // Fallback sederhana jika query gagal
            return "INV-" . $tanggal . "-" . uniqid();
        }
    }

    public function create($userId, $keranjang, $totalHarga, $uangBayar, $uangKembali) {
        $kodeTransaksi = $this->generateKodeTransaksi();

        // Mulai database transaction
        $this->db->beginTransaction();

        try {
             // 1. Insert ke tabel transaksi (header)
            $sqlHeader = "INSERT INTO transaksi (kode_transaksi, user_id, total_harga, uang_bayar, uang_kembali) VALUES (?, ?, ?, ?, ?)";
            $stmtHeader = $this->conn->prepare($sqlHeader);
            if ($stmtHeader === false) throw new Exception("Gagal prepare header: " . $this->conn->error);
            // Perbaiki tipe data terakhir dari 's' menjadi 'd' untuk uang_kembali
            $stmtHeader->bind_param("siddd", $kodeTransaksi, $userId, $totalHarga, $uangBayar, $uangKembali); // <--- PERUBAHAN DI SINI
            if (!$stmtHeader->execute()) {
                throw new Exception("Gagal execute header: " . $stmtHeader->error);
            }
            $transaksiId = $this->db->lastInsertId();
            $stmtHeader->close();

            // 2. Insert ke tabel detail_transaksi dan kurangi stok barang
            $sqlDetail = "INSERT INTO detail_transaksi (transaksi_id, barang_id, jumlah, harga_saat_transaksi, subtotal) VALUES (?, ?, ?, ?, ?)";
            $stmtDetail = $this->conn->prepare($sqlDetail);
             if ($stmtDetail === false) throw new Exception("Gagal prepare detail: " . $this->conn->error);

            foreach ($keranjang as $item) {
                $barangId = $item['id'];
                $jumlah = $item['jumlah'];
                $hargaSaatTransaksi = $item['harga']; // Ambil harga dari keranjang (harga saat itu)
                $subtotal = $hargaSaatTransaksi * $jumlah;

                // Bind parameter untuk detail
                $stmtDetail->bind_param("iiidd", $transaksiId, $barangId, $jumlah, $hargaSaatTransaksi, $subtotal);
                 if (!$stmtDetail->execute()) {
                    throw new Exception("Gagal execute detail untuk barang ID " . $barangId . ": " . $stmtDetail->error);
                }

                // 3. Kurangi stok barang menggunakan BarangModel
                if (!$this->barangModel->kurangiStok($barangId, $jumlah)) {
                    // Jika gagal kurangi stok (misal stok tidak cukup), batalkan transaksi
                    throw new Exception("Stok tidak cukup untuk barang ID " . $barangId);
                }
            }
            $stmtDetail->close();

            // Jika semua berhasil, commit transaksi
            $this->db->commit();
            return $kodeTransaksi; // Kembalikan kode transaksi jika sukses

        } catch (Exception $e) {
            // Jika ada error, rollback transaksi
            $this->db->rollback();
            error_log("Transaksi Model: Gagal create - " . $e->getMessage());
            return false; // Atau throw exception lagi
        }
    }

     // Fungsi untuk mengambil data transaksi berdasarkan Kode
    public function getByKode($kodeTransaksi) {
        $sql = "SELECT t.*, u.nama_lengkap as nama_kasir
                FROM transaksi t
                JOIN users u ON t.user_id = u.id
                WHERE t.kode_transaksi = ?";
         if ($this->db->query($sql)) {
            $this->db->bind([$kodeTransaksi]);
            return $this->db->single();
        }
        return null;
    }

    // Fungsi untuk mengambil detail transaksi berdasarkan ID Transaksi
    public function getDetailByTransaksiId($transaksiId) {
        $sql = "SELECT dt.*, b.nama_barang
                FROM detail_transaksi dt
                JOIN barang b ON dt.barang_id = b.id
                WHERE dt.transaksi_id = ?";
         if ($this->db->query($sql)) {
             // Bind sebagai integer
             $this->stmt = $this->conn->prepare($sql);
             if ($this->stmt === false) return [];
             $this->stmt->bind_param("i", $transaksiId);
             $result = $this->stmt->execute();
             $resultSet = $this->stmt->get_result();
             $rows = [];
             if ($resultSet) {
                 while ($row = $resultSet->fetch_assoc()) {
                     $rows[] = $row;
                 }
             }
             $this->stmt->close();
             return $rows;
        }
        return [];
    }
    public function countUsers() {
        $sql = "SELECT COUNT(id) as count FROM users";
        if ($this->db->query($sql)) {
            $result = $this->db->single();
            return $result['count'] ?? 0;
        }
        return 0;
    }
    public function countToday() {
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        $sql = "SELECT COUNT(id) as count FROM transaksi WHERE tanggal_transaksi BETWEEN ? AND ?";
        if ($this->db->query($sql)) {
            $this->db->bind([$todayStart, $todayEnd]);
            $result = $this->db->single();
            return $result['count'] ?? 0;
        }
        return 0;
    }
    
    public function sumSalesToday() {
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        $sql = "SELECT SUM(total_harga) as total FROM transaksi WHERE tanggal_transaksi BETWEEN ? AND ?";
        if ($this->db->query($sql)) {
            $this->db->bind([$todayStart, $todayEnd]);
            $result = $this->db->single();
            return $result['total'] ?? 0;
        }
        return 0;
    }
}
?>
