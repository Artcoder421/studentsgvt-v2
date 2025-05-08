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

// Handle actions
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mark as responded
    if (isset($_POST['mark_responded'])) {
        $id = intval($_POST['id']);
        $sql = "UPDATE feedback SET status='Responded', admin_id={$_SESSION['user_id']} WHERE id=$id";
        if ($conn->query($sql)) {
            $message = '<div class="alert alert-success">Feedback marked as responded!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error updating feedback: ' . $conn->error . '</div>';
        }
    }
    
    // Send response
    if (isset($_POST['send_response'])) {
        $id = intval($_POST['id']);
        $response = $conn->real_escape_string($_POST['response']);
        $sql = "UPDATE feedback SET response='$response', status='Responded', admin_id={$_SESSION['user_id']}, response_date=NOW() WHERE id=$id";
        if ($conn->query($sql)) {
            $message = '<div class="alert alert-success">Response sent successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error sending response: ' . $conn->error . '</div>';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM feedback WHERE id=$id";
    if ($conn->query($sql)) {
        $message = '<div class="alert alert-success">Feedback deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting feedback: ' . $conn->error . '</div>';
    }
}

// Fetch all feedback
$sql = "SELECT f.*, s.email, s.phone 
        FROM feedback f
        LEFT JOIN students s ON f.user_id = s.id
        ORDER BY f.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management | Admin Portal</title>
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
        
        .feedback-container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-top: 4px solid var(--secondary-blue);
        }
        
        .feedback-card {
            border-left: 4px solid var(--primary-blue);
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .feedback-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .feedback-header {
            background-color: var(--light-gray);
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .feedback-body {
            padding: 20px;
        }
        
        .feedback-footer {
            padding: 15px;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        
        .status-responded {
            background-color: #28a745;
            color: white;
        }
        
        .user-info {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .user-info i {
            width: 20px;
            color: var(--secondary-blue);
        }
        
        .response-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px dashed #ddd;
        }
        
        .admin-response {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-top: 15px;
            border-left: 3px solid var(--accent-gold);
        }
        
        .toggle-btn {
            background: transparent;
            border: none;
            color: white;
            font-size: 22px;
            cursor: pointer;
            margin-right: 15px;
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
            <i class="fas fa-comments"></i> Feedback Management
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
            <?php echo $message; ?>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-comment-dots"></i> Student Feedback</h2>
                <div class="btn-group">
                    <button class="btn btn-outline-primary" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            
            <div class="feedback-container">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($feedback = $result->fetch_assoc()): ?>
                        <div class="feedback-card">
                            <div class="feedback-header">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($feedback['name']) ?></h5>
                                    <div class="user-info">
                                        <span><i class="fas fa-id-card"></i> <?= htmlspecialchars($feedback['reg_no']) ?></span>
                                        <span class="mx-2">|</span>
                                        <span><i class="far fa-clock"></i> <?= date('M j, Y g:i A', strtotime($feedback['created_at'])) ?></span>
                                    </div>
                                </div>
                                <span class="status-badge <?= ($feedback['status'] == 'Pending') ? 'status-pending' : 'status-responded' ?>">
                                    <?= htmlspecialchars($feedback['status']) ?>
                                </span>
                            </div>
                            
                            <div class="feedback-body">
                                <p><?= nl2br(htmlspecialchars($feedback['feedback'])) ?></p>
                                
                                <?php if ($feedback['status'] == 'Responded' && !empty($feedback['response'])): ?>
                                    <div class="admin-response">
                                        <h6><i class="fas fa-reply"></i> Admin Response</h6>
                                        <p class="mb-1"><?= nl2br(htmlspecialchars($feedback['response'])) ?></p>
                                        <small class="text-muted">
                                            Responded on <?= date('M j, Y g:i A', strtotime($feedback['response_date'])) ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($feedback['status'] == 'Pending'): ?>
                                    <div class="response-form">
                                        <form method="POST">
                                            <input type="hidden" name="id" value="<?= $feedback['id'] ?>">
                                            <div class="form-group">
                                                <label>Response</label>
                                                <textarea name="response" class="form-control" rows="3" placeholder="Type your response here..."></textarea>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <a href="mailto:<?= htmlspecialchars($feedback['email']) ?>" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-envelope"></i> Email
                                                    </a>
                                                    <?php if (!empty($feedback['phone'])): ?>
                                                        <a href="tel:<?= htmlspecialchars($feedback['phone']) ?>" class="btn btn-outline-success btn-sm ml-2">
                                                            <i class="fas fa-phone"></i> Call
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <button type="submit" name="send_response" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-paper-plane"></i> Send Response
                                                    </button>
                                                    <button type="submit" name="mark_responded" class="btn btn-secondary btn-sm ml-2">
                                                        <i class="fas fa-check"></i> Mark as Responded
                                                    </button>
                                                    <a href="feedbacks.php?delete=<?= $feedback['id'] ?>" class="btn btn-danger btn-sm ml-2" onclick="return confirm('Are you sure you want to delete this feedback?');">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="feedback-footer">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    <?php if ($feedback['status'] == 'Responded'): ?>
                                        Responded by Admin on <?= date('M j, Y', strtotime($feedback['response_date'])) ?>
                                    <?php else: ?>
                                        Awaiting response
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comment-slash"></i>
                        <h3>No Feedback Submitted</h3>
                        <p class="lead">There are currently no feedback submissions to review.</p>
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