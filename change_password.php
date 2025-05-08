<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "studentsportal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['reg_no'])) {
    header("Location: login.php");
    exit();
}

$reg_no = $_SESSION['reg_no'];
$successMsg = "";
$errorMsg = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch current password from the database
    $stmt = $conn->prepare("SELECT password FROM students WHERE reg_no = ?");
    $stmt->bind_param("s", $reg_no);
    $stmt->execute();
    $stmt->bind_result($db_password);
    $stmt->fetch();
    $stmt->close();

    // Check if entered current password is correct
    if ($db_password !== $current_password) {
        $errorMsg = "Current password is incorrect!";
    } elseif ($new_password !== $confirm_password) {
        $errorMsg = "New passwords do not match!";
    } else {
        // Update password in database
        $stmt = $conn->prepare("UPDATE students SET password = ? WHERE reg_no = ?");
        $stmt->bind_param("ss", $new_password, $reg_no);
        if ($stmt->execute()) {
            $successMsg = "Password changed successfully!";
        } else {
            $errorMsg = "Error updating password!";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | SONIT</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
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
            padding-top: 80px;
            font-family: 'Roboto', sans-serif;
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
            min-height: calc(100vh - 70px);
        }
        
        .toggle-btn {
            background: transparent;
            border: none;
            color: white;
            font-size: 22px;
            cursor: pointer;
            margin-right: 15px;
        }
        
        .password-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .form-control:focus {
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-blue);
            border-color: var(--secondary-blue);
        }
        
        .password-header {
            color: var(--primary-blue);
            border-bottom: 2px solid var(--accent-gold);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .password-strength {
            height: 5px;
            margin-top: 5px;
            background-color: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .strength-0 {
            width: 0%;
            background-color: #dc3545;
        }
        
        .strength-1 {
            width: 25%;
            background-color: #dc3545;
        }
        
        .strength-2 {
            width: 50%;
            background-color: #fd7e14;
        }
        
        .strength-3 {
            width: 75%;
            background-color: #ffc107;
        }
        
        .strength-4 {
            width: 100%;
            background-color: #28a745;
        }
        
        @media (max-width: 768px) {
            .password-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-user-shield"></i> SONIT Password Change
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
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
        <div class="container">
            <div class="password-container">
                <h3 class="password-header text-center mb-4">
                    <i class="fas fa-key mr-2"></i>Change Password
                </h3>
                
                <?php if ($errorMsg): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $errorMsg; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if ($successMsg): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $successMsg; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="passwordForm">
                    <div class="form-group">
                        <label for="current_password"><i class="fas fa-lock mr-2"></i>Current Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <div class="input-group-append">
                                <span class="input-group-text" id="toggleCurrentPassword" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password"><i class="fas fa-key mr-2"></i>New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="input-group-append">
                                <span class="input-group-text" id="toggleNewPassword" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <div class="password-strength mt-2">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <small class="form-text text-muted">Password must be at least 8 characters long</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password"><i class="fas fa-check-circle mr-2"></i>Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <div class="input-group-append">
                                <span class="input-group-text" id="toggleConfirmPassword" style="cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <small id="passwordMatch" class="form-text"></small>
                    </div>
                    
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save mr-2"></i>Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
        
        // Toggle password visibility
        $('#toggleCurrentPassword').click(function() {
            const passwordField = $('#current_password');
            const icon = $(this).find('i');
            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
        
        $('#toggleNewPassword').click(function() {
            const passwordField = $('#new_password');
            const icon = $(this).find('i');
            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
        
        $('#toggleConfirmPassword').click(function() {
            const passwordField = $('#confirm_password');
            const icon = $(this).find('i');
            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
        
        // Password strength checker
        $('#new_password').on('input', function() {
            const password = $(this).val();
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength++;
            
            // Contains lowercase letter
            if (/[a-z]/.test(password)) strength++;
            
            // Contains uppercase letter
            if (/[A-Z]/.test(password)) strength++;
            
            // Contains number or special character
            if (/[0-9!@#$%^&*]/.test(password)) strength++;
            
            // Update strength bar
            $('#strengthBar').removeClass().addClass('strength-' + strength);
            
            // Color coding
            let color = '';
            let text = '';
            switch(strength) {
                case 0:
                case 1:
                    color = 'danger';
                    text = 'Weak';
                    break;
                case 2:
                    color = 'warning';
                    text = 'Moderate';
                    break;
                case 3:
                    color = 'info';
                    text = 'Strong';
                    break;
                case 4:
                    color = 'success';
                    text = 'Very Strong';
                    break;
            }
            
            $('#strengthBar')
                .addClass('bg-' + color)
                .css('width', (strength * 25) + '%')
                .attr('data-original-title', text)
                .tooltip('update');
        });
        
        // Password match checker
        $('#confirm_password').on('input', function() {
            const newPassword = $('#new_password').val();
            const confirmPassword = $(this).val();
            
            if (confirmPassword.length === 0) {
                $('#passwordMatch').text('').removeClass('text-danger text-success');
            } else if (newPassword === confirmPassword) {
                $('#passwordMatch').text('Passwords match').removeClass('text-danger').addClass('text-success');
            } else {
                $('#passwordMatch').text('Passwords do not match').removeClass('text-success').addClass('text-danger');
            }
        });
        
        // Initialize tooltips
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>