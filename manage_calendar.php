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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_event'])) {
        // Add new event
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $location = $conn->real_escape_string($_POST['location']);
        $event_type = $conn->real_escape_string($_POST['event_type']);
        $start_datetime = $conn->real_escape_string($_POST['start_datetime']);
        $end_datetime = $conn->real_escape_string($_POST['end_datetime']);
        $is_public = isset($_POST['is_public']) ? 1 : 0;
        
        $sql = "INSERT INTO calendar_events (title, description, location, event_type, start_datetime, end_datetime, is_public, created_by) 
                VALUES ('$title', '$description', '$location', '$event_type', '$start_datetime', '$end_datetime', $is_public, {$_SESSION['user_id']})";
        $conn->query($sql);
    } elseif (isset($_POST['delete_event'])) {
        // Delete event
        $event_id = intval($_POST['event_id']);
        $conn->query("DELETE FROM calendar_events WHERE id = $event_id");
        $conn->query("DELETE FROM event_attendance WHERE event_id = $event_id");
    }
}

// Fetch all events
$sql = "SELECT * FROM calendar_events ORDER BY start_datetime ASC";
$result = $conn->query($sql);
$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

// Fetch attendance data for all events
$attendance_data = [];
$attendance_sql = "SELECT e.id as event_id, e.title, 
                  SUM(CASE WHEN a.status = 'going' THEN 1 ELSE 0 END) as going_count,
                  SUM(CASE WHEN a.status = 'maybe' THEN 1 ELSE 0 END) as maybe_count,
                  SUM(CASE WHEN a.status = 'not_going' THEN 1 ELSE 0 END) as not_going_count
                  FROM calendar_events e
                  LEFT JOIN event_attendance a ON e.id = a.event_id
                  GROUP BY e.id, e.title";
$attendance_result = $conn->query($attendance_sql);
while ($row = $attendance_result->fetch_assoc()) {
    $attendance_data[$row['event_id']] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Calendar | SONIT</title>
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
        
        .admin-controls {
            margin-bottom: 20px;
            padding: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .attendance-table {
            margin-top: 30px;
        }
        
        .attendance-table th {
            background-color: var(--primary-blue);
            color: white;
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
            <i class="fas fa-calendar-alt"></i> SONIT Admin Calendar
        </a>
    </nav>
    
    <div class="sidebar" id="sidebar">
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
    
    <div class="content" id="content">
        <div class="container">
            <h2 class="mb-4"><i class="fas fa-calendar-alt mr-2"></i>Calendar Management</h2>
            
            <div class="admin-controls">
                <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addEventModal">
                    <i class="fas fa-plus"></i> Add New Event
                </button>
                
                <div class="calendar-container">
                    <div id="calendar"></div>
                </div>
            </div>
            
            <div class="attendance-table">
                <h4><i class="fas fa-clipboard-list mr-2"></i>Event Attendance Summary</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Going</th>
                                <th>Maybe</th>
                                <th>Not Going</th>
                                <th>Total Responses</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance_data as $event): ?>
                            <tr>
                                <td><?= htmlspecialchars($event['title']) ?></td>
                                <td><?= $event['going_count'] ?></td>
                                <td><?= $event['maybe_count'] ?></td>
                                <td><?= $event['not_going_count'] ?></td>
                                <td><?= $event['going_count'] + $event['maybe_count'] + $event['not_going_count'] ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info view-attendance" data-event-id="<?= $event['event_id'] ?>">
                                        <i class="fas fa-users"></i> View Attendees
                                    </button>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                        <button type="submit" name="delete_event" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this event?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1" role="dialog" aria-labelledby="addEventModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="addEventModalLabel">Add New Event</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="title">Event Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="event_type">Event Type</label>
                            <select class="form-control" id="event_type" name="event_type" required>
                                <option value="academic">Academic</option>
                                <option value="social">Social</option>
                                <option value="holiday">Holiday</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="start_datetime">Start Date & Time</label>
                            <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" required>
                        </div>
                        <div class="form-group">
                            <label for="end_datetime">End Date & Time</label>
                            <input type="datetime-local" class="form-control" id="end_datetime" name="end_datetime" required>
                        </div>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" class="form-control" id="location" name="location">
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_public" name="is_public" checked>
                            <label class="form-check-label" for="is_public">Public Event (visible to all students)</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_event" class="btn btn-primary">Add Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade event-modal" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
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
                    <p><strong>Visibility:</strong> <span id="eventVisibility"></span></p>
                    <p id="eventDescription"></p>
                    
                    <div class="attendance-summary mt-4">
                        <h5>Attendance Summary</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-success text-white text-center mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title" id="goingCount">0</h5>
                                        <p class="card-text">Going</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-dark text-center mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title" id="maybeCount">0</h5>
                                        <p class="card-text">Maybe</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-danger text-white text-center mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title" id="notGoingCount">0</h5>
                                        <p class="card-text">Not Going</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-info" id="viewAttendeesBtn">
                        <i class="fas fa-users"></i> View Attendees
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendees Modal -->
    <div class="modal fade" id="attendeesModal" tabindex="-1" role="dialog" aria-labelledby="attendeesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="attendeesModalLabel">Event Attendees</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="attendeeTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="going-tab" data-toggle="tab" href="#going" role="tab">Going</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="maybe-tab" data-toggle="tab" href="#maybe" role="tab">Maybe</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="notgoing-tab" data-toggle="tab" href="#notgoing" role="tab">Not Going</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="attendeeTabsContent">
                        <div class="tab-pane fade show active" id="going" role="tabpanel">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                    </tr>
                                </thead>
                                <tbody id="goingAttendees">
                                    <!-- Filled by AJAX -->
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="maybe" role="tabpanel">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                    </tr>
                                </thead>
                                <tbody id="maybeAttendees">
                                    <!-- Filled by AJAX -->
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="notgoing" role="tabpanel">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                    </tr>
                                </thead>
                                <tbody id="notGoingAttendees">
                                    <!-- Filled by AJAX -->
                                </tbody>
                            </table>
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
                editable: true, // Allow drag-and-drop
                eventDrop: function(info) {
                    // Update event dates when dragged
                    const event = info.event;
                    updateEventDates(event.id, event.start, event.end);
                },
                eventResize: function(info) {
                    // Update event dates when resized
                    const event = info.event;
                    updateEventDates(event.id, event.start, event.end);
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
                        is_public: <?= $event['is_public'] ? 'true' : 'false' ?>,
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
                    $('#eventVisibility').text(event.extendedProps.is_public ? 'Public' : 'Private');
                    
                    // Set attendance counts
                    const eventId = event.id;
                    const attendanceData = <?= json_encode($attendance_data) ?>;
                    
                    if (attendanceData[eventId]) {
                        $('#goingCount').text(attendanceData[eventId].going_count);
                        $('#maybeCount').text(attendanceData[eventId].maybe_count);
                        $('#notGoingCount').text(attendanceData[eventId].not_going_count);
                    } else {
                        $('#goingCount').text('0');
                        $('#maybeCount').text('0');
                        $('#notGoingCount').text('0');
                    }
                    
                    // Store event ID for attendees view
                    $('#eventModal').data('eventId', eventId);
                    $('#viewAttendeesBtn').off('click').on('click', function() {
                        loadAttendees(eventId);
                        $('#attendeesModal').modal('show');
                    });
                    
                    $('#eventModal').modal('show');
                }
            });
            calendar.render();
            
            // Handle view attendees buttons in the table
            $('.view-attendance').click(function() {
                const eventId = $(this).data('event-id');
                loadAttendees(eventId);
                $('#attendeesModal').modal('show');
            });
        });
        
        function updateEventDates(eventId, start, end) {
            // Format dates for database
            const startStr = start ? start.toISOString().slice(0, 19).replace('T', ' ') : null;
            const endStr = end ? end.toISOString().slice(0, 19).replace('T', ' ') : null;
            
            $.post('update_event_dates.php', {
                event_id: eventId,
                start_datetime: startStr,
                end_datetime: endStr
            }, function(response) {
                if (response.success) {
                    // Optional: Show success message
                } else {
                    alert('Error updating event: ' + response.message);
                    // Reload calendar to reset to original dates
                    $('#calendar').fullCalendar('refetchEvents');
                }
            }, 'json');
        }
        
        function loadAttendees(eventId) {
            // Load attendees for each status
            $.get('get_attendees.php', {event_id: eventId, status: 'going'}, function(data) {
                $('#goingAttendees').html(data);
            });
            
            $.get('get_attendees.php', {event_id: eventId, status: 'maybe'}, function(data) {
                $('#maybeAttendees').html(data);
            });
            
            $.get('get_attendees.php', {event_id: eventId, status: 'not_going'}, function(data) {
                $('#notGoingAttendees').html(data);
            });
        }
    </script>
</body>
</html>