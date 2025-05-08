<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_announcements.php");
    exit();
}

// Get form data
$id = (int)$_POST['id'];
$title = $_POST['title'];
$content = $_POST['content'];

// Database connection
$conn = new mysqli("localhost", "root", "", "studentsportal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle images
$existingImages = $_POST['existing_images'] ?? [];
$deletedImages = $_POST['deleted_images'] ?? [];

// Remove deleted images from array
$remainingImages = array_diff($existingImages, $deletedImages);

// Process new image uploads
$uploadDir = 'uploads/announcements/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$newImages = [];
if (!empty($_FILES['new_images']['name'][0])) {
    foreach ($_FILES['new_images']['tmp_name'] as $key => $tmpName) {
        $fileName = basename($_FILES['new_images']['name'][$key]);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExt, $allowedTypes)) {
            $newFileName = uniqid() . '.' . $fileExt;
            $uploadPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($tmpName, $uploadPath)) {
                $newImages[] = 'announcements/' . $newFileName;
            }
        }
    }
}

// Combine remaining and new images
$allImages = array_merge($remainingImages, $newImages);
$imagesJson = json_encode($allImages);

// Update announcement in database
$stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ?, images = ? WHERE id = ?");
$stmt->bind_param("sssi", $title, $content, $imagesJson, $id);

if ($stmt->execute()) {
    // Delete actual files for deleted images
    foreach ($deletedImages as $image) {
        $filePath = 'uploads/' . $image;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    header("Location: admin_announcements.php?success=updated");
} else {
    header("Location: edit_announcement.php?id=$id&error=update_failed");
}

$stmt->close();
$conn->close();