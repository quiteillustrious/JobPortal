<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";

switch ($request) {

	case "viewlogs":

		$queryviewlogs = execsqlSRS("
		SELECT l.logs_id, l.logs_details, l.logs_dt, l.logs_location, l.logs_operation, ua.Username, l.logs_newdetails, l.IsActive
		FROM tbl_Logs l

		LEFT JOIN Sys_UserAccount ua
		ON ua.UserID = l.logs_doneby

		ORDER BY l.logs_id DESC",
		"Search",
		array()
		);

			foreach ($queryviewlogs as $logs) {
				echo "<tr>";
				echo "<td>" . htmlspecialchars($logs["logs_id"]) . "</td>";
				echo "<td>" . htmlspecialchars($logs["logs_details"]) . "</td>";
				echo "<td>" . htmlspecialchars($logs["logs_dt"]) . "</td>";
				echo "<td>" . htmlspecialchars($logs["logs_location"]) . "</td>";
				if (htmlspecialchars($logs["logs_operation"]) == "add") {
					echo "<td><p class='font-weight-bold text-success'>add</p></td>";
				}
				
				else if (htmlspecialchars($logs["logs_operation"]) == "edit") {
					echo "<td><p class='font-weight-bold text-warning'>edit</p></td>";
				}
				else if (htmlspecialchars($logs["logs_operation"]) == "delete") {
					echo "<td><p class='font-weight-bold text-danger'>delete</p></td>";
				}
				echo "<td>" . htmlspecialchars($logs["Username"]) . "</td>";
				echo "<td>" . htmlspecialchars($logs["logs_newdetails"]) . "</td>";
				
				if (htmlspecialchars($logs["IsActive"]) == 0) {
					echo "<td><i class='fa-solid fa-toggle-on fa-2x text-success'
								 data-statuscontent='". htmlspecialchars($logs["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($logs["logs_id"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='tbl_Logs'
								 data-databaseparam='logs_id'
								 data-tableid='#tblviewlogs'
								 data-tablebackendurl='backend/bk_logsmanagement.php'
								 data-tablerequest='viewlogs'
								 ></i></td>";
				}

				else if (htmlspecialchars($logs["IsActive"]) == 1) {
					echo "<td><i class='fa-solid fa-toggle-off fa-2x text-danger'
								 data-statuscontent='". htmlspecialchars($logs["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($logs["logs_id"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='tbl_Logs'
								 data-databaseparam='logs_id'
								 data-tableid='#tblviewlogs'
								 data-tablebackendurl='backend/bk_logsmanagement.php'
								 data-tablerequest='viewlogs'
								 ></i></td>";
				}
				
				echo "</tr>";
			}
	break;
}
?>