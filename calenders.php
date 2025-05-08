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

// Fetch events (public events + user's private events)
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM calendar_events 
        WHERE is_public = TRUE OR created_by = $user_id
        ORDER BY start_datetime ASC";
$result = $conn->query($sql);
$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

// Fetch user's attendance status for events
$attendance = [];
$attendance_sql = "SELECT event_id, status FROM event_attendance WHERE user_id = $user_id";
$attendance_result = $conn->query($attendance_sql);
while ($row = $attendance_result->fetch_assoc()) {
    $attendance[$row['event_id']] = $row['status'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Calendar | SONIT</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
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
        
        .calendar-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 20px;
        }
        
        .fc-event {
            cursor: pointer;
        }
        
        .event-academic {
            background-color: #007bff;
            border-color: #006fe6;
        }
        
        .event-social {
            background-color: #28a745;
            border-color: #23923d;
        }
        
        .event-holiday {
            background-color: #ffc107;
            border-color: #e6b100;
            color: #212529;
        }
        
        .event-other {
            background-color: #6c757d;
            border-color: #626a71;
        }
        
        .event-modal .modal-header {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .attendance-buttons .btn {
            margin-right: 5px;
        }
        
        @media (max-width: 768px) {
            .calendar-container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <a class="navbar-brand" href="#">
            <i class="fas fa-calendar-alt"></i> SONIT Calendar
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
        <a href="chat_area.php"><i class="fas fa-comments"></i> Community Chat</a>
        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    
    <div class="content" id="content">
        <div class="container">
            <h2 class="mb-4"><i class="fas fa-calendar-alt mr-2"></i>Academic Calendar</h2>
            
            <div class="calendar-container">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade event-modal" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Event Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h4 id="eventTitle"></h4>
                    <p><strong>Type:</strong> <span id="eventType"></span></p>
                    <p><strong>Date & Time:</strong> <span id="eventDateTime"></span></p>
                    <p><strong>Location:</strong> <span id="eventLocation"></span></p>
                    <p id="eventDescription"></p>
                    <div class="attendance-section mt-4">
                        <h6>Your Attendance:</h6>
                        <div class="attendance-buttons" id="attendanceButtons">
                            <button class="btn btn-sm btn-success attendance-btn" data-status="going">
                                <i class="fas fa-check"></i> Going
                            </button>
                            <button class="btn btn-sm btn-warning attendance-btn" data-status="maybe">
                                <i class="fas fa-question"></i> Maybe
                            </button>
                            <button class="btn btn-sm btn-danger attendance-btn" data-status="not_going">
                                <i class="fas fa-times"></i> Not Going
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
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
        
        // Initialize calendar
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                events: [
                    <?php foreach ($events as $event): ?>
                    {
                        id: '<?= $event['id'] ?>',
                        title: '<?= addslashes($event['title']) ?>',
                        start: '<?= $event['start_datetime'] ?>',
                        end: '<?= $event['end_datetime'] ?>',
                        description: '<?= addslashes($event['description']) ?>',
                        location: '<?= addslashes($event['location']) ?>',
                        extendedProps: {
                            type: '<?= $event['event_type'] ?>'
                        },
                        className: 'event-<?= $event['event_type'] ?>'
                    },
                    <?php endforeach; ?>
                ],
                eventClick: function(info) {
                    const event = info.event;
                    $('#eventTitle').text(event.title);
                    $('#eventType').text(event.extendedProps.type.charAt(0).toUpperCase() + event.extendedProps.type.slice(1));
                    
                    // Format date and time
                    const start = event.start ? new Date(event.start) : null;
                    const end = event.end ? new Date(event.end) : null;
                    
                    let dateTimeStr = '';
                    if (start) {
                        dateTimeStr = start.toLocaleDateString() + ' ' + start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    }
                    if (end) {
                        dateTimeStr += ' - ' + end.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    }
                    $('#eventDateTime').text(dateTimeStr);
                    
                    $('#eventLocation').text(event.extendedProps.location || 'Not specified');
                    $('#eventDescription').text(event.extendedProps.description || 'No description provided.');
                    
                    // Set attendance buttons
                    const eventId = event.id;
                    const attendanceStatus = <?= json_encode($attendance) ?>[eventId] || '';
                    $('.attendance-btn').removeClass('active');
                    if (attendanceStatus) {
                        $(`.attendance-btn[data-status="${attendanceStatus}"]`).addClass('active');
                    }
                    
                    // Store event ID for attendance update
                    $('#eventModal').data('eventId', eventId);
                    
                    $('#eventModal').modal('show');
                }
            });
            calendar.render();
            
            // Handle attendance buttons
            $('.attendance-btn').click(function() {
                const status = $(this).data('status');
                const eventId = $('#eventModal').data('eventId');
                
                // Update UI
                $('.attendance-btn').removeClass('active');
                $(this).addClass('active');
                
                // Send to server
                $.post('update_attendance.php', {
                    event_id: eventId,
                    status: status
                }, function(response) {
                    // Optional: Show confirmation
                });
            });
        });
    </script>
</body>
</html>