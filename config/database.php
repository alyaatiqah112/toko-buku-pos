<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Ganti dengan user DB Anda
define('DB_PASS', '');     // Ganti dengan password DB Anda
define('DB_NAME', 'kasir_db');

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh; // Database Handler
    private $stmt;
    private $error;

    public function __construct() {
        // Buat koneksi menggunakan MySQLi
        $this->dbh = new mysqli($this->host, $this->user, $this->pass, $this->dbname);

        // Cek koneksi
        if ($this->dbh->connect_error) {
            // Sebaiknya log error atau tampilkan pesan yang lebih ramah
            die("Koneksi Gagal: " . $this->dbh->connect_error);
        }
    }

    // Mendapatkan instance koneksi MySQLi
    public function getConnection() {
        return $this->dbh;
    }

    // Method prepare statement (contoh sederhana, bisa dikembangkan)
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
        if ($this->stmt === false) {
            $this->error = "Prepare failed: (" . $this->dbh->errno . ") " . $this->dbh->error;
            // Handle error (log, throw exception, etc.)
            error_log($this->error); // Log error ke PHP error log
            return false;
        }
        return $this; // Mengembalikan instance untuk chaining method
    }

    // Binding parameter (contoh sederhana)
    public function bind($params = []) {
        if ($this->stmt && !empty($params)) {
            $types = str_repeat('s', count($params)); // Asumsi semua string, sesuaikan jika perlu (i, d, b)
            $this->stmt->bind_param($types, ...$params);
        }
        return $this;
    }

    // Eksekusi prepared statement
    public function execute() {
        $result = $this->stmt->execute();
        if ($result === false) {
            $this->error = "Execute failed: (" . $this->stmt->errno . ") " . $this->stmt->error;
             error_log($this->error);
        }
        return $result;
    }

    // Mengambil hasil sebagai array associative
    public function resultSet() {
        $this->execute();
        $result = $this->stmt->get_result();
        $rows = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        $this->stmt->close();
        return $rows;
    }

    // Mengambil satu baris hasil
    public function single() {
        $this->execute();
        $result = $this->stmt->get_result();
         $row = null;
        if ($result) {
             $row = $result->fetch_assoc();
        }
        $this->stmt->close();
        return $row;
    }

    // Mendapatkan jumlah baris yang terpengaruh
    public function rowCount() {
        return $this->stmt->affected_rows;
    }

     // Mendapatkan ID terakhir yang diinsert
    public function lastInsertId() {
        return $this->dbh->insert_id;
    }

    // Memulai transaksi
    public function beginTransaction() {
        return $this->dbh->begin_transaction();
    }

    // Commit transaksi
    public function commit() {
        return $this->dbh->commit();
    }

    // Rollback transaksi
    public function rollback() {
        return $this->dbh->rollback();
    }

    // Menutup koneksi (dipanggil di akhir script jika perlu)
    public function close() {
        if ($this->stmt) {
             $this->stmt->close();
        }
        if ($this->dbh) {
            $this->dbh->close();
        }
    }

     // Destructor untuk otomatis menutup koneksi saat objek tidak lagi digunakan
    public function __destruct() {
        // $this->close(); // Bisa ditambahkan jika ingin otomatis close
    }
}
?>
