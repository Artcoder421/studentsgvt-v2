<?php
$conn = new mysqli("localhost", "root", "", "studentsportal");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $announcement_id = $_POST["announcement_id"];
    $user_name = $conn->real_escape_string($_POST["user_name"]);
    $comment = $conn->real_escape_string($_POST["comment"]);

    $sql = "INSERT INTO announcement_comments (announcement_id, user_name, comment) 
            VALUES ('$announcement_id', '$user_name', '$comment')";

    if ($conn->query($sql) === TRUE) {
        header("Location: announcements.php");
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>
