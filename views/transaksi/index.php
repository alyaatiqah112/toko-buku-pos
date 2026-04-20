<?php
// File ini di-include oleh index.php
// Variable $dataBarangTersedia sudah di-passing dari TransaksiController::index()
?>

<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Kasir Toko Buku</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Daftar Barang Tersedia -->
        <div class="lg:col-span-1 bg-white shadow-md rounded-lg p-4 overflow-hidden">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Pilih Barang</h2>
            <!-- Tambahkan fitur search jika perlu -->
            <!-- <input type="text" id="search-barang" placeholder="Cari barang..." class="mb-4 w-full px-3 py-2 border rounded focus:outline-none focus:ring-1 focus:ring-indigo-500"> -->
            <div class="max-h-[60vh] overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200" id="daftar-barang-tersedia">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barang</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($dataBarangTersedia)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-gray-500">Tidak ada barang tersedia.</td></tr>
                        <?php else: ?>
                            <?php foreach ($dataBarangTersedia as $barang): ?>
                                <tr class="<?= $barang['stok'] <= 0 ? 'opacity-50' : '' ?>">
                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($barang['nama_barang']); ?></td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600 text-right"><?= number_format($barang['harga'], 0, ',', '.'); ?></td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600 text-center"><?= $barang['stok']; ?></td>
                                    <td class="px-4 py-2 whitespace-nowrap text-center text-sm font-medium">
                                        <button type="button"
                                                class="tambah-keranjang text-indigo-600 hover:text-indigo-900 disabled:opacity-50 disabled:cursor-not-allowed"
                                                data-id="<?= $barang['id']; ?>"
                                                data-nama="<?= htmlspecialchars($barang['nama_barang']); ?>"
                                                data-harga="<?= $barang['harga']; ?>"
                                                data-stok="<?= $barang['stok']; ?>"
                                                <?= $barang['stok'] <= 0 ? 'disabled' : '' ?>>
                                            Tambah
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kolom Kanan: Keranjang & Pembayaran -->
        <div class="lg:col-span-2 bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Keranjang Belanja</h2>

            <form id="form-transaksi" method="POST" action="index.php?page=transaksi&action=process">
                <div class="overflow-x-auto mb-6 max-h-[40vh] overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="keranjang-tbody">
                            <!-- Isi keranjang akan ditambahkan oleh JavaScript -->
                             <tr>
                                <td colspan="6" class="text-center py-4 text-gray-500 italic">Keranjang kosong</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Ringkasan dan Pembayaran -->
                <div class="border-t pt-6">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-lg font-medium text-gray-700">Total Belanja:</span>
                        <span class="text-xl font-bold text-indigo-600" id="total-harga">Rp 0</span>
                    </div>
                    <div class="mb-4">
                        <label for="uang-bayar" class="block text-sm font-medium text-gray-700 mb-1">Uang Bayar (Rp):</label>
                        <input type="number" id="uang-bayar" name="uang_bayar" min="0" step="100" required
                               class="shadow-sm appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-lg"
                               placeholder="0">
                    </div>
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-lg font-medium text-gray-700">Uang Kembali:</span>
                        <span class="text-xl font-bold text-green-600" id="uang-kembali">Rp 0</span>
                    </div>

                    <!-- Input tersembunyi untuk data keranjang -->
                    <input type="hidden" name="keranjang_data" id="keranjang-data" value="[]">

                    <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded focus:outline-none focus:shadow-outline text-lg inline-flex items-center justify-center disabled:opacity-50"
                            id="btn-proses-transaksi">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                        </svg>
                        Proses Transaksi & Cetak Struk
                    </button>
                </div>
            </form>
        </div> <!-- End Kolom Kanan -->

    </div> <!-- End Grid -->
</div>

<!-- Pastikan script.js sudah di-include di footer.php -->
