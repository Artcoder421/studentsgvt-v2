<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "studentsportal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$sql = "SELECT first_name, last_name, reg_no FROM students WHERE id = '$user_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Insert feedback
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $feedback = $conn->real_escape_string($_POST['feedback']);
    $sql = "INSERT INTO feedback (user_id, name, reg_no, feedback) VALUES ('$user_id', '{$user['first_name']} {$user['last_name']}', '{$user['reg_no']}', '$feedback')";

    if ($conn->query($sql) === TRUE) {
        $message = ['type' => 'success', 'text' => 'Feedback submitted successfully!'];
    } else {
        $message = ['type' => 'danger', 'text' => 'Error submitting feedback. Please try again.'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Portal | SONIT</title>
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
        
        .feedback-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            border-top: 5px solid var(--accent-gold);
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .page-header h2 {
            color: var(--primary-blue);
            font-weight: 700;
            position: relative;
        }
        
        .page-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--accent-gold);
        }
        
        .form-group label {
            font-weight: 600;
            color: var(--primary-blue);
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 6px;
            padding: 12px 15px;
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
            width: 100%;
            margin-top: 20px;
        }
        
        .btn-submit:hover {
            background: linear-gradient(to right, var(--secondary-blue), var(--primary-blue));
            transform: translateY(-2px);
        }
        
        .readonly-field {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        
        .alert-custom {
            border-radius: 6px;
            margin-bottom: 25px;
        }
        
        @media (max-width: 768px) {
            .feedback-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-comment-alt"></i> SONIT Feedback
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
        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="content" id="content">
        <div class="feedback-container">
            <div class="page-header">
                <h2><i class="fas fa-comment-dots mr-2"></i>Student Feedback Portal</h2>
                <p class="text-muted">Your opinion helps us improve the student experience</p>
            </div>
            
            <?php if (isset($message)): ?>
                <div class="alert alert-<?= $message['type'] ?> alert-custom alert-dismissible fade show">
                    <?= $message['text'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" class="form-control readonly-field" value="<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Registration Number</label>
                            <input type="text" class="form-control readonly-field" value="<?= htmlspecialchars($user['reg_no']); ?>" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Your Feedback</label>
                    <textarea class="form-control" name="feedback" rows="8" placeholder="Please share your thoughts, suggestions, or concerns..." required></textarea>
                    <small class="form-text text-muted">We value your input and will use it to improve our services.</small>
                </div>
                
                <div class="form-group text-center">
                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Feedback
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
        
        // Auto-dismiss alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>