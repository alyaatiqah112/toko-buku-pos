<?php
// File ini di-include oleh index.php setelah header.php

// Idealnya, data ini diambil di controller/router sebelum view dipanggil.
// Tapi untuk contoh, kita panggil model di sini (pastikan $db dan model tersedia)
$authModel = new Auth($db); // $db harus sudah diinisialisasi di index.php
$barangModel = new Barang($db);
$transaksiModel = new Transaksi($db, $barangModel); // Transaksi butuh BarangModel

// --- Ambil Data Statistik ---
$namaUser = htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Pengguna');

// Fungsi untuk mendapatkan jumlah produk (Tambahkan method ini di Barang.php jika belum ada)
if (method_exists($barangModel, 'countAll')) {
    $jumlahProduk = $barangModel->countAll();
} else {
    // Fallback jika method countAll tidak ada: hitung dari getAll
    $jumlahProduk = count($barangModel->getAll());
}


// Fungsi untuk mendapatkan jumlah transaksi hari ini (Tambahkan method ini di Transaksi.php)
if (method_exists($transaksiModel, 'countToday')) {
     // Anda mungkin perlu mengirim user_id jika ingin statistik per kasir
    $transaksiHariIni = $transaksiModel->countToday();
} else {
    $transaksiHariIni = 0; // Default jika method tidak ada
}


// Fungsi untuk mendapatkan total penjualan hari ini (Tambahkan method ini di Transaksi.php)
if (method_exists($transaksiModel, 'sumSalesToday')) {
     // Anda mungkin perlu mengirim user_id jika ingin statistik per kasir
    $totalPenjualanHariIni = $transaksiModel->sumSalesToday();
} else {
    $totalPenjualanHariIni = 0; // Default jika method tidak ada
}

// Fungsi untuk mendapatkan jumlah pengguna (Tambahkan method ini di Auth.php)
if (method_exists($authModel, 'countUsers')) {
    $jumlahUsers = $authModel->countUsers();
} else {
     $jumlahUsers = 1; // Default jika method tidak ada
}


// Helper function for formatting currency
function formatRP($number) {
    return 'Rp ' . number_format($number ?? 0, 0, ',', '.');
}

?>

<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-semibold text-gray-800 mb-6">Selamat Datang, <?= $namaUser ?>!</h1>

    <!-- Statistik Ringkas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <!-- Card Jumlah Produk -->
        <div class="bg-white shadow-md rounded-lg p-6 flex items-center space-x-4 hover:shadow-lg transition-shadow duration-200">
            <div class="bg-blue-100 p-3 rounded-full">
                <!-- Heroicon: cube -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Jumlah Jenis Produk</p>
                <p class="text-2xl font-bold text-gray-800"><?= number_format($jumlahProduk) ?></p>
            </div>
        </div>

        <!-- Card Transaksi Hari Ini -->
        <div class="bg-white shadow-md rounded-lg p-6 flex items-center space-x-4 hover:shadow-lg transition-shadow duration-200">
            <div class="bg-green-100 p-3 rounded-full">
                 <!-- Heroicon: collection -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
             <div>
                <p class="text-sm text-gray-500">Transaksi Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800"><?= number_format($transaksiHariIni) ?></p>
            </div>
        </div>

         <!-- Card Total Penjualan Hari Ini -->
        <div class="bg-white shadow-md rounded-lg p-6 flex items-center space-x-4 hover:shadow-lg transition-shadow duration-200">
             <div class="bg-yellow-100 p-3 rounded-full">
                 <!-- Heroicon: currency-dollar -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0c1.657 0 3-.895 3-2s-1.343-2-3-2m0 8c-1.11 0-2.08-.402-2.599-1M12 16v1m-6-3H4a1 1 0 00-1 1v3a1 1 0 001 1h16a1 1 0 001-1v-3a1 1 0 00-1-1h-2" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500">Penjualan Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800"><?= formatRP($totalPenjualanHariIni) ?></p>
            </div>
        </div>

        <!-- Card Jumlah User -->
         <div class="bg-white shadow-md rounded-lg p-6 flex items-center space-x-4 hover:shadow-lg transition-shadow duration-200">
            <div class="bg-purple-100 p-3 rounded-full">
                <!-- Heroicon: users -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
             </div>
            <div>
                <p class="text-sm text-gray-500">Jumlah Pengguna</p>
                <p class="text-2xl font-bold text-gray-800"><?= number_format($jumlahUsers) ?></p>
            </div>
        </div>
    </div>

    <!-- Aksi Cepat -->
    <div class="mb-8">
         <h2 class="text-xl font-semibold text-gray-700 mb-4">Aksi Cepat</h2>
         <div class="flex flex-wrap gap-4">
             <a href="index.php?page=transaksi" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded inline-flex items-center transition duration-150 ease-in-out transform hover:scale-105 shadow-md">
                 <!-- Heroicon: shopping-cart -->
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                     <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                 </svg>
                 Mulai Transaksi Baru
             </a>
             <a href="index.php?page=barang" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded inline-flex items-center transition duration-150 ease-in-out transform hover:scale-105 shadow-md">
                 <!-- Heroicon: archive -->
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" />
                    <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd" />
                 </svg>
                Lihat & Kelola Barang
             </a>
             <!-- Tambahkan link lain jika perlu, misal ke Laporan -->
         </div>
    </div>

    <!-- Bisa ditambahkan bagian lain seperti Grafik (butuh library chart JS) atau tabel aktivitas terbaru -->
    <!-- Contoh Placeholder untuk Aktivitas Terbaru -->
    <!--
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
         <h2 class="text-xl font-semibold text-gray-700 p-4 border-b">Aktivitas Terbaru (Contoh)</h2>
         <p class="p-4 text-gray-500 italic">
            Tabel transaksi terakhir atau notifikasi stok menipis bisa ditampilkan di sini.
            Membutuhkan query tambahan pada model Transaksi dan Barang.
         </p>
         </div>
    -->

</div>

<?php
// Tambahkan method-method yang dipanggil di atas ke dalam Model yang sesuai:
/*
// --- Tambahkan ke models/Barang.php ---
public function countAll() {
    $sql = "SELECT COUNT(id) as count FROM barang";
    if ($this->db->query($sql)) {
        $result = $this->db->single();
        return $result['count'] ?? 0;
    }
    return 0;
}

// --- Tambahkan ke models/Transaksi.php ---
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


// --- Tambahkan ke models/Auth.php ---
public function countUsers() {
    $sql = "SELECT COUNT(id) as count FROM users";
     if ($this->db->query($sql)) {
        $result = $this->db->single();
        return $result['count'] ?? 0;
    }
    return 0;
}

*/
?>
