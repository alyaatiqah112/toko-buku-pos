<?php
// Mulai session di awal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Autoload sederhana (opsional, tapi membantu)
spl_autoload_register(function ($class_name) {
    $paths = [
        __DIR__ . '/models/',
        __DIR__ . '/controllers/',
        __DIR__ . '/config/'
    ];
    foreach ($paths as $path) {
        $file = $path . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
     // Jika butuh PDFStruk, load manual krn beda folder/konvensi nama
     if ($class_name === 'PDFStruk') {
         $pdfFile = __DIR__ . '/models/PDFStruk.php';
         if (file_exists($pdfFile)) require_once $pdfFile;
     }
});

// Inisialisasi Database
$db = new Database();

// --- Routing Sederhana ---
$page = $_GET['page'] ?? 'login'; // Halaman default login
$action = $_GET['action'] ?? null;

// Cek apakah perlu login
$auth = new Auth($db);
$halamanPublic = ['login', 'proses_login']; // Halaman yang bisa diakses tanpa login

// Jika mencoba akses halaman non-publik tanpa login, redirect ke login
if (!$auth->isLoggedIn() && !in_array($page, $halamanPublic) && $action !== 'login') {
    header('Location: index.php?page=login');
    exit;
}
// Jika sudah login dan mencoba akses halaman login, redirect ke dashboard
if ($auth->isLoggedIn() && $page === 'login') {
    header('Location: index.php?page=dashboard');
    exit;
}


// --- Bagian Controller Logic ---
// (Biasanya dipisah ke file Controller, tapi untuk sederhana bisa di sini)

// Autentikasi
if ($page === 'login' && $action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController = new AuthController($db);
    $authController->prosesLogin(); // Method ini akan handle redirect
} elseif ($page === 'logout') {
     $authController = new AuthController($db);
     $authController->logout(); // Method ini akan handle redirect
}
// Manajemen Barang
elseif ($page === 'barang' && $action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
     $barangController = new BarangController($db);
     $barangController->store(); // Akan redirect setelah proses
} elseif ($page === 'barang' && $action === 'delete' && isset($_GET['id'])) {
     $barangController = new BarangController($db);
     $barangController->delete(); // Akan redirect setelah proses
}
// Transaksi
elseif ($page === 'transaksi' && $action === 'process' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaksiController = new TransaksiController($db);
    $transaksiController->processTransaction(); // Handle transaksi dan redirect/PDF
}
elseif ($page === 'transaksi' && $action === 'cetak_struk' && isset($_GET['kode'])) {
     $transaksiController = new TransaksiController($db);
     $transaksiController->cetakStruk(); // Handle generate PDF
}


// --- Menampilkan View ---
// Include header (kecuali jika hanya proses logic tanpa view)
$tampilkanHeaderFooter = !in_array($action, ['login', 'logout', 'store', 'delete', 'process', 'cetak_struk']) || $page === 'login'; // Atur kondisi kapan header/footer tampil
if ($tampilkanHeaderFooter) {
    include __DIR__ . '/views/layouts/header.php';
}

// Tentukan view yang akan ditampilkan berdasarkan $page
switch ($page) {
    case 'login':
        include __DIR__ . '/views/auth/login.php';
        break;
    case 'dashboard':
        include __DIR__ . '/views/dashboard.php';
        break;
    case 'barang':
        // Logika untuk menampilkan form tambah atau daftar barang
        $barangController = new BarangController($db);
        if ($action === 'create') {
             $barangController->showForm(); // Method untuk menampilkan form
        } else {
             $barangController->index(); // Method untuk menampilkan daftar
        }
        break;
    case 'transaksi':
         // Hanya ada 1 halaman transaksi utama (POS)
        $transaksiController = new TransaksiController($db);
        $transaksiController->index(); // Method untuk menampilkan halaman POS
        break;
    // Tambahkan case lain jika ada halaman lain
    default:
        // Jika halaman tidak ditemukan, tampilkan dashboard atau pesan error
        if ($auth->isLoggedIn()) {
            include __DIR__ . '/views/dashboard.php';
        } else {
             include __DIR__ . '/views/auth/login.php'; // atau halaman 404
        }
        break;
}

// Include footer (jika perlu)
if ($tampilkanHeaderFooter) {
    include __DIR__ . '/views/layouts/footer.php';
}

// Tutup koneksi DB jika diperlukan (opsional karena PHP biasanya menutup otomatis)
// $db->close();

?>
