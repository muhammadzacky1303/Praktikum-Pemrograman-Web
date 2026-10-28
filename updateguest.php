
<?php
require_once __DIR__ . "/koneksi.php";

// Ambil data dari form
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');

// Periksa data
if ($id < 1) {
    die("ID tidak valid. Silakan kembali dan ulangi proses edit.");
}

if ($firstname === '' || $lastname === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Data tidak lengkap atau format email tidak valid.");
}

// Update data
$sql = "UPDATE MyGuests
        SET firstname = ?, lastname = ?, email = ?
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query gagal: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "sssi",
    $firstname,
    $lastname,
    $email,
    $id
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: bacadata2.php");
    exit;
} else {
    echo "Gagal memperbarui data: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
