<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$event_id = intval($_GET['event_id']);
$status = $_GET['status'];

$sql = "SELECT u.id, u.first_name, u.last_name, u.email, u.department 
        FROM students u
        JOIN event_attendance a ON u.id = a.user_id
        WHERE a.event_id = ? AND a.status = ?
        ORDER BY u.first_name, u.last_name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $event_id, $status);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['first_name']). ' ' . htmlspecialchars($row['last_name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['email']) . '</td>';
    echo '<td>' . htmlspecialchars($row['department']) . '</td>';
    echo '</tr>';
}

if ($result->num_rows === 0) {
    echo '<tr><td colspan="3" class="text-center">No attendees in this category</td></tr>';
}

$stmt->close();
$conn->close();
?>