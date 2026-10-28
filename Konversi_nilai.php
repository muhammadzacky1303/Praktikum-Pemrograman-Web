<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Halaman ini harus diakses dari form input.";
    exit;
}

$nama_mahasiswa = trim((string) ($_POST['nama_mhs'] ?? ''));
$nilai_akhir = $_POST['nilai_akhir'] ?? null;

if ($nama_mahasiswa === '' || $nilai_akhir === null || $nilai_akhir === '' || !is_numeric($nilai_akhir)) {
    echo "Nama dan nilai tidak valid.";
    exit;
}

$nilai_akhir = (float) $nilai_akhir;
$nilai_huruf = "";
$predikat = "";

if ($nilai_akhir < 0 || $nilai_akhir > 100) {
    $nilai_huruf = "Invalid";
    $predikat = "Invalid Value";
} elseif ($nilai_akhir <= 34.50) {
    $nilai_huruf = "E";
    $predikat = "Tidak Lulus";
} elseif ($nilai_akhir <= 50.50) {
    $nilai_huruf = "D";
    $predikat = "Kurang Memuaskan";
} elseif ($nilai_akhir <= 65.50) {
    $nilai_huruf = "C";
    $predikat = "Cukup Memuaskan";
} elseif ($nilai_akhir <= 80.50) {
    $nilai_huruf = "B";
    $predikat = "Memuaskan";
} else {
    $nilai_huruf = "A";
    $predikat = "Sangat Memuaskan";
}

echo "<h2>Hasil Konversi Nilai</h2>";
echo "<p>Nama Mahasiswa: " . htmlspecialchars($nama_mahasiswa) . "</p>";
echo "<p>Nilai Akhir: " . number_format($nilai_akhir, 2) . "</p>";
echo "<p>Nilai Huruf: " . $nilai_huruf . "</p>";
echo "<p>Predikat: " . $predikat . "</p>";