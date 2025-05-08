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

// Fetch president
$presidentQuery = "SELECT * FROM leaders WHERE position = 'President' LIMIT 1";
$presidentResult = $conn->query($presidentQuery);
$president = $presidentResult->fetch_assoc();

// Fetch other leaders
$leadersQuery = "SELECT * FROM leaders WHERE position != 'President'";
$leadersResult = $conn->query($leadersQuery);

// Fetch history
$historyQuery = "SELECT history FROM leaders LIMIT 1"; 
$historyResult = $conn->query($historyQuery);
$history = $historyResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SONIT Student Portal - Official</title>
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
            color: var(--dark-gray);
            font-family: 'Roboto', sans-serif;
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
        }
        
        .leader-section {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        
        .leader-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            width: 250px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            border-top: 4px solid var(--secondary-blue);
        }
        
        .leader-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .leader-card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: block;
            border: 2px solid var(--accent-gold);
        }
        
        .leader-card h4 {
            color: var(--primary-blue);
            text-align: center;
            margin-bottom: 5px;
        }
        
        .leader-card p {
            color: var(--dark-gray);
            text-align: center;
            font-size: 0.9rem;
        }
        
        header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        footer {
            background: var(--primary-blue);
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            .leader-card {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-university"></i> SONIT PORTAL
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
        <a href="election.php"><i class="fas fa-vote-yea"></i> Elections</a>
        <a href="lost.php"><i class="fas fa-search"></i> Lost & Found</a>
        <a href="feedback.php"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="chat_area.php"><i class="fas fa-users"></i> Student Community</a>
        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="content" id="content">
        <header>
            <h1>National Institute of Transport</h1>
            <p class="lead">Student Organization Portal</p>
        </header>
        
        <section>
            <h2><i class="fas fa-user-tie"></i> President</h2>
            <div class="leader-card">
                <img src="uploads/<?php echo htmlspecialchars($president['picture']); ?>" alt="President">
                <h4><?php echo htmlspecialchars($president['name']); ?></h4>
                <p><?php echo htmlspecialchars($president['bio']); ?></p>
            </div>
        </section>
        
        <section>
            <h2><i class="fas fa-users"></i> Leadership Council</h2>
            <div class="leader-section">
                <?php while ($leader = $leadersResult->fetch_assoc()): ?>
                <div class="leader-card">
                    <img src="uploads/<?php echo htmlspecialchars($leader['picture']); ?>" alt="<?php echo htmlspecialchars($leader['name']); ?>">
                    <h4><?php echo htmlspecialchars($leader['name']); ?></h4>
                    <p><?php echo htmlspecialchars($leader['position']); ?></p>
                </div>
                <?php endwhile; ?>
            </div>
        </section>

        <section>
            <h2><i class="fas fa-landmark"></i> Organization History</h2>
            <p class="lead"><?php echo htmlspecialchars($history['history']); ?></p>
        </section>
    </div>

    <footer>
        <p class="mb-0">&copy; 2025 National Institute of Transport - Student Organization</p>
    </footer>
    
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