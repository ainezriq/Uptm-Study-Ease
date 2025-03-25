<?php
session_start();
include 'auth/conn.php'; // Include database connection

// Fetch notices for the student
$notices = [];
if (isset($_SESSION['email'])) {
    $userEmail = $_SESSION['email'];
$stmt = $conn->prepare("SELECT * FROM notices WHERE user_id = ? ORDER BY created_at DESC"); // Updated query
$stmt->bind_param("i", $userId); // Changed from email to userId


    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $notices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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

                                    if (response.success) {
                                        alert(response.success);
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
            <a href="inbox.php">Inbox</a>
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
        <a href="inbox.php">Inbox</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content-container">
        <div class="notifications">

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
        </div>
        <div class="calendar-container">


        <div id="calendar"></div>
    </div>
    <div class="enrollment-container">
        <h2>Enroll in Subjects</h2>
        <form method="POST" action="functions/store_subjects.php">
            <label for="subjects">Select Subjects:</label>
            <select name="subjects[]" id="subjects" multiple>
                <?php
                // Fetch available subjects
                $subject_stmt = $conn->prepare("SELECT subject_code, subject_name FROM subjects");
                $subject_stmt->execute();
                $subjects = $subject_stmt->get_result();

                while ($row = $subjects->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($row['subject_code']) . '">' . htmlspecialchars($row['subject_name']) . '</option>';
                }
                ?>
            </select>
            <button type="submit">Enroll</button>
        </form>
    </div>


</html>
