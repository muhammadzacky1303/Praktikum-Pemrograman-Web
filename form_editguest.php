
<?php
require_once __DIR__ . "/koneksi.php";

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Periksa ID
if ($id < 1) {
    die("ID tidak valid. Silakan kembali dan klik tombol Edit.");
}

// Ambil data tamu berdasarkan ID
$sql = "SELECT firstname, lastname, email FROM MyGuests WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Query gagal: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Periksa apakah data ditemukan
if (!$data) {
    die("Data tamu tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit My Guest</title>
</head>
<body>

    <h2>Edit My Guest</h2>

    <form action="updateguest.php" method="POST">

        <input type="hidden" name="id" value="<?= $id ?>">

        <label for="firstname">First Name:</label><br>
        <input
            type="text"
            id="firstname"
            name="firstname"
            value="<?= htmlspecialchars($data['firstname'], ENT_QUOTES, 'UTF-8') ?>"
            required
        >
        <br><br>

        <label for="lastname">Last Name:</label><br>
        <input
            type="text"
            id="lastname"
            name="lastname"
            value="<?= htmlspecialchars($data['lastname'], ENT_QUOTES, 'UTF-8') ?>"
            required
        >
        <br><br>

        <label for="email">Email:</label><br>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($data['email'], ENT_QUOTES, 'UTF-8') ?>"
            required
        >
        <br><br>

        <button type="submit">Update Data</button>
        <a href="bacadata2.php">
            <button type="button">Batal</button>
        </a>

    </form>

</body>
</html>
