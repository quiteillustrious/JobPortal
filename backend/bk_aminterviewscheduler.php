<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$fields = isset($_POST["fields"]) ? $_POST["fields"] : "";
$operator = isset($_POST["operator"]) ? $_POST["operator"] : "";
$datavalue = isset($_POST["datavalue"]) ? $_POST["datavalue"] : "";
$logslocation = isset($_POST["logslocation"]) ? $_POST["logslocation"] : "";
$userid = isset($_POST["userid"]) ? $_POST["userid"] : "";

$currentdt = date("Y-m-d H:i:s");

switch ($request) {

    case "addevent":

        echo '
		<div class="p-3 bg-light border rounded border-success shadow" id="workexperiencediv">

			<!-- Event Title -->
			<div class="form-group">
				<label for="schedule_title">Event Title <span class="text-danger">*</span></label>
				<input type="text" class="form-control border-success" id="schedule_title" name="schedule_title" placeholder="e.g. Examination">
			</div>

			<!-- Event Description -->
			<div class="form-group">
				<label for="schedule_description">Event Description <span class="text-danger">*</span></label>
				<input type="text" class="form-control border-success" id="schedule_description" name="schedule_description" placeholder="e.g. Final Examination">
			</div>

			<!-- Start Date -->
			<div class="form-group">
				<label for="schedule_startdate">Start Date/Time <span class="text-danger">*</span></label>
				<input type="datetime-local" class="form-control border-success" id="schedule_startdate" name="schedule_startdate">
			</div>

			<!-- End Date -->
			<div class="form-group">
				<label for="schedule_enddate">End Date/Time <span class="text-danger">*</span></label>
				<input type="datetime-local" class="form-control border-success" id="schedule_enddate" name="schedule_enddate">
			</div>

			<!-- Select Vacancy -->
			<div class="form-group" id="schedule_vacancy">
				<label for="work_status">
					Select Vacancy <span class="text-danger">*</span>
				</label>

				<!-- Dropdown -->
				<select class="form-control border-success" id="schedule_vacancy" name="schedule_vacancy">
					<option value="">--Select Vacancy--</option><hr>';

        $appointment = execsqlSRS("
						SELECT [appoint_id],
							[appoint_desc],
							[IsActive]
						FROM [tbl_ProfExpAppoint]
						WHERE [IsActive] = 0
						ORDER BY [appoint_desc]
					", "Select", array());

        foreach ($appointment as $app) {
            echo '<option value="' . htmlspecialchars($app['appoint_id']) . '">' . htmlspecialchars($app['appoint_desc']) . '</option>';
        }

        echo '      </select>
			</div>

			<!-- Save Button -->
			<div class="form-group mt-3 d-flex justify-content-center">
				<button type="button"
						class="btn btn-success"
						id="save_workexperiencedetails"
						>
					Save Event <i class="fa-solid fa-floppy-disk"></i>
				</button>
			</div>

		</div>
        ';

        break;
}
