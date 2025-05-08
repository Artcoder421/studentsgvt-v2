<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "studentsportal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $date_posted = date('Y-m-d H:i:s');
    
    // Handle image uploads
    $image_paths = [];
    if (!empty($_FILES['images']['name'][0])) {
        $upload_dir = "uploads/";
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
    
        // Loop through uploaded images
        foreach ($_FILES['images']['name'] as $i => $name) {
            $filename = time() . "_" . basename($name); // Create unique filename
            $target_file = $upload_dir . $filename;
    
            // Move uploaded file to the target directory
            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_file)) {
                $image_paths[] = $filename; // Add the filename to the array
            }
        }
            $image_paths_str = json_encode($image_paths); 
        
    
    }
    

    
    $images = implode(",", $image_paths);
    
    $sql = "INSERT INTO announcements (title, content, images, date_posted) VALUES ('$title', '$content', '$images', '$date_posted')";
    if ($conn->query($sql) === TRUE) {
        header("Location: admin_announcement.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Announcement | SONIT Portal</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
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
            font-family: 'Roboto', sans-serif;
            color: var(--dark-gray);
            padding-top: 80px;
        }
        
        h1, h2, h3, h4 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-blue);
        }
        
        .navbar {
            background-color: var(--primary-blue);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        #sidebar {
            background-color: white;
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: -250px;
            padding-top: 80px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar-link {
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
        
        .sidebar-link:hover {
            background-color: rgba(0, 51, 102, 0.05);
            border-left: 4px solid var(--accent-gold);
        }
        
        .sidebar-link i {
            width: 25px;
            text-align: center;
            color: var(--secondary-blue);
        }
        
        #content {
            margin-left: 0;
            transition: margin-left 0.3s;
            padding: 30px;
        }
        
        .sidebar-visible #sidebar {
            left: 0;
        }
        
        .sidebar-visible #content {
            margin-left: 250px;
        }
        
        .form-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-top: 4px solid var(--secondary-blue);
        }
        
        .form-control {
            border-radius: 4px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 0.2rem rgba(0, 91, 150, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-blue);
            border-color: var(--secondary-blue);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .toggle-btn {
            background: transparent;
            border: none;
            color: white;
            font-size: 22px;
            cursor: pointer;
            margin-right: 15px;
        }
        
        .file-upload-wrapper {
            position: relative;
            margin-bottom: 20px;
        }
        
        .file-upload-label {
            display: block;
            padding: 12px;
            background: var(--light-gray);
            border: 1px dashed #aaa;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-upload-label:hover {
            border-color: var(--secondary-blue);
            background: rgba(0, 91, 150, 0.05);
        }
        
        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .sidebar-visible #content {
                margin-left: 0;
                position: relative;
                left: 250px;
                width: calc(100% - 250px);
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-university"></i> ADMIN PORTAL
        </a>
    </nav>
    
    <div id="sidebar">
        <h4 class="text-center mb-4" style="color: var(--primary-blue);">Admin Panel</h4>
        <hr>
        <a href="admin_dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a>
        <a href="admin_announcement.php" class="sidebar-link"><i class="fas fa-bullhorn"></i> Post Announcements</a>
        <a href="admin_election.php" class="sidebar-link"><i class="fas fa-poll"></i> Manage Elections</a>
        <a href="update_contacts.php" class="sidebar-link"><i class="fas fa-address-book"></i> Update Contacts</a>
        <a href="update_leaders.php" class="sidebar-link"><i class="fas fa-users"></i> Update Leaders</a>
        <a href="feedbacks.php" class="sidebar-link"><i class="fas fa-comment"></i> Feedbacks</a>
        <a href="lost_found.php" class="sidebar-link"><i class="fas fa-search"></i> Lost & Found</a>
        <a href="manage_calendar.php" class="sidebar-link"><i class="fas fa-calendar"></i> Manage Calendar</a>
        <a href="logout.php" class="sidebar-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div id="content">
        <div class="container">
            <div class="form-container">
                <h2 class="mb-4"><i class="fas fa-bullhorn"></i> Post New Announcement</h2>
                
                <form action="post_announcement.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Content</label>
                        <textarea name="content" class="form-control" rows="8" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Upload Images</label>
                        <div class="file-upload-wrapper">
                            <label class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                Click to upload images or drag and drop<br>
                                <small class="text-muted">(Multiple files allowed)</small>
                            </label>
                            <input type="file" name="images[]" class="file-upload-input" multiple>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Post Announcement
                        </button>
                        <a href="admin_announcement.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-visible');
            
            // Store sidebar state in localStorage
            const isVisible = document.body.classList.contains('sidebar-visible');
            localStorage.setItem('sidebarVisible', isVisible);
        }
        
        // Check for saved sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedState = localStorage.getItem('sidebarVisible');
            if (savedState === 'true') {
                document.body.classList.add('sidebar-visible');
            }
            
            // File upload display
            const fileInput = document.querySelector('.file-upload-input');
            const fileLabel = document.querySelector('.file-upload-label');
            
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    if (this.files.length === 1) {
                        fileLabel.innerHTML = `<i class="fas fa-file-image"></i> ${this.files[0].name}`;
                    } else {
                        fileLabel.innerHTML = `<i class="fas fa-file-image"></i> ${this.files.length} files selected`;
                    }
                } else {
                    fileLabel.innerHTML = `<i class="fas fa-cloud-upload-alt fa-2x mb-2"></i><br>
                                         Click to upload images or drag and drop<br>
                                         <small class="text-muted">(Multiple files allowed)</small>`;
                }
            });
        });
    </script>
</body>
</html>