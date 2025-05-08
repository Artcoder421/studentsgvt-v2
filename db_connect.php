<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "studentsportal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection has failed: " . $conn->connect_error);
}
?>
