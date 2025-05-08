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

// Handle approve/decline actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $status = $_POST['action'] === 'approve' ? 'Accepted' : 'Declined';
    $conn->query("UPDATE contestants SET status='$status' WHERE id=$id");
}

// Fetch contestants grouped by position
$positions = ["President", "Parliament Representative", "General Secretary", "Treasurer"];
$contestants = [];

foreach ($positions as $position) {
    if ($position == "Parliament Representative") {
        $query = "SELECT * FROM contestants WHERE position = '$position' ORDER BY course ASC";
    } else {
        $query = "SELECT * FROM contestants WHERE position = '$position'";
    }
    $contestants[$position] = $conn->query($query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contestants</title>
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
            --success-green: #28a745;
            --danger-red: #dc3545;
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
        
        #sidebar {
            background-color: white;
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: -250px;
            padding-top: 80px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar-link {
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
        
        .sidebar-link:hover {
            background-color: rgba(0, 51, 102, 0.05);
            border-left: 4px solid var(--accent-gold);
        }
        
        .sidebar-link i {
            width: 25px;
            text-align: center;
            color: var(--secondary-blue);
        }
        
        #content {
            margin-left: 0;
            transition: margin-left 0.3s;
            padding: 30px;
        }
        
        .sidebar-visible #sidebar {
            left: 0;
        }
        
        .sidebar-visible #content {
            margin-left: 250px;
        }
        
        .table {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .table thead {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .table th {
            font-weight: 500;
        }
        
        .status-dot {
            height: 12px;
            width: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        
        .accepted {
            background-color: var(--success-green);
        }
        
        .declined {
            background-color: var(--danger-red);
        }
        
        .btn-success {
            background-color: var(--success-green);
            border-color: var(--success-green);
        }
        
        .btn-danger {
            background-color: var(--danger-red);
            border-color: var(--danger-red);
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.875rem;
            border-radius: 4px;
        }
        
        .toggle-btn {
            background: transparent;
            border: none;
            color: white;
            font-size: 22px;
            cursor: pointer;
            margin-right: 15px;
        }
        
        @media (max-width: 768px) {
            .sidebar-visible #content {
                margin-left: 0;
                position: relative;
                left: 250px;
                width: calc(100% - 250px);
            }
            
            .table-responsive {
                display: block;
                width: 100%;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-university"></i> ADMIN PORTAL
        </a>
    </nav>
    
    <div id="sidebar">
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
    
    <div id="content">
        <header>
            <h2>Manage Contestants</h2>
            <p class="lead">Review and approve election candidates</p>
        </header>
        
        <?php foreach ($contestants as $position => $data) : ?>
            <div class="mb-5">
                <h3><?php echo $position; ?></h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Full Name</th>
                                <th>Reg Number</th>
                                <th>Course</th>
                                <th>Level</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $data->fetch_assoc()) : ?>
                                <tr>
                                    <td>
                                        <?php
                                        $statusClass = ($row['status'] == 'Accepted') ? 'accepted' : (($row['status'] == 'Declined') ? 'declined' : '');
                                        echo "<span class='status-dot $statusClass'></span>";
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                    <td><?php echo $row['reg_number']; ?></td>
                                    <td><?php echo $row['course']; ?></td>
                                    <td><?php echo $row['level']; ?></td>
                                    <td>
                                        <form method="POST" style="display:inline-block;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                        <form method="POST" style="display:inline-block;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="action" value="decline" class="btn btn-danger btn-sm">Decline</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-visible');
            
            // Store sidebar state in localStorage
            const isVisible = document.body.classList.contains('sidebar-visible');
            localStorage.setItem('sidebarVisible', isVisible);
        }
        
        // Check for saved sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedState = localStorage.getItem('sidebarVisible');
            if (savedState === 'true') {
                document.body.classList.add('sidebar-visible');
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>