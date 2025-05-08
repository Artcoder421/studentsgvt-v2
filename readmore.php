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

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $comment = $conn->real_escape_string($_POST['comment']);
    $announcement_id = (int)$_POST['announcement_id'];
    $reg_no = $_SESSION['reg_no'];
    $first_name = $_SESSION['first_name'];
    $date_posted = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO announcement_comments (announcement_id, reg_no, user_name, comment, date_posted) VALUES ( ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $announcement_id, $reg_no, $first_name, $comment, $date_posted);
    $stmt->execute();
    $stmt->close();
    
    // Redirect to first page to show new comment
    header("Location: readmore.php?id=$announcement_id");
    exit();
}

// Fetch announcement by ID
$id = $_GET['id'] ?? 0;
$sql = "SELECT * FROM announcements WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$announcement = $result->fetch_assoc();

if (!$announcement) {
    header("Location: announcements.php");
    exit();
}

// Comment pagination
$commentsPerPage = 5;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $commentsPerPage;

// Fetch total comments count
$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM announcement_comments WHERE announcement_id = ?");
$countStmt->bind_param("i", $id);
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalComments = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalComments / $commentsPerPage);

// Fetch paginated comments
$sql = "SELECT reg_no, first_name, comment, date_posted FROM announcement_comments 
        WHERE announcement_id = ? 
        ORDER BY date_posted DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $id, $commentsPerPage, $offset);
$stmt->execute();
$comments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($announcement['title']); ?> | SONIT</title>
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
            padding-bottom: 200px; /* Space for fixed comment form */
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
            padding: 15px 20px;
            color: var(--dark-gray);
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: all 0.2s;
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
        
        .announcement-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .announcement-title {
            color: var(--primary-blue);
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .announcement-meta {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .announcement-meta i {
            margin-right: 5px;
            color: var(--secondary-blue);
        }
        
        .announcement-content {
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        .carousel {
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .carousel-item img {
            height: 400px;
            object-fit: cover;
        }
        
        .comments-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 30px;
        }
        
        .comments-title {
            color: var(--primary-blue);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .comments-container {
            max-height: 500px;
            overflow-y: auto;
            margin-bottom: 20px;
            padding-right: 10px;
        }
        
        .comment-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 3px solid var(--accent-gold);
        }
        
        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .comment-user {
            font-weight: 600;
            color: var(--primary-blue);
        }
        
        .comment-meta {
            font-size: 0.8rem;
            color: #6c757d;
            margin-left: 10px;
        }
        
        .comment-text {
            margin-bottom: 10px;
        }
        
        .comment-footer {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .comment-form-container {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: white;
            padding: 20px;
            border-top: 1px solid #eee;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary-blue);
        }
        
        .btn-submit {
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-blue));
            border: none;
            padding: 10px 25px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background: linear-gradient(to right, var(--secondary-blue), var(--primary-blue));
            transform: translateY(-2px);
        }
        
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .page-link {
            color: var(--primary-blue);
        }
        
        .comments-container::-webkit-scrollbar {
            width: 8px;
        }
        
        .comments-container::-webkit-scrollbar-thumb {
            background-color: #c1c1c1;
            border-radius: 4px;
        }
        
        .comments-container::-webkit-scrollbar-track {
            background-color: #f1f1f1;
        }
        
        @media (max-width: 768px) {
            .carousel-item img {
                height: 250px;
            }
            
            .announcement-container,
            .comments-section {
                padding: 20px;
            }
            
            body {
                padding-bottom: 250px; /* More space for mobile */
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-bullhorn"></i> Announcement Details
        </a>
    </nav>
    
    <div class="sidebar" id="sidebar">
        <a href="homepage.php"><i class="fas fa-home"></i> Home</a>
        <a href="announcements.php"><i class="fas fa-bullhorn"></i> Announcements</a>
        <a href="election.php"><i class="fas fa-vote-yea"></i> Elections</a>
        <a href="lost.php"><i class="fas fa-search"></i> Lost & Found</a>
        <a href="feedback.php"><i class="fas fa-comment-alt"></i> Feedback</a>
        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="content" id="content">
        <div class="announcement-container">
            <h1 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h1>
            
            <div class="announcement-meta">
                <i class="far fa-calendar-alt"></i>
                Posted on <?php echo date('F j, Y \a\t g:i A', strtotime($announcement['date_posted'])); ?>
            </div>
            <?php 
// Assuming the 'images' field is stored as a comma-separated string
$images = explode(',', $announcement['images']); 
if (!empty($images)): ?>
    <div id="announcementCarousel" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($images as $index => $image): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <img src="uploads/<?php echo htmlspecialchars($image); ?>" class="d-block w-100" alt="Announcement Image">
                </div>
            <?php endforeach; ?>
        </div>
        <a class="carousel-control-prev" href="#announcementCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </a>
        <a class="carousel-control-next" href="#announcementCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </a>
    </div>
<?php endif; ?>

            
            <div class="announcement-content">
                <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
            </div>
        </div>
        
        <div class="comments-section">
            <h3 class="comments-title">
                <i class="fas fa-comments mr-2"></i>
                Comments 
                <span class="badge badge-primary"><?php echo $totalComments; ?></span>
            </h3>
            
            <div class="comments-container">
                <?php if ($comments->num_rows > 0): ?>
                    <?php while ($comment = $comments->fetch_assoc()): ?>
                        <div class="comment-card">
                            <div class="comment-header">
                                <span class="comment-user"><?php echo htmlspecialchars($comment['first_name']); ?></span>
                                <span class="comment-meta">
                                    <?php echo date('M j, Y g:i A', strtotime($comment['date_posted'])); ?>
                                </span>
                            </div>
                            <div class="comment-text">
                                <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                            </div>
                            <div class="comment-footer">
                                Reg: <?php echo substr(htmlspecialchars($comment['reg_no']), 0, 3) . '...' . substr(htmlspecialchars($comment['reg_no']), -3); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        No comments yet. Be the first to comment!
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Comments pagination">
                    <ul class="pagination">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="readmore.php?id=<?php echo $id; ?>&page=<?php echo $currentPage - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                <a class="page-link" href="readmore.php?id=<?php echo $id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="readmore.php?id=<?php echo $id; ?>&page=<?php echo $currentPage + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Fixed comment form at bottom -->
    <div class="comment-form-container">
        <form method="POST" action="">
            <input type="hidden" name="announcement_id" value="<?php echo $id; ?>">
            <div class="form-group">
                <label for="comment" class="form-label">Leave a Comment</label>
                <textarea class="form-control" id="comment" name="comment" rows="3" required 
                          placeholder="Share your thoughts..."></textarea>
            </div>
            <button type="submit" class="btn btn-submit text-white">
                <i class="fas fa-paper-plane mr-2"></i>Post Comment
            </button>
        </form>
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

        // Auto-scroll to comment form if coming from submission
        window.addEventListener('DOMContentLoaded', (event) => {
            if (window.location.hash === '#comment-form') {
                document.getElementById('comment').scrollIntoView();
                document.getElementById('comment').focus();
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>