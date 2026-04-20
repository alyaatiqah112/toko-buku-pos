<?php
// File ini di-include oleh index.php
// Variable $barang bisa null (untuk create) atau berisi data (jika edit)
// Variable $_SESSION['old_input'] mungkin ada jika terjadi error validasi

$isEdit = isset($barang) && $barang !== null && isset($barang['id']);
$formAction = 'index.php?page=barang&action=store'; // Default action untuk create
$pageTitle = 'Tambah Barang Baru';
$buttonText = 'Simpan Barang';

// Jika ini mode edit (belum diimplementasikan di controller store sepenuhnya, tapi view siap)
// if ($isEdit) {
//     $formAction = 'index.php?page=barang&action=update'; // Action untuk update
//     $pageTitle = 'Edit Barang';
//     $buttonText = 'Update Barang';
// }

// Ambil data lama jika ada error validasi
$oldInput = $_SESSION['old_input'] ?? [];
$namaValue = htmlspecialchars($barang['nama_barang'] ?? $oldInput['nama_barang'] ?? '');
$hargaValue = htmlspecialchars($barang['harga'] ?? $oldInput['harga'] ?? '');
$stokValue = htmlspecialchars($barang['stok'] ?? $oldInput['stok'] ?? '');

?>

<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6"><?= $pageTitle ?></h1>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form method="POST" action="<?= $formAction ?>">
            <?php /* Jika mode edit, sertakan ID tersembunyi
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($barang['id']); ?>">
            <?php endif; ?>
            */ ?>

            <div class="mb-4">
                <label for="nama_barang" class="block text-gray-700 text-sm font-bold mb-2">Nama Barang</label>
                <input type="text" id="nama_barang" name="nama_barang" required value="<?= $namaValue ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="Masukkan nama barang">
            </div>

            <div class="mb-4">
                <label for="harga" class="block text-gray-700 text-sm font-bold mb-2">Harga (Rp)</label>
                <input type="number" id="harga" name="harga" required min="0" step="500" value="<?= $hargaValue ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="Masukkan harga barang">
            </div>

            <div class="mb-6">
                <label for="stok" class="block text-gray-700 text-sm font-bold mb-2">Stok Awal</label>
                <input type="number" id="stok" name="stok" required min="0" step="1" value="<?= $stokValue ?>"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="Masukkan jumlah stok awal">
            </div>

            <div class="flex items-center justify-start space-x-4">
                <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    <?= $buttonText ?>
                </button>
                <a href="index.php?page=barang" class="text-gray-600 hover:text-gray-800 font-medium py-2 px-4">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
