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

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM lost_found WHERE id=$id";
    if ($conn->query($sql)) {
        $message = '<div class="alert alert-success">Item deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting item: ' . $conn->error . '</div>';
    }
}

// Handle status change
if (isset($_GET['change_status'])) {
    $id = intval($_GET['change_status']);
    $current_status = $conn->real_escape_string($_GET['current_status']);
    $new_status = ($current_status == 'Lost') ? 'Found' : 'Lost';
    
    $sql = "UPDATE lost_found SET status='$new_status' WHERE id=$id";
    if ($conn->query($sql)) {
        $message = '<div class="alert alert-success">Status updated successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error updating status: ' . $conn->error . '</div>';
    }
}

// Fetch all items
$sql = "SELECT * FROM lost_found ORDER BY date_posted DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Lost & Found | SONIT</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
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
            padding-top: 80px;
            font-family: 'Roboto', sans-serif;
        }
        
        .navbar {
            background-color: var(--primary-blue);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-bottom: 3px solid var(--accent-gold);
        }
        
        #sidebar {
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
            min-height: calc(100vh - 70px);
        }
        
        .sidebar-visible #sidebar {
            left: 0;
        }
        
        .sidebar-visible #content {
            margin-left: 250px;
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
        
        .btn-admin {
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-blue));
            border: none;
            padding: 10px 20px;
            font-weight: 500;
            margin-bottom: 30px;
        }
        
        .btn-admin:hover {
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
        
        .admin-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        
        .admin-actions .btn {
            flex: 1;
            margin: 0 5px;
        }
        
        .btn-status {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-status:hover {
            background-color: #e0a800;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-search"></i> Admin Lost & Found
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
        <div class="container">
            <?php if (isset($message)) echo $message; ?>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-search mr-2"></i>Manage Lost & Found</h2>
                <a href="adminlost&found.php" class="btn btn-admin">
                    <i class="fas fa-plus-circle mr-2"></i>Add New Item
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
                                        <p><i class="fas fa-user"></i> <strong>Posted by:</strong> <?php echo htmlspecialchars($row['posted_by']); ?></p>
                                        <p><i class="fas fa-phone"></i> <strong>Contact:</strong> <?php echo htmlspecialchars($row['contact']); ?></p>
                                        <p><i class="far fa-clock"></i> <small>Posted on <?php echo date('M j, Y g:i A', strtotime($row['date_posted'])); ?></small></p>
                                    </div>
                                    
                                    <div class="admin-actions">
                                        <a href="edit_lost_item.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="lost_found.php?change_status=<?= $row['id'] ?>&current_status=<?= $row['status'] ?>" class="btn btn-status btn-sm">
                                            <i class="fas fa-sync-alt"></i> Status
                                        </a>
                                        <a href="lost_found.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
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
                            <a href="add_lost_item.php" class="btn btn-admin mt-3">
                                <i class="fas fa-plus-circle mr-2"></i>Add First Item
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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