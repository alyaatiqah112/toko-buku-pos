<?php
// Pastikan file fpdf.php ada di dalam lib/fpdf/
require_once __DIR__ . '/../lib/fpdf/fpdf.php';

class PDFStruk extends FPDF { // Bisa juga dibuat wrapper, tidak harus extends

    private $namaToko = "Toko Buku Pintar";
    private $alamatToko = "Jl. Cendekia No. 123, Jakarta";
    private $teleponToko = "021-555-1234";

    // Override Header (jika diperlukan)
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, $this->namaToko, 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, $this->alamatToko, 0, 1, 'C');
        $this->Cell(0, 5, $this->teleponToko, 0, 1, 'C');
        $this->Ln(5); // Line break
        // Garis pemisah
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(2);
    }

    // Override Footer (jika diperlukan)
    function Footer() {
        $this->SetY(-20); // Posisi 2 cm dari bawah
         $this->Line(10, $this->GetY(), 200, $this->GetY());
         $this->Ln(2);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Terima kasih telah berbelanja!', 0, 0, 'C');
         $this->Ln(4);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Fungsi utama untuk generate struk
    public function generate($transaksiData, $detailData) {
        $this->AliasNbPages(); // Agar {nb} di footer berfungsi
        $this->AddPage();
        $this->SetFont('Arial', '', 10);

        // Informasi Transaksi
        $this->Cell(30, 6, 'No. Transaksi', 0, 0);
        $this->Cell(5, 6, ':', 0, 0);
        $this->Cell(0, 6, $transaksiData['kode_transaksi'], 0, 1);

        $this->Cell(30, 6, 'Tanggal', 0, 0);
        $this->Cell(5, 6, ':', 0, 0);
        $this->Cell(0, 6, date('d-m-Y H:i:s', strtotime($transaksiData['tanggal_transaksi'])), 0, 1);

        $this->Cell(30, 6, 'Kasir', 0, 0);
        $this->Cell(5, 6, ':', 0, 0);
        $this->Cell(0, 6, $transaksiData['nama_kasir'], 0, 1); // Ambil dari join di getByKode
        $this->Ln(5);

        // Header Tabel Detail Barang
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(10, 7, 'No', 1, 0, 'C');
        $this->Cell(80, 7, 'Nama Barang', 1, 0, 'C');
        $this->Cell(25, 7, 'Harga', 1, 0, 'C');
        $this->Cell(15, 7, 'Jml', 1, 0, 'C');
        $this->Cell(30, 7, 'Subtotal', 1, 1, 'C'); // Pindah baris

        // Isi Tabel Detail Barang
        $this->SetFont('Arial', '', 10);
        $no = 1;
        $totalItem = 0;
        foreach ($detailData as $item) {
            $this->Cell(10, 6, $no++, 1, 0, 'C');
            $this->Cell(80, 6, $item['nama_barang'], 1, 0); // Nama barang dari join
            $this->Cell(25, 6, 'Rp ' . number_format($item['harga_saat_transaksi'], 0, ',', '.'), 1, 0, 'R');
            $this->Cell(15, 6, $item['jumlah'], 1, 0, 'C');
            $this->Cell(30, 6, 'Rp ' . number_format($item['subtotal'], 0, ',', '.'), 1, 1, 'R'); // Pindah baris
             $totalItem += $item['jumlah'];
        }
         // Garis bawah tabel
         //$this->Line(10, $this->GetY(), 200, $this->GetY());
         $this->Ln(2);


        // Ringkasan Total
        $this->SetFont('Arial', 'B', 10);
        $lebarKanan = 30 + 15; // Lebar kolom Jumlah + Subtotal
        $lebarKiri = 190 - $lebarKanan; // 210 (lebar kertas A4) - 10 (margin kiri) - 10 (margin kanan) = 190

        $this->Cell($lebarKiri, 7, 'Total Item: ' . $totalItem, 0, 0, 'R');
        $this->Cell($lebarKanan, 7,'', 0, 1); // Spacer

        $this->Cell($lebarKiri, 7, 'Total Belanja:', 0, 0, 'R');
        $this->Cell($lebarKanan, 7, 'Rp ' . number_format($transaksiData['total_harga'], 0, ',', '.'), 0, 1, 'R');

        $this->Cell($lebarKiri, 7, 'Tunai:', 0, 0, 'R');
        $this->Cell($lebarKanan, 7, 'Rp ' . number_format($transaksiData['uang_bayar'], 0, ',', '.'), 0, 1, 'R');

        $this->Cell($lebarKiri, 7, 'Kembali:', 0, 0, 'R');
        $this->Cell($lebarKanan, 7, 'Rp ' . number_format($transaksiData['uang_kembali'], 0, ',', '.'), 0, 1, 'R');


        // Output PDF ke browser (paksa download)
        $namaFile = 'Struk-' . $transaksiData['kode_transaksi'] . '.pdf';
        $this->Output('D', $namaFile);
         exit; // Penting untuk menghentikan eksekusi script PHP setelah output PDF
    }
}
?>
