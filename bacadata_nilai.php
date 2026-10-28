
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db5d";

// Koneksi ke database
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Periksa koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil data mahasiswa
$sql = "SELECT * FROM mahasiswa ORDER BY nim ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Nilai Mahasiswa</title>
</head>
<body>

    <h2>Data Nilai Mahasiswa</h2>

    <a href="form_addnilai.php">
        <button type="button" style="padding: 8px 16px; margin-bottom: 12px; cursor: pointer;">Add Mahasiswa</button>
    </a>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama Lengkap</th>
                <th>Nilai Akhir</th>
                <th>Nilai Huruf</th>
                <th>Predikat</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php
            if ($result && mysqli_num_rows($result) > 0) {
                $no = 1;

                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";

                    echo "<td>" . $no++ . "</td>";
                    echo "<td>" . htmlspecialchars($row['nama_lengkap'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nilai_akhir'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nilai_huruf'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['predikat'] ?? '') . "</td>";

                    echo "<td>
                            <a href='edit_nilai.php?nim=" . urlencode($row['nim']) . "'>Edit</a>
                            |
                            <a href='hapus_nilai.php?nim=" . urlencode($row['nim']) . "'
                               onclick=\"return confirm('Apakah Anda yakin ingin menghapus data ini?')\">
                               Delete
                            </a>
                          </td>";

                    echo "</tr>";
                }
            } else {
                echo "<tr>
                        <td colspan='6'>Belum ada data mahasiswa.</td>
                      </tr>";
            }

            mysqli_close($conn);
            ?>
        </tbody>
    </table>

</body>
</html>
