
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db5d";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// SQL query
$sql = "SELECT * FROM mahasiswa";

// Execute the SQL query
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    // Output data dalam bentuk tabel
    echo "<table border='1' cellpadding='8' cellspacing='0'>";
    echo "<tr bgcolor='#f2f2f2'>
            <th>NIM</th>
            <th>Nama Lengkap</th>
            <th>Nilai Akhir</th>
            <th>Nilai Huruf</th>
            <th>Predikat</th>
          </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row["nim"] . "</td>";
        echo "<td>" . $row["nama_lengkap"] . "</td>";
        echo "<td>" . $row["nilai_akhir"] . "</td>";
        echo "<td>" . $row["nilai_huruf"] . "</td>";
        echo "<td>" . $row["predikat"] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "0 results - Data not found";
}

// Close connection
mysqli_close($conn);
?>
```