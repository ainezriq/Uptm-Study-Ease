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



    // Add More Subject Fields
    $("#addSubject").click(function () {
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
        updateDropdownOptions(); // Ensure dropdowns disable already selected subjects
    });

    // Remove Subject Field
    $(document).on("click", ".removeSubject", function () {
        $(this).parent().remove();
        updateDropdownOptions(); // Re-enable options in remaining dropdowns
    });

    // Prevent Duplicate Subjects & Disable Selected Options
    $(document).on("change", ".subject-dropdown", function () {
        updateDropdownOptions();
    });

    function updateDropdownOptions() {
        let selectedSubjects = $(".subject-dropdown").map(function () {
            return $(this).val();
        }).get().filter(value => value !== "");

        $(".subject-dropdown option").prop("disabled", false);

        selectedSubjects.forEach(subject => {
            if (subject) {
                $(".subject-dropdown").not(`[value="${subject}"]`).find(`option[value="${subject}"]`).prop("disabled", true);
            }
        });
    }

    // Save Subjects to Database
    $("#subjectForm").submit(function (e) {
        e.preventDefault();

        let selectedSubjects = $(".subject-dropdown").map(function () {
    return $(this).val();
}).get().filter(value => value !== "");

if (selectedSubjects.length === 0) {
    alert("Please select at least one subject.");
    return;
}

console.log($("#subjectForm").serialize());
        $.ajax({
            url: 'auth/store_subjects.php',
            type: 'POST',
            data: $("#subjectForm").serialize() + "&submit=true",
contentType: "application/x-www-form-urlencoded",

            success: function (response) {
                console.log("Server Response:", response); // Debugging
                alert(response);
                location.reload(); // Refresh to update UI
            },
            error: function (xhr) {
                console.error("AJAX Error:", xhr.responseText);
                alert("Error saving subjects.");
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


    <div class="calendar-container">
        <div id="calendar"></div>
    </div>
</body>

</html>
