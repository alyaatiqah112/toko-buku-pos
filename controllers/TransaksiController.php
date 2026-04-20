<?php
class TransaksiController {
    private $db;
    private $transaksiModel;
    private $barangModel;
    private $authModel;

    public function __construct(Database $db) {
        $this->db = $db;
        $this->authModel = new Auth($db);

        // Semua aksi di controller ini memerlukan login
        if (!$this->authModel->isLoggedIn()) {
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Anda harus login untuk mengakses halaman ini.'];
            header('Location: index.php?page=login');
            exit;
        }

        // Inisialisasi model lain setelah cek login
        $this->barangModel = new Barang($db);
        $this->transaksiModel = new Transaksi($db, $this->barangModel); // Transaksi butuh BarangModel
    }

    // Menampilkan halaman utama kasir (POS)
    public function index() {
        // Ambil semua barang yang tersedia untuk ditampilkan di daftar pilihan
        $dataBarangTersedia = $this->barangModel->getAll();

        // Load view kasir, passing data barang
        include __DIR__ . '/../views/transaksi/index.php';
    }

    // Memproses transaksi dari form kasir
    public function processTransaction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=transaksi');
            exit;
        }

        // Ambil data dari POST
        $keranjangJson = $_POST['keranjang_data'] ?? '[]';
        $uangBayar = filter_input(INPUT_POST, 'uang_bayar', FILTER_VALIDATE_FLOAT);
        $keranjangArray = json_decode($keranjangJson, true); // Decode JSON jadi array PHP

        // Validasi Sisi Server
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($keranjangArray)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Data keranjang tidak valid.'];
            header('Location: index.php?page=transaksi');
            exit;
        }

        if (empty($keranjangArray)) {
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Keranjang belanja masih kosong.'];
            header('Location: index.php?page=transaksi');
            exit;
        }

        if ($uangBayar === false || $uangBayar < 0) {
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Jumlah uang bayar tidak valid.'];
            header('Location: index.php?page=transaksi');
            // Sebaiknya simpan keranjang di session agar tidak hilang saat redirect
            // $_SESSION['keranjang_temp'] = $keranjangArray;
            exit;
        }

        // Hitung ulang total harga di server (lebih aman)
        $totalHargaServer = 0;
         $itemsToProcess = []; // Simpan item yang valid dengan harga dari DB
        foreach ($keranjangArray as $itemClient) {
             if (!isset($itemClient['id']) || !isset($itemClient['jumlah']) || $itemClient['jumlah'] <= 0) continue; // Skip item tidak valid

             $barangDb = $this->barangModel->getById($itemClient['id']);
             if (!$barangDb) {
                 $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Error: Barang dengan ID ' . $itemClient['id'] . ' tidak ditemukan di database.'];
                 header('Location: index.php?page=transaksi');
                 exit;
             }
              if ($itemClient['jumlah'] > $barangDb['stok']) {
                 $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Error: Stok untuk ' . htmlspecialchars($barangDb['nama_barang']) . ' tidak mencukupi (tersisa ' . $barangDb['stok'] . ').'];
                 header('Location: index.php?page=transaksi');
                 exit;
             }

             // Gunakan harga dari DB saat ini untuk kalkulasi server
             $hargaSaatIni = $barangDb['harga'];
             $subtotal = $hargaSaatIni * $itemClient['jumlah'];
             $totalHargaServer += $subtotal;

              // Simpan item yang valid untuk diproses
             $itemsToProcess[] = [
                'id' => $itemClient['id'],
                'jumlah' => $itemClient['jumlah'],
                'harga' => $hargaSaatIni // Gunakan harga dari DB
             ];
        }

         if (empty($itemsToProcess)) {
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Tidak ada item valid dalam keranjang.'];
             header('Location: index.php?page=transaksi');
             exit;
         }


        // Cek kecukupan uang bayar
        if ($uangBayar < $totalHargaServer) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Uang pembayaran kurang. Total: ' . number_format($totalHargaServer, 0, ',', '.') . ', Bayar: ' . number_format($uangBayar, 0, ',', '.')];
            header('Location: index.php?page=transaksi');
            // $_SESSION['keranjang_temp'] = $keranjangArray; // Simpan keranjang
            exit;
        }

        // Hitung uang kembali
        $uangKembali = $uangBayar - $totalHargaServer;

        // Ambil user ID dari session
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
             // Seharusnya tidak terjadi jika sudah melewati cek login, tapi sebagai pengaman
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Sesi user tidak ditemukan. Silakan login ulang.'];
            header('Location: index.php?page=login');
            exit;
        }

        // Panggil model untuk membuat transaksi
         // Gunakan $itemsToProcess yang sudah divalidasi server
        $kodeTransaksi = $this->transaksiModel->create($userId, $itemsToProcess, $totalHargaServer, $uangBayar, $uangKembali);

        if ($kodeTransaksi) {
            // Transaksi sukses
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Transaksi berhasil disimpan dengan kode: ' . $kodeTransaksi];
            // Langsung redirect ke halaman cetak struk
            header('Location: index.php?page=transaksi&action=cetak_struk&kode=' . $kodeTransaksi);
            exit;
        } else {
            // Transaksi gagal (misal karena error saat update stok atau insert DB)
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Gagal memproses transaksi. Terjadi kesalahan pada database atau stok tidak konsisten.'];
            header('Location: index.php?page=transaksi');
            // $_SESSION['keranjang_temp'] = $keranjangArray; // Simpan keranjang
            exit;
        }
    }

    // Menampilkan struk dalam format PDF
    public function cetakStruk() {
        $kodeTransaksi = $_GET['kode'] ?? null;

        if (!$kodeTransaksi) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Kode transaksi tidak ditemukan untuk dicetak.'];
            header('Location: index.php?page=dashboard'); // atau halaman riwayat transaksi
            exit;
        }

        // Ambil data transaksi header
        $transaksiData = $this->transaksiModel->getByKode($kodeTransaksi);
        if (!$transaksiData) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Data transaksi dengan kode ' . htmlspecialchars($kodeTransaksi) . ' tidak ditemukan.'];
             header('Location: index.php?page=dashboard');
             exit;
        }

        // Ambil data detail transaksi
        $detailData = $this->transaksiModel->getDetailByTransaksiId($transaksiData['id']);
         // Tidak perlu cek $detailData kosong, karena transaksi header pasti punya detail jika create berhasil

         // Buat objek PDF
         try {
             $pdf = new PDFStruk(); // Pastikan class PDFStruk sudah di-require/autoload
             $pdf->generate($transaksiData, $detailData);
             // Method generate() di PDFStruk sudah melakukan Output() dan exit()
         } catch (Exception $e) {
              error_log("Error generating PDF: " . $e->getMessage());
              $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Gagal membuat file PDF struk.'];
              header('Location: index.php?page=dashboard'); // Kembali ke halaman aman
              exit;
         }
    }
}
?>
