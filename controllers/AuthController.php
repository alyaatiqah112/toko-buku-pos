<?php
class AuthController {
    private $db;
    private $authModel;

    public function __construct(Database $db) {
        $this->db = $db;
        $this->authModel = new Auth($db);
         // Tidak perlu cek login di constructor controller ini
         // karena controller ini justru menangani proses login/logout
    }

     // Menampilkan halaman login
    public function showLoginForm() {
         // Jika sudah login, redirect ke dashboard
         if ($this->authModel->isLoggedIn()) {
             header('Location: index.php?page=dashboard');
             exit;
         }
        include __DIR__ . '/../views/auth/login.php';
    }

    // Memproses data login dari form
    public function prosesLogin() {
        // Pastikan ini adalah request POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=login');
            exit;
        }

        // Ambil data dari form
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? ''; // Password tidak perlu di-trim

        // Validasi sederhana (bisa ditambahkan lebih kompleks)
        if (empty($username) || empty($password)) {
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Username dan Password wajib diisi.'];
             header('Location: index.php?page=login');
             exit;
        }

        // Coba login menggunakan model Auth
        if ($this->authModel->login($username, $password)) {
            // Login sukses, redirect ke dashboard
            header('Location: index.php?page=dashboard');
            exit;
        } else {
            // Login gagal
             $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Username atau Password salah.'];
             header('Location: index.php?page=login');
             exit;
        }
    }

    // Proses logout
    public function logout() {
        $this->authModel->logout();
        // Redirect ke halaman login setelah logout
        header('Location: index.php?page=login');
        exit;
    }
}
?>
