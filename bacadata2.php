
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
$sql = "SELECT id, firstname, lastname, email, reg_date FROM MyGuests";
$result = mysqli_query($conn, $sql);

// Button Add My Guest
echo "<button onclick=\"window.location.href='form_guest.html'\">Add My Guest</button>";

// Wrapper untuk judul dan tabel
echo "<div style='width: fit-content;'>";

// Title
echo "<h3 style='text-align: center;'>My Guests List</h3>";

// Display data
if (mysqli_num_rows($result) > 0) {

    echo "<table border='1' cellpadding='5' cellspacing='0'
          style='border-collapse: collapse;'>";

    // Table header
    echo "<tr bgcolor='#f2f2f2'>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Reg Date</th>
            <th>Actions</th>
          </tr>";

    // Display each row
    while ($row = mysqli_fetch_assoc($result)) {

        echo "<tr>";

        echo "<td>" . $row["id"] . "</td>";

        echo "<td>" .
             htmlspecialchars($row["firstname"] . " " . $row["lastname"]) .
             "</td>";

        echo "<td>" .
             htmlspecialchars($row["email"]) .
             "</td>";

        echo "<td>" . $row["reg_date"] . "</td>";

        // Action buttons
        echo "<td>
                <a href='form_editguest.php?id=" . $row["id"] . "'>
                    <button type='button'>Edit</button>
                </a>
                |
                <a href='hapusguest.php?id=" . $row["id"] . "'
                   onclick=\"return confirm('Apakah Anda yakin ingin menghapus record ini?')\">
                    <button type='button'>Delete</button>
                </a>
              </td>";

        echo "</tr>";
    }

    echo "</table>";

} else {
    echo "0 results - Data not found!";
}

echo "</div>";

// Close connection
mysqli_close($conn);
?>
