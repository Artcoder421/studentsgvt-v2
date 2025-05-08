<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_reg_no = $_SESSION['reg_no'];
$conn = new mysqli("localhost", "root", "", "studentsportal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = $conn->real_escape_string($_POST['message']);
    $reference_id = isset($_POST['reference_id']) ? (int)$_POST['reference_id'] : null;
    $timestamp = date('Y-m-d H:i:s');

    if ($reference_id) {
        // Get referenced message
        $refQuery = $conn->prepare("SELECT message FROM messages WHERE id = ?");
        $refQuery->bind_param("i", $reference_id);
        $refQuery->execute();
        $refResult = $refQuery->get_result();
        $refMessage = $refResult->fetch_assoc()['message'];
        $refQuery->close();
        
        $stmt = $conn->prepare("INSERT INTO messages (sender_reg_no, message, timestamp, reference_id, reference) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $user_reg_no, $message, $timestamp, $reference_id, $refMessage);
    } else {
        $stmt = $conn->prepare("INSERT INTO messages (sender_reg_no, message, timestamp) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user_reg_no, $message, $timestamp);
    }
    $stmt->execute();
    $stmt->close();
}

// Fetch messages with user details
$messagesQuery = "SELECT m.*, u.first_name, u.last_name, u.year_joined, u.course, u.profile_pic 
                 FROM messages m 
                 JOIN students u ON m.sender_reg_no = u.reg_no 
                 ORDER BY m.timestamp ASC";
$messagesResult = $conn->query($messagesQuery);

function formatMessageDate($timestamp) {
    $date = new DateTime($timestamp);
    $now = new DateTime();

    $interval = $now->diff($date);
    if ($interval->d == 0) {
        return "Today " . $date->format('H:i');
    } elseif ($interval->d == 1) {
        return "Yesterday " . $date->format('H:i');
    } elseif ($interval->y == 0) {
        return $date->format('d M');
    } else {
        return $date->format('d M Y');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Chat | SONIT</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #003366;
            --secondary-blue: #005b96;
            --accent-gold: #d4af37;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
            --chat-bg: #f5f7fa;
        }
        
        body {
            background-color: var(--light-gray);
            color: var(--dark-gray);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 70px;
            height: 100vh;
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
            height: calc(100vh - 70px);
            display: flex;
            flex-direction: column;
        }
        
        .toggle-btn {
            background: transparent;
            border: none;
            color: white;
            font-size: 22px;
            cursor: pointer;
            margin-right: 15px;
        }
        
        .chat-container {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin: 20px;
        }
        
        .chat-header {
            background-color: var(--primary-blue);
            color: white;
            padding: 15px 20px;
            text-align: center;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        
        .chat-box {
            flex-grow: 1;
            overflow-y: auto;
            padding: 20px;
            background-color: var(--chat-bg);
        }
        
        .message-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .message-box {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            word-wrap: break-word;
            position: relative;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .message-left {
            background-color: white;
            align-self: flex-start;
            border-top-left-radius: 5px;
            margin-right: auto;
            border: 1px solid #e1e5eb;
        }
        
        .message-right {
            background-color: var(--secondary-blue);
            color: white;
            align-self: flex-end;
            border-top-right-radius: 5px;
            margin-left: auto;
        }
        
        .message-header {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
            border: 2px solid var(--accent-gold);
        }
        
        .user-info {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .user-meta {
            font-size: 0.8rem;
            color: #6c757d;
            margin-left: 5px;
        }
        
        .message-right .user-meta {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .message-content {
            margin-bottom: 5px;
            line-height: 1.4;
        }
        
        .message-time {
            font-size: 0.75rem;
            text-align: right;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .message-right .message-time {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .reply-indicator {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 5px;
            padding-left: 10px;
            border-left: 3px solid var(--accent-gold);
        }
        
        .chat-input-container {
            padding: 15px 20px;
            background-color: white;
            border-top: 1px solid #e1e5eb;
            display: flex;
            align-items: center;
        }
        
        .chat-input {
            flex-grow: 1;
            border-radius: 20px;
            padding: 10px 20px;
            border: 1px solid #ddd;
            outline: none;
            transition: all 0.3s;
        }
        
        .chat-input:focus {
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 0.2rem rgba(0, 91, 150, 0.1);
        }
        
        .send-btn {
            background-color: var(--secondary-blue);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin-left: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .send-btn:hover {
            background-color: var(--primary-blue);
        }
        
        .chat-box::-webkit-scrollbar {
            width: 8px;
        }
        
        .chat-box::-webkit-scrollbar-thumb {
            background-color: #c1c1c1;
            border-radius: 4px;
        }
        
        .chat-box::-webkit-scrollbar-track {
            background-color: #f1f1f1;
        }
        
        @media (max-width: 768px) {
            .message-box {
                max-width: 85%;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-comments"></i> SONIT Community
        </a>
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
        <div class="chat-container">
            <div class="chat-header">
                <h4><i class="fas fa-users mr-2"></i>Community Chat</h4>
                <p class="mb-0 small">Connect with fellow students</p>
            </div>
            
            <div class="chat-box" id="chat-box">
                <?php while ($msg = $messagesResult->fetch_assoc()): ?>
                    <div class="message-container">
                        <?php if ($msg['reference']): ?>
                            <div class="reply-indicator">
                                <i class="fas fa-reply mr-1"></i>
                                Replying to: <?php echo htmlspecialchars($msg['reference']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="message-box <?php echo ($msg['sender_reg_no'] == $user_reg_no) ? 'message-right' : 'message-left'; ?>" 
                             data-id="<?php echo $msg['id']; ?>" 
                             onclick="replyMessage(<?php echo $msg['id']; ?>, '<?php echo htmlspecialchars(addslashes($msg['message'])); ?>')">
                            
                            <div class="message-header">
                                <?php if ($msg['sender_reg_no'] != $user_reg_no): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($msg['profile_pic']); ?>" alt="Profile" class="user-avatar">
                                <?php endif; ?>
                                
                                <div>
                                    <span class="user-info"><?php echo htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']); ?></span>
                                    <span class="user-meta"><?php echo htmlspecialchars($msg['course'] . ' · ' . $msg['year_joined']); ?></span>
                                </div>
                            </div>
                            
                            <div class="message-content">
                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                            </div>
                            
                            <div class="message-time">
                                <?php echo formatMessageDate($msg['timestamp']); ?>
                                <?php if ($msg['sender_reg_no'] == $user_reg_no): ?>
                                    <i class="fas fa-check ml-1"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <form id="chat-form" method="POST" class="chat-input-container">
                <input type="hidden" name="reference_id" id="reference_id" value="">
                <input type="text" class="form-control chat-input" id="message" name="message" placeholder="Type your message here..." required>
                <button type="submit" class="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
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

        function replyMessage(messageId, messageText) {
            document.getElementById("reference_id").value = messageId;
            document.getElementById("message").value = "Replying to: " + messageText;
            document.getElementById("message").focus();
        }

        // Auto-scroll to bottom of chat
        const chatBox = document.getElementById("chat-box");
        chatBox.scrollTop = chatBox.scrollHeight;

        // Keep scroll at bottom when new messages arrive
        const observer = new MutationObserver(function(mutations) {
            chatBox.scrollTop = chatBox.scrollHeight;
        });
        
        observer.observe(chatBox, {
            childList: true,
            subtree: true
        });

        // Handle form submission with AJAX
        document.getElementById("chat-form").addEventListener("submit", function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    // Reload the page to show new message
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>