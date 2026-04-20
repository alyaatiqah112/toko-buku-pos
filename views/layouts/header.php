<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Toko Buku</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles jika diperlukan */
        /* Contoh: Mengatur tinggi minimal halaman */
        body { display: flex; flex-direction: column; min-height: 100vh; }
        main { flex-grow: 1; }
        /* Sembunyikan panah di input number */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield; /* Firefox */
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">

    <?php
        // Cek apakah user sudah login untuk menampilkan navigasi
        $isLoggedIn = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'];
        if ($isLoggedIn):
            $namaUser = $_SESSION['nama_lengkap'] ?? 'User';
    ?>
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <span class="font-bold text-xl text-indigo-600">Toko Buku Berjalan</span>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="index.php?page=dashboard" class="text-gray-700 hover:bg-gray-200 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                            <a href="index.php?page=transaksi" class="text-gray-700 hover:bg-gray-200 px-3 py-2 rounded-md text-sm font-medium">Kasir</a>
                            <a href="index.php?page=barang" class="text-gray-700 hover:bg-gray-200 px-3 py-2 rounded-md text-sm font-medium">Manajemen Barang</a>
                            <!-- Tambah menu lain jika perlu -->
                        </div>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        <span class="text-gray-600 mr-3">Halo, <?= htmlspecialchars($namaUser) ?></span>
                        <a href="index.php?page=logout" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                    </div>
                </div>
                 <!-- Mobile menu button (implementasi JS jika perlu) -->
                 <div class="-mr-2 flex md:hidden">
                    <button type="button" class="bg-gray-800 inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <!-- Icon menu -->
                         <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                         <!-- Icon close -->
                         <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
         <!-- Mobile menu, show/hide based on menu state. -->
        <div class="md:hidden" id="mobile-menu" style="display: none;"> <!-- Awalnya disembunyikan -->
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="index.php?page=dashboard" class="text-gray-700 hover:bg-gray-200 block px-3 py-2 rounded-md text-base font-medium">Dashboard</a>
                <a href="index.php?page=transaksi" class="text-gray-700 hover:bg-gray-200 block px-3 py-2 rounded-md text-base font-medium">Kasir</a>
                <a href="index.php?page=barang" class="text-gray-700 hover:bg-gray-200 block px-3 py-2 rounded-md text-base font-medium">Manajemen Barang</a>
             </div>
             <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="flex items-center px-5">
                    <div class="ml-3">
                        <div class="text-base font-medium leading-none text-gray-800"><?= htmlspecialchars($namaUser) ?></div>
                    </div>
                </div>
                <div class="mt-3 px-2 space-y-1">
                     <a href="index.php?page=logout" class="block px-3 py-2 rounded-md text-base font-medium text-red-600 hover:bg-gray-100">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <main class="container mx-auto px-4 py-6 max-w-7xl">
        <?php
        // Tampilkan flash message jika ada
        if (isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            $bgColor = ($flash['type'] === 'success') ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
            echo '<div class="' . $bgColor . ' border px-4 py-3 rounded relative mb-4" role="alert">';
            echo '<span class="block sm:inline">' . $flash['message'] . '</span>';
            echo '</div>';
            unset($_SESSION['flash_message']); // Hapus pesan setelah ditampilkan
        }
        ?>
        <!-- Konten Halaman akan dimuat di sini -->
