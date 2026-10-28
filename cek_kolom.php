<?php
require_once __DIR__ . "/koneksi.php";
$res = mysqli_query($conn, "DESCRIBE mahasiswa");
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " | " . $row['Type'] . " | " . $row['Null'] . " | " . $row['Default'] . "\n";
}
mysqli_close($conn);
