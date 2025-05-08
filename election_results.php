<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Database connection
$conn = new mysqli("localhost", "root", "", "studentsportal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all courses that have PR elections
$coursesQuery = "SELECT DISTINCT course FROM contestants WHERE position = 'Parliament Representative' ORDER BY course";
$coursesResult = $conn->query($coursesQuery);

// Fetch presidential candidates with vote counts
$presidentQuery = "SELECT c.*, COUNT(pv.id) as vote_count 
                  FROM contestants c 
                  LEFT JOIN president_votes pv ON c.id = pv.candidate_id 
                  WHERE c.position = 'President' AND c.status = 'Accepted'
                  GROUP BY c.id 
                  ORDER BY vote_count DESC";
$presidentResult = $conn->query($presidentQuery);

// Store PR results by course
$prResults = [];
while ($course = $coursesResult->fetch_assoc()) {
    $courseName = $course['course'];
    $prQuery = "SELECT c.*, COUNT(prv.id) as vote_count 
               FROM contestants c 
               LEFT JOIN pr_votes prv ON c.id = prv.candidate_id 
               WHERE c.position = 'Parliament Representative' AND c.course = '$courseName' AND c.status = 'Accepted'
               GROUP BY c.id 
               ORDER BY vote_count DESC";
    $prResults[$courseName] = $conn->query($prQuery);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SONIT Election Results</title>
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
        
        .votes-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--primary-blue);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
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
        
        .rank-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--accent-gold);
            color: var(--primary-blue);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1rem;
        }
        
        .winner-card {
            border: 3px solid var(--accent-gold);
            transform: scale(1.02);
            position: relative;
        }
        
        .winner-card::after {
            content: 'WINNER';
            position: absolute;
            top: -15px;
            right: -15px;
            background: var(--accent-gold);
            color: var(--primary-blue);
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.8rem;
            transform: rotate(15deg);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
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
            <i class="fas fa-chart-bar"></i> ELECTION RESULTS
        </a>
       
    </nav>
    
    <div class="sidebar" id="sidebar">
    <h4 class="text-center mb-4" style="color: var(--primary-blue);">Admin Panel</h4>
        <hr>
        <a href="admin_dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> Dashboard</a>
        <a href="admin_announcement.php" class="sidebar-link"><i class="fas fa-bullhorn"></i> Post Announcements</a>
        <a href="admin_election.php" class="sidebar-link"><i class="fas fa-poll"></i> Manage Elections</a>
        <a href="update_contacts.php" class="sidebar-link"><i class="fas fa-address-book"></i> Update Contacts</a>
        <a href="update_leaders.php" class="sidebar-link"><i class="fas fa-users"></i> Update Leaders</a>
        <a href="admin_feedbacks.php" class="sidebar-link"><i class="fas fa-comment"></i> Feedbacks</a>
        <a href="admin_lost_found.php" class="sidebar-link"><i class="fas fa-search"></i> Lost & Found</a>
        <a href="manage_calendar.php" class="sidebar-link"><i class="fas fa-calendar"></i> Manage Calendar</a>
        <a href="logout.php" class="sidebar-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="content" id="content">
        <header>
            <h1>Election Results</h1>
            <p class="lead">Official voting results for SONIT student elections</p>
        </header>
        
        <!-- Presidential Results -->
        <section>
            <div class="section-title">
                <i class="fas fa-user-tie"></i>
                <h2>Presidential Election Results</h2>
            </div>
            <div class="candidate-section">
                <?php if ($presidentResult->num_rows > 0): ?>
                    <?php 
                    $rank = 1;
                    while ($candidate = $presidentResult->fetch_assoc()): 
                        $isWinner = $rank === 1;
                    ?>
                    <div class="candidate-card <?php echo $isWinner ? 'winner-card' : ''; ?>">
                        <span class="rank-badge"><?php echo $rank; ?></span>
                        <span class="position-badge">PRESIDENT</span>
                        <img src="uploads/<?php echo htmlspecialchars($candidate['image']); ?>" alt="<?php echo htmlspecialchars($candidate['fullname']); ?>">
                        <h4><?php echo htmlspecialchars($candidate['fullname']); ?></h4>
                        <p>"<?php echo htmlspecialchars($candidate['motto']); ?>"</p>
                        <span class="votes-badge">
                            <?php echo $candidate['vote_count']; ?> vote<?php echo $candidate['vote_count'] != 1 ? 's' : ''; ?>
                        </span>
                    </div>
                    <?php 
                    $rank++;
                    endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        No presidential candidates or votes recorded yet.
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Parliament Representative Results by Course -->
        <?php foreach ($prResults as $course => $result): ?>
            <?php if ($result->num_rows > 0): ?>
            <section>
                <div class="section-title">
                    <i class="fas fa-users"></i>
                    <h2>Parliament Representatives <span class="course-badge"><?php echo htmlspecialchars($course); ?></span></h2>
                </div>
                <div class="candidate-section">
                    <?php 
                    $rank = 1;
                    $result->data_seek(0); // Reset pointer
                    while ($candidate = $result->fetch_assoc()): 
                        $isWinner = $rank === 1;
                    ?>
                    <div class="candidate-card <?php echo $isWinner ? 'winner-card' : ''; ?>">
                        <span class="rank-badge"><?php echo $rank; ?></span>
                        <span class="position-badge">REPRESENTATIVE</span>
                        <img src="uploads/<?php echo htmlspecialchars($candidate['image']); ?>" alt="<?php echo htmlspecialchars($candidate['fullname']); ?>">
                        <h4><?php echo htmlspecialchars($candidate['fullname']); ?></h4>
                        <p>"<?php echo htmlspecialchars($candidate['motto']); ?>"</p>
                        <span class="votes-badge">
                            <?php echo $candidate['vote_count']; ?> vote<?php echo $candidate['vote_count'] != 1 ? 's' : ''; ?>
                        </span>
                    </div>
                    <?php 
                    $rank++;
                    endwhile; ?>
                </div>
            </section>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (count($prResults) === 0 || array_sum(array_map(function($r) { return $r->num_rows; }, $prResults)) === 0): ?>
            <section>
                <div class="alert alert-info">
                    No parliament representative candidates or votes recorded yet.
                </div>
            </section>
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