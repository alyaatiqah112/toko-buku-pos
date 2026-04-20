<?php
class BarangController {
    private $db;
    private $barangModel;
    private $authModel;

    public function __construct(Database $db) {
        $this->db = $db;
        $this->barangModel = new Barang($db);
        $this->authModel = new Auth($db);

        // Semua aksi di controller ini memerlukan login
        if (!$this->authModel->isLoggedIn()) {
            // Simpan URL tujuan agar bisa kembali setelah login (opsional)
            // $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Anda harus login untuk mengakses halaman ini.'];
            header('Location: index.php?page=login');
            exit;
        }
    }

    // Menampilkan daftar barang
    public function index() {
        $dataBarang = $this->barangModel->getAll();
        // Load view dan passing data
        include __DIR__ . '/../views/barang/index.php';
    }

     // Menampilkan form tambah barang (action=create)
    public function showForm() {
         $barang = null; // Untuk form tambah, data barang kosong
        include __DIR__ . '/../views/barang/form.php';
    }


    // Menyimpan data barang baru atau update (jika ada id)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Ambil data dari POST
             // ID hanya ada jika ini proses edit, tapi untuk contoh ini kita fokus ke create
            $nama = trim($_POST['nama_barang'] ?? '');
            $harga = filter_input(INPUT_POST, 'harga', FILTER_VALIDATE_FLOAT);
            $stok = filter_input(INPUT_POST, 'stok', FILTER_VALIDATE_INT);

            // Validasi Sisi Server
            $errors = [];
            if (empty($nama)) {
                $errors[] = "Nama barang wajib diisi.";
            }
            if ($harga === false || $harga <= 0) {
                $errors[] = "Harga harus angka positif.";
            }
            if ($stok === false || $stok < 0) {
                $errors[] = "Stok harus angka non-negatif.";
            }
             // Bisa ditambahkan validasi lain seperti cek duplikasi nama barang, dll.

            if (empty($errors)) {
                // Tidak ada error validasi, coba simpan ke DB
                 if ($this->barangModel->create($nama, $harga, $stok)) {
                     $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Barang berhasil ditambahkan!'];
                     header('Location: index.php?page=barang');
                     exit;
                 } else {
                     // Gagal simpan ke DB (misal karena error SQL)
                     $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Gagal menyimpan barang ke database.'];
                 }
            } else {
                 // Ada error validasi
                 $_SESSION['flash_message'] = ['type' => 'error', 'message' => implode('<br>', $errors)];
                 // Simpan input lama untuk ditampilkan kembali di form
                 $_SESSION['old_input'] = $_POST;
            }

             // Jika ada error validasi atau error DB, kembali ke form tambah
             header('Location: index.php?page=barang&action=create');
             exit;

        } else {
             // Jika bukan POST, redirect ke halaman daftar barang
             header('Location: index.php?page=barang');
             exit;
        }
    }

    // Menghapus barang
    public function delete() {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($id) {
            // Optional: Cek dulu apakah barang terkait dengan transaksi?
            // Jika iya, mungkin tidak boleh dihapus atau hanya di-nonaktifkan.
            // Untuk contoh ini, kita langsung hapus.

            if ($this->barangModel->delete($id)) {
                 $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Barang berhasil dihapus!'];
            } else {
                 // Gagal hapus (mungkin ID tidak ada atau error DB)
                 $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Gagal menghapus barang. Barang mungkin tidak ditemukan atau terkait data lain.'];
            }
        } else {
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'ID Barang tidak valid untuk dihapus.'];
        }

        // Redirect kembali ke halaman daftar barang
         header('Location: index.php?page=barang');
         exit;
    }
}
?>
    