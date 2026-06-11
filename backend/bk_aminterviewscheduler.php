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

		$schedule_title = isset($_POST["schedule_title"]) ? $_POST["schedule_title"] : "";
		$schedule_description = isset($_POST["schedule_description"]) ? $_POST["schedule_description"] : "";
		$schedule_startdate = isset($_POST["schedule_startdate"]) ? $_POST["schedule_startdate"] : "";
		$schedule_enddate = isset($_POST["schedule_enddate"]) ? $_POST["schedule_enddate"] : "";
		$schedule_vacancy = isset($_POST["schedule_vacancy"]) ? $_POST["schedule_vacancy"] : "";
		$applicants = isset($_POST["applicants"])
			? json_decode($_POST["applicants"], true)
			: [];

		$schedule_startdate = date('Y-m-d H:i:s', strtotime($schedule_startdate));
		$schedule_enddate = date('Y-m-d H:i:s', strtotime($schedule_enddate));

		$insertsched = execsqlSRS("
			INSERT INTO tbl_SnapshotSched
			(
				sched_title,
				sched_desc,
				start_date,
				end_date,
				created_by,
				created_at,
				IsActive
			)
			VALUES
			(
				?, ?, ?, ?, ?, ?, 0
			)
		", "Insert", array(
			$schedule_title,
			$schedule_description,
			$schedule_startdate,
			$schedule_enddate,
			$userid,
			$currentdt
		));

		$selectsched = execsqlSRS("
			SELECT TOP 1 [snapsched_id], [sched_title], [sched_desc]
			FROM [tbl_SnapshotSched]
			WHERE [created_by] = ?
			ORDER BY [snapsched_id] DESC
		", "Select", array(
			$userid
		));

		$snapsched_id = $selectsched[0]['snapsched_id'];
		$sched_desc = $selectsched[0]['sched_desc'];
		$sched_title = $selectsched[0]['sched_title'];

		foreach ($applicants as $snap_id) {

			$sql = execsqlSRS("
            INSERT INTO tbl_SnapshotSchedLib
            (
                snapsched_id,
                snap_id,
                IsActive
            )
            VALUES
            (
                ?, ?, 0
            )
			", "Insert", array(
				$snapsched_id,
				$snap_id
			));

			$insertnotifs = execsqlSRS("
				INSERT INTO tbl_Notifications
				(
					notif_title,
					notif_message,
					color_id,
					UserID,
					target_url,
					IsRead,
					IsActive
				)
				VALUES
				(
					?, ?, 2, ?, 'applicationstatus.php', 1, 0
				)
			", "Insert", array(
				$sched_title,
				$sched_desc,
				$snap_id,
			));

			$updatesnapshot = execsqlSRS("
				UPDATE [tbl_Snapshot]
				SET [snap_status] = 6
				    [UpdatedAt] = ?
				WHERE [snap_id] = ?
				", "Update", array(
				$currentdt,
				$snap_id
			));

			$insertsnaphistory = execsqlSRS("
				INSERT INTO [tbl_SnapshotHistory]
				(
					[snap_id]
					,[snap_status]
					,[changed_at]
					,[changed_by]
					,[remarks]
					,[IsActive]
				)
					VALUES(?, 6, ?, ?, 'Interview and Examination', '0')
				", "Insert", array(
				$snap_id,
				$currentdt,
				$userid,
			));
		}

		echo json_encode([
			"status" => "success",
			"message" => "Event has been successfully scheduled."
		]);

		break;


	case "fetchevents":

		$events = execsqlSRS("
		SELECT
			snapsched_id,
			sched_title,
			start_date,
			end_date
		FROM tbl_SnapshotSched
		WHERE IsActive = 0
	", "Select", []);

		$data = [];

		foreach ($events as $event) {

			$data[] = [
				"snapsched_id" => $event["snapsched_id"],
				"title" => $event["sched_title"],
				"start" => date('Y-m-d\TH:i:s', strtotime($event["start_date"])),
				"end" => date('Y-m-d\TH:i:s', strtotime($event["end_date"])),
				"backgroundColor" => "#17a2b8",
				"borderColor" => "#17a2b8",
				"textColor" => "#fff"
			];
		}

		echo json_encode($data);
		break;

	case "viewevent":

		$snapsched_id = isset($_POST["datavalue"])
			? intval($_POST["datavalue"])
			: 0;

		if ($snapsched_id <= 0) {
			echo '
            <div class="alert alert-danger">
                Invalid Schedule ID.
            </div>
        ';
			exit;
		}

		$schedule = execsqlSRS("
        SELECT
            snapsched_id,
            sched_title,
            sched_desc,
            start_date,
            end_date,
            created_at
        FROM tbl_SnapshotSched
        WHERE snapsched_id = ?
    ", "Select", array(
			$snapsched_id
		));

		if (empty($schedule)) {
			echo '
            <div class="alert alert-danger">
                Schedule not found.
            </div>
        ';
			exit;
		}

		$schedule = $schedule[0];

		$applicants = execsqlSRS("
        SELECT
            su.LastName,
            su.FirstName,
            su.ExtName,
            su.Sex
        FROM tbl_SnapshotSchedLib ssl
        INNER JOIN tbl_SnapshotUser su
            ON ssl.snap_id = su.snap_id
        WHERE ssl.snapsched_id = ?
        AND ssl.IsActive = 0
        ORDER BY su.LastName ASC, su.FirstName ASC
    ", "Select", array(
			$snapsched_id
		));
?>

		<div class="container-fluid">

			<div class="row mb-3">
				<div class="col-md-12">
					<label class="fw-bold">Schedule Title</label>
					<div class="form-control bg-light">
						<?php echo htmlspecialchars($schedule['sched_title']); ?>
					</div>
				</div>
			</div>

			<div class="row mb-3">
				<div class="col-md-12">
					<label class="fw-bold">Description</label>
					<div class="form-control bg-light" style="min-height:100px;">
						<?php echo nl2br(htmlspecialchars($schedule['sched_desc'])); ?>
					</div>
				</div>
			</div>

			<div class="row mb-3">

				<div class="col-md-6">
					<label class="fw-bold">Start Date</label>
					<div class="form-control bg-light">
						<?php echo date('F d, Y h:i A', strtotime($schedule['start_date'])); ?>
					</div>
				</div>

				<div class="col-md-6">
					<label class="fw-bold">End Date</label>
					<div class="form-control bg-light">
						<?php echo date('F d, Y h:i A', strtotime($schedule['end_date'])); ?>
					</div>
				</div>

			</div>

			<div class="row">
				<div class="col-md-12">

					<label class="fw-bold">
						Applicants Scheduled (<?php echo count($applicants); ?>)
					</label>

					<div class="table-responsive">

						<table class="table table-bordered table-hover align-middle">
							<thead class="table-success">
								<tr>
									<th width="80%">Full Name</th>
									<th width="20%">Sex</th>
								</tr>
							</thead>
							<tbody>

								<?php if (!empty($applicants)) { ?>

									<?php foreach ($applicants as $applicant) {

										$fullname =
											$applicant['LastName'] . ', ' .
											$applicant['FirstName'];

										$extname = trim($applicant['ExtName']);

										if (!empty($extname) && strtolower($extname) !== 'n/a') {
											$fullname .= ' ' . $extname;
										}
									?>

										<tr>
											<td><?php echo htmlspecialchars($fullname); ?></td>
											<td><?php echo htmlspecialchars($applicant['Sex']); ?></td>
										</tr>

									<?php } ?>

								<?php } else { ?>

									<tr>
										<td colspan="2" class="text-center text-muted">
											No applicants found.
										</td>
									</tr>

								<?php } ?>

							</tbody>
						</table>

					</div>

				</div>
			</div>

		</div>

<?php

		break;
}
