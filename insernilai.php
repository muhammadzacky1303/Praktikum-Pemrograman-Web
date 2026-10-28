<?php
require_once __DIR__ . "/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: form_addnilai.php");
    exit;
}

$nim = trim($_POST["nim"] ?? "");
$nama = trim($_POST["nama_lengkap"] ?? "");
$nilai = (float) ($_POST["nilai_akhir"] ?? 0);

if ($nim === "" || $nama === "" || $nilai < 0 || $nilai > 100) {
    die("Data tidak valid.");
}

if ($nilai >= 80.51) {
    [$huruf, $predikat] = ["A", "Sangat Memuaskan"];
} elseif ($nilai >= 65.51) {
    [$huruf, $predikat] = ["B", "Memuaskan"];
} elseif ($nilai >= 50.51) {
    [$huruf, $predikat] = ["C", "Cukup Memuaskan"];
} elseif ($nilai >= 34.51) {
    [$huruf, $predikat] = ["D", "Kurang"];
} else {
    [$huruf, $predikat] = ["E", "Sangat Kurang"];
}

$stmt = mysqli_prepare($conn, "INSERT INTO mahasiswa (nim, nama_lengkap, nilai_akhir, nilai_huruf, predikat) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Gagal menyiapkan query insert.");
}

mysqli_stmt_bind_param($stmt, "sssss", $nim, $nama, $nilai, $huruf, $predikat);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header("Location: bacadata_nilai.php");
    exit;
}

die("Gagal menyimpan data: " . mysqli_error($conn));
