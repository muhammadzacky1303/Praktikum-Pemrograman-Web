
<?php
require_once __DIR__ . "/koneksi.php";

// Konversi nilai
function konversiNilai($nilai) {
    if ($nilai >= 80.51) {
        return ["A", "Sangat Memuaskan"];
    } elseif ($nilai >= 65.51) {
        return ["B", "Memuaskan"];
    } elseif ($nilai >= 50.51) {
        return ["C", "Cukup Memuaskan"];
    } elseif ($nilai >= 34.51) {
        return ["D", "Kurang"];
    } else {
        return ["E", "Sangat Kurang"];
    }
}

$pesan = "";
$row = null;

// Proses Update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nim = trim($_POST["nim"] ?? "");
    $namalengkap = trim($_POST["nama_lengkap"] ?? "");
    $nilaiakhir = $_POST["nilai_akhir"] ?? "";

    if (
        $nim === "" ||
        $namalengkap === "" ||
        !is_numeric($nilaiakhir) ||
        $nilaiakhir < 0 ||
        $nilaiakhir > 100
    ) {
        $pesan = "Data tidak valid.";
    } else {
        $nilaiakhir = (float) $nilaiakhir;
        [$nilaihuruf, $predikat] = konversiNilai($nilaiakhir);

        $sql = "UPDATE mahasiswa
                SET nama_lengkap = ?,
                    nilai_akhir = ?,
                    nilai_huruf = ?,
                    predikat = ?
                WHERE nim = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "sdsss",
                $namalengkap,
                $nilaiakhir,
                $nilaihuruf,
                $predikat,
                $nim
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                mysqli_close($conn);

                echo "<script>
                    alert('Data nilai berhasil diperbarui!');
                    window.location.href = 'bacadata_nilai.php';
                </script>";
                exit();
            }

            $pesan = "Gagal memperbarui data: " .
                     mysqli_stmt_error($stmt);

            mysqli_stmt_close($stmt);
        } else {
            $pesan = "Gagal menyiapkan query.";
        }
    }

    // Menyiapkan kembali data untuk form jika validasi gagal
    $row = [
        "nim" => $nim,
        "nama_lengkap" => $namalengkap,
        "nilai_akhir" => $nilaiakhir
    ];

} else {
    // Mengambil data yang akan diedit
    $nim = trim($_GET["nim"] ?? "");

    if ($nim === "") {
        mysqli_close($conn);
        die("NIM tidak ditemukan.");
    }

    $sql = "SELECT nim, nama_lengkap, nilai_akhir
            FROM mahasiswa
            WHERE nim = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        die("Gagal menyiapkan query.");
    }

    mysqli_stmt_bind_param($stmt, "s", $nim);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$row) {
        mysqli_close($conn);
        die("Data mahasiswa tidak ditemukan.");
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Nilai Mahasiswa</title>
</head>
<body>

    <h2>Edit Nilai Mahasiswa</h2>

    <?php if ($pesan !== ""): ?>
        <p><?= htmlspecialchars($pesan) ?></p>
    <?php endif; ?>

    <?php if ($row !== null): ?>
        <form action="edit_nilai.php" method="POST">

            <label>NIM:</label><br>
            <input type="text"
                   name="nim"
                   value="<?= htmlspecialchars($row['nim']) ?>"
                   readonly>
            <br><br>

            <label>Nama Lengkap:</label><br>
            <input type="text"
                   name="nama_lengkap"
                   value="<?= htmlspecialchars($row['nama_lengkap']) ?>"
                   required>
            <br><br>

            <label>Nilai Akhir:</label><br>
            <input type="number"
                   name="nilai_akhir"
                   value="<?= htmlspecialchars((string) $row['nilai_akhir']) ?>"
                   step="0.01"
                   min="0"
                   max="100"
                   required>
            <br><br>

            <button type="submit">Update</button>
            <a href="bacadata_nilai.php">
                <button type="button">Cancel</button>
            </a>

        </form>
    <?php endif; ?>

</body>
</html>
