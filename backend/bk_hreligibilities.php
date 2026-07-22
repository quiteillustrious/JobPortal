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

$selectlevel = execsqlSRS("SELECT * FROM tbl_Level WHERE IsStatus = '0'","SELECT", []);

switch ($request) {

	case "vieweligibilities":

		$vieweligibilities = execsqlSRS("
		SELECT [eligibility_id]
                ,[eligibility_desc]
                ,[lvl_id]
                ,[IsActive]
		FROM [tbl_ProfEligibilityLibrary]

		ORDER BY [eligibility_desc]",
		"Search",
		array()
		);
	
			foreach ($vieweligibilities as $eligibility) {
				echo "<tr>";
				echo "<td>" . htmlspecialchars($eligibility["eligibility_id"]) . "</td>";
				echo "<td>" . htmlspecialchars($eligibility["eligibility_desc"]) . "</td>";
				echo "<td>" . htmlspecialchars($eligibility["lvl_id"]) . "</td>";
				
				if (htmlspecialchars($eligibility["IsActive"])){
					$togglevar = 'off';
					$textcolor = 'danger';
				}
				else{
					$togglevar = 'on' ;
					$textcolor = 'success';
				}
				
				echo "<td><i class='fa-solid fa-toggle-{$togglevar} fa-2x text-{$textcolor}'
							 data-statuscontent='". htmlspecialchars($eligibility["IsActive"]) ."'
							 data-statusdata='" . htmlspecialchars($eligibility["eligibility_id"]) . "'
							 id='statustrigger'
							 data-backendurl='backend/bk_statustrigger.php'
							 data-backendmethod='POST'
							 data-backendrequest='statustoggle'
							 data-databasedir='tbl_ProfEligibilityLibrary'
							 data-databaseparam='eligibility_id'
							 data-tableid='#tblvieweligibilities'
							 data-tablebackendurl='backend/bk_hreligibilities.php'
							 data-tablerequest='vieweligibilities'
							 ></i></td>";

				echo "<td>
						<button type='button' 
								class='btn btn-warning m-1' 
								id='edit_data' 
								data-tooltip='View/Edit'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Edit Eligibility'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_hreligibilities.php'
								data-backendrequest='editelegibility'
								data-datavalue='" . htmlspecialchars($eligibility["eligibility_id"]) . "'>
							<i class='fa-solid fa-pencil'></i>
						</button>
					  </td>";

				echo "</tr>";
			}
	break;

	case "addeligibility":
	
		echo '
		<div class="p-3">
		  <div class="form-group">
			<label for="">Description</label>
			<input type="text" class="form-control field-input" id="field1" placeholder="e.g. Primary...">
		  </div>';
	
		 echo ' <div class="form-group">
			<label for="">Level of Eligibility</label>';
			echo'<select " class="form-control field-input"  id="field3">';
			foreach($selectlevel as $l){
				echo'<option value='.$l["lvl_id"].'>'.$l["level_name"].'</option>';
			}
			echo'<select>';
			
		 echo ' </div> ';
		  
		echo '  <div class="form-group">
			<label for="">Status (0/1)</label>
			<input type="text" class="form-control field-input" id="field2" placeholder="0...">
		  </div>

		  <div class="form-group pt-2 d-flex justify-content-center">
			<button type="submit" 
					id="save_data"
					data-openmodal="#addeditmodal"
					data-operator="add"
					data-backendrequest="saveeligibility"
					data-backendurl="backend/bk_hreligibilities.php"
					data-tableid="#tblvieweligibilities"
					data-tablerequest="vieweligibilities"
					class="btn btn-success">Add Eligibility</button>
		  </div>
		</div>';

	break;
		
	case "editelegibility":

		$queryedit = execsqlSRS("
			SELECT pl.[eligibility_id]
				  ,pl.[eligibility_desc]
				  ,pl.[lvl_id]
				  ,l.[level_name]
				  ,pl.[IsActive]
			FROM [tbl_ProfEligibilityLibrary] pl
			LEFT JOIN [tbl_Level] l ON l.lvl_id = pl.lvl_id
			WHERE pl.eligibility_id = :eligibility_id", 
			"Select", [
						":eligibility_id" => $datavalue
					  ]); 
					  
			foreach ($queryedit as $edit) {

			echo "
				<div class='p-3'>
				<div class='form-group'>
					<label for=''>Description</label>
                    <input type='text' class='form-control field-input' id='field1' value='" . htmlspecialchars($edit["eligibility_desc"]) . "'>
				  </div>";
				  
	   echo ' <div class="form-group">
			<label for="">Level of Eligibility</label>';
			echo'<select " class="form-control field-input"  id="field3">';
			echo'<option value='.$edit["lvl_id"].'>'.$edit["level_name"].'</option>';
			foreach($selectlevel as $l){
				echo'<option value=' . htmlspecialchars($l["lvl_id"]) . '>'.$l["level_name"].'</option>';
			}
			echo'<select>';
			
		 echo ' </div> ';
		 
		 
			echo "<div class='form-group'>
					<label for=''>Status</label>
                    <input type='text' class='form-control field-input' id='field2' value='" . htmlspecialchars($edit["IsActive"]) . "'>
				  </div>";

				echo    "<div class='form-group d-flex justify-content-center pt-2'>
				<button 
						type='submit' 
						class='btn btn-success' 
						id='save_data'
						data-openmodal='#addeditmodal'
						data-operator='edit'
						data-backendrequest='saveeligibility'
						data-backendurl='backend/bk_hreligibilities.php'
						data-tableid='#tblvieweligibilities'
						data-tablerequest='vieweligibilities'
						data-datavalue='" . htmlspecialchars($edit["eligibility_id"]) . "'>
						Save Changes
				</button>
              </div>
			  </div>";
			}

	break;

	case "deleteuser":

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
						data-backendrequest='saveuser'
						data-backendurl='backend/bk_usermanagement.php'
						data-logslocation='User Management'
						data-tableid='#tblviewusers'
						data-tablerequest='viewusers'
						data-datavalue='" . htmlspecialchars($datavalue) . "'>
					Confirm Deletion
				</button>
				</div>
			</div>
	";

	break;

	case "saveeligibility":

		if ($operator == "edit") {
			
			$querysave = execsqlSRS("
				UPDATE tbl_ProfEligibilityLibrary
				SET eligibility_desc = :desc, IsActive = :status, lvl_id = :lvl_id
				WHERE eligibility_id = :datavalue", 
				"Update", [
							":desc" => $fields["field1"], 
							":status" => intval($fields["field2"]), 
							":lvl_id" => intval($fields["field3"]), 
							":datavalue" => intval($datavalue)
						  ]);
						  
			echo json_encode (['status' => 'success', 'message' => 'System: Changes have been Saved.']);

		}

		else if ($operator == "add") {

			$querysave = execsqlSRS("
					INSERT INTO [tbl_ProfEligibilityLibrary] ([eligibility_desc],[lvl_id], [IsActive])
					VALUES (:desc, :lvl_id, :status)", 
					"Insert", [
								":desc" => $fields["field1"], 
								":lvl_id" => intval($fields["field3"]),
								":status" => intval($fields["field2"])
							  ]);

			echo json_encode (['status' => 'success', 'message' => 'System: Eligibility Added!']);
		}
		
		else {
			echo json_encode (['status' => 'error', 'message' => 'System: Error Fetching. No Operator']);
			return;
		}

	break;

}

?>