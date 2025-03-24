<?php
session_start();
if (!isset($_SESSION['studentId'])) {
    echo "<script>alert('Session not set, please log in again'); window.location.href='index.php';</script>";
    exit;
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
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">
            <img src="assets/logo.png" alt="Logo" class="logo-img">
            <span class="website-name">UPTM Study Ease</span>
        </div>
        <div class="nav-links">
        <span class="welcome-text">Welcome, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Student'; ?></span>
            <a href="home.php">Home</a>
            <a href="inbox.php">Dashboard</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
        <div class="hamburger">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    <div class="mobile-menu">
        <a href="home.php">Home</a>
        <a href="inbox.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- SUBJECT SELECTION -->
    <div class="subject-container">
        <h3>Select Subjects</h3>
        <form id="subjectForm">
            <div id="subjectList">
                <div class="subject-entry">
                    <select name="subjects[]" class="subject-dropdown">
                        <option value="">Select Subject</option>
                        <option value="ITC1083">Business Information Management Strategy</option>
                        <option value="ITC2173">Enterprise Information Systems</option>
                        <option value="ITC2193">Information Technology Essentials</option>
                        <option value="ARC3043">Linux OS</option>
                        <option value="SWC3403">Introduction to Mobile Application Development</option>
                        <option value="FYP3024">Computing Project</option>
                    </select>
                </div>
            </div>
            <button type="button" id="addSubject">+ Add Subject</button>
            <button type="submit">Save Subjects</button>
        </form>
    </div>

    <!-- Calendar Section -->
    <div class="calendar-container">
        <div id="calendar"></div>
    </div>

    <script>
        $(document).ready(function() {
            $("#addSubject").click(function() {
                $("#subjectList").append(`
                    <div class="subject-entry">
                        <select name="subjects[]" class="subject-dropdown">
                            <option value="">Select Subject</option>
                            <option value="ITC1083">Business Information Management Strategy</option>
                            <option value="ITC2173">Enterprise Information Systems</option>
                            <option value="ITC2193">Information Technology Essentials</option>
                            <option value="ARC3043">Linux OS</option>
                            <option value="SWC3403">Introduction to Mobile Application Development</option>
                            <option value="FYP3024">Computing Project</option>
                        </select>
                        <button type="button" class="removeSubject">❌</button>
                    </div>
                `);
                updateDropdownOptions();
            });

            $(document).on("click", ".removeSubject", function() {
                $(this).parent().remove();
                updateDropdownOptions();
            });

            $(document).on("change", ".subject-dropdown", function() {
                updateDropdownOptions();
            });

            function updateDropdownOptions() {
                let selectedSubjects = $(".subject-dropdown").map(function() {
                    return $(this).val();
                }).get().filter(value => value !== "");

                $(".subject-dropdown option").prop("disabled", false);

                selectedSubjects.forEach(subject => {
                    if (subject) {
                        $(".subject-dropdown").not(`[value="${subject}"]`).find(`option[value="${subject}"]`).prop("disabled", true);
                    }
                });
            }

            $("#subjectForm").submit(function(e) {
                e.preventDefault();

                let selectedSubjects = $(".subject-dropdown").map(function() {
                    return $(this).val();
                }).get().filter(value => value !== "");

                if (selectedSubjects.length === 0) {
                    alert("Please select at least one subject.");
                    return;
                }

                $.ajax({
                    url: 'auth/store_subjects.php',
                    type: 'POST',
                    data: $("#subjectForm").serialize(),
                    success: function(response) {
                        console.log("Server Response:", response);
                        try {
                            let jsonResponse = JSON.parse(response);
                            alert(jsonResponse.message);
                            if (jsonResponse.status === "success") {
                                location.reload();
                            }
                        } catch (error) {
                            alert("Unexpected server response.");
                            console.error("Parsing Error:", error, "Response:", response);
                        }
                    },
                    error: function(xhr) {
                        console.error("AJAX Error:", xhr.responseText);
                        alert("Error saving subjects.");
                    }
                });
            });

            $('#calendar').fullCalendar({
                selectable: true,
                editable: true,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                events: function(start, end, timezone, callback) {
                    $.ajax({
                        url: 'functions/fetch_events.php',
                        dataType: 'json',
                        success: function(data) {
                            callback(data);
                        },
                        error: function(xhr) {
                            console.error('Error fetching events:', xhr.responseText);
                        }
                    });
                },
                select: function(start) {
                    let title = prompt("Enter event title:");
                    if (title) {
                        $.post('functions/add_events.php', {
                            title: title,
                            start: moment(start).format('YYYY-MM-DD HH:mm:ss')
                        }, function(response) {
                            if (response.success) {
                                alert(response.success);
                                $('#calendar').fullCalendar('refetchEvents');
                            } else {
                                alert(response.error);
                            }
                        }, "json");
                    }
                    $('#calendar').fullCalendar('unselect');
                },
                eventDrop: function(event) {
                    $.post('functions/update_events.php', {
                        id: event.id,
                        new_date: moment(event.start).format('YYYY-MM-DD HH:mm:ss')
                    }, function(response) {
                        alert("Event updated successfully!");
                    });
                },
                eventRender: function(event, element) {
                    let deleteBtn = $('<span class="event-delete"> ❌ </span>');
                    deleteBtn.on('click', function(e) {
                        e.stopPropagation();
                        if (confirm("Are you sure you want to delete this event?")) {
                            $.post('functions/delete_events.php', { id: event.id }, function() {
                                $('#calendar').fullCalendar('removeEvents', event.id);
                                alert("Event deleted successfully!");
                            });
                        }
                    });
                    element.find('.fc-title').append(deleteBtn);
                }
            });
        });
    </script>
</body>
</html>
