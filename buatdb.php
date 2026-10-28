<?php
$servername = "localhost";
$username = "root";
$password = "";

// Koneksi ke MySQL
$conn = mysqli_connect($servername, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS db5d";

if ($conn->query($sql) === TRUE) {
    echo "Database <strong>db5d</strong> created successfully";
} else {
    echo "Error creating database: " . $conn->error;
}

// Close connection
$conn->close();
?>