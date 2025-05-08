<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['reg_no'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$reg_no = $_SESSION['reg_no'];
$position = isset($_GET['position']) ? $_GET['position'] : '';

// Validate position parameter
if (!in_array($position, ['president', 'pr'])) {
    header("Location: election.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "studentsportal");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if election is enabled
$electionEnabledQuery = "SELECT status FROM settings WHERE id = 1";
$electionEnabledResult = $conn->query($electionEnabledQuery);
$electionEnabled = $electionEnabledResult->fetch_assoc()['status'];

if (!$electionEnabled) {
    $conn->close();
    header("Location: election.php?error=election_disabled");
    exit();
}

// Check if user has already voted for this position
$votedCheckQuery = "";
if ($position === 'president') {
    $votedCheckQuery = "SELECT id FROM president_votes WHERE voter_id = '$user_id'";
} else {
    $votedCheckQuery = "SELECT id FROM pr_votes WHERE voter_id = '$user_id'";
}

$votedCheckResult = $conn->query($votedCheckQuery);
if ($votedCheckResult->num_rows > 0) {
    $conn->close();
    header("Location: election.php?error=already_voted");
    exit();
}

// Fetch student course
$courseQuery = "SELECT course FROM students WHERE id = '$user_id' LIMIT 1";
$courseResult = $conn->query($courseQuery);
$student = $courseResult->fetch_assoc();
$studentCourse = $student['course'];

// Fetch candidates based on position
$candidatesQuery = "";
if ($position === 'president') {
    $candidatesQuery = "SELECT * FROM contestants WHERE position = 'President' AND status = 'Accepted'";
} else {
    $candidatesQuery = "SELECT * FROM contestants WHERE position = 'Parliament Representative' AND course = '$studentCourse' AND status = 'Accepted'";
}

$candidatesResult = $conn->query($candidatesQuery);
$hasCandidates = $candidatesResult->num_rows > 0;

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidate_id'])) {
    $candidate_id = intval($_POST['candidate_id']);
    
    // Verify candidate exists and is valid for this position
    $verifyCandidateQuery = "";
    if ($position === 'president') {
        $verifyCandidateQuery = "SELECT id FROM contestants WHERE id = $candidate_id AND position = 'President' AND status = 'Accepted'";
    } else {
        $verifyCandidateQuery = "SELECT id FROM contestants WHERE id = $candidate_id AND position = 'Parliament Representative' AND course = '$studentCourse' AND status = 'Accepted'";
    }
    
    $verifyResult = $conn->query($verifyCandidateQuery);
    if ($verifyResult->num_rows === 0) {
        $conn->close();
        header("Location: poll.php?position=$position&error=invalid_candidate");
        exit();
    }
    
    // Insert vote into appropriate table
    if ($position === 'president') {
        $insertQuery = "INSERT INTO president_votes (voter_id, reg_no, candidate_id, voted_at) VALUES (?, ?, ?, NOW())";
    } else {
        $insertQuery = "INSERT INTO pr_votes (voter_id, reg_no, candidate_id, course, voted_at) VALUES (?, ?, ?, ?, NOW())";
    }
    
    $stmt = $conn->prepare($insertQuery);
    
    if ($position === 'president') {
        $stmt->bind_param("isi", $user_id, $reg_no, $candidate_id);
    } else {
        $stmt->bind_param("isss", $user_id, $reg_no, $candidate_id, $studentCourse);
    }
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: election.php?success=voted");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: poll.php?position=$position&error=vote_failed");
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SONIT Election Poll</title>
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
        
        .vote-btn {
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
        
        .vote-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            color: white;
        }
        
        .vote-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
        
        .radio-container {
            display: block;
            position: relative;
            padding-left: 35px;
            margin-bottom: 12px;
            cursor: pointer;
            font-size: 1rem;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            text-align: left;
            margin-top: 15px;
        }
        
        .radio-container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }
        
        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 25px;
            width: 25px;
            background-color: #eee;
            border-radius: 50%;
        }
        
        .radio-container:hover input ~ .checkmark {
            background-color: #ccc;
        }
        
        .radio-container input:checked ~ .checkmark {
            background-color: var(--primary-blue);
        }
        
        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }
        
        .radio-container input:checked ~ .checkmark:after {
            display: block;
        }
        
        .radio-container .checkmark:after {
            top: 9px;
            left: 9px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: white;
        }
        
        .confirmation-modal .modal-header {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .selected-candidate {
            font-weight: bold;
            color: var(--primary-blue);
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
            <i class="fas fa-vote-yea"></i> SONIT VOTING
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
            <h1>
                <?php echo $position === 'president' ? 'Presidential Election' : 'Parliament Representative Election'; ?>
                <?php if ($position === 'pr'): ?>
                    <span class="course-badge"><?php echo htmlspecialchars($studentCourse); ?></span>
                <?php endif; ?>
            </h1>
            <p class="lead">Select your preferred candidate and submit your vote</p>
        </header>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php
                switch ($_GET['error']) {
                    case 'invalid_candidate':
                        echo 'Invalid candidate selected. Please try again.';
                        break;
                    case 'vote_failed':
                        echo 'Failed to record your vote. Please try again.';
                        break;
                    default:
                        echo 'An error occurred. Please try again.';
                }
                ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <form id="voteForm" method="post">
            <div class="candidate-section">
                <?php if ($hasCandidates): ?>
                    <?php while ($candidate = $candidatesResult->fetch_assoc()): ?>
                    <div class="candidate-card">
                        <span class="position-badge">
                            <?php echo $position === 'president' ? 'PRESIDENT' : 'REPRESENTATIVE'; ?>
                        </span>
                        <img src="uploads/<?php echo htmlspecialchars($candidate['image']); ?>" alt="<?php echo htmlspecialchars($candidate['fullname']); ?>">
                        <h4><?php echo htmlspecialchars($candidate['fullname']); ?></h4>
                        <p>"<?php echo htmlspecialchars($candidate['motto']); ?>"</p>
                        
                        <label class="radio-container">
                            <input type="radio" name="candidate_id" value="<?php echo $candidate['id']; ?>" onchange="handleSelection(this)">
                            <span class="checkmark"></span>
                            Select this candidate
                        </label>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-candidates">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <p>No candidates available for this position.</p>
                        <a href="election.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Elections
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($hasCandidates): ?>
                <button type="button" id="submitVoteBtn" class="vote-btn" disabled onclick="confirmVote()">
                    <i class="fas fa-vote-yea mr-2"></i> SUBMIT VOTE
                </button>
            <?php endif; ?>
        </form>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade confirmation-modal" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Your Vote</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>You are about to vote for: <span id="selectedCandidateName" class="selected-candidate"></span></p>
                    <p>This action cannot be undone. Are you sure you want to proceed?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitVote()">Yes, Confirm Vote</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
        
        let selectedCandidateId = null;
        let selectedCandidateName = '';
        
        function handleSelection(radio) {
            if (radio.checked) {
                // Disable all other radio buttons
                document.querySelectorAll('input[type="radio"]').forEach(r => {
                    if (r !== radio) {
                        r.disabled = true;
                    }
                });
                
                // Enable submit button
                document.getElementById('submitVoteBtn').disabled = false;
                
                // Store selected candidate info
                selectedCandidateId = radio.value;
                selectedCandidateName = radio.closest('.candidate-card').querySelector('h4').textContent;
            }
        }
        
        function confirmVote() {
            if (selectedCandidateId) {
                document.getElementById('selectedCandidateName').textContent = selectedCandidateName;
                $('#confirmationModal').modal('show');
            }
        }
        
        function submitVote() {
            $('#confirmationModal').modal('hide');
            
            // Create a hidden input with the selected candidate ID
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'candidate_id';
            hiddenInput.value = selectedCandidateId;
            
            // Append to form and submit
            document.getElementById('voteForm').appendChild(hiddenInput);
            document.getElementById('voteForm').submit();
        }
    </script>
</body>
</html>