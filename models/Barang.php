<?php
class Barang
{
    private $db;
    private $conn;
    private $stmt;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->conn = $db->getConnection();
    }

    public function getAll()
    {
        $sql = "SELECT id, nama_barang, harga, stok FROM barang ORDER BY nama_barang ASC";
        if ($this->db->query($sql)) {
            return $this->db->resultSet();
        } else {
            error_log("Barang Model: Gagal query getAll - " . $this->conn->error);
            return [];
        }
    }

    public function getById($id)
    {
        $sql = "SELECT id, nama_barang, harga, stok FROM barang WHERE id = ?";
        if ($this->db->query($sql)) {
            $this->db->bind([$id]);
            return $this->db->single();
        } else {
            error_log("Barang Model: Gagal query getById - " . $this->conn->error);
            return null;
        }
    }

    public function create($nama_barang, $harga, $stok)
    {
        $sql = "INSERT INTO barang (nama_barang, harga, stok) VALUES (?, ?, ?)";
        if ($this->db->query($sql)) {
            // Pastikan tipe data sesuai (string, double, integer)
            // Perlu penyesuaian di method bind jika tipe berbeda
            $this->stmt = $this->conn->prepare($sql); // Prepare ulang untuk bind type spesifik
            if ($this->stmt === false) return false;
            $this->stmt->bind_param("sdi", $nama_barang, $harga, $stok);
            $result = $this->stmt->execute();
            $this->stmt->close();
            return $result;
        } else {
            error_log("Barang Model: Gagal query create - " . $this->conn->error);
            return false;
        }
    }

    public function delete($id)
    {
        $sql = "DELETE FROM barang WHERE id = ?";
        if ($this->db->query($sql)) {
            $this->stmt = $this->conn->prepare($sql);
            if ($this->stmt === false) return false;
            $this->stmt->bind_param("i", $id);
            $result = $this->stmt->execute();

            if ($result && $this->stmt->affected_rows > 0) {
                $this->stmt->close();
                return true; // Barang berhasil dihapus
            } else {
                error_log("Barang Model: Tidak ada baris yang terhapus (ID: $id).");
                $this->stmt->close();
                return false; // Tidak ada baris yang terhapus
            }
        } else {
            error_log("Barang Model: Gagal query delete - " . $this->conn->error);
            return false;
        }
    }


    // Fungsi untuk mengurangi stok saat transaksi
    public function kurangiStok($id, $jumlah)
    {
        $sql = "UPDATE barang SET stok = stok - ? WHERE id = ? AND stok >= ?";
        if ($this->db->query($sql)) {
            // Bind sebagai integer 'i', 'i', 'i'
            $stmt = $this->conn->prepare($sql); // Prepare ulang jika perlu bind type spesifik
            if ($stmt === false) {
                error_log("Barang Model: Gagal prepare kurangiStok - " . $this->conn->error);
                return false; // Gagal prepare
            }
            $stmt->bind_param("iii", $jumlah, $id, $jumlah); // Pastikan stok cukup saat update
            $result = $stmt->execute();
            $affectedRows = $stmt->affected_rows; // Dapatkan affected rows
            $stmt->close();

            if ($result && $affectedRows > 0) {
                return true; // Sukses jika eksekusi berhasil DAN ada baris yang terpengaruh
            } else {
                // Jika result false ATAU affectedRows 0 (stok tidak cukup atau id salah)
                if (!$result) {
                    error_log("Barang Model: Gagal execute kurangiStok - " . $stmt->error); // Log error execute
                } else {
                    error_log("Barang Model: Gagal kurangiStok - Stok tidak cukup atau ID $id tidak ditemukan saat UPDATE."); // Log kasus affectedRows 0
                }
                return false; // Gagal mengurangi stok
            }
        } else {
            error_log("Barang Model: Gagal query kurangiStok - " . $this->conn->error); // Error pada query awal (jarang)
            return false;
        }
    }

    // Fungsi untuk menambah stok (jika diperlukan, misal pembatalan transaksi)
    public function tambahStok($id, $jumlah)
    {
        $sql = "UPDATE barang SET stok = stok + ? WHERE id = ?";
        if ($this->db->query($sql)) {
            $this->stmt = $this->conn->prepare($sql);
            if ($this->stmt === false) return false;
            $this->stmt->bind_param("ii", $jumlah, $id);
            $result = $this->stmt->execute();
            $this->stmt->close();
            return $result;
        } else {
            error_log("Barang Model: Gagal query tambahStok - " . $this->conn->error);
            return false;
        }
    }
    public function countAll()
    {
        $sql = "SELECT COUNT(id) as count FROM barang";
        if ($this->db->query($sql)) {
            $result = $this->db->single();
            return $result['count'] ?? 0;
        }
        return 0;
    }
}
