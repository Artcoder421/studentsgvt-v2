<?php
session_start(); // Start the session
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "studentsportal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errorMsg = "";
$successMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reg_no = $_POST['reg_no'];
    $password = $_POST['password'];

    $isAdmin = false;
    if (strpos($reg_no, '@admin') !== false) {
        $isAdmin = true;
        $reg_no = str_replace('@admin', '', $reg_no);
    }

    // Split reg_no into parts
    $parts = explode("/", $reg_no);
    if (count($parts) !== 4) {
        $errorMsg = "Invalid registration number format!";
    } else {
        list($institution, $course, $year_joined, $unique_no) = $parts;

        // Use prepared statement for security
        $stmt = $conn->prepare("SELECT id, first_name, second_name, password FROM students WHERE course=? AND year_joined=? AND unique_no=?");
        $stmt->bind_param("sss", $course, $year_joined, $unique_no);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();

            // Compare plain text password
            if ($password === $student['password']) {
                // Store user data in session
                $_SESSION['user_id'] = $student['id'];
                $_SESSION['reg_no'] = $reg_no;
                $_SESSION['first_name'] = $student['first_name'];
                $_SESSION['second_name'] = $student['second_name'];
                $_SESSION['year_joined'] = $student['year_joined'];
                $_SESSION['course'] = $student['course'];

                // Redirect based on user type
                if ($isAdmin) {
                    header("location: admin_dashboard.php");
                } else {
                    header("location: homepage.php");
                }
                exit();
            } else {
                $errorMsg = "Invalid registration number or password!";
            }
        } else {
            $errorMsg = "Invalid registration number or password!";
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
    <title>Student Login | SONIT Portal</title>
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
            height: 100vh;
            display: flex;
            align-items: center;
            background-image: linear-gradient(rgba(0, 51, 102, 0.8), rgba(0, 91, 150, 0.8)), 
                              url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');
            background-size: cover;
            background-position: center;
        }
        
        .login-container {
            max-width: 450px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            font-family: 'Playfair Display', serif;
            color: var(--primary-blue);
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-header img {
            height: 80px;
            margin-bottom: 15px;
        }
        
        .form-control {
            height: 45px;
            border-radius: 4px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 0.2rem rgba(0, 91, 150, 0.25);
        }
        
        .btn-login {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px;
            font-weight: 500;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background-color: var(--secondary-blue);
            color: white;
        }
        
        .forgot-password {
            color: var(--secondary-blue);
            text-align: center;
            display: block;
            margin-top: 15px;
        }
        
        .forgot-password:hover {
            color: var(--primary-blue);
            text-decoration: none;
        }
        
        .alert {
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <img src="https://via.placeholder.com/80x80?text=NIT+Logo" alt="Institution Logo">
                <h2>SONIT Student Portal</h2>
                <p>Sign in to access your account</p>
            </div>
            
            <?php if ($errorMsg): ?>
                <div class="alert alert-danger"><?= $errorMsg; ?></div>
            <?php endif; ?>
            <?php if ($successMsg): ?>
                <div class="alert alert-success"><?= $successMsg; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="reg_no">Registration Number</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        </div>
                        <input type="text" class="form-control" id="reg_no" name="reg_no" 
                               placeholder="e.g., NIT/BIT/2022/5674 or NIT/BIT/2022/5674@admin" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Your last name or new password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                
                <a href="#" class="forgot-password">Forgot your password?</a>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>