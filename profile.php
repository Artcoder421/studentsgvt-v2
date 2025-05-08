<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT reg_no, first_name, second_name, last_name, course, year_joined, profile_pic FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | SONIT</title>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 70px;
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
        
        .profile-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .profile-header h2 {
            color: var(--primary-blue);
            font-weight: 700;
            position: relative;
        }
        
        .profile-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--accent-gold);
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--accent-gold);
            margin: 0 auto 20px;
            display: block;
        }
        
        .profile-details {
            margin-top: 30px;
        }
        
        .detail-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--primary-blue);
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 1.1rem;
        }
        
        .btn-edit {
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-blue));
            border: none;
            padding: 10px 25px;
            font-weight: 500;
            transition: all 0.3s;
            color: white;
            margin-top: 20px;
        }
        
        .btn-edit:hover {
            background: linear-gradient(to right, var(--secondary-blue), var(--primary-blue));
            transform: translateY(-2px);
            color: white;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                padding: 20px;
            }
            
            .profile-picture {
                width: 120px;
                height: 120px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-user-circle"></i> My Profile
        </a>
    </nav>
    
    <div class="sidebar" id="sidebar">
        <a href="homepage.php"><i class="fas fa-home"></i> Home</a>
        <a href="announcements.php"><i class="fas fa-bullhorn"></i> Announcements</a>
        <a href="election.php"><i class="fas fa-vote-yea"></i> Elections</a>
        <a href="lost.php"><i class="fas fa-search"></i> Lost & Found</a>
        <a href="feedback.php"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="chat_area.php"><i class="fas fa-comments"></i> Community Chat</a>
        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="content" id="content">
        <div class="profile-container">
            <div class="profile-header">
                <h2><i class="fas fa-user-circle mr-2"></i>My Profile</h2>
                <p class="text-muted">View and manage your account information</p>
            </div>
            
            <div class="text-center">
                <img src="uploads/<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'default.jpg'; ?>" 
                     alt="Profile Picture" class="profile-picture">
                <h3 class="mt-3"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                <p class="text-muted"><?php echo htmlspecialchars($user['course']); ?> Student</p>
            </div>
            
            <div class="profile-details">
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-id-card mr-2"></i>Registration Number</div>
                    <div class="detail-value"><?php echo htmlspecialchars($user['reg_no']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-user mr-2"></i>Full Name</div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['second_name'] . ' ' . $user['last_name']); ?>
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-book mr-2"></i>Course</div>
                    <div class="detail-value"><?php echo htmlspecialchars($user['course']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-calendar-alt mr-2"></i>Year of Study</div>
                    <div class="detail-value">
                        Year <?php echo (date("Y") - $user['year_joined']); ?> (Joined <?php echo $user['year_joined']; ?>)
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="#" class="btn btn-edit">
                        <i class="fas fa-edit mr-2"></i>Edit Profile
                    </a>
                    <a href="change_password.php" class="btn btn-edit ml-3">
                        <i class="fas fa-key mr-2"></i>Change Password
                    </a>
                </div>
            </div>
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
    </script>
</body>
</html>