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
		<style>
			.applicant-card {
				font-size: 12px;
				margin-right: 6px;
			}

			.applicant-card:hover {
				background-color: #f0fff4;
			}

			.selected-applicant {
				background-color: #28a745 !important;
				color: white !important;
				border-color: #28a745 !important;
			}

			.selected-applicant span {
				color: white !important;
			}
		</style>
		';

		echo '
		<div class="p-3 bg-light border rounded border-success shadow" id="schedulediv">

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
			<div class="form-group" id="schedule_vacancy_container">
				<label for="work_status">
					Select Vacancy <span class="text-danger">*</span>
				</label>

				<!-- Dropdown -->
				<select class="form-control border-success" id="schedule_vacancy" name="schedule_vacancy">
					<option value="">--Select Vacancy--</option><hr>';

		$appointment = execsqlSRS("
			SELECT  pos.[pubpos_id]
					,pos.[publication_id]
					,pos.[position_title]
					,office.[office_desc]
			FROM [tbl_PublicationPosition] pos

			LEFT JOIN [tbl_Publication] pub
			ON pub.[publication_id] = pos.[publication_id]

			LEFT JOIN [tbl_Office] office
			ON office.[office_id] = pos.[office_id]

			WHERE pos.[IsActive] = 0
				AND pub.[pubstatus_id] = 4
			ORDER BY pos.[position_title]
		", "Select", array());

		foreach ($appointment as $app) {
			echo '<option value="' . htmlspecialchars($app['pubpos_id']) . '">'
				. htmlspecialchars($app['position_title'])
				. ' — '
				. htmlspecialchars($app['office_desc'])
				. '</option>';
		}

		echo '      </select>
			</div>

			<!-- Applicants Dropdown -->
			<div class="form-group">
				<label>Select Applicant <span class="text-danger">*</span></label>

				<div id="applicantsdropdown">
					<span class="font-weight-bold text-danger">Select a Vacancy First...</span>
				</div>
			</div>

			<!-- Save Button -->
			<div class="form-group mt-3 d-flex justify-content-center">
				<button type="button"
						class="btn btn-success"
						id="save_schedule"
						>
					Save Event <i class="fa-solid fa-floppy-disk"></i>
				</button>
			</div>

		</div>

		<script>
		    $(document).on("change", "#schedule_vacancy", function() {

				var pubpos_id = $(this).val();

				$.ajax({
					url: "backend/bk_aminterviewscheduler.php",
					type: "POST",
					data: {
						request: "applicantsdropdown",
						pubpos_id: pubpos_id
					},
                    beforeSend: function() {
                        $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                    },
					success: function(response) {
                        $("#loadingSpinner").fadeOut(200, function() {
                            $("#loadingSpinner").css("display", "none");
                        });
						$("#applicantsdropdown").html(response);
					}
				});

			});

		let selectedApplicants = [];

		$(document).on("click", ".applicant-card", function () {

			let id = $(this).data("id");

			if (id === "all") {

				selectedApplicants = [];

				if (!$(this).hasClass("selected-applicant")) {

					$(".applicant-card").each(function () {

						let appId = $(this).data("id");

						if (appId !== "all") {
							selectedApplicants.push(appId);
							$(this).addClass("selected-applicant");
						}
					});

					$(this).addClass("selected-applicant");

				} else {

					$(".applicant-card").removeClass("selected-applicant");
				}

				console.log(selectedApplicants);
				return;
			}

			$(this).toggleClass("selected-applicant");

			if (selectedApplicants.includes(id)) {
				selectedApplicants = selectedApplicants.filter(x => x != id);
			} else {
				selectedApplicants.push(id);
			}

			console.log(selectedApplicants);
		});
		</script>
        ';

		break;

	case "applicantsdropdown":

		$pubpos_id = isset($_POST["pubpos_id"]) ? $_POST["pubpos_id"] : "";

		$applicants = execsqlSRS("
			SELECT 	snap.[snap_id]
					,snap.[pubpos_id]
					,userdet.[FirstName]
					,userdet.[LastName]
			FROM [tbl_Snapshot] snap

			LEFT JOIN [tbl_SnapshotUser] userdet
			ON userdet.[snap_id] = snap.[snap_id]

			WHERE snap.[IsActive] = 0
				AND snap.[pubpos_id] = ?
			", "Select", array(intval($pubpos_id)));

		$applicantscount = count($applicants);

		if (!empty($applicants)) {
			echo '<div id="schedule_applicant_list" class="d-flex flex-wrap gap-2">';

			echo '
			<div class="applicant-card select-all-card border border-danger rounded px-2 py-1 bg-light"
				data-id="all"
				style="cursor:pointer; transition:0.2s;">

				<span class="text-danger font-weight-bold small">
					Select All
				</span>

			</div>';
			foreach ($applicants as $app) {

				echo '
				<div class="applicant-card border border-success rounded px-2 py-1 bg-white"
					data-id="' . htmlspecialchars($app['snap_id']) . '"
					style="cursor:pointer; transition:0.2s;">

					<span class="text-success font-weight-bold small">
						' . htmlspecialchars($app['FirstName']) . ' ' . htmlspecialchars($app['LastName']) . '
					</span>

				</div>';
			}

			echo '</div>';
		} else {
			echo '<div class="">
				<div class="text-danger font-weight-bold">
					No Applicants for this Vacancy...
				</div>
			</div>';
		}

		break;

	case "saveapplicantschedule":

		break;


	case "viewevents":



		$data = [
			[
				"title" => "Examination",
				"start" => "2026-05-13T13:00:00",
				"end" => "2026-05-13T14:30:00",
				"backgroundColor" => "#dc3545",
				"borderColor" => "#dc3545",
				"textColor" => "#fff"
			],

			[
				"title" => "Initial Interview",
				"start" => "2026-05-15T12:00:00",
				"end" => "2026-05-15T17:00:00",
				"backgroundColor" => "#28a745",
				"borderColor" => "#28a745",
				"textColor" => "#fff"
			],

			[
				"title" => "Final Interview",
				"start" => "2026-05-18T07:00:00",
				"end" => "2026-05-18T17:00:00",
				"backgroundColor" => "#17a2b8",
				"borderColor" => "#17a2b8",
				"textColor" => "#fff"
			],

			[
				"title" => "Final Exam",
				"start" => "2026-05-18T07:00:00",
				"end" => "2026-05-18T17:00:00",
				"backgroundColor" => "#17a2b8",
				"borderColor" => "#17a2b8",
				"textColor" => "#fff"
			],

			[
				"title" => "Final Exam",
				"start" => "2026-06-17T07:00:00",
				"end" => "2026-06-17T17:00:00",
				"backgroundColor" => "#17a2b8",
				"borderColor" => "#17a2b8",
				"textColor" => "#fff"
			]
		];

		echo json_encode($data);
		break;
}
