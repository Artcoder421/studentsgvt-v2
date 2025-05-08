<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$event_id = intval($_POST['event_id']);
$start_datetime = $_POST['start_datetime'];
$end_datetime = $_POST['end_datetime'];

$sql = "UPDATE calendar_events 
        SET start_datetime = ?, end_datetime = ?
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $start_datetime, $end_datetime, $event_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$stmt->close();
$conn->close();
?>