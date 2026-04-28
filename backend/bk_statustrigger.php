<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$datavalue = isset($_POST["datavalue"]) ? $_POST["datavalue"] : "";
$dataparam = isset($_POST["dataparam"]) ? $_POST["dataparam"] : "";
$databasedir = isset($_POST["databasedir"]) ? $_POST["databasedir"] : "";
$databaseparam = isset($_POST["databaseparam"]) ? $_POST["databaseparam"] : "";

switch ($request) {

	case "statustoggle":
	
		if ($dataparam){
			
			$query = execsqlSRS("
			UPDATE $databasedir
			SET IsActive = '0'
			WHERE $databaseparam = :datavalue",
			"Update",
			[
				"datavalue" => $datavalue
			]
			);

			echo json_encode(['status' => 'success', 'message' => 'System: Toggled On...']);
			exit;

		}
		
		else{
			
			$query = execsqlSRS("
			UPDATE $databasedir
			SET IsActive = '1'
			WHERE $databaseparam = :datavalue",
			"Update",
			[
				"datavalue" => $datavalue
			]
			);
			
			echo json_encode(['status' => 'success', 'message' => 'System: Toggled Off...']);
			exit;
			
		}

	break;

}
?>