<?php
// File ini TIDAK BOLEH include header/footer yang ada navigasi
// Biasanya halaman login berdiri sendiri
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Buku POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm mx-auto">
        <h2 class="text-2xl font-bold mb-6 text-center text-indigo-600">Login Kasir</h2>

        <?php
        // Tampilkan flash message jika ada (khusus halaman login)
        if (isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            $bgColor = ($flash['type'] === 'success') ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
            echo '<div class="' . $bgColor . ' border px-4 py-3 rounded relative mb-4 text-sm" role="alert">';
            echo '<span class="block sm:inline">' . $flash['message'] . '</span>';
            echo '</div>';
            unset($_SESSION['flash_message']); // Hapus pesan setelah ditampilkan
        }
        ?>

        <form method="POST" action="index.php?page=login&action=login">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" id="username" name="username" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="Masukkan username">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" id="password" name="password" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="Masukkan password">
            </div>
            <div class="flex items-center justify-between">
                <button type="submit"
                        class="w-full bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Login
                </button>
            </div>
        </form>
        <p class="text-center text-gray-500 text-xs mt-6">
            ©<?= date('Y') ?> Toko Buku POS.
        </p>
    </div>

</body>
</html>
