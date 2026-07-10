<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$fields = isset($_POST["fields"]) ? $_POST["fields"] : "";
$operator = isset($_POST["operator"]) ? $_POST["operator"] : "";
$datavalue = isset($_POST["datavalue"]) ? $_POST["datavalue"] : "";
$logslocation = isset($_POST["logslocation"]) ? $_POST["logslocation"] : "";
$userid = isset($_POST["userid"]) ? $_POST["userid"] : "";

$currentdt = date("Y-m-d H:i:s");

switch ($request) {

	case "fetchpublications":

		$queryselect = execsqlSRS(
			"
	SELECT  pub.[publication_id]
			,pub.[pubtitle_name]
			,pub.[pubtitle_startdt]
			,pub.[pubtitle_enddt]
			,stat.[pubstatus_desc]
			,c.[color_desc]
			,pub.[UserID]
			,pub.[created_at]
			,pub.[IsActive]

	FROM [tbl_Publication] pub

	LEFT JOIN [tbl_PublicationStatus] stat
	ON stat.pubstatus_id = pub.pubstatus_id

	LEFT JOIN [tbl_Colors] c
	ON c.color_id = stat.color_id

	WHERE pub.[pubstatus_id] != '1'

	ORDER BY pub.publication_id DESC",
			"Search",
			array()
		);

		foreach ($queryselect as $publication) {

			$status = htmlspecialchars($publication["pubstatus_desc"]);
			$color = strtolower($publication["color_desc"] ?? 'secondary');

			echo "<tr id='fetchposition_" . htmlspecialchars($publication["publication_id"]) . "'
				  data-datavalue='" . htmlspecialchars($publication["publication_id"]) . "'>";
			echo "<td class='font-weight-bold'>" . htmlspecialchars($publication["pubtitle_name"]) . "</td>";
			echo "<td>" . htmlspecialchars($publication["pubtitle_startdt"]) . " - " . htmlspecialchars($publication["pubtitle_enddt"]) . "</td>";
			echo "<td><span class='badge badge-$color p-2'>$status</span></td>";
			echo "</tr>";
		}

		break;

	case "fetchpositions":

		$queryselect = execsqlSRS(
			"
			SELECT  pos.[pubpos_id]
					,pos.[publication_id]
					,pos.[position_title]
					,app.[appoint_desc]
					,c.[color_desc]
					,office.[office_desc]

			FROM [tbl_PublicationPosition] pos

			LEFT JOIN [tbl_ProfExpAppoint] app
			ON app.appoint_id = pos.appoint_id

			LEFT JOIN [tbl_Colors] c
			ON c.color_id = app.color_id

			LEFT JOIN [tbl_Attachment] att
			ON att.attach_id = pos.attach_id

			LEFT JOIN [tbl_Office] office
			ON office.[office_id] = pos.[office_id]

			ORDER BY pos.position_title, pos.publication_id",
			"Search",
			array()
		);

		foreach ($queryselect as $position) {

			$getpubdate = execsqlSRS("
                SELECT pub.[pubtitle_startdt], pub.[pubtitle_enddt]
                FROM [tbl_PublicationPosition] pos

                LEFT JOIN [tbl_Publication] pub
                ON pub.publication_id = pos.publication_id
                WHERE pos.pubpos_id = ?
                ", "Select", array($position["pubpos_id"]));

			$appoint = htmlspecialchars($position["appoint_desc"]);
			$color = strtolower($position["color_desc"] ?? 'secondary');

			echo "<tr id='fetchapplicants_" . htmlspecialchars($position["pubpos_id"]) . "'
				  data-datavalue='" . htmlspecialchars($position["pubpos_id"]) . "'>";
			echo "<td class='font-weight-bold'>" . htmlspecialchars($position["position_title"]) . "</td>";
			echo "<td class='font-weight-bold'>" . htmlspecialchars($position["office_desc"]) . "</td>";
			echo "<td class='font-weight-bold'>"
				. date("F j, Y", strtotime($getpubdate[0]["pubtitle_startdt"]))
				. " - "
				. date("F j, Y", strtotime($getpubdate[0]["pubtitle_enddt"]))
				. "</td>";
			echo "<td>
				<button class='btn btn-sm btn-info'
						id='positionsummary_" . htmlspecialchars($position["pubpos_id"]) . "'
						data-datavalue='" . htmlspecialchars($position["pubpos_id"]) . "'
						data-openmodallabel='" . htmlspecialchars($position["position_title"]) . "'
						data-tooltip='View Summary'>
					<i class='fa-solid fa-rectangle-list'></i>
				</button>
                <button class='btn btn-sm btn-info'
                        id='view_position_" . htmlspecialchars($position["pubpos_id"]) . "'
                        data-datavalue='" . htmlspecialchars($position["pubpos_id"]) . "'
                        data-backendurl='backend/bk_homepage.php'
                        data-backendrequest='viewpositiondetailsuser'
                        data-openmodal='#attachmentmodal'
                        data-openmodallabel='View Position - " . htmlspecialchars($position["position_title"]) . "'
                        data-openmodalbody='#attachmentmodalcontent'
                        data-tooltip='View Position'>
                    <i class='fa-solid fa-eye'></i>
                </button>
			</td>";
			echo "</tr>";
		}

		break;

	case "fetchapplicants":
		$fetchapplicants = execsqlSRS(
			"
			SELECT
				snap.snap_id,
				stat.status_code,
				c.color_desc,
				snap.UserID,
				userdet.LastName,
				userdet.FirstName,
				userdet.MiddleName,
				snap.AppliedDate

			FROM tbl_Snapshot snap

			OUTER APPLY (
				SELECT TOP 1
					u.LastName,
					u.FirstName,
					u.MiddleName
				FROM tbl_SnapshotUser u
				WHERE u.UserID = snap.UserID
				ORDER BY u.UserID
			) userdet

			OUTER APPLY (
				SELECT TOP 1
					s.status_code,
					s.color_id
				FROM tbl_SnapshotStatus s
				WHERE s.snap_status = snap.snap_status
				ORDER BY s.snap_status
			) stat

			OUTER APPLY (
				SELECT TOP 1
					c.color_desc
				FROM tbl_Colors c
				WHERE c.color_id = stat.color_id
			) c

			WHERE snap.pubpos_id = ?

			ORDER BY userdet.FirstName
		",
			"Select",
			array(intval($datavalue))
		);

		$fetchposition = execsqlSRS("
            SELECT  pos.[pubpos_id]
                    ,pos.[position_title]
            FROM [tbl_PublicationPosition] pos

            WHERE pos.[pubpos_id] = ?
            ", "Select", array(intval($datavalue)));

		$totalApplicants = count($fetchapplicants);

		echo "<div class='card border border-success'>";

		echo "<div class='card-body p-0 table-responsive'>";

		echo "<table class='table table-hover mb-0'>";

		echo "<thead class='table-success'>";

		echo "
        <tr class='bg-success'>
            <th colspan='10' style='position: sticky; top: 0; z-index: 20;'>
                <div class='font-weight-bold ml-2'>
                    <span>
                        Applicants for " . $fetchposition[0]['position_title'] . "
                    </span>
                </div>
            </th>
        </tr>";

		echo "<tr>";
		echo "<th>#</th>";
		echo "<th>Name of Applicant</th>";
		echo "<th>Date of Application</th>";
		echo "<th>Status of Application</th>";
		echo "</tr>";
		echo "</thead>";

		echo "<tbody>";

		if (!empty($fetchapplicants)) {

			$i = 1;

			foreach ($fetchapplicants as $app) {

				$lastname = htmlspecialchars($app["LastName"] ?? '');
				$firstname = htmlspecialchars($app["FirstName"] ?? '');
				$middlename = htmlspecialchars($app["MiddleName"] ?? '');

				$fullname = trim($firstname . " " . $middlename . " " . $lastname);

				$rawDate = $app["AppliedDate"] ?? '';
				$formattedDate = '';

				if (!empty($rawDate)) {
					$formattedDate = date("l, F d, Y • h:i A", strtotime($rawDate));
				}

				echo "<tr id='attachmentreviewer_" . htmlspecialchars($app['snap_id']) . "'
					  data-datavalue='" . htmlspecialchars($app['snap_id']) . "'
					  data-userid='" . htmlspecialchars($app['UserID']) . "'
					  data-openmodallabel='" . htmlspecialchars($fullname) . "'
				  >";

				echo "<td class='font-weight-bold text-success'>$i</td>";

				echo "<td class='font-weight-bold'>$fullname</td>";

				echo "<td>$formattedDate</td>";

				echo "<td><span class='badge badge-" . $app['color_desc'] . " p-2'>" . $app['status_code'] . "</span></td>";

				echo "</tr>";

				$i++;
			}
		} else {

			echo "<tr>";
			echo "<td colspan='4' class='text-center font-weight-bold p-3'>";
			echo "No applicants found...";
			echo "</td>";
			echo "</tr>";
		}

		echo "</tbody>";
		echo "</table>";
		echo "</div>";
		echo "</div>";

		break;

	case "attachmentreviewer":

		$files = execsqlSRS("
		SELECT snapattach_id, snap_id, entity_type, entity_id,
			attach_id, file_name, file_path, created_at, checked
		FROM tbl_SnapshotAttachment
		WHERE snap_id = ?
	", "Select", [intval($datavalue)]);

		$getpubpos = execsqlSRS("
		SELECT TOP 1 pubpos_id
		FROM tbl_Snapshot
		WHERE snap_id = ?
		", "", array(intval($datavalue)));

		$user = execsqlSRS("
		SELECT TOP 1 *
		FROM tbl_SnapshotUser
		WHERE snap_id = ?
	", "Select", [intval($datavalue)]);

		$user = $user[0] ?? [];
		
        $profilepicture = execsqlSRS("
        SELECT file_path
        FROM tbl_SnapshotAttachment
        WHERE snap_id = ?
        AND entity_type = 'snapuser_id'
        AND file_name LIKE 'profile%'
    ", "Select", array(intval($datavalue)));

        $img = '/JobPortal/dist/img/tau-logo.png';

        if (!empty($profilepicture[0]['file_path'])) {
            $img = '/JobPortal/' . str_replace('../', '', $profilepicture[0]['file_path']);
        }

		$typeLabels = [
			'snapuser_id'     => 'PDS/Exp/PR',
			'snapeduc_id'     => 'Education',
			'snapelig_id'     => 'Eligibility',
			'snapexp_id'      => 'Work Experience',
			'snapvolwork_id'  => 'Volunteer Work',
			'snapld_id'       => 'Learning & Development',
			'snaporgassoc_id' => 'Organization Association',
			'snapnonacad_id'  => 'Non-Academic Recognition'
		];

		$tableMap = [
			'snapeduc_id' => [
				'table' => 'tbl_SnapshotEducation',
				'key'   => 'snapeduc_id',
				'label' => 'degree_name'
			],
			'snapelig_id' => [
				'table' => 'tbl_SnapshotEligibility',
				'key'   => 'snapelig_id',
				'label' => 'elig_type'
			],
			'snapexp_id' => [
				'table' => 'tbl_SnapshotExp',
				'key'   => 'snapexp_id',
				'label' => 'position'
			],
			'snapvolwork_id' => [
				'table' => 'tbl_SnapshotVolWork',
				'key'   => 'snapvolwork_id',
				'label' => 'org_name'
			],
			'snapld_id' => [
				'table' => 'tbl_SnapshotLD',
				'key'   => 'snapld_id',
				'label' => 'ld_title'
			],
			'snaporgassoc_id' => [
				'table' => 'tbl_SnapshotOrgAssoc',
				'key'   => 'snaporgassoc_id',
				'label' => 'orgassoc_desc'
			],
			'snapnonacad_id' => [
				'table' => 'tbl_SnapshotNonAcad',
				'key'   => 'snapnonacad_id',
				'label' => 'nonacad_desc'
			]
		];

 echo "
<div class='card border border-success mb-3'>
<div class='card-header bg-success text-white'>
    <h5 class='mb-0'>Applicant Profile</h5>
</div>

<div class='card-body'>

    <div class='d-flex align-items-start' style='gap:20px;'>

        <!-- PROFILE IMAGE -->
        <div style='flex:0 0 200px;'>
            <div style='
                width: 180px;
                height: 180px;
                border-radius: 50%;
                overflow: hidden;
                border: 3px solid #28a745;
            '>
                <img src='{$img}'
                     style='width:100%; height:100%; object-fit:cover;'>
            </div>
        </div>

        <!-- DETAILS -->
        <div style='flex:1;'>

            <div class='row'>

                <!-- BASIC INFO CARD -->
                <div class='col-md-6 mb-3'>
                    <div class='card shadow-sm'>
                        <div class='card-body'>
                            <h6 class='text-success mb-2'>Basic Information</h6>

                            <strong>Name:</strong><br>
                            " . htmlspecialchars(
            trim(
                ($user['FirstName'] ?? '') . ' ' .
                    ($user['MiddleName'] ?? '') . ' ' .
                    ($user['LastName'] ?? '') . ' ' .
                    (
                        !empty($user['ExtName']) && strtolower($user['ExtName']) !== 'n/a'
                        ? $user['ExtName']
                        : ''
                    )
            )
        ) . "
                            <hr class='my-2'>

                            <strong>Email:</strong> " . htmlspecialchars($user['Email'] ?? '') . "<br>
                            <strong>Mobile:</strong> " . htmlspecialchars($user['MobileNumber'] ?? '') . "<br>
                            <strong>Telephone:</strong> " . htmlspecialchars($user['TelephoneNumber'] ?? '') . "<br>
                        </div>
                    </div>
                </div>

                <!-- PERSONAL INFO CARD -->
                <div class='col-md-6'>
                    <div class='card shadow-sm'>
                        <div class='card-body'>
                            <h6 class='text-success mb-2'>Personal Details</h6>

                            <strong>Date of Birth:</strong> " . (!empty($user['DateOfBirth'])
            ? date('F d, Y', strtotime($user['DateOfBirth']))
            : '') . "<br>

                            <strong>Age:</strong> " . htmlspecialchars($user['Age'] ?? '') . "<br>
                            <strong>Sex:</strong> " . htmlspecialchars($user['Sex'] ?? '') . "<br>
                            <strong>Civil Status:</strong> " . htmlspecialchars($user['CivilStatus'] ?? '') . "<br>
                            <strong>Nationality:</strong> " . htmlspecialchars($user['Nationality'] ?? '') . "<br>
                            <strong>Religion:</strong> " . htmlspecialchars($user['Religion'] ?? '') . "<br>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ADDRESS SECTION -->
    <div class='row'>

        <div class='col-md-6'>
            <div class='card border-light shadow-sm'>
                <div class='card-body'>
                    <h6 class='text-success'>Home Address</h6>
                    " . htmlspecialchars(
            ($user['HmHouse'] ?? '') . ' ' .
                ($user['HmStreet'] ?? '') . ', ' .
                ($user['HmBarangay'] ?? '') . ', ' .
                ($user['HmCity'] ?? '') . ', ' .
                ($user['HmProvince'] ?? '') . ' ' .
                ($user['HmZip'] ?? '')
        ) . "
                </div>
            </div>
        </div>

        <div class='col-md-6'>
            <div class='card border-light shadow-sm'>
                <div class='card-body'>
                    <h6 class='text-success'>Current Address</h6>
                    " . htmlspecialchars(
            ($user['CurHouse'] ?? '') . ' ' .
                ($user['CurStreet'] ?? '') . ', ' .
                ($user['CurBarangay'] ?? '') . ', ' .
                ($user['CurCity'] ?? '') . ', ' .
                ($user['CurProvince'] ?? '') . ' ' .
                ($user['CurZip'] ?? '')
        ) . "
                </div>
            </div>
        </div>

    </div>

</div>
</div>
";

		if (empty($files)) {
			echo "<div class='alert alert-warning'>No attachments found for this snapshot.</div>";
			break;
		}

		echo "
	<div class='card border border-success'>
	<div class='card-header bg-success text-white'>
		<h5 class='mb-0'>Attachments</h5>
	</div>

	<div class='card-body p-0 table-responsive'>
		<table class='table table-hover mb-0'>
		<thead class='table-success'>
			<tr>
			<th>#</th>
			<th>Reference</th>
			<th>Type</th>
			<th>Uploaded</th>
			<th>Checklist</th>
			</tr>
		</thead>
		<tbody>
	";

		$i = 1;

		foreach ($files as $f) {

			$id       = $f['snapattach_id'];
			$type     = $f['entity_type'] ?? '';
			$entityId = $f['entity_id'] ?? 0;
			$date = '';

			if (!empty($f['created_at'])) {
				try {
					$dt = new DateTime($f['created_at']);
					$date = $dt->format('l, F j, Y • g:i A');
				} catch (Exception $e) {
					$date = htmlspecialchars($f['created_at']);
				}
			}

			$typeLabel = $typeLabels[$type] ?? $type;

			$reference = 'Unknown';

			if ($type === 'snapuser_id') {
				$reference = 'PDS/Work Experience/Performance Rating';
			} elseif (!empty($tableMap[$type])) {

				$tbl = $tableMap[$type]['table'];
				$key = $tableMap[$type]['key'];
				$col = $tableMap[$type]['label'];

				$res = execsqlSRS("
				SELECT TOP 1 $col AS label
				FROM $tbl
				WHERE $key = ?
			", "Select", [$entityId]);

				if (!empty($res[0]['label'])) {
					$reference = $res[0]['label'];
				}
			}

			$path = $f['file_path'] ?? '';
			$url  = !empty($path) ? str_replace('../', '/JobPortal/', $path) : '';

			echo '
		<tr onclick="togglePreview(' . $id . ', \'' . $url . '\')" style="cursor:pointer;">
			<td class="text-success font-weight-bold">' . $i . '</td>
			<td>' . $reference . '</td>
			<td>' . $typeLabel . '</td>
			<td>' . $date . '</td>

			<td style="text-align:center; cursor:pointer;">';

			$isChecked = ($f['checked'] === "0" || $f['checked'] === 0);

			if ($isChecked) {

				echo "
					<i class='fa-solid fa-toggle-on fa-2x text-success checklist-toggle'
					id='attachmentchecklist_" . $id . "'
					data-id='" . $id . "'
					data-value='0'
					onclick='toggleChecklist(this, event)';>
					</i>
				";
			} else {

				echo "
					<i class='fa-solid fa-toggle-off fa-2x text-danger checklist-toggle'
					id='attachmentchecklist_" . $id . "'
					data-id='" . $id . "'
					data-value='1'
					onclick='toggleChecklist(this, event)';>
					</i>
				";
			}

			echo '
			</td>
		</tr>

		<tr id="preview-' . $id . '" style="display:none;">
			<td colspan="5">
				<iframe src="" style="width:100%;height:400px;border:1px solid #ddd;"></iframe>
			</td>
		</tr>
		';

			$i++;
		}

		echo "
		</tbody>
		</table>
	</div>
	</div>

	<div class='card border border-danger mt-3'>
	<div class='card-header bg-danger text-white'>
		<h6 class='mb-0'>Reviewer Action</h6>
	</div>

	<div class='card-body'>

		<!-- Remarks -->
		<!--
		<div class='form-group'>
		<label class='font-weight-bold'>Remarks</label>
		<textarea class='form-control' rows='4' placeholder='Enter your remarks here...' id='reviewattachremarks'></textarea>
		</div>
		-->

		<!-- Buttons -->
		<div class='d-flex justify-content-center mt-3' style='gap:10px;'>

		<button class='btn btn-success px-4'
				id='markasreviewed_" . $f['snap_id'] . "'
				data-datavalue=" . $f['snap_id'] . "
				data-userid=" . $user['UserID'] . "
				data-pubpos=" . $getpubpos[0]['pubpos_id'] . "
				>
			<i class='fa fa-check mr-1'></i> Mark as Reviewed
		</button>

		</div>

	</div>
	</div>

	<script>
	function togglePreview(id, url) {
		const row = document.getElementById('preview-' + id);
		const iframe = row.querySelector('iframe');

		const isOpen = row.style.display === 'table-row';

		if (!isOpen) {
			iframe.src = url;
			row.style.display = 'table-row';
		} else {
			iframe.src = '';
			row.style.display = 'none';
		}
	}

	function toggleChecklist(el, event = null) {

		if (event) event.stopPropagation();

		const id = el.getAttribute('data-id');
		const current = el.getAttribute('data-value');

		const newValue = (current == '0') ? 1 : 0;

		fetch('backend/bk_amreviewattachments.php', {
			method: 'POST',
			body: new URLSearchParams({
				request: 'update_checklist',
				id: id,
				checked: newValue
			})
		})
		.then(res => res.json())
		.then(res => {
			if (res.success) {

				if (newValue == 0) {
					el.classList.remove('fa-toggle-off', 'text-danger');
					el.classList.add('fa-toggle-on', 'text-success');
					el.setAttribute('data-value', '0');
				} else {
					el.classList.remove('fa-toggle-on', 'text-success');
					el.classList.add('fa-toggle-off', 'text-danger');
					el.setAttribute('data-value', '1');
				}

			} else {
				alert('Update failed');
			}
		})
		.catch(() => {
			alert('Error updating checklist');
		});
	}
	</script>
	";

		break;

	case "update_checklist":

		$id = intval($_POST['id'] ?? 0);
		$checked = $_POST['checked'] ?? null;

		execsqlSRS("
			UPDATE tbl_SnapshotAttachment
			SET checked = ?
			WHERE snapattach_id = ?
		", "Update", [$checked, $id]);

		header('Content-Type: application/json');
		echo json_encode(["success" => true]);

		break;

	case "mark_as_reviewed":

		$changedby = isset($_POST["changedby"]) ? $_POST["changedby"] : "";
		$remarks = isset($_POST["remarks"]) ? $_POST["remarks"] : "";
		$snapid = isset($_POST["snapid"]) ? $_POST["snapid"] : "";

		$markasreviewed = execsqlSRS("
			UPDATE [tbl_Snapshot]
			SET [snap_status] = '3'
				,[UpdatedAt] = ?
			WHERE [snap_id] = ?
			", "Update", array($currentdt, intval($snapid)));

		$insertlogs = execsqlSRS(
			"
			INSERT INTO [tbl_SnapshotHistory]
			(
				[snap_id]
				,[snap_status]
				,[changed_at]
				,[changed_by]
				,[remarks]
				,[IsActive]
			)
			VALUES(?, '3', ?, ?, 'Application has been included for selection Line-up', '0')
			",
			"Insert",
			array(
				intval($snapid),
				$currentdt,
				intval($changedby)
			)
		);

		$insertintonotifs = execsqlSRS("
				INSERT INTO [tbl_Notifications]
				(
					[notif_title],
					[notif_message],
					[color_id],
					[UserID],
					[target_url],
					[IsRead],
					[ReadAt],
					[IsActive]
				)
				VALUES (?, 'Application (TAU-APP-000{$snapid}) has been included for selection line-up', ?, ?, ?, ?, NULL, ?)
			", "Insert", [
			'Reviewed',
			2,
			intval($userid),
			'applicationstatus.php',
			1,
			0
		]);

		$selectuserdetails = execsqlSRS("
			SELECT TOP 1
				snap.[Email],
				snap.[LastName],
				snap.[FirstName],
				snap.[MiddleName],
				pos.[pubpos_id]
			FROM [tbl_SnapshotUser] snap
			LEFT JOIN [tbl_Snapshot] pos
				ON pos.[snap_id] = snap.[snap_id]
			WHERE snap.[UserID] = ? AND snap.[snap_id] = ?
		", "Select", [
			intval($userid),
			intval($snapid)
		]);

		$email = $selectuserdetails[0]['Email'] ?? '';
		$lastname = $selectuserdetails[0]['LastName'] ?? '';
		$firstname = $selectuserdetails[0]['FirstName'] ?? '';
		$middlename = $selectuserdetails[0]['MiddleName'] ?? '';
		$appnum = $selectuserdetails[0]['pubpos_id'] ?? '';

		$mail = new PHPMailer(true);

		try {

			$mail->isSMTP();
			$mail->SMTPDebug = 0;
			$mail->Host = 'smtp.gmail.com';
			$mail->SMTPAuth = true;
			$mail->Username = 'tau_hrmo-rsp@tau.edu.ph';
			$mail->Password = 'quec zbkq ntka efhp';
			$mail->SMTPSecure = 'tls';
			$mail->Port = 587;

			$mail->setFrom(
				'tau_hrmo-rsp@tau.edu.ph',
				'TAU HRMO - Recruitment, Selection and Placement'
			);

			$mail->addAddress($email, "$firstname $lastname");

			$mail->isHTML(true);
			$mail->Subject = 'TAU Job Portal Status Update';

			$mail->Body =
				"Hello <b>$firstname $lastname</b><br><br>" .
				"We would like to inform you that your application " .
				"<b>TAU-APP-000{$appnum}</b> has been <b>Reviewed</b> by HRMO.<br><br>" .
				"You may check the status in the TAU Job Portal.<br><br>";

			$mail->send();
		} catch (Exception $e) {

			echo json_encode([
				'status' => 'error',
				'message' => $mail->ErrorInfo
			]);

			exit;
		}

		echo json_encode([
			'status' => 'success',
			'message' => 'Successfully Marked as Reviewed.'
		]);

		break;
}
