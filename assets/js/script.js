document.addEventListener('DOMContentLoaded', function () {
    const daftarBarangTersedia = document.getElementById('daftar-barang-tersedia'); // Tabel barang di kiri
    const keranjangTableBody = document.getElementById('keranjang-tbody'); // tbody tabel keranjang
    const totalHargaElement = document.getElementById('total-harga');
    const uangBayarInput = document.getElementById('uang-bayar');
    const uangKembaliElement = document.getElementById('uang-kembali');
    const formTransaksi = document.getElementById('form-transaksi'); // Form utama
    const keranjangDataInput = document.getElementById('keranjang-data'); // Hidden input

    let keranjang = {}; // Objek untuk menyimpan item di keranjang { barangId: { id, nama, harga, jumlah, stokAsli } }
    let totalHarga = 0;

    // Format Rupiah Helper
    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }

    // Update Tampilan Keranjang
    function updateTampilanKeranjang() {
        if (!keranjangTableBody) return;
        keranjangTableBody.innerHTML = ''; // Kosongkan tabel
        totalHarga = 0;
        let no = 1;

        for (const barangId in keranjang) {
            if (keranjang.hasOwnProperty(barangId)) {
                const item = keranjang[barangId];
                const subtotal = item.harga * item.jumlah;
                totalHarga += subtotal;

                const row = document.createElement('tr');
                row.classList.add('border-b');
                row.innerHTML = `
                    <td class="py-2 px-3 text-center">${no++}</td>
                    <td class="py-2 px-3">${item.nama}</td>
                    <td class="py-2 px-3 text-right">${formatRupiah(item.harga)}</td>
                    <td class="py-2 px-3 text-center">
                        <button type="button" class="text-red-500 hover:text-red-700 kurangi-jumlah" data-id="${item.id}">-</button>
                        <span class="mx-2">${item.jumlah}</span>
                        <button type="button" class="text-green-500 hover:text-green-700 tambah-jumlah" data-id="${item.id}">+</button>
                    </td>
                    <td class="py-2 px-3 text-right">${formatRupiah(subtotal)}</td>
                    <td class="py-2 px-3 text-center">
                        <button type="button" class="text-red-600 hover:text-red-800 hapus-item" data-id="${item.id}">Hapus</button>
                    </td>
                `;
                keranjangTableBody.appendChild(row);
            }
        }

        // Update total harga
        if (totalHargaElement) {
            totalHargaElement.textContent = formatRupiah(totalHarga);
        }
         // Update hidden input untuk form submission
        if(keranjangDataInput) {
            keranjangDataInput.value = JSON.stringify(Object.values(keranjang)); // Kirim sbg array of objects
        }
        updateUangKembali(); // Hitung ulang kembalian setiap keranjang berubah
    }

    // Update Uang Kembali
    function updateUangKembali() {
        if (!uangBayarInput || !uangKembaliElement) return;
        const bayar = parseFloat(uangBayarInput.value.replace(/[^0-9]/g, '')) || 0; // Hapus non-digit
        const kembali = bayar - totalHarga;

        if (kembali >= 0) {
            uangKembaliElement.textContent = formatRupiah(kembali);
            uangKembaliElement.classList.remove('text-red-500');
            uangKembaliElement.classList.add('text-green-600');
        } else {
            uangKembaliElement.textContent = '-' + formatRupiah(Math.abs(kembali));
            uangKembaliElement.classList.remove('text-green-600');
            uangKembaliElement.classList.add('text-red-500');
        }
    }

    // Event Listener untuk tombol "Tambah ke Keranjang"
    if (daftarBarangTersedia) {
        daftarBarangTersedia.addEventListener('click', function (e) {
            if (e.target.classList.contains('tambah-keranjang')) {
                const button = e.target;
                const barangId = button.dataset.id;
                const namaBarang = button.dataset.nama;
                const hargaBarang = parseFloat(button.dataset.harga);
                const stokBarang = parseInt(button.dataset.stok);

                if (stokBarang <= 0) {
                     alert(`Stok ${namaBarang} habis!`);
                    return;
                }

                // Cek apakah sudah ada di keranjang
                if (keranjang[barangId]) {
                    // Cek apakah penambahan melebihi stok
                    if (keranjang[barangId].jumlah < stokBarang) {
                        keranjang[barangId].jumlah++;
                    } else {
                        alert(`Stok ${namaBarang} tidak mencukupi (maksimal ${stokBarang})`);
                    }
                } else {
                    // Tambah baru ke keranjang
                    keranjang[barangId] = {
                        id: barangId,
                        nama: namaBarang,
                        harga: hargaBarang,
                        jumlah: 1,
                        stokAsli: stokBarang // Simpan stok asli jika perlu validasi lbh lanjut
                    };
                }
                updateTampilanKeranjang();
            }
        });
    }

    // Event Listener untuk tombol di dalam keranjang (+, -, Hapus)
    if (keranjangTableBody) {
        keranjangTableBody.addEventListener('click', function (e) {
            const target = e.target;
            const barangId = target.dataset.id;

            if (!barangId || !keranjang[barangId]) return; // Pastikan ID valid dan ada di keranjang

             const item = keranjang[barangId];

            if (target.classList.contains('tambah-jumlah')) {
                 // Cek stok sebelum menambah
                 if (item.jumlah < item.stokAsli) {
                    item.jumlah++;
                } else {
                     alert(`Stok ${item.nama} tidak mencukupi (maksimal ${item.stokAsli})`);
                }
            } else if (target.classList.contains('kurangi-jumlah')) {
                item.jumlah--;
                if (item.jumlah <= 0) {
                    // Jika jumlah jadi 0 atau kurang, hapus item
                    delete keranjang[barangId];
                }
            } else if (target.classList.contains('hapus-item')) {
                // Konfirmasi sebelum hapus
                if (confirm(`Yakin ingin menghapus ${item.nama} dari keranjang?`)) {
                    delete keranjang[barangId];
                }
            }
            updateTampilanKeranjang();
        });
    }

    // Event Listener untuk input uang bayar
    if (uangBayarInput) {
        uangBayarInput.addEventListener('input', function() {
             // Format saat input (opsional, bisa membuat UX agak aneh)
             // let value = this.value.replace(/[^0-9]/g, '');
             // this.value = formatRupiah(value).replace('Rp', '').trim(); // Format tanpa Rp
             updateUangKembali();
        });
    }

     // Event Listener untuk submit form transaksi
     if (formTransaksi) {
         formTransaksi.addEventListener('submit', function(e) {
             // Validasi sebelum submit
             if (Object.keys(keranjang).length === 0) {
                 alert('Keranjang masih kosong!');
                 e.preventDefault(); // Batalkan submit
                 return;
             }

             const bayar = parseFloat(uangBayarInput.value.replace(/[^0-9]/g, '')) || 0;
             if (bayar < totalHarga) {
                  alert('Uang pembayaran kurang!');
                  e.preventDefault(); // Batalkan submit
                  uangBayarInput.focus();
                  return;
             }

             // Konfirmasi sebelum proses
             if (!confirm('Proses transaksi ini? Pastikan data sudah benar.')) {
                 e.preventDefault();
                 return;
             }

             // Biarkan form submit secara normal
             // Data keranjang sudah ada di hidden input 'keranjang-data'
              // Tampilkan loading indicator (opsional)
            const submitButton = formTransaksi.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Memproses...';
            }
         });
     }

    // Inisialisasi tampilan keranjang saat halaman dimuat (jika ada data lama di session/storage, bisa dimuat di sini)
    updateTampilanKeranjang();

});
