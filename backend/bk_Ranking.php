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
$UserID = isset($_POST["UserID"]) ? $_POST["UserID"] : "";
$RID = isset($_POST["RID"]) ? $_POST["RID"] : "";
$pubposid = isset($_POST["pubposid"]) ? $_POST["pubposid"] : "";
$fullname = isset($_POST["fullname"]) ? $_POST["fullname"] : "";
$title = isset($_POST["title"]) ? $_POST["title"] : "";

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
				  data-datavalue='" . htmlspecialchars($position["pubpos_id"]) . "'
				  data-title='" . htmlspecialchars($position["position_title"]) . "'
				  
				  >";
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


	case "updaterecordsofusers":
	$fetchapplicants = execsqlSRS(
			"
			SELECT
				snap.snap_id,
				snap.UserID,
				userdet.LastName,
				userdet.FirstName,
				userdet.MiddleName,
				snap.AppliedDate,
				score.snap_id AS scored,
				sbr.[avg_points]
			FROM tbl_Snapshot snap
			LEFT JOIN [tbl_SnapshotDelRem] dr ON dr.snap_id = snap.snap_id
			LEFT JOIN [tbl_SnapshotSBRanking] sbr ON sbr.snap_id = snap.snap_id
			
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
				ORDER BY u.snap_id
			) score
			
			WHERE dr.IsQual = '0' AND snap.pubpos_id = ?

			ORDER BY sbr.[avg_points] DESC
		",
			"Select",
			array(intval($datavalue))
		);
		
		$getstatus_desc = execsqlSRS("SELECT [status_desc],[status_code] FROM [tbl_SnapshotStatus] where [snap_status] = '7' AND [IsActive] = '0'", "SELECT", []);
		$status_desc = $getstatus_desc[0]["status_desc"] ?? "";
		$status_code = $getstatus_desc[0]["status_code"] ?? "";
		
		foreach ($fetchapplicants as $app) {
			$snap_id = htmlspecialchars($app["snap_id"] ?? '');
			$UserID = htmlspecialchars($app["UserID"] ?? '');
			
			
			$update = execsqlSRS("UPDATE [tbl_Snapshot] SET 
				[snap_status] = '7',
				[UpdatedAt] = GETDATE()
				WHERE [snap_id] = '$snap_id' ","Update",[]);
				
			//History
			$insert = execsqlSRS("INSERT INTO [tbl_SnapshotHistory]
			([snap_id],[snap_status],[changed_at],[changed_by],[remarks],[IsActive])
			VALUES
			(:snap_id,:snap_status,GETDATE(),:changed_by,:remarks,:IsActive)
			","Insert",[
			":snap_id"=>$snap_id,
			":snap_status"=>'7',
			":changed_by"=>$userid,
			":remarks"=>$status_desc,
			":IsActive"=>'0',
			]);		
			//Notifications
			$insert = execsqlSRS("INSERT INTO [tbl_Notifications]
			([notif_title],[notif_message],[color_id],[UserID],[target_url],[IsRead],[IsActive])
			VALUES
			(:notif_title,:notif_message,:color_id,:UserID,:target_url,:IsRead,:IsActive)
			","Insert",[
			":notif_title"=>$status_code,
			":notif_message"=>$status_desc,
			":color_id"=>'2',
			":UserID"=>$UserID,
			":target_url"=>'applicationstatus.php',
			":IsRead"=>'1',
			":IsActive"=>'0',
			]);
		}
		execsqlSRS("INSERT INTO [tbl_SnapShotSBChecker] ([pubpos_id],[IsActive]) VALUES ($datavalue, '0')","Insert",[]);
		
		echo json_encode(["title"=>"Success", "result"=>"success", "message"=>"Successfully save the score."]);
		
		
	break;


	case "fetchapplicants":
		if($RID <= 3){
			$fetchapplicants = execsqlSRS(
			"
			SELECT
				snap.snap_id,
				snap.UserID,
				userdet.LastName,
				userdet.FirstName,
				userdet.MiddleName,
				snap.AppliedDate,
				score.snap_id AS scored,
				sbr.[avg_points]
			FROM tbl_Snapshot snap
			LEFT JOIN [tbl_SnapshotDelRem] dr ON dr.snap_id = snap.snap_id
			LEFT JOIN [tbl_SnapshotSBRanking] sbr ON sbr.snap_id = snap.snap_id
			
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
				ORDER BY u.snap_id
			) score
			
			WHERE dr.IsQual = '0' AND snap.pubpos_id = ?

			ORDER BY sbr.[avg_points] DESC
		",
			"Select",
			array(intval($datavalue))
		);
		
		
			
		}else{
		$fetchapplicants = execsqlSRS(
			"
			SELECT
				snap.snap_id,
				snap.UserID,
				userdet.LastName,
				userdet.FirstName,
				userdet.MiddleName,
				snap.AppliedDate,
				score.snap_id AS scored,
				SUM(sbb.score) as sumscore
			FROM tbl_Snapshot snap
			LEFT JOIN [tbl_SnapshotDelRem] dr ON dr.snap_id = snap.snap_id
			LEFT JOIN [tbl_SnapshotSB] sbb ON sbb.[snap_id] = snap.[snap_id] AND sbb.commmitte_id = '$UserID'
			
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
				WHERE u.[snap_id] = snap.[snap_id] AND u.commmitte_id = '$UserID'
				ORDER BY u.snap_id
			) score
			
			
			WHERE dr.IsQual = '0' AND snap.pubpos_id = ?
				
			GROUP BY snap.snap_id, snap.UserID,userdet.LastName,
				userdet.FirstName,
				userdet.MiddleName,snap.AppliedDate, score.snap_id
			ORDER BY sumscore DESC
		",
			"Select",
			array(intval($datavalue))
		);
		
		}
		
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

		echo "<tr>";
		echo "<th>Rank</th>";
		echo "<th>Name of Applicant</th>";
		echo "<th>Date of Application</th>";
		echo "<th>Scores</th>";
		echo "</tr>";
		echo "</thead>";

		echo "<tbody>";

		if (!empty($fetchapplicants)) {

			$i = 1;
			
			foreach ($fetchapplicants as $app) {

				$snap_id = htmlspecialchars($app["snap_id"] ?? '');
				$userid = htmlspecialchars($app["UserID"] ?? '');
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

				echo "<tr 
				  >";

				echo "<td class='font-weight-bold text-success'>$i</td>";

				echo "<td 
				id='attachmentreviewer_" . htmlspecialchars($app['snap_id']) . "'
					  data-datavalue='" . htmlspecialchars($app['snap_id']) . "'
					  data-pubposid='" . $datavalue . "'
					  data-userid='" . htmlspecialchars($app['UserID']) . "'
					  data-title='" .$title. "'
					  data-openmodallabel='" . htmlspecialchars($fullname) . "'
				
				class='font-weight-bold'>$fullname</td>";

				echo "<td>$formattedDate</td>";
				if($scored == "" || $scored == null){
					echo "<td><span class='badge badge-danger p-2'>No Score Yet</span></td>";
				}else if($RID <= 3){
					
				$gradescommitte = execsqlSRS("SELECT DISTINCT [commmitte_id], [snap_id] FROM tbl_SnapshotSB
										WHERE snap_id = '$snap_id'", "SELECT", []);	
										
				$avg = 0;
				$sumavg = 0;
				if(count($gradescommitte) == 0){
					$counternumber = 1;
				}else{
					$counternumber = count($gradescommitte);
				}
				$count = $counternumber ?? 1;
				foreach($gradescommitte as $com){
				$user = $com["commmitte_id"] ?? "";
				$snapid = $com["snap_id"] ?? "";
				
				$fetchnames = execsqlSRS("SELECT CONCAT(FirstName, ' ', LastName) as FullName, UserID FROM Sys_UserAccount WHERE UserID = '$user'","SELECT",[]);		
					
					foreach($fetchnames as $names){
						$FullName = $names["FullName"] ?? "";
						$userID = $names["UserID"] ?? "";
						
						$getrecords = execsqlSRS("SELECT SUM(score) as total FROM tbl_SnapshotSB WHERE snap_id ='$snap_id'AND commmitte_id = '$userID'", "SELECT", []);
						
						foreach($getrecords as $rec){
							$total = $rec["total"] ?? 0;
						}
						
					}
					$avg += $total;
				
				}
				$sumavg = $avg / $count;
				
				echo "<td><button class='btn btn-success p-2' id='SetCummulate'
					  data-datavalue='" . htmlspecialchars($app['snap_id']) . "'
					  data-pubposid='" . $datavalue . "'
					  data-userid='" . htmlspecialchars($app['UserID']) . "'
					  data-title='" .$title. "'
					  data-openmodallabel='" . htmlspecialchars($fullname) . "'
				>Total Average: ".$sumavg."</button></td>";
				
				$checkranking = execsqlSRS("SELECT TOP 1 [rank_id] FROM [tbl_SnapshotSBRanking] 
											WHERE [snap_id] = '$snap_id' AND [UserID] = '$userid'", "SELECT",[]);
					if($checkranking){
						$updaterank = execsqlSRS("UPDATE [tbl_SnapshotSBRanking] SET [avg_points] = '$sumavg'
						WHERE [snap_id] = '$snap_id' AND [UserID] = '$userid'", "Update", []);
					}else{
						$insertranking = execsqlSRS("INSERT INTO tbl_SnapshotSBRanking 
						([avg_points], [snap_id], [UserID]) VALUES ('$sumavg','$snap_id','$userid')","Insert", []);
					}
					
				}else{
					$getsum = execsqlSRS("SELECT SUM(score) as total
										FROM [tbl_SnapshotSB] WHERE 
										[snap_id] = '$snap_id' AND commmitte_id = '$UserID'", "SELECT", []);
										
					$total = $getsum[0]["total"] ?? "";
						echo "<td><span class='badge badge-success p-2'>Score: ".$total." </span></td>";
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

	
	
	case "viewbreakdown":
	
	$checkrecord = execsqlSRS("SELECT * FROM tbl_SnapshotSB
								WHERE [commmitte_id] = '$UserID' AND [snap_id] = '$datavalue'
								","SELECT",[]);
							
		$getcriteria = execsqlSRS("SELECT 
						[col_id]
					  ,[mothercol_id]
					  ,[rating_col]
					  ,[max_value]
					  ,[IsActive] FROM [tbl_SnapshotSBCol] WHERE [mothercol_id] = '0' AND IsActive = '0'", "SELECT", array());

		echo "<table class='table table-hover mb-0' style='width: 100%;'>";
		echo "<tbody>";
		
		
		echo "<tr>";
		
		foreach($getcriteria as $first){
			$Mother = $first["col_id"] ?? "";
			$Title = $first["rating_col"] ?? "";
			$Points = $first["max_value"] ?? "";
			
			echo '<td colspan = 2 style="width: 50%; text-align:center; background: green; color: white;">'.$Title. " ( " . $Points . " ) " .'</td>';
			
					$getchild2 = execsqlSRS("SELECT 
						s.[col_id]
						,s.[score]
					  ,sb.[mothercol_id]
					  ,sb.[rating_col]
					  ,sb.[max_value]
					  ,sb.[IsActive]
					  FROM [tbl_SnapshotSBCol] sb
					  LEFT JOIN [tbl_SnapshotSB] s ON s.[col_id] = sb.[col_id]
					  WHERE sb.[mothercol_id] = '$Mother'  AND sb.IsActive = '0'
					  AND s.[commmitte_id] = '$UserID' AND s.[snap_id] = '$datavalue'", "SELECT", array());
					foreach($getchild2  as $second2){
						
						$rating_col = $second2["rating_col"] ?? "";
						$score = $second2["score"] ?? "";
						
						$Points2 = $second2["max_value"] ?? "";
						echo "<tr>";
						echo '<td style="width: 50%; ">'.$rating_col. " ( " . $Points2 . " ) " .'</td>';
						echo '<td style="width: 50%; ">Score: '.$score.'
						</td>';
						echo "</tr>";
						
					}
		}
		
		echo "</tr>";
		echo "</tbody>";
		echo "</table>";
		$getcomment = execsqlSRS("SELECT TOP 1 [comments] FROM [tbl_SnapshotSBComments]
								WHERE [commmitte_id] = '$UserID' AND [snap_id] = '$datavalue'", "SELECT", []);
								
			if($getcomment){
				$comment = $getcomment[0]["comments"] ?? "";
			echo '<td colspan = 2 style="width: 50%; text-align:center; ">
			<label>Comments / Remarks:</label>
			<textarea readonly class="form-control" p>"'.$comment.'"</textarea></td>';
			}else{
			'<td colspan = 2 style="width: 50%; text-align:center; ">
			<label>Comments / Remarks:</label>
			<textarea readonly class="form-control" p>"No Comment"</textarea></td>';
			}
		$getchild3 = execsqlSRS("SELECT 
				      SUM(score) as total
					  FROM [tbl_SnapshotSB] 
					  WHERE [commmitte_id] = '$UserID' AND [snap_id] = '$datavalue'", "SELECT", array());
			$total = $getchild3[0]["total"]	?? "";	  
			
			echo "<div class='btn btn-info float-right m-2'
			id='attachmentreviewer_" . $datavalue . "'
			data-datavalue='" . $datavalue . "'
			data-userid='" .$UserID . "'
			data-openmodallabel='" . $fullname . "'
			>Back</div>";
			echo "<div class='btn btn-success float-right m-2'>Total Score: ".$total."</div>";
		
	break;
	
	
	case"savescore":
	$committeid = isset($_POST["committeid"]) ? $_POST["committeid"] : "";
	$comment_section = isset($_POST["comment_section"]) ? $_POST["comment_section"] : "";
	$snapid = isset($_POST["snapid"]) ? $_POST["snapid"] : "";
	
	$checkrecord = execsqlSRS("SELECT TOP 1 [commmitte_id] FROM tbl_SnapshotSB
								WHERE [commmitte_id] = '$committeid' AND [snap_id] = '$snapid'
								","SELECT",[]);
	if($checkrecord){
			echo json_encode(["title"=>"Already Scored", "result"=>"info", "message"=>"You have already scored this applicant"]);			
			return;
	}
	
	$insert2 = execsqlSRS("INSERT INTO [tbl_SnapshotSBComments] (snap_id, [commmitte_id], [comments])
							VALUES(:snap_id, :commmitte_id, :comments)","Insert",
							[
							":snap_id"=>$snapid,
							":commmitte_id"=>$committeid,
							":comments"=>$comment_section
							]);	
							
	foreach($_POST as $key => $value){
		
    if(in_array($key, ["request", "committeid", "comment_section", "snapid"])) {
        continue;
    }
  
    $col_id = $key;
    $score  = $value;
		if($score == "" || $score == null){
			echo json_encode(["title"=>"Check", "result"=>"info", "message"=>"Please fill all fields"]);	
			return;			
		}					
	}
	
	foreach($_POST as $key => $value){
		
    if(in_array($key, ["request", "committeid", "comment_section", "snapid"])) {
        continue;
    }
  
    $col_id = $key;
    $score  = $value;
	
	$insert = execsqlSRS("INSERT INTO [tbl_SnapshotSB] (snap_id, [commmitte_id], col_id, score)
							VALUES(:snap_id, :commmitte_id, :col_id, :score)","Insert",
							[
							":snap_id"=>$snapid,
							":commmitte_id"=>$committeid,
							":col_id"=>$col_id,
							":score"=>$score
							]);			
	}
	
	/* $update = execsqlSRS("UPDATE [tbl_Snapshot] SET 
				[snap_status] = '7',
				[UpdatedAt] = GETDATE()
				WHERE [snap_id] = '$snapid' ","Update",[]);
	 */
	echo json_encode(["title"=>"Success", "result"=>"success", "message"=>"Successfully save the score."]);
	
	
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
		<div class='row'>

		<div class='col-md-6'>
			<p><strong>Full Name:</strong> "
			. htmlspecialchars(($user['FirstName'] ?? '') . ' ' . ($user['MiddleName'] ?? '') . ' ' . ($user['LastName'] ?? '') . ' ' . ($user['ExtName'] ?? '')) . "
			</p>

			<p><strong>Email:</strong> " . htmlspecialchars($user['Email'] ?? '') . "</p>
			<p><strong>Mobile:</strong> " . htmlspecialchars($user['MobileNumber'] ?? '') . "</p>
			<p><strong>Telephone:</strong> " . htmlspecialchars($user['TelephoneNumber'] ?? '') . "</p>

			<p><strong>Date of Birth:</strong> " . (!empty($user['DateOfBirth'])
				? date('F d, Y', strtotime($user['DateOfBirth']))
				: '') . "</p>
			<p><strong>Age:</strong> " . htmlspecialchars($user['Age'] ?? '') . "</p>

			<p><strong>Sex:</strong> " . htmlspecialchars($user['Sex'] ?? '') . "</p>
			<p><strong>Civil Status:</strong> " . htmlspecialchars($user['CivilStatus'] ?? '') . "</p>
			<p><strong>Nationality:</strong> " . htmlspecialchars($user['Nationality'] ?? '') . "</p>
			<p><strong>Religion:</strong> " . htmlspecialchars($user['Religion'] ?? '') . "</p>
		</div>

		<div class='col-md-6'>
			<p><strong>Home Address:</strong><br>
				" . htmlspecialchars(
				($user['HmHouse'] ?? '') . ' ' .
					($user['HmStreet'] ?? '') . ', ' .
					($user['HmBarangay'] ?? '') . ', ' .
					($user['HmCity'] ?? '') . ', ' .
					($user['HmProvince'] ?? '') . ' ' .
					($user['HmZip'] ?? '')
			) . "
			</p>

			<p><strong>Current Address:</strong><br>
				" . htmlspecialchars(
				($user['CurHouse'] ?? '') . ' ' .
					($user['CurStreet'] ?? '') . ', ' .
					($user['CurBarangay'] ?? '') . ', ' .
					($user['CurCity'] ?? '') . ', ' .
					($user['CurProvince'] ?? '') . ' ' .
					($user['CurZip'] ?? '')
			) . "
			</p>
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
			<td>' . $date . '</td>';

			

			echo '</tr>

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
	<div>
	<button class='btn btn-info float-right' id='backList'
	data-datavalue='$pubposid'
	data-title='$title'
	
	>Back to List</button>
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
		
		
		
	case "SetCummulate":
	
	$SelectBreakdown = execsqlSRS("SELECT sbc.col_id, sbc.rating_col, SUM(sb.score) as Totalu  FROM[tbl_SnapshotSB] sb
									LEFT JOIN [tbl_SnapshotSBCol] sbc ON sbc.col_id = sb.col_id
									WHERE sb.snap_id = '$datavalue'
									GROUP BY sbc.rating_col, sbc.col_id
									ORDER BY sbc.col_id
									","SELECT",[]);
	echo "<table class='table'>";
	echo "<thead class='table-success'>";
	echo "<th>";
	echo "Categories";
	echo "</th>";	
	echo "<th>";
	echo "Accumulated Score";
	echo "</th>";
	echo "</thead>";
	echo "<tbody>";

	foreach($SelectBreakdown as $sb){
			echo "<tr>";
		$rating_col = $sb["rating_col"] ?? "";
		$Totalu = $sb["Totalu"] ?? "";
		echo"<td>";
		echo $rating_col;
		echo"</td>";		
		echo"<td>";
		echo $Totalu;
		echo"</td>";
		echo "</tr>";
	}
	
	echo "</tbody>";
	echo "</table>";
	echo "
		<div>
	<button class='btn btn-info float-right' id='backList'
	data-datavalue='$pubposid'
	data-title='$title'
	
	>Back to List</button>
	</div>";
	break;
}
