<?php
date_default_timezone_set('Asia/Manila');
include "modals.php";
?>
<style>
    .scheduler-wrapper {
        padding: 20px;
    }

    .scheduler-card {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
    }

    .scheduler-header {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 18px 25px;
    }

    .scheduler-title {
        font-size: 22px;
        font-weight: 600;
        margin: 0;
    }

    .scheduler-subtitle {
        opacity: 0.9;
        font-size: 14px;
        margin-top: 4px;
    }

    .scheduler-body {
        background: white;
        padding: 20px;
    }

    #calendar {
        width: 100%;
    }

    .fc .fc-toolbar-title {
        font-size: 24px;
        font-weight: 700;
        color: #343a40;
    }

    .fc .fc-button {
        background: #28a745 !important;
        border: none !important;
        padding: 8px 14px !important;
        font-weight: 600;
        box-shadow: none !important;
    }

    .fc .fc-button:hover {
        background: #218838 !important;
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active {
        background: #1e7e34 !important;
    }

    .fc-theme-standard td,
    .fc-theme-standard th {
        border-color: #e9ecef;
    }

    .fc-col-header-cell {
        background: #f8f9fa;
        padding: 10px 0;
    }

    .fc-day-today {
        background: rgba(40, 167, 69, 0.08) !important;
    }

    .fc-event {
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .fc-daygrid-event {
        border-radius: 6px;
    }

    .fc-timegrid-event {
        border-radius: 6px;
        overflow: hidden;
    }

    #container #calendar .fc-daygrid-event {
        background-color: var(--fc-event-bg-color) !important;
        border-color: var(--fc-event-border-color) !important;
        color: var(--fc-event-text-color) !important;
    }

    #calendar .fc-daygrid-day,
    #calendar .fc-timegrid-slot,
    #calendar .fc-event,
    #calendar .fc-daygrid-event {
        cursor: pointer;
    }

    #calendar .fc-daygrid-day.fc-day-today {
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.25), rgba(32, 201, 151, 0.15)) !important;
        border: 2px solid #28a745 !important;
    }
</style>

<div class="scheduler-wrapper">

    <div class="card scheduler-card">

        <div class="scheduler-header">

            <h3 class="scheduler-title">
                HRMU Interview/Exam Scheduler
            </h3>

            <div class="scheduler-subtitle">
                Manage applicant interviews and exam schedules
            </div>

        </div>

        <div class="scheduler-body">

            <div id="calendar"></div>

        </div>

    </div>

</div>

<script>
    function initializeJobCalendar() {

        if (window.jobCalendar) {
            window.jobCalendar.destroy();
        }

        const calendarEl = document.getElementById('calendar');

        if (!calendarEl) return;

        window.jobCalendar = new FullCalendar.Calendar(calendarEl, {

            initialView: 'dayGridMonth',

            height: 'auto',

            selectable: true,

            nowIndicator: true,

            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },

            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },

            buttonText: {
                today: 'Today',
                month: 'Month',
                week: 'Week',
                day: 'Day'
            },

            slotMinTime: "07:00:00",
            slotMaxTime: "19:00:00",

            dateClick: function(info) {

                $.ajax({
                    url: 'backend/bk_aminterviewscheduler.php',
                    method: "POST",
                    data: {
                        request: "addevent",
                        datavalue: info.dateStr,
                        userid: UserInfo["UserID"]
                    },

                    beforeSend: function() {
                        $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                    },

                    success: function(response) {
                        $("#loadingSpinner").fadeOut(200, function() {
                            $("#loadingSpinner").css("display", "none");
                        });

                        $("#addeditcontent").html(response);
                        const formattedDate = new Date(info.dateStr).toLocaleDateString('en-US', {
                            month: 'long',
                            day: 'numeric',
                            year: 'numeric'
                        });

                        $("#addeditlabel").html("Schedule an event for " + formattedDate);
                        $("#addeditmodal").modal("show");
                    }
                });

            },

            eventClick: function(info) {

                Swal.fire({
                    title: info.event.title,
                    html: `
                            <b>Date:</b><br>
                            ${info.event.start.toLocaleString()}
                        `,
                    icon: 'success',
                    confirmButtonColor: '#28a745',
                    scrollbarPadding: false
                });

            },
            events: [

                {
                    title: 'Examination',
                    start: '2026-05-13T13:00:00',
                    end: '2026-05-13T14:30:00',
                    backgroundColor: '#dc3545',
                    borderColor: '#dc3545',
                    textColor: '#fff'
                },

                {
                    title: 'Initial Interview',
                    start: '2026-05-15T12:00:00',
                    end: '2026-05-15T17:00:00',
                    backgroundColor: '#28a745',
                    borderColor: '#28a745',
                    textColor: '#fff'
                },

                {
                    title: 'Final Interview',
                    start: '2026-05-18T07:00:00',
                    end: '2026-05-18T17:00:00',
                    backgroundColor: '#17a2b8',
                    borderColor: '#17a2b8',
                    textColor: '#fff'
                },

                {
                    title: 'Final Exam',
                    start: '2026-05-18T07:00:00',
                    end: '2026-05-18T17:00:00',
                    backgroundColor: '#17a2b8',
                    borderColor: '#17a2b8',
                    textColor: '#fff'
                },

                {
                    title: 'Final Exam',
                    start: '2026-06-17T07:00:00',
                    end: '2026-06-17T17:00:00',
                    backgroundColor: '#17a2b8',
                    borderColor: '#17a2b8',
                    textColor: '#fff'
                }

            ]

        });

        window.jobCalendar.render();

    }

    initializeJobCalendar();
</script>
