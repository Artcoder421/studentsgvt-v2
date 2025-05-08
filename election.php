<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Database connection
$conn = new mysqli("localhost", "root", "", "studentsportal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch student course
$courseQuery = "SELECT course FROM students WHERE id = '$user_id' LIMIT 1";
$courseResult = $conn->query($courseQuery);
$student = $courseResult->fetch_assoc();
$studentCourse = $student['course'];

// Check if election is enabled
$electionEnabledQuery = "SELECT status FROM settings WHERE id = 1";
$electionEnabledResult = $conn->query($electionEnabledQuery);
$electionEnabled = $electionEnabledResult->fetch_assoc()['status'];

// Fetch Presidential Candidates
$presidentQuery = "SELECT * FROM contestants WHERE position = 'President' AND status = 'Accepted'";
$presidentResult = $conn->query($presidentQuery);
$hasPresidents = $presidentResult->num_rows > 0;

// Fetch Parliament Representatives for the student's course
$prQuery = "SELECT * FROM contestants WHERE position = 'Parliament Representative' AND course = '$studentCourse' AND status = 'Accepted'";
$prResult = $conn->query($prQuery);
$hasPRs = $prResult->num_rows > 0;

$showApplyButton = $electionEnabled && !$hasPresidents && !$hasPRs;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SONIT Student Elections</title>
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
            --official-red: #cc0000;
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
            border-bottom: 3px solid var(--accent-gold);
        }
        
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 1px;
            font-size: 1.5rem;
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
        
        .candidate-section {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            justify-content: center;
            padding: 20px 0;
        }
        
        .candidate-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            width: 280px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            border-top: 4px solid var(--secondary-blue);
            position: relative;
            overflow: hidden;
        }
        
        .candidate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .candidate-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--accent-gold);
        }
        
        .candidate-card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: block;
            border: 3px solid var(--accent-gold);
            object-fit: cover;
        }
        
        .candidate-card h4 {
            color: var(--primary-blue);
            text-align: center;
            margin-bottom: 5px;
        }
        
        .candidate-card p {
            color: var(--dark-gray);
            text-align: center;
            font-size: 0.9rem;
            font-style: italic;
        }
        
        .position-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--official-red);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
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
            color: var(--primary-blue);
            position: relative;
            display: inline-block;
        }
        
        header h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--accent-gold);
        }
        
        section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e0e0;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-gold);
        }
        
        .section-title i {
            margin-right: 10px;
            color: var(--secondary-blue);
            font-size: 1.5rem;
        }
        
        .apply-btn {
            display: block;
            margin: 30px auto;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: bold;
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-blue));
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 300px;
        }
        
        .apply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            color: white;
        }
        
        .no-candidates {
            text-align: center;
            padding: 30px;
            background: rgba(0, 0, 0, 0.03);
            border-radius: 8px;
            font-style: italic;
            color: var(--dark-gray);
        }
        
        .course-badge {
            background: var(--accent-gold);
            color: var(--primary-blue);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            display: inline-block;
            margin-left: 10px;
        }
        
        .vote-btn {
            display: block;
            margin: 20px auto 0;
            padding: 10px 20px;
            background-color: var(--official-red);
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            width: fit-content;
        }
        
        .vote-btn:hover {
            background-color: #b30000;
            text-decoration: none;
            color: white;
        }
        
        @media (max-width: 768px) {
            .candidate-card {
                width: 100%;
            }
            
            header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-vote-yea"></i> SONIT ELECTIONS
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
        <header>
            <h1>Student Organization Elections</h1>
            <p class="lead">View candidates for the upcoming academic year</p>
        </header>
        
        <section>
            <div class="section-title">
                <i class="fas fa-user-tie"></i>
                <h2>Presidential Candidates</h2>
            </div>
            <div class="candidate-section">
                <?php if ($hasPresidents): ?>
                    <?php while ($president = $presidentResult->fetch_assoc()): ?>
                    <div class="candidate-card">
                        <span class="position-badge">PRESIDENT</span>
                        <img src="<?php echo htmlspecialchars($president['image']); ?>" alt="<?php echo htmlspecialchars($president['fullname']); ?>">
                        <h4><?php echo htmlspecialchars($president['fullname']); ?></h4>
                        <p>"<?php echo htmlspecialchars($president['motto']); ?>"</p>
                    </div>
                    <?php endwhile; ?>
                    <div style="width: 100%; text-align: center;">
                        <a href="poll.php?position=president" class="vote-btn">
                            <i class="fas fa-vote-yea mr-2"></i> VOTE NOW FOR PRESIDENT
                        </a>
                    </div>
                <?php else: ?>
                    <div class="no-candidates">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <p>No presidential candidates have been nominated yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section>
            <div class="section-title">
                <i class="fas fa-users"></i>
                <h2>Parliament Representatives <span class="course-badge"><?php echo htmlspecialchars($studentCourse); ?></span></h2>
            </div>
            <div class="candidate-section">
                <?php if ($hasPRs): ?>
                    <?php while ($pr = $prResult->fetch_assoc()): ?>
                    <div class="candidate-card">
                        <span class="position-badge">REPRESENTATIVE</span>
                        <img src="uploads/<?php echo htmlspecialchars($pr['image']); ?>" alt="<?php echo htmlspecialchars($pr['fullname']); ?>">
                        <h4><?php echo htmlspecialchars($pr['fullname']); ?></h4>
                        <p>"<?php echo htmlspecialchars($pr['motto']); ?>"</p>
                    </div>
                    <?php endwhile; ?>
                    <div style="width: 100%; text-align: center;">
                        <a href="poll.php?position=pr" class="vote-btn">
                            <i class="fas fa-vote-yea mr-2"></i> VOTE NOW FOR REPRESENTATIVE
                        </a>
                    </div>
                <?php else: ?>
                    <div class="no-candidates">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <p>No representatives have been nominated for your course yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($showApplyButton): ?>
            <a href="apply.php" class="apply-btn">
                <i class="fas fa-user-plus mr-2"></i> APPLY FOR POSITION
            </a>
        <?php endif; ?>
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