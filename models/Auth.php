<?php
class Auth
{
    private $db;
    private $conn;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->conn = $db->getConnection(); // Dapatkan koneksi mysqli
    }

    public function login($username, $password)
    {
        // Gunakan prepared statement untuk keamanan
        $sql = "SELECT id, username, password, nama_lengkap FROM users WHERE username = ?";
        if ($this->db->query($sql)) {
            $this->db->bind([$username]);
            $user = $this->db->single();

            if ($user) {
                // Verifikasi password
                if ($password === $user['password']) {
                    // Login sukses, simpan data user ke session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                    $_SESSION['is_logged_in'] = true;
                    return true;
                } else {
                    // Password salah
                    return false;
                }
            } else {
                // User tidak ditemukan
                return false;
            }
        } else {
            error_log("Auth Model: Gagal prepare statement login - " . $this->conn->error);
            return false; // Gagal query
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
    }

    public function getUser()
    {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'nama_lengkap' => $_SESSION['nama_lengkap']
            ];
        }
        return null;
    }

    // Fungsi untuk membuat user baru (jika diperlukan)
    public function register($username, $password, $nama_lengkap)
    {
        // Hash password sebelum disimpan
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, password, nama_lengkap) VALUES (?, ?, ?)";
        if ($this->db->query($sql)) {
            $this->db->bind([$username, $hashedPassword, $nama_lengkap]);
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            } else {
                error_log("Auth Model: Gagal execute register - " . $this->conn->error);
                return false;
            }
        } else {
            error_log("Auth Model: Gagal prepare statement register - " . $this->conn->error);
            return false;
        }
    }
    public function countUsers()
    {
        $sql = "SELECT COUNT(id) as count FROM users";
        if ($this->db->query($sql)) {
            $result = $this->db->single();
            return $result['count'] ?? 0;
        }
        return 0;
    }
}
