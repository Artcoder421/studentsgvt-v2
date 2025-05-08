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

// Handle form actions
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add new leader
    if (isset($_POST['add_leader'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $position = $conn->real_escape_string($_POST['position']);
        $bio = $conn->real_escape_string($_POST['bio']);
        
        // Handle image upload
        $picture = '';
        if ($_FILES['picture']['name']) {
            $target_dir = "uploads/";
            $target_file = $target_dir . time() . '_' . basename($_FILES['picture']['name']);
            if (move_uploaded_file($_FILES['picture']['tmp_name'], $target_file)) {
                $picture = basename($target_file);
            }
        }
        
        $sql = "INSERT INTO leaders (name, position, bio, picture) 
                VALUES ('$name', '$position', '$bio', '$picture')";
        if ($conn->query($sql)) {
            $message = '<div class="alert alert-success">Leader added successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error adding leader: ' . $conn->error . '</div>';
        }
    }
    
    // Update leader
    if (isset($_POST['update_leader'])) {
        $id = intval($_POST['id']);
        $name = $conn->real_escape_string($_POST['name']);
        $position = $conn->real_escape_string($_POST['position']);
        $bio = $conn->real_escape_string($_POST['bio']);
        
        // Handle image update
        $picture_update = '';
        if ($_FILES['picture']['name']) {
            $target_dir = "uploads/";
            $target_file = $target_dir . time() . '_' . basename($_FILES['picture']['name']);
            if (move_uploaded_file($_FILES['picture']['tmp_name'], $target_file)) {
                $picture_update = ", picture='" . basename($target_file) . "'";
            }
        }
        
        $sql = "UPDATE leaders SET 
                name='$name', 
                position='$position', 
                bio='$bio'
                $picture_update 
                WHERE id=$id";
        if ($conn->query($sql)) {
            $message = '<div class="alert alert-success">Leader updated successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error updating leader: ' . $conn->error . '</div>';
        }
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM leaders WHERE id=$id";
    if ($conn->query($sql)) {
        $message = '<div class="alert alert-success">Leader deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting leader: ' . $conn->error . '</div>';
    }
}

// Fetch all leaders
$leaders = $conn->query("SELECT * FROM leaders ORDER BY 
    CASE position 
        WHEN 'President' THEN 1
        WHEN 'Vice President' THEN 2
        WHEN 'Secretary' THEN 3
        WHEN 'Treasurer' THEN 4
        ELSE 5
    END, name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leaders | Admin Portal</title>
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
            font-family: 'Roboto', sans-serif;
            color: var(--dark-gray);
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
        
        .table-container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-top: 4px solid var(--secondary-blue);
        }
        
        .table th {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-blue);
            border-color: var(--secondary-blue);
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.875rem;
            border-radius: 4px;
        }
        
        .leader-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent-gold);
        }
        
        .modal-header {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .toggle-btn {
            background: transparent;
            border: none;
            color: white;
            font-size: 22px;
            cursor: pointer;
            margin-right: 15px;
        }
        
        .position-badge {
            background-color: var(--secondary-blue);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            display: inline-block;
        }
        
        @media (max-width: 768px) {
            .sidebar-visible #content {
                margin-left: 0;
                position: relative;
                left: 250px;
                width: calc(100% - 250px);
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-users-cog"></i> Manage Leaders
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
        <a href="lost_found.php" class="sidebar-link"><i class="fas fa-search"></i> Lost & Found</a>
        <a href="manage_calendar.php" class="sidebar-link"><i class="fas fa-calendar"></i> Manage Calendar</a>
        <a href="logout.php" class="sidebar-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div id="content">
        <div class="container">
            <?php echo $message; ?>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-tie"></i> Student Leaders Management</h2>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addLeaderModal">
                    <i class="fas fa-plus"></i> Add New Leader
                </button>
            </div>
            
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Bio</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($leaders->num_rows > 0): ?>
                                <?php while ($leader = $leaders->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($leader['picture'])): ?>
                                                <img src="uploads/<?= htmlspecialchars($leader['picture']) ?>" class="leader-img" alt="<?= htmlspecialchars($leader['name']) ?>">
                                            <?php else: ?>
                                                <img src="uploads/default.jpg" class="leader-img" alt="Default">
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($leader['name']) ?></td>
                                        <td><span class="position-badge"><?= htmlspecialchars($leader['position']) ?></span></td>
                                        <td><?= htmlspecialchars(substr($leader['bio'], 0, 50)) . (strlen($leader['bio']) > 50 ? '...' : '') ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editLeaderModal<?= $leader['id'] ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <a href="update_leaders.php?delete=<?= $leader['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this leader?');">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    
                                    <!-- Edit Leader Modal -->
                                    <div class="modal fade" id="editLeaderModal<?= $leader['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="editLeaderModalLabel<?= $leader['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editLeaderModalLabel<?= $leader['id'] ?>">Edit Leader</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form method="POST" enctype="multipart/form-data">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $leader['id'] ?>">
                                                        <div class="form-group">
                                                            <label>Name</label>
                                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($leader['name']) ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Position</label>
                                                            <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($leader['position']) ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Bio</label>
                                                            <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($leader['bio']) ?></textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Update Photo</label>
                                                            <input type="file" name="picture" class="form-control-file">
                                                            <?php if (!empty($leader['picture'])): ?>
                                                                <small class="text-muted">Current: <?= htmlspecialchars($leader['picture']) ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        <button type="submit" name="update_leader" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-info-circle fa-2x mb-3" style="color: var(--secondary-blue);"></i>
                                        <h4>No Leaders Found</h4>
                                        <p>Add new leaders using the button above</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Leader Modal -->
    <div class="modal fade" id="addLeaderModal" tabindex="-1" role="dialog" aria-labelledby="addLeaderModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addLeaderModalLabel">Add New Leader</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Position</label>
                            <input type="text" name="position" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Bio</label>
                            <textarea name="bio" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Photo</label>
                            <input type="file" name="picture" class="form-control-file" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="add_leader" class="btn btn-primary">Add Leader</button>
                    </div>
                </form>
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