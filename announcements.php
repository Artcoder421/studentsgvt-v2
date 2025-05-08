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

// Fetch announcements with pagination
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM announcements ORDER BY date_posted DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Get total count for pagination
$countQuery = $conn->query("SELECT COUNT(*) as total FROM announcements");
$totalAnnouncements = $countQuery->fetch_assoc()['total'];
$totalPages = ceil($totalAnnouncements / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | SONIT</title>
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
        
        .announcements-container {
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
        
        .announcement-card {
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border-left: 4px solid var(--secondary-blue);
            background-color: white;
        }
        
        .announcement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        
        .announcement-title {
            color: var(--primary-blue);
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .announcement-meta {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .announcement-meta i {
            margin-right: 5px;
            color: var(--secondary-blue);
        }
        
        .announcement-content {
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .read-more-btn {
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-blue));
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .read-more-btn:hover {
            background: linear-gradient(to right, var(--secondary-blue), var(--primary-blue));
            color: white;
            transform: translateY(-2px);
        }
        
        .carousel {
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .carousel-item img {
            height: 300px;
            object-fit: cover;
        }
        
        .carousel-control-prev,
        .carousel-control-next {
            width: 5%;
        }
        
        .no-announcements {
            text-align: center;
            padding: 40px;
            background-color: rgba(0, 0, 0, 0.03);
            border-radius: 8px;
        }
        
        .pagination {
            justify-content: center;
            margin-top: 30px;
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .page-link {
            color: var(--primary-blue);
        }
        
        @media (max-width: 768px) {
            .carousel-item img {
                height: 200px;
            }
            
            .announcement-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-bullhorn"></i> SONIT Announcements
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
        <a href="admin_feedback.php"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="chat_area.php"><i class="fas fa-comments"></i> Community Chat</a>
        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="content" id="content">
        <div class="announcements-container">
            <div class="page-header">
                <h2><i class="fas fa-bullhorn mr-2"></i>Latest Announcements</h2>
                <p class="text-muted">Stay updated with the latest news and events</p>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): 
                    $images = explode(',', $row['images']); 
                ?>
                    <div class="announcement-card">
                        <h4 class="announcement-title"><?php echo htmlspecialchars($row['title']); ?></h4>
                        <div class="announcement-meta">
                            <i class="far fa-calendar-alt"></i>
                            Posted on <?php echo date('F j, Y', strtotime($row['date_posted'])); ?>
                        </div>
                        
                        <?php if (!empty($images)): ?>
                            <div id="carousel<?php echo $row['id']; ?>" class="carousel slide" data-ride="carousel">
                                <div class="carousel-inner">
                                    <?php foreach ($images as $index => $image): ?>
                                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                            <img src="uploads/<?php echo htmlspecialchars($image); ?>" class="d-block w-100" alt="Announcement Image">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <a class="carousel-control-prev" href="#carousel<?php echo $row['id']; ?>" role="button" data-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                </a>
                                <a class="carousel-control-next" href="#carousel<?php echo $row['id']; ?>" role="button" data-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="announcement-content">
                            <p><?php echo nl2br(htmlspecialchars(substr($row['content'], 0, 200) . '...')); ?></p>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="readmore.php?id=<?php echo $row['id']; ?>" class="btn read-more-btn">
                                <i class="fas fa-book-reader mr-2"></i>Read More
                            </a>
                            <span class="text-muted small">
                                <i class="far fa-comment-alt"></i> 
                                <?php 
                                    $commentCount = $conn->query("SELECT COUNT(*) as count FROM announcement_comments WHERE announcement_id = {$row['id']}")->fetch_assoc()['count'];
                                    echo $commentCount . ' comment' . ($commentCount != 1 ? 's' : '');
                                ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Announcements pagination">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-announcements">
                    <i class="far fa-bell-slash fa-3x mb-3" style="color: var(--secondary-blue);"></i>
                    <h4>No Announcements Available</h4>
                    <p>There are currently no announcements posted.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
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