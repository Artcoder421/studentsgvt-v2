<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "studentsportal");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM lost_found ORDER BY date_posted DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost & Found Items | SONIT</title>
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
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            height: 100%;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        
        .card-img-top {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .card-title {
            color: var(--primary-blue);
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-found {
            background-color: #28a745;
            color: white;
        }
        
        .status-lost {
            background-color: #dc3545;
            color: white;
        }
        
        .report-btn {
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-blue));
            border: none;
            padding: 10px 20px;
            font-weight: 500;
            margin-bottom: 30px;
        }
        
        .report-btn:hover {
            background: linear-gradient(to right, var(--secondary-blue), var(--primary-blue));
        }
        
        .item-meta {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .item-meta i {
            width: 20px;
            color: var(--secondary-blue);
        }
        
        .empty-state {
            text-align: center;
            padding: 50px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--secondary-blue);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-search"></i> SONIT Lost & Found
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
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-search mr-2"></i>Lost & Found Items</h2>
                <a href="lost&found.php" class="btn btn-primary report-btn">
                    <i class="fas fa-plus-circle mr-2"></i>Report Item
                </a>
            </div>
            
            <div class="row">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card">
                                <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['item_name']); ?>">
                                <span class="status-badge <?php echo ($row['status'] == 'Found') ? 'status-found' : 'status-lost'; ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($row['item_name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                                    
                                    <div class="item-meta">
                                        <p><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                                        <p><i class="fas fa-user"></i> <strong>Posted by:</strong> <?php echo htmlspecialchars($row['reported_by']); ?></p>
                                        <p><i class="fas fa-phone"></i> <strong>Contact:</strong> <?php echo htmlspecialchars($row['contact']); ?></p>
                                        <p><i class="far fa-clock"></i> <small>Posted on <?php echo date('M j, Y g:i A', strtotime($row['date_posted'])); ?></small></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <h3>No Items Reported</h3>
                            <p class="lead">There are currently no lost or found items in the system.</p>
                            <a href="lost&found.php" class="btn btn-primary mt-3">
                                <i class="fas fa-plus-circle mr-2"></i>Report First Item
                            </a>
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