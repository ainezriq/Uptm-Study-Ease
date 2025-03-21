<?php
session_start();
//var_dump($_SESSION); 
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
                                        response = JSON.parse(response);
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
                        e.stopPropagation();
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

        function addSubjectField() {
    var subjectList = document.getElementById("subject-list");

    var newSubject = document.createElement("div");
    newSubject.classList.add("subject-item");
    
    newSubject.innerHTML = `
        <select name="subjects[]">
            <option value="">Select Subject</option>
            <option value="ITC2193 - Information Technology Essentials">ITC2193  - Information Technology Essentials</option>
            <option value="ITC2173 - Enterprise Information Systems">ITC2173 - Enterprise Information Systems</option>
            <option value="FYP3024 - Computing Project">FYP3024 - Computing Project</option>
            <option value="ITC1083 - Business Information Management Strategy">ITC1083 - Business Information Management Strategy</option>
            <option value="ARC3043 - Linux OS">ARC3043 - Linux OS</option>
            <option value="SWC3403 - Introduction to Mobile Application Development">SWC3403 - Introduction to Mobile Application Development</option>
        </select>
        <button class="remove-subject-btn" onclick="removeSubjectField(this)">❌</button>
    `;

    subjectList.appendChild(newSubject);
}

function removeSubjectField(button) {
    button.parentElement.remove();
}

    </script>
</head>

<body>
    <div class="navbar">
        <div class="logo">
            <img src="assets/logo.png" alt="Logo" class="logo-img">
            <span class="website-name">UPTM Study Ease</span>
        </div>
        <div class="nav-links">
            <a href="home.php">Home</a>
            <a href="inbox.php">Inbox</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="subject-container">
    <h2>Select Subjects</h2>
    <div id="subject-list" class="subject-list"></div>
    <button class="subject-button" onclick="addSubjectField()">Add Another Subject</button>
</div>


    <div class="calendar-container">
        <div id="calendar"></div>
    </div>
</body>
</html>
