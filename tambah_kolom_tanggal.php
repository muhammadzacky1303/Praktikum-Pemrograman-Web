<?php
require_once __DIR__ . "/koneksi.php";

// Tambah kolom created_at jika belum ada
$check = mysqli_query($conn, "SHOW COLUMNS FROM mahasiswa LIKE 'created_at'");
if (mysqli_num_rows($check) == 0) {
    $alter = "ALTER TABLE mahasiswa ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    if (mysqli_query($conn, $alter)) {
        echo "Kolom 'created_at' berhasil ditambahkan!<br>";
        // Update data yang sudah ada agar punya timestamp
        mysqli_query($conn, "UPDATE mahasiswa SET created_at = NOW() WHERE created_at IS NULL");
        echo "Data existing sudah diupdate dengan timestamp sekarang.";
    } else {
        echo "Gagal: " . mysqli_error($conn);
    }
} else {
    echo "Kolom 'created_at' sudah ada.";
}

mysqli_close($conn);
