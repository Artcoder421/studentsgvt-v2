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

// Fetch leaders
$sql = "SELECT * FROM leaders ORDER BY 
        CASE position 
            WHEN 'President' THEN 1
            WHEN 'Vice President' THEN 2
            WHEN 'Secretary' THEN 3
            WHEN 'Treasurer' THEN 4
            ELSE 5
        END, name";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Leaders | SONIT</title>
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
        
        .leaders-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 20px;
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
        
        .leader-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border-top: 4px solid var(--secondary-blue);
            background-color: white;
        }
        
        .leader-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        
        .leader-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--accent-gold);
            margin: 0 auto 15px;
            display: block;
        }
        
        .leader-name {
            color: var(--primary-blue);
            font-weight: 600;
            text-align: center;
            margin-bottom: 5px;
        }
        
        .leader-position {
            color: var(--secondary-blue);
            font-weight: 500;
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .leader-contact {
            text-align: center;
            margin-bottom: 5px;
        }
        
        .leader-contact i {
            color: var(--secondary-blue);
            margin-right: 8px;
        }
        
        .no-leaders {
            text-align: center;
            padding: 30px;
            background-color: rgba(0, 0, 0, 0.03);
            border-radius: 8px;
        }
        
        .position-badge {
            background-color: var(--primary-blue);
            color: white;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .leader-card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-users"></i> SONIT Leaders
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
        <a href="chat_area.php"><i class="fas fa-comments"></i> Community Chat</a>
        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="content" id="content">
        <div class="leaders-container">
            <div class="page-header">
                <h2><i class="fas fa-user-tie mr-2"></i>Student Leadership</h2>
                <p class="text-muted">Meet your student representatives</p>
            </div>
            
            <div class="row">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="leader-card">
                                <span class="position-badge"><?php echo htmlspecialchars($row['position']); ?></span>
                                <?php
                                    $imagePath = "uploads/" . htmlspecialchars($row['picture']);
                                    if (file_exists($imagePath) && !empty($row['picture'])) {
                                        echo "<img src='$imagePath' alt='" . htmlspecialchars($row['name']) . "' class='leader-image'>";
                                    } else {
                                        echo "<img src='uploads/default.jpg' alt='Default Image' class='leader-image'>";
                                    }
                                ?>
                                <h4 class="leader-name"><?php echo htmlspecialchars($row['name']); ?></h4>
                                <p class="leader-position"><?php echo htmlspecialchars($row['position']); ?></p>
                                
                                <div class="leader-contact">
                                    <i class="fas fa-envelope"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($row['contacts']); ?>"><?php echo htmlspecialchars($row['contacts']); ?></a>
                                </div>
                                
                                <?php if (!empty($row['bio'])): ?>
                                    <div class="leader-bio mt-3">
                                        <p class="small"><?php echo htmlspecialchars($row['bio']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="no-leaders">
                            <i class="fas fa-info-circle fa-3x mb-3" style="color: var(--secondary-blue);"></i>
                            <h4>No Leaders Found</h4>
                            <p>There are currently no leaders registered in the system.</p>
                        </div>
                    </div>
                <?php endif; ?>
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
<?php $conn->close(); ?>