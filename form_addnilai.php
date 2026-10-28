<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Tambah Nilai Mahasiswa</title>
</head>
<body>

    <form action="insernilai.php" method="POST">
        <h2>Tambah Nilai Mahasiswa</h2>

        <label for="nim">NIM</label><br>
        <input type="text" id="nim" name="nim" placeholder="Masukkan NIM" required>
        <br><br>

        <label for="nama_lengkap">Nama Lengkap</label><br>
        <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
        <br><br>

        <label for="nilai_akhir">Nilai Akhir</label><br>
        <input type="number" id="nilai_akhir" name="nilai_akhir" placeholder="Masukkan nilai akhir" required step="0.01" min="0" max="100">
        <br><br>

        <button type="submit">Simpan</button>
    </form>

</body>
</html>
