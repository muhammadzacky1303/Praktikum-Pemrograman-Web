<?php
require_once __DIR__ . "/koneksi.php";

$nim = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nim = trim($_POST["nim"] ?? "");
} else {
    $nim = trim($_GET["nim"] ?? "");
}

if ($nim === "") {
    header("Location: bacadata_nilai.php");
    exit();
}

$stmt = mysqli_prepare($conn, "DELETE FROM mahasiswa WHERE nim = ?");
if (!$stmt) {
    mysqli_close($conn);
    die("Gagal menyiapkan query penghapusan.");
}

mysqli_stmt_bind_param($stmt, "s", $nim);

if (mysqli_stmt_execute($stmt)) {
    $pesan = mysqli_stmt_affected_rows($stmt) > 0
        ? "Data nilai mahasiswa berhasil dihapus!"
        : "Data mahasiswa tidak ditemukan.";
} else {
    $pesan = "Gagal menghapus data: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

echo "<script>
    alert(" . json_encode($pesan) . ");
    window.location.href = 'bacadata_nilai.php';
</script>";
exit();
?>