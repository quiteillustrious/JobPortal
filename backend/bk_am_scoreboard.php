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
$RID = isset($_POST["RID"]) ? $_POST["RID"] : "";

$currentdt = date("Y-m-d H:i:s");

switch ($request) {

	case "fetchpublications":

		$queryselect = execsqlSRS(
			"
	SELECT  pub.[publication_id]
			,pub.[job_type]
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

	WHERE pub.[pubstatus_id] = '4' AND job_type = '1'

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
		$type = 1;
		if($RID == 4){
			$type = 0;
		}
		$queryselect = execsqlSRS(
			"
			SELECT  pos.[pubpos_id]
					,pos.[publication_id]
					,pos.[job_type]
					,pos.[position_title]
					,app.[appoint_desc]
					,c.[color_desc]
					,office.[office_desc]

			FROM [tbl_PublicationPosition] pos
			LEFT JOIN [tbl_Publication] pub ON pub.publication_id = pos.publication_id
			LEFT JOIN [tbl_ProfExpAppoint] app
			ON app.appoint_id = pos.appoint_id

			LEFT JOIN [tbl_Colors] c
			ON c.color_id = app.color_id

			LEFT JOIN [tbl_Attachment] att
			ON att.attach_id = pos.attach_id

			LEFT JOIN [tbl_Office] office
			ON office.[office_id] = pos.[office_id]
			WHERE pub.[pubstatus_id] = '4' AND pos.job_type = '$type'
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
				snap.UserID,
				userdet.LastName,
				userdet.FirstName,
				userdet.MiddleName,
				snap.AppliedDate,
				score.snap_id AS scored
			FROM tbl_Snapshot snap
			LEFT JOIN [tbl_SnapshotDelRem] dr ON dr.snap_id = snap.snap_id

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
					[snap_id]
				FROM [tbl_SnapshotSB] u
				WHERE u.[snap_id] = snap.[snap_id]
				AND u.[pubpos_id] = $datavalue
				ORDER BY u.snap_id
			) score
			
			
			WHERE dr.IsQual = '0' AND snap.pubpos_id = ?

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

				$scored = htmlspecialchars($app["scored"] ?? '');
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
				if($scored == "" || $scored == null){
						echo "<td><span class='badge badge-danger p-2'>No Score Yet</span></td>";
				}else{
					
				}
			
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
		
		$getcriteria = execsqlSRS("SELECT 
						[col_id]
					  ,[mothercol_id]
					  ,[rating_col]
					  ,[max_value]
					  ,[IsActive] FROM [tbl_SnapshotSBCol] WHERE [mothercol_id] = '0'", "SELECT", array());

		echo "<table class='table table-hover mb-0' style='width: 100%;'>";
		echo "<tbody>";
		
		
		echo "<tr>";
		foreach($getcriteria as $first){
			$Mother = $first["col_id"] ?? "";
			$Title = $first["rating_col"] ?? "";
			$Points = $first["max_value"] ?? "";
			
			echo '<td style="width: 50%; text-align:center;">'.$Title. " ( " . $Points . " ) " .'</td>';
			echo "<tr>";
			$getchild = execsqlSRS("SELECT 
						[col_id]
					  ,[mothercol_id]
					  ,[rating_col]
					  ,[max_value]
					  ,[IsActive] FROM [tbl_SnapshotSBCol] WHERE [mothercol_id] = '$Mother'", "SELECT", array());
				foreach($getchild  as $second){
					$Mother2 = $second["col_id"] ?? "";
					$Title2 = $second["rating_col"] ?? "";
					$Points2 = $second["max_value"] ?? "";
					
					echo '<td style="width: 50%; text-align:center;">'.$Title2. " ( " . $Points2 . " ) " .'</td>';
					
					
				}
			echo "</tr>";
			
		}
		echo "</tr>";
		
		echo "</tbody>";
		echo "</table>";
		
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
