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

    case "viewannouncements":

		$queryannouncements = execsqlSRS("
			SELECT 
				announcement_id,
				announcement_title,

				CONVERT(VARCHAR(10), dt_start, 120) AS dt_start_date,
				RIGHT(CONVERT(VARCHAR(16), dt_start, 108), 5) AS dt_start_time,

				CONVERT(VARCHAR(10), dt_end, 120) AS dt_end_date,
				RIGHT(CONVERT(VARCHAR(16), dt_end, 108), 5) AS dt_end_time,

				announcement_icon,
				IsActive
				
			FROM tbl_Announcements

			WHERE announcement_id = :datavalue", 
			"Select", 
			[
				":datavalue" => $datavalue
			]
		);
		
		foreach ($queryannouncements as $announcement) {

				echo "<tr style='background-color: #B3FFB3;'>";
				echo "<td>". htmlspecialchars($announcement["announcement_id"]) ."</td>";
				echo "<td class='font-weight-bold text-danger'>" . htmlspecialchars($announcement["announcement_title"]) . "</td>";
				echo "<td>" . htmlspecialchars($announcement["dt_start_date"]) . " | " . htmlspecialchars($announcement["dt_start_time"]) . "</td>";
				echo "<td>" . htmlspecialchars($announcement["dt_end_date"]) . " | " . htmlspecialchars($announcement["dt_end_time"]) . "</td>";
				echo "<td><i class='" . $announcement["announcement_icon"] . " mr-2'></i></td>";

				if (htmlspecialchars($announcement["IsActive"]) == 0) {
					echo "<td><i class='fa-solid fa-toggle-on fa-2x text-success'
								 data-statuscontent='". htmlspecialchars($announcement["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($announcement["announcement_id"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='tbl_Announcements'
								 data-databaseparam='announcement_id'
								 data-tableid='#tblviewannouncements'
								 data-tablebackendurl='backend/bk_announcementmanagement.php'
								 data-tablerequest='viewannouncements'
								 data-datavalue='" . htmlspecialchars($announcement["announcement_id"]) . "'
								 ></i></td>";
				}

				else if (htmlspecialchars($announcement["IsActive"]) == 1) {
					echo "<td><i class='fa-solid fa-toggle-off fa-2x text-danger'
								 data-statuscontent='". htmlspecialchars($announcement["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($announcement["announcement_id"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='tbl_Announcements'
								 data-databaseparam='announcement_id'
								 data-tableid='#tblviewannouncements'
								 data-tablebackendurl='backend/bk_announcementmanagement.php'
								 data-tablerequest='viewannouncements'
								 data-datavalue='" . htmlspecialchars($announcement["announcement_id"]) . "'
								 ></i></td>";
				}

				echo "<td>
						<button type='button' 
								class='btn btn-warning m-1' 
								id='edit_data' 
								data-tooltip='View/Edit'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Edit Header'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_announcementmanagement.php'
								data-backendrequest='editheader'
								data-datavalue='" . htmlspecialchars($announcement["announcement_id"]) . "'>
							<i class='fa-solid fa-pencil'></i>
						</button>

						<button type='button' 
								class='btn btn-danger m-1' 
								id='delete_data' 
								data-tooltip='Delete'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Delete Header'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_announcementmanagement.php'
								data-backendrequest='deleteheader'
								data-datavalue='" . htmlspecialchars($announcement["announcement_id"]) . "'>
							<i class='fa-solid fa-trash'></i>

						<button type='button' 
								class='btn btn-success m-1' 
								id='add_data' 
								data-tooltip='Add Content'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Add Content'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_announcementmanagement.php'
								data-backendrequest='addcontent'
								data-datavalue='" . htmlspecialchars($announcement["announcement_id"]) . "'>
							<i class='fa-solid fa-circle-plus'></i>
					  </td>";

				echo "<tr>";

		}

    break;

	case "addheader":

		echo '
		<div class="p-3">
		  <div class="form-group">
			<label for="">Announcement Title</label>
			<input type="text" class="form-control field-input" id="field1" placeholder="Title...">
		  </div>

		  <div class="form-group">
			<label for="">Start Date</label>
			<input type="datetime-local" class="form-control field-input" id="field2">
		  </div>

		  <div class="form-group">
			<label for="">End Date</label>
			<input type="datetime-local" class="form-control field-input" id="field3">
		  </div>

		  <div class="form-group">
			<label for="">Icon</label>
			<input type="text" class="form-control field-input" id="field4" placeholder="fa-solid fa-icon...">
		  </div>

		  <div class="form-group">
			<label for="">Status</label>
			<input type="text" class="form-control field-input" id="field5" placeholder="0 or 1...">
		  </div>

		  <div class="form-group pt-2 d-flex justify-content-center">
			<button type="submit" 
					id="save_data"
					data-openmodal="#addeditmodal"
					data-operator="add"
					data-backendrequest="saveheader"
					data-backendurl="backend/bk_announcementmanagement.php"
					data-tableid="#tblviewannouncements"
					data-tablerequest="viewannouncements"
					class="btn btn-success">Add Announcement</button>
		  </div>
		</div>';

	break;

	case "editheader":

		$queryeditheader = execsqlSRS("
			SELECT announcement_title, dt_start, dt_end, announcement_icon, IsActive, announcement_id
			FROM tbl_Announcements
			WHERE announcement_id = :datavalue", 
			"Select", [
						":datavalue" => $datavalue
					  ]); 

			foreach ($queryeditheader as $edit) {

			echo "<div class='form-group'>
					<label for=''>Announcement Title</label>
                    <input type='text' class='form-control field-input' id='field1' value='" . htmlspecialchars($edit["announcement_title"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for=''>Start Date</label>
                    <input type='datetime-local' class='form-control field-input' id='field2' value='" . htmlspecialchars($edit["dt_start"]) . "'>
				  </div>";

			echo "<div class='form-group'>
					<label for=''>End Date</label>
                    <input type='datetime-local' class='form-control field-input' id='field3' value='" . htmlspecialchars($edit["dt_end"]) . "'>
				  </div>";
				  
			echo "<div class='form-group'>
					<label for=''>Icon</label>
                    <input type='text' class='form-control field-input' id='field4' value='" . htmlspecialchars($edit["announcement_icon"]) . "'>
				  </div>";
				  
			echo "<div class='form-group'>
					<label for=''>Status</label>
                    <input type='text' class='form-control field-input' id='field5' value='" . htmlspecialchars($edit["IsActive"]) . "'>
				  </div>";

				echo    "<div class='form-group d-flex justify-content-center pt-2'>
				<button 
						type='submit' 
						class='btn btn-success' 
						id='save_data'
						data-openmodal='#addeditmodal'
						data-operator='edit'
						data-backendrequest='saveheader'
						data-backendurl='backend/bk_announcementmanagement.php'
						data-tableid='#tblviewannouncements'
						data-tablerequest='viewannouncements'
						data-datavalue='" . htmlspecialchars($edit["announcement_id"]) . "'>
						Save Changes
				</button>
              </div>";
			}

	break;
	
	case "deleteheader":
	
	echo "
			<div class='d-flex justify-content-center pt-3'>
					<div class='card border border-danger ml-2 shadow' style='max-width:40rem;'>
						<div class='card-title ml-3 mt-3 mb-3 mr-3'>
							<i class='fa-solid fa-triangle-exclamation text-danger'></i>
								<span class='font-weight-bold text-danger'>Warning:</span>
								<span>Deleting will remove this data from its main Table. It will be transferred to the Logs.</span>
						</div>
					</div>
			</div>
			<div class='d-flex justify-content-center'>
				<div>
				<button type='button' 
						class='btn btn-danger' 
						id='save_data' 
						data-openmodal='#addeditmodal'
						data-operator='delete'
						data-backendrequest='saveheader'
						data-backendurl='backend/bk_announcementmanagement.php'
						data-logslocation='Announcement Management (Header)'
						data-tableid='#tblviewusers'
						data-tablerequest='viewusers'
						data-datavalue='" . htmlspecialchars($datavalue) . "'>
					Confirm Deletion
				</button>
				</div>
			</div>
	";

	break;

	case "saveheader":

	if ($operator == "delete") {
		
		$getolddata = execsqlSRS("
				SELECT *
				FROM tbl_Announcements
				WHERE announcement_id = :datavalue", 
				"Select", [
							":datavalue" => $datavalue
						  ]); 
						  
		$string = implode (' | ', $getolddata[0]);

		$querysave = execsqlSRS("
				INSERT INTO tbl_Logs (logs_details, logs_dt, logs_location, logs_operation, logs_doneby, IsActive)
				VALUES (:string, :currentdt, :logslocation, :operator, :userid, '0')", 
				"Insert", [
							":string" => $string, 
							":currentdt" => $currentdt, 
							":logslocation" => $logslocation, 
							":operator" => $operator, 
							":userid" => $userid
						  ]); 

		$querydelete = execsqlSRS("
				DELETE FROM tbl_Announcements
				WHERE announcement_id = :datavalue", 
				"Delete", [
							":datavalue" => $datavalue
						  ]); 

		$querydelete2 = execsqlSRS("
				DELETE FROM tbl_AnnouncementContent
				WHERE announcement_id = :datavalue", 
				"Delete", [
							":datavalue" => $datavalue
						  ]); 

		echo json_encode (['status' => 'success', 'message' => 'System: Macro has been Moved to the Logs.']);

		break;
	}
	
	if ( empty($fields["field1"]) || empty($fields["field2"]) || empty($fields["field3"]) ){
		echo json_encode (['status' => 'error', 'message' => 'System: Some Fields are Missing.']);
		return;
	}

	if (!is_numeric($fields["field5"]) ){
		echo json_encode (['status' => 'error', 'message' => 'System: Status should either be 0 or 1.']);
		return;
	}

		if ($operator == "edit") {
			
			$querysave = execsqlSRS("
				UPDATE tbl_Announcements
				SET announcement_title = :title, dt_start = :dt_start, dt_end = :dt_end, announcement_icon = :icon, IsActive = :status
				WHERE announcement_id = :datavalue", 
				"Update", [
							":title" => $fields["field1"], 
							":dt_start" => $fields["field2"], 
							":dt_end" => $fields["field3"], 
							":icon" => $fields["field4"], 
							":status" => intval($fields["field5"]), 
							":datavalue" => intval($datavalue)
						  ]);

			echo json_encode (['status' => 'success', 'message' => 'System: Changes have been saved.']);

		}

		else if ($operator == "add") {

			$querysave = execsqlSRS("
					INSERT INTO tbl_Announcements (announcement_title, dt_start, dt_end, announcement_icon, created_at, IsActive)
					VALUES (:title, :dtstart, :dtend, :icon, :currendt, :status)", 
					"Insert", [
								":title" => $fields["field1"], 
								":dtstart" => $fields["field2"], 
								":dtend" => $fields["field3"], 
								":icon" => $fields["field4"], 
								":currendt" => $currentdt, 
								":status" => intval($fields["field5"])
							  ]); 

			echo json_encode (['status' => 'success', 'message' => 'System: Announcement Added!']);
		}

		else {
			echo json_encode (['status' => 'error', 'message' => 'System: Error Fetching. No Operator']);
			return;
		}

	break;	
}
?>
