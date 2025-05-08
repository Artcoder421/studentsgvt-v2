<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "studentsportal");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = $conn->real_escape_string($_POST['item_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $location = $conn->real_escape_string($_POST['location']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $reported_by = $_SESSION['user_id'];

    // Handle file upload
    $targetDir = "uploads/";
    $fileName = basename($_FILES["image"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    $allowedTypes = array('jpg', 'png', 'jpeg', 'gif');
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $sql = "INSERT INTO lost_found (item_name, description, location, contact, image, reported_by, status) 
                    VALUES ('$item_name', '$description', '$location', '$contact', '$fileName', '$reported_by', 'Lost')";
            if ($conn->query($sql)) {
                $message = "<div class='alert alert-success'>Item reported successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
            }
        } else {
            $message = "<div class='alert alert-warning'>File upload failed.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Only JPG, JPEG, PNG & GIF files are allowed.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Lost & Found</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #003366;
            --secondary-blue: #005b96;
            --accent-gold: #d4af37;
            --light-gray: #f5f5f5;
            --dark-gray: #333333;
        }
        
        body {
            background-color: var(--light-gray);
            color: var(--dark-gray);
            font-family: 'Roboto', sans-serif;
            padding-top: 80px;
        }
        
        .navbar {
            background-color: var(--primary-blue);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid var(--accent-gold);
        }
        
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .sidebar {
            background-color: white;
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: -250px;
            padding-top: 80px;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            border-right: 2px solid var(--primary-blue);
        }
        
        .sidebar a {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    color: var(--dark-gray);
    font-weight: 500;
    border-left: 4px solid transparent;
    transition: all 0.2s;
    text-decoration: none;
    gap: 10px;
}
        
        .sidebar a:hover {
            background-color: rgba(0, 51, 102, 0.05);
            border-left: 4px solid var(--accent-gold);
            color: var(--primary-blue);
        }
        
        .sidebar a i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
            color: var(--secondary-blue);
        }
        
        .content {
            margin-left: 0;
            transition: margin-left 0.3s;
            padding: 30px;
            background-color: white;
            min-height: calc(100vh - 80px);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }
        
        .toggle-btn {
            background: transparent;
            border: none;
            color: white;
            font-size: 22px;
            cursor: pointer;
            margin-right: 15px;
        }
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-title {
            color: var(--primary-blue);
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--accent-gold);
        }
        
        .btn-submit {
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-blue));
            border: none;
            padding: 10px 25px;
            font-weight: 500;
        }
        
        .btn-submit:hover {
            background: linear-gradient(to right, var(--secondary-blue), var(--primary-blue));
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-search"></i> Lost & Found
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="announcements.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>
                <li class="nav-item"><a class="nav-link" href="calenders.php"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
                <li class="nav-item"><a class="nav-link" href="contacts.php"><i class="fas fa-address-book"></i> Contacts</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="sidebar" id="sidebar">
        <a href="homepage.php"><i class="fas fa-home"></i> Home</a>
        <a href="election.php"><i class="fas fa-vote-yea"></i> Elections</a>
        <a href="lost.php"><i class="fas fa-search"></i> Lost & Found</a>
        <a href="feedback.php"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="chat_area.php"><i class="fas fa-users"></i> Student Community</a>
        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="content" id="content">
        <div class="form-container">
            <h2 class="form-title"><i class="fas fa-search-location"></i> Report Lost Item</h2>
            <?php echo $message; ?>
            <form action="lost.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" name="item_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label>Location Where Lost/Found</label>
                    <input type="text" name="location" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Contact Information</label>
                    <input type="text" name="contact" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Upload Image</label>
                    <div class="custom-file">
                        <input type="file" name="image" class="custom-file-input" id="customFile" required>
                        <label class="custom-file-label" for="customFile">Choose file</label>
                    </div>
                    <small class="form-text text-muted">Max size: 2MB (JPG, PNG, GIF)</small>
                </div>
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-submit text-white">
                        <i class="fas fa-paper-plane mr-2"></i> Report Item
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            const content = document.getElementById("content");
            if (sidebar.style.left === "0px") {
                sidebar.style.left = "-250px";
                content.style.marginLeft = "0";
            } else {
                sidebar.style.left = "0px";
                content.style.marginLeft = "250px";
            }
        }

        // Update file input label
        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            var fileName = document.getElementById("customFile").files[0].name;
            var nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });
    </script>
</body>
</html>