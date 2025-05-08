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

$user_id = $_SESSION['user_id'];
$studentQuery = "SELECT first_name, last_name, reg_no, course, level FROM students WHERE id = '$user_id'";
$studentResult = $conn->query($studentQuery);
$student = $studentResult->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $student['first_name'] . " " . $student['last_name'];
    $reg_number = $student['reg_no'];
    $course = $student['course'];
    $level = $_POST['level'];
    $position = $_POST['position'];
    $reason = $_POST['reason'];
    $motto = $_POST['motto'];
    
    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    $stmt = $conn->prepare("INSERT INTO contestants (fullname, reg_number, course, level, position, reason, motto, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $fullname, $reg_number, $course, $level, $position, $reason, $motto, $image);
    
    if ($stmt->execute()) {
        echo "<script>alert('Application submitted successfully!'); window.location.href='election.php';</script>";
    } else {
        echo "<script>alert('Error: Could not submit application.');</script>";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Elections | SONIT</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #003366;
            --secondary-blue: #005b96;
            --accent-gold: #d4af37;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
        }
        
        body {
            background-color: var(--light-gray);
            color: var(--dark-gray);
            padding-top: 70px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--primary-blue);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid var(--accent-gold);
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
        }
        
        .toggle-btn {
            background: transparent;
            border: none;
            color: white;
            font-size: 22px;
            cursor: pointer;
            margin-right: 15px;
        }
        
        .application-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            border-top: 5px solid var(--accent-gold);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .form-header h3 {
            color: var(--primary-blue);
            font-weight: 700;
        }
        
        .form-group label {
            font-weight: 600;
            color: var(--primary-blue);
            margin-bottom: 8px;
        }
        
        .form-control, .form-control:disabled {
            border-radius: 6px;
            padding: 12px 15px;
            min-height: 45px;
            line-height: 1.5;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 0.2rem rgba(0, 91, 150, 0.25);
        }
        
        .btn-submit {
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-blue));
            border: none;
            padding: 12px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background: linear-gradient(to right, var(--secondary-blue), var(--primary-blue));
            transform: translateY(-2px);
        }
        
        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        
        .file-upload-btn {
            border: 2px dashed #ddd;
            border-radius: 6px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        
        .file-upload-btn:hover {
            border-color: var(--secondary-blue);
            background-color: rgba(0, 91, 150, 0.05);
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
        
        .preview-image {
            max-width: 150px;
            max-height: 150px;
            margin-top: 15px;
            border-radius: 6px;
            display: none;
        }
        
        @media (max-width: 768px) {
            .application-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-vote-yea"></i> SONIT Elections
        </a>
    </nav>
    
    <div class="sidebar" id="sidebar">
        <a href="homepage.php"><i class="fas fa-home"></i> Home</a>
        <a href="election.php"><i class="fas fa-vote-yea"></i> Elections</a>
        <a href="lost.php"><i class="fas fa-search"></i> Lost & Found</a>
        <a href="feedback.php"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="content" id="content">
        <div class="application-card">
            <div class="form-header">
                <h3><i class="fas fa-user-tie mr-2"></i>Election Candidacy Application</h3>
                <p class="text-muted">Complete this form to apply for a student leadership position</p>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($student['first_name'] . " " . $student['last_name']); ?>" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Registration Number</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($student['reg_no']); ?>" disabled>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Course of Study</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($student['course']); ?>" disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Current Academic Level</label>
                            <select name="level" class="form-control" required>
                                <option value="">Select Level</option>
                                <option value="First Year">First Year</option>
                                <option value="Second Year">Second Year</option>
                                <option value="Third Year">Third Year</option>
                                <option value="Fourth Year">Fourth Year</option>
                                <option value="Fifth Year">Fifth Year</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Position Applying For</label>
                    <select name="position" class="form-control" required>
                        <option value="">Select Position</option>
                        <option value="President">President</option>
                        <option value="Parliament Representative">Parliament Representative</option>
                        <option value="Treasurer">Treasurer</option>
                        <option value="General Secretary">General Secretary</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Reason for Applying</label>
                    <textarea name="reason" class="form-control" rows="5" placeholder="Explain why you're the best candidate for this position..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Campaign Motto</label>
                    <input type="text" name="motto" class="form-control" placeholder="Your short campaign slogan (max 100 characters)" maxlength="100" required>
                </div>
                
                <div class="form-group">
                    <label>Official Portrait Photo</label>
                    <div class="file-upload">
                        <div class="file-upload-btn">
                            <i class="fas fa-camera fa-2x mb-3" style="color: var(--secondary-blue);"></i>
                            <p>Click to upload candidate photo</p>
                            <p class="small text-muted">(JPG/PNG, max 2MB)</p>
                            <img id="imagePreview" class="preview-image" src="#" alt="Preview">
                        </div>
                        <input type="file" name="image" class="file-upload-input" id="imageInput" accept="image/*" required>
                    </div>
                </div>
                
                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-submit btn-lg">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Application
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

        // Image preview functionality
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>