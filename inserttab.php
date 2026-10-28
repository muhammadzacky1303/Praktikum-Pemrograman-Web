<?php
require "koneksi.php";

$fname = "Nedina";
$lname = "Nurazida";
$email = "nedina@example.com";

// SQL untuk memasukkan data
$sql = "INSERT INTO MyGuests (firstname, lastname, email)
VALUES ('$fname', '$lname', '$email')";

if (mysqli_query($conn, $sql)) {
    echo "New record inserted successfully";
} else {
    echo "Error inserting record: " . mysqli_error($conn);
}

mysqli_close($conn);
?>