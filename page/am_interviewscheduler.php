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

            events: {
                url: 'backend/bk_aminterviewscheduler.php',
                method: 'POST',

                extraParams: {
                    request: 'viewevents'
                },

                failure: function(error) {

                    console.error("FullCalendar Event Fetch Error:");
                    console.error(error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Event Loading Failed',
                        html: `
                                <div style="text-align:left;">
                                    <b>Status:</b> Failed to fetch events<br><br>
                                    Check the browser console for detailed debugging.
                                </div>
                              `
                    });

                }
            }

        });

        window.jobCalendar.render();

    }

    initializeJobCalendar();

    $(document).off("click", "#save_schedule").on("click", "#save_schedule", function(e) {

        e.preventDefault();
        e.stopPropagation();

        let schedule_title = $("#schedule_title").val().trim();
        let schedule_description = $("#schedule_description").val().trim();
        let schedule_startdate = $("#schedule_startdate").val();
        let schedule_enddate = $("#schedule_enddate").val();
        let schedule_vacancy = $("#schedule_vacancy").val();

        let applicants = selectedApplicants || [];
        /*
                alert(
                    "Schedule Title: " + schedule_title + "\n\n" +
                    "Description: " + schedule_description + "\n\n" +
                    "Start Date: " + schedule_startdate + "\n\n" +
                    "End Date: " + schedule_enddate + "\n\n" +
                    "Vacancy ID: " + schedule_vacancy + "\n\n" +
                    "Applicants: " + JSON.stringify(applicants, null, 2)
                );
                die();
        */
        if (
            schedule_title === "" ||
            schedule_description === "" ||
            schedule_startdate === "" ||
            schedule_enddate === "" ||
            schedule_vacancy === ""
        ) {

            Swal.fire({
                title: "Missing Fields",
                text: "Please complete all required fields.",
                icon: "warning",
                confirmButtonText: "OK",
                scrollbarPadding: false
            });

            return;
        }

        if (applicants.length === 0) {

            Swal.fire({
                title: "No Applicants Selected",
                text: "Please select at least one applicant.",
                icon: "warning",
                confirmButtonText: "OK",
                scrollbarPadding: false
            });

            return;
        }

        Swal.fire({
            title: "Save Event?",
            text: "This will create the scheduled event.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, save it!",
            cancelButtonText: "Cancel",
            reverseButtons: true,
            scrollbarPadding: false
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({

                    url: "backend/bk_aminterviewscheduler.php",
                    type: "POST",

                    data: {
                        request: "saveevent",

                        schedule_title: schedule_title,
                        schedule_description: schedule_description,
                        schedule_startdate: schedule_startdate,
                        schedule_enddate: schedule_enddate,
                        schedule_vacancy: schedule_vacancy,

                        applicants: JSON.stringify(applicants),

                        userid: UserInfo["UserID"]
                    },

                    beforeSend: function() {

                        $("#loadingSpinner")
                            .css("display", "flex")
                            .hide()
                            .fadeIn(200);
                    },

                    success: function(dataResult) {

                        $("#loadingSpinner").fadeOut(200, function() {
                            $("#loadingSpinner").css("display", "none");
                        });

                        console.log("Raw Response:", dataResult);

                        let res;

                        try {

                            res = typeof dataResult === "object" ?
                                dataResult :
                                JSON.parse(dataResult);

                        } catch (err) {

                            console.error("JSON Parse Error:", err);
                            console.error("Server Response:", dataResult);

                            Swal.fire({
                                title: "Invalid Response",
                                text: "The server returned invalid JSON.",
                                icon: "error",
                                scrollbarPadding: false
                            });

                            return;
                        }

                        if (res.status === "success") {

                            Swal.fire({
                                title: "Saved!",
                                text: res.message,
                                icon: "success",
                                confirmButtonText: "OK",
                                scrollbarPadding: false
                            });

                            $("#schedulediv").find("input").val("");
                            $("#schedule_vacancy").val("");

                            selectedApplicants = [];

                            $("#applicantsdropdown").html(`
                            <span class="font-weight-bold text-danger">
                                Select a Vacancy First...
                            </span>
                        `);

                        } else {

                            let errorText = res.message || "Something went wrong.";

                            if (res.errors && res.errors.length > 0) {
                                errorText += "\n\n" + res.errors.join("\n");
                            }

                            Swal.fire({
                                title: "Save Failed",
                                text: errorText,
                                icon: "error",
                                confirmButtonText: "OK",
                                scrollbarPadding: false
                            });
                        }
                    },

                    error: function(xhr, status, error) {

                        $("#loadingSpinner").fadeOut(200, function() {
                            $("#loadingSpinner").css("display", "none");
                        });

                        console.error("SAVE EVENT AJAX ERROR");
                        console.error("Status:", status);
                        console.error("Error:", error);
                        console.error("HTTP Code:", xhr.status);
                        console.error("Response:", xhr.responseText);

                        Swal.fire({
                            title: "AJAX Error!",
                            html: `
                            <div style="text-align:left;">

                                <b>Status:</b> ${status}<br>
                                <b>Error:</b> ${error}<br>
                                <b>HTTP Code:</b> ${xhr.status}<br><br>

                                <b>Server Response:</b>

                                <div style="
                                    max-height:250px;
                                    overflow:auto;
                                    background:#f8f9fa;
                                    padding:10px;
                                    border-radius:6px;
                                    border:1px solid #ddd;
                                    font-size:12px;
                                    text-align:left;
                                ">
                                    ${xhr.responseText || 'No response from server'}
                                </div>

                            </div>
                        `,
                            icon: "error",
                            width: 750,
                            scrollbarPadding: false
                        });
                    }
                });
            }
        });
    });
</script>
