<?php
session_start();
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
                events: function(start, end, timezone, callback) {
                    $.ajax({
                        url: 'functions/fetch_events.php',
                        dataType: 'json',
                        success: function(data) {
                            callback(data);
                        }
                    });
                },
                select: function(start) {
                    var title = prompt("Enter event title:");
                    if (title) {
                        $.ajax({
                            url: 'functions/add_events.php',
                            type: 'POST',
                            data: { title: title, start: moment(start).format('YYYY-MM-DD HH:mm:ss') },
                            success: function(response) {
                                alert("Event added successfully!");
                                $('#calendar').fullCalendar('refetchEvents');
                            }
                        });
                    }
                    $('#calendar').fullCalendar('unselect');
                },
                eventRender: function(event, element) {
                    element.find('.fc-time').remove();
                    var deleteBtn = $('<span class="event-delete"> ❌ </span>');
                    deleteBtn.on('click', function(e) {
                        e.stopPropagation();
                        if (confirm("Are you sure you want to delete this event?")) {
                            $.ajax({
                                url: 'functions/delete_events.php',
                                type: 'POST',
                                data: { id: event.id },
                                success: function() {
                                    $('#calendar').fullCalendar('removeEvents', event.id);
                                    alert("Event deleted successfully!");
                                }
                            });
                        }
                    });
                    element.find('.fc-title').append(deleteBtn);
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
                    <option value="ITC2193">ITC2193 - Information Technology Essentials</option>
                    <option value="ITC2173">ITC2173 - Enterprise Information Systems</option>
                    <option value="FYP3024">FYP3024 - Computing Project</option>
                    <option value="ITC1083">ITC1083 - Business Information Management Strategy</option>
                    <option value="ARC3043">ARC3043 - Linux OS</option>
                    <option value="SWC3403">SWC3403 - Introduction to Mobile Application Development</option>
                </select>
                <button class="remove-subject-btn" onclick="removeSubjectField(this)">❌</button>
            `;
            subjectList.appendChild(newSubject);
        }
        
        function removeSubjectField(button) {
            button.parentElement.remove();
        }
    </script>
    <style>
        .content-container {
            display: flex;
            gap: 20px;
            padding: 20px;
        }
        .subject-container {
            width: 300px;
            background: white;
            border-radius: 8px;
            box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
            padding: 15px;
            display: flex;
            flex-direction: column;
        }
        .subject-container h2 {
            font-size: 18px;
            text-align: center;
        }
        .subject-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .subject-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .calendar-container {
            flex-grow: 1;
        }
    </style>
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

    <div class="content-container">
        <div class="subject-container">
            <h2>Select Subjects</h2>
            <div id="subject-list" class="subject-list"></div>
            <button class="subject-button" onclick="addSubjectField()">Add Another Subject</button>
        </div>

        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </div>
</body>
</html>
