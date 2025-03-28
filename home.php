<?php
session_start();
include 'auth/conn.php'; // Include database connection

// Fetch notices for the student
$notices = [];
if (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];
    $stmt = $conn->prepare("SELECT * FROM notices WHERE userId = ? ORDER BY created_at DESC"); // Fetch notices for the user
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $notices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Store fetched notices
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Calendar</title>
    <link rel="stylesheet" href="styles/style.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@3.2.0/dist/fullcalendar.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@3.2.0/dist/fullcalendar.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#calendar').fullCalendar({
                selectable: true,
                selectHelper: true,
                editable: true,
                droppable: true,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },

                // Fetch events
                events: function(start, end, timezone, callback) {
                    $.ajax({
                        url: 'functions/fetch_events.php',
                        dataType: 'json',
                        success: function(data) {
                            console.log("Fetched Events:", data);
                            callback(data);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching events:', xhr.responseText);
                        }
                    });
                },

                // Add events
                select: function(start) {
                    var title = prompt("Enter event title:");
                    if (title) {
                        $.ajax({
                            url: 'functions/add_events.php',
                            type: 'POST',
                            data: {
                                title: title,
                                start: moment(start).format('YYYY-MM-DD HH:mm:ss')
                            },
                            success: function(response) {
                                try {
                                    console.log("Add Event Response:", response);
                                    if (typeof response === "string") {
                                        response = JSON.parse(response); // Ensure it's valid JSON
                                    }

                                    if (response.status === "success") {
                                        alert(response.message);
                                        $('#calendar').fullCalendar('refetchEvents');
                                    } else {
                                        alert(response.error);
                                    }
                                } catch (error) {
                                    console.error("JSON Parse Error:", error, "Response:", response);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error adding event:', xhr.responseText);
                                alert('Error adding event: ' + xhr.responseText);
                            }
                        });
                    }
                    $('#calendar').fullCalendar('unselect');
                },

                // Render events
                eventRender: function(event, element) {
                    console.log("Rendering event:", event);
                    element.find('.fc-time').remove();
                    var deleteBtn = $('<span class="event-delete"> ❌ </span>');

                    deleteBtn.on('click', function(e) {
                        e.stopPropagation(); // Prevent triggering eventClick
                        if (confirm("Are you sure you want to delete this event?")) {
                            $.ajax({
                                url: 'functions/delete_events.php',
                                type: 'POST',
                                data: {
                                    id: event.id
                                },
                                success: function(response) {
                                    $('#calendar').fullCalendar('removeEvents', event.id);
                                    alert("Event deleted successfully!");
                                },
                                error: function(xhr, status, error) {
                                    console.log('Error deleting event:', error);
                                }
                            });
                        }
                    });

                    element.find('.fc-title').append(deleteBtn);
                },

                // Update event date on drag
                eventDrop: function(event) {
                    console.log("Event dropped:", event);
                    var newDate = moment(event.start).format('YYYY-MM-DD HH:mm:ss');
                    $.ajax({
                        url: 'functions/update_events.php',
                        type: 'POST',
                        data: {
                            id: event.id,
                            new_date: newDate
                        },
                        success: function(response) {
                            console.log("Update Response:", response);
                            alert("Event updated successfully!");
                        },
                        error: function(xhr, status, error) {
                            console.error('Error updating event:', xhr.responseText);
                        }
                    });
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const hamburger = document.querySelector(".hamburger");
            const mobileMenu = document.querySelector(".mobile-menu");
            const navLinks = document.querySelector(".nav-links");

            hamburger.addEventListener("click", function() {
                mobileMenu.classList.toggle("active");
            });

            function checkScreenSize() {
                if (window.innerWidth > 768) {
                    mobileMenu.classList.remove("active");
                    navLinks.style.display = "flex";
                } else {
                    navLinks.style.display = "none";
                }
            }
            checkScreenSize();
            window.addEventListener("resize", checkScreenSize);
        });
    </script>

</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">
            <img src="assets/logo.png" alt="Logo" class="logo-img">
            <span class="website-name">UPTM Study Ease</span>
        </div>

        <!-- Desktop Navigation -->
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="inbox.php">Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>

        <!-- Mobile Menu -->
        <div class="hamburger">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div class="mobile-menu">
        <a href="home.php">Home</a>
        <a href="inbox.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content-container">
    <?php if ($_SESSION['userType'] === 'Student'): ?>
        <div class="welcome-message" style="flex-basis: 40%;">
            <h2>Welcome, Student!</h2>
        </div>
        <div class="notifications" style="flex-basis: 40%;">
            <h2>Notices</h2>
            <?php if (empty($notices)): ?>
                <p>No new notices.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($notices as $notice): ?>
                        <li>
                            <strong><?= htmlspecialchars($notice['content']) ?></strong>
                            <small>Posted on: <?= $notice['created_at'] ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php elseif ($_SESSION['userType'] === 'Lecturer'): ?>
        <div class="lecturer-message" style="flex-basis: 40%;">
            <h2>Welcome, Lecturer!</h2>
            <p>Please go to the <a href="inbox.php">Dashboard</a> to post learning materials.</p>
        </div>
    <?php endif; ?>
    </div>

    <h2>Task Calendar</h2> <!-- Added title for the calendar -->
    <div class="calendar-container" style="width: 50%; text-align: center;"> <!-- Centering the calendar -->
        <div id="calendar"></div>
    </div>
    <div class="enrollment-container">
    <?php if ($_SESSION['userType'] === 'Student'): ?>
        <h2>Enroll in Subjects</h2>
        <form method="POST" action="functions/enroll_subject.php">
            <label for="subjects">Select Subjects:</label><br>
            <div>
                <input type="checkbox" name="subjects[]" value="ITC1083"> ITC1083 - Business Information Management Strategy<br>
                <input type="checkbox" name="subjects[]" value="ITC2173"> ITC2173 - Enterprise Information Systems<br>
                <input type="checkbox" name="subjects[]" value="ITC2193"> ITC2193 - Information Technology Essentials<br>
                <input type="checkbox" name="subjects[]" value="ARC3043"> ARC3043 - Linux OS<br>
                <input type="checkbox" name="subjects[]" value="SWC3403"> SWC3403 - Introduction to Mobile Application Development<br>
                <input type="checkbox" name="subjects[]" value="FYP3024"> FYP3024 - Computing Project<br>
                <input type="hidden" name="subject_names[]" value="Business Information Management Strategy">
                <input type="hidden" name="subject_names[]" value="Enterprise Information Systems">
                <input type="hidden" name="subject_names[]" value="Information Technology Essentials">
                <input type="hidden" name="subject_names[]" value="Linux OS">
                <input type="hidden" name="subject_names[]" value="Introduction to Mobile Application Development">
                <input type="hidden" name="subject_names[]" value="Computing Project">
            </div>
            <button type="submit">Enroll</button>
        </form>
    <?php endif; ?>
    </div>
</body>
</html>
