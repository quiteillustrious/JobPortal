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

		echo "
        <tr class='bg-success'>
            <th colspan='10' style='position: sticky; top: 0; z-index: 20;'>
                <div class='font-weight-bold ml-2'>
                    <span>
                        Applicants for " . $fetchposition[0]['position_title'] . "";
						
		//Check if already have a record
		$checker = execsqlSRS("SELECT [pubpos_id] FROM [tbl_SnapShotSBChecker] 
								WHERE [pubpos_id] = '$datavalue' AND IsActive = '0'", "SELECT", []);
		if($RID <= 3 AND !$checker){				
		echo "<button class='btn btn-warning float-right updaterecordsofusers' 
				data-datavalue = ".$datavalue."
				data-userid = ".$UserID."
				data-request = 'updaterecordsofusers'
				>Finalize Scores</button>";
		}else if($RID <= 3 AND $checker ){
			echo "<button class='btn btn-success float-right ' 
				>Already Finalized</button>";
		}
       	echo "        </span>
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

				echo "<tr id='attachmentreviewer_" . htmlspecialchars($app['snap_id']) . "'
					  data-datavalue='" . htmlspecialchars($app['snap_id']) . "'
					  data-pubposid='" . $datavalue . "'
					  data-userid='" . htmlspecialchars($app['UserID']) . "'
					  data-openmodallabel='" . htmlspecialchars($fullname) . "'
				  >";

				echo "<td class='font-weight-bold text-success'>$i</td>";

				echo "<td class='font-weight-bold'>$fullname</td>";

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
				
				echo "<td><span class='badge badge-success p-2'>Total Average: ".$sumavg."</span></td>";
				
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

	case "attachmentreviewer":
	$UserID = isset($_POST["UserID"]) ? $_POST["UserID"] : "";
	$datavalue = isset($_POST["datavalue"]) ? $_POST["datavalue"] : "";
	if($RID <= 3){
		$gradescommitte = execsqlSRS("SELECT DISTINCT [commmitte_id], [snap_id] FROM tbl_SnapshotSB
										WHERE snap_id = '$datavalue'", "SELECT", []);
		echo"<span class='card'> ↓ Click the name to see breakdown results</span>";								
		echo "<table class='table table-hover mb-0' style='width: 100%;'>";
		echo "<thead>";
		echo "<th style='text-align: center;'>Committee";
		echo "</th>";	
		echo "<th style='text-align: center;'>Score";
		echo "</th>";	
		/* echo "<th style='text-align: center;'>Comments / Remarks";
		echo "</th>";	 */
		echo "</thead>";
		echo "<tbody>";
			
		$avg = 0;

		if(count($gradescommitte) == 0){
			$countnumber = 1;
		}else{
			$countnumber = count($gradescommitte);
		}
		
		$count = $countnumber ?? 1;
			foreach($gradescommitte as $com){
				echo "<tr>";
				$user = $com["commmitte_id"] ?? "";
				$snapid = $com["snap_id"] ?? "";
				
				$fetchnames = execsqlSRS("SELECT CONCAT(FirstName, ' ', LastName) as FullName, UserID FROM Sys_UserAccount WHERE UserID = '$user'","SELECT",[]);		
					
				foreach($fetchnames as $names){
					$FullName = $names["FullName"] ?? "";
					$userID = $names["UserID"] ?? "";
					
					$getrecords = execsqlSRS("SELECT SUM(score) as total FROM tbl_SnapshotSB WHERE snap_id ='$datavalue' AND commmitte_id = '$userID'", "SELECT", []);
					
					/* $getcomment = execsqlSRS("SELECT TOP 1 [comments] FROM [tbl_SnapshotSBComments]
								WHERE [commmitte_id] = '$userID' AND [snap_id] = '$datavalue'", "SELECT", []);
				 */
					//foreach($getcomment as $com){
						//$comment = $com["comments"] ?? 0;
						foreach($getrecords as $rec){
							$total = $rec["total"] ?? 0;
							
							echo '<td  style="width: 33.33%; text-align:center; background: green; color: white;"
									id="fecthbreakdown" data-committeid ='.$user.' 
									data-fullname="'.$fullname.'"
									data-datavalue ='.$snapid.' >'.$FullName. '</td>';
							echo '<td  style="width: 33.33%; text-align:center;">'.$total. '</td>';
						//	echo '<td  style="width: 33.33%; text-align:center;">'.$comment. '</td>';
						}
					//}
					
				}
				$avg += $total;
				
				echo "</tr>";
			
			}
		
		echo "</tbody>";
		echo "</table>";
		echo "<div class='btn btn-success float-right'>Total Average: ".$avg / $count."</div>";
		/* $getallrecords = execsqlSRS("
		SELECT
		(SELECT DISTINCT commmitte_id FROM tbl_SnapshotSB) as committe,
		SUM(sb.score) as total
		FROM [tbl_SnapshotSB] sb
		LEFT JOIN [tbl_Snapshot] ss ON ss.snap_id = sb.snap_id
		WHERE sb.[snap_id] = '$datavalue'
		","SELECT",[]); */
		
		
		/* foreach($getallrecords as $first){
			$total = $first["total"] ?? "";
			$committe = $first["committe"] ?? "";
			echo '<td colspan = 2 style="width: 50%; text-align:center; background: green; color: white;">'.$committe.$total.'</td>';
		} */
		
	}else{
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
			
			if(!$checkrecord){	
			
			$getchild = execsqlSRS("SELECT 
						[col_id]
					  ,[mothercol_id]
					  ,[rating_col]
					  ,[max_value]
					  ,[IsActive] FROM [tbl_SnapshotSBCol] WHERE [mothercol_id] = '$Mother'  AND IsActive = '0'", "SELECT", array());
					foreach($getchild  as $second){
						$Mother2 = $second["col_id"] ?? "";
						$Title2 = $second["rating_col"] ?? "";
						$Points2 = $second["max_value"] ?? "";
						echo "<tr>";
						echo '<td style="width: 50%; ">'.$Title2. " ( " . $Points2 . " ) " .'</td>';
						echo '<td style="width: 50%; ">Score: 
						<input  type="number"
							class="form-control" id="'.$Mother2.'" min="0" max="'.$Points2.'"
							oninput="
								if(this.value > '.$Points2.') {
									this.value = '.$Points2.';
								}
							">
						</td>';
						echo "</tr>";
						
					}
				
		
				}else{
					
					
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
		}
		
		$getcomment = execsqlSRS("SELECT TOP 1 [comments] FROM [tbl_SnapshotSBComments]
								WHERE [commmitte_id] = '$UserID' AND [snap_id] = '$datavalue'", "SELECT", []);
								
			if($getcomment){
				$comment = $getcomment[0]["comments"] ?? "";
			echo '<td colspan = 2 style="width: 50%; text-align:center; ">
			<label>Comments / Remarks:</label>
			<textarea readonly class="form-control" p>"'.$comment.'"</textarea></td>';
			}else{
			echo '<td colspan = 2 style="width: 50%; text-align:center; ">
			<label>Comments / Remarks:</label>
			<textarea id="comment_section" class="form-control" placeholder="Please input your comment here."></textarea></td>';
			}
	
		echo "</tr>";
		echo "</tbody>";
		echo "</table>";
		
		if(!$checkrecord){	
	
		echo "<div class='float-right m-2'>
				<button id='SubmitSb'class='btn btn-success'
				data-snapid=".$datavalue."
				data-committeid=".$UserID."
				data-request='savescore'
				>Submit Score</button>
			 </div>
			  ";
		}else{
			$getchild3 = execsqlSRS("SELECT 
				      SUM(score) as total
					  FROM [tbl_SnapshotSB] 
					  WHERE [commmitte_id] = '$UserID' AND [snap_id] = '$datavalue'", "SELECT", array());
			$total = $getchild3[0]["total"]	?? "";	  
			echo "<div class='btn btn-success float-right'>Total Score: ".$total."</div>";
		}
	}
	
	
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
}
