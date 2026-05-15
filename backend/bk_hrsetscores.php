<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$fields = isset($_POST["fields"]) ? $_POST["fields"] : "";
$operator = isset($_POST["operator"]) ? $_POST["operator"] : "";
$datavalue = isset($_POST["datavalue"]) ? $_POST["datavalue"] : "0";
$logslocation = isset($_POST["logslocation"]) ? $_POST["logslocation"] : "";
$userid = isset($_POST["userid"]) ? $_POST["userid"] : "";

$currentdt = date("Y-m-d H:i:s");

switch ($request) {

	case "vieweduclevels":

		$viewlevels = execsqlSRS("
		SELECT [col_id]
		  ,[mothercol_id]
		  ,[rating_col]
		  ,[max_value]
		  ,[IsActive]
		FROM [tbl_SnapshotSBCol]
		WHERE mothercol_id = '0'
		ORDER BY [col_id]",
		"Search",
		array()
		);

			foreach ($viewlevels as $level) {
				echo "<tr>";
				echo "<td>" . htmlspecialchars($level["col_id"]) . "</td>";
				echo "<td>" . htmlspecialchars($level["rating_col"]) . "</td>";
				echo "<td>" . htmlspecialchars($level["max_value"]) . "</td>";
				
				if (htmlspecialchars($level["IsActive"])){
					$togglevar = 'off';
					$textcolor = 'danger';
				}
				else{
					$togglevar = 'on' ;
					$textcolor = 'success';
				}
				
				echo "<td><i class='fa-solid fa-toggle-{$togglevar} fa-2x text-{$textcolor}'
							 data-statuscontent='". htmlspecialchars($level["IsActive"]) ."'
							 data-statusdata='" . htmlspecialchars($level["col_id"]) . "'
							 id='statustrigger'
							 data-backendurl='backend/bk_statustrigger.php'
							 data-backendmethod='POST'
							 data-backendrequest='statustoggle'
							 data-databasedir='tbl_SnapshotSBCol'
							 data-databaseparam='col_id'
							 data-tableid='#tblvieweduclevels'
							 data-tablebackendurl='backend/bk_hrsetscores.php'
							 data-tablerequest='vieweduclevels'
							 ></i></td>";

				echo "<td>
						<button type='button' 
								class='btn btn-warning m-1' 
								id='edit_data' 
								data-tooltip='Edit'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Edit Category'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_hrsetscores.php'
								data-backendrequest='editlevel'
								data-datavalue='" . htmlspecialchars($level["col_id"]) . "'>
							<i class='fa-solid fa-pencil'></i>
						</button>
						<button type='button' 
								class='btn btn-info m-1' 
								id='edit_data' 
								data-tooltip='View/Edit Content'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Content'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_hrsetscores.php'
								data-backendrequest='viewchildlevel'
								data-datavalue='" . htmlspecialchars($level["col_id"]) . "'>
							<i class='fa-solid fa-eye'></i>
						</button>
					  </td>";

				echo "</tr>";
			}
	break;


		
		
	case "viewchildlevel":
	
	echo '<button type="button" 
					class="btn btn-success" 
					id="add_data2"
					data-openmodal="#addeditmodal"
					data-openmodallabel="Add Content"
					data-openmodalbody="#addeditcontent"
					data-backendurl="backend/bk_hrsetscores.php"
					data-datavalue="'.$datavalue.'"
					data-backendrequest="addlevel">Add Content +</button>';

		$viewlevels = execsqlSRS("
		SELECT [col_id]
		  ,[mothercol_id]
		  ,[rating_col]
		  ,[max_value]
		  ,[IsActive]
		FROM [tbl_SnapshotSBCol]
		WHERE mothercol_id = $datavalue
		",
		"Search",
		array()
		);
		
		
		echo '<div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped">
                  <thead class="thead-dark">
                    <tr>
                      <th>ID</th>
                      <th>Description</th>
                      <th>Value</th>
					  <th>Status</th>
					  <th>Action</th>
                    </tr>
                  </thead>
				  <tbody >';
				  
					
			foreach ($viewlevels as $level) {
				echo "<tr>";
				echo "<td>" . htmlspecialchars($level["col_id"]) . "</td>";
				echo "<td>" . htmlspecialchars($level["rating_col"]) . "</td>";
				echo "<td>" . htmlspecialchars($level["max_value"]) . "</td>";
				
				if (htmlspecialchars($level["IsActive"])){
					$togglevar = 'off';
					$textcolor = 'danger';
				}
				else{
					$togglevar = 'on' ;
					$textcolor = 'success';
				}
				
				echo "<td><i class='fa-solid fa-toggle-{$togglevar} fa-2x text-{$textcolor}'
							 data-statuscontent='". htmlspecialchars($level["IsActive"]) ."'
							 data-statusdata='" . htmlspecialchars($level["col_id"]) . "'
							 id='statustrigger'
							 data-backendurl='backend/bk_statustrigger.php'
							 data-backendmethod='POST'
							 data-backendrequest='statustoggle'
							 data-databasedir='tbl_SnapshotSBCol'
							 data-databaseparam='col_id'
							 data-tableid='#tblvieweduclevels'
							 data-tablebackendurl='backend/bk_hrsetscores.php'
							 data-tablerequest='vieweduclevels'
							 ></i></td>";

				echo "<td>
						<button type='button' 
								class='btn btn-warning m-1' 
								id='edit_data' 
								data-tooltip='Edit'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Edit Category'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_hrsetscores.php'
								data-backendrequest='editlevel'
								data-datavalue='" . htmlspecialchars($level["col_id"]) . "'>
							<i class='fa-solid fa-pencil'></i>
						</button>";
						
					if($datavalue = 0){	
					echo "	<button type='button' 
								class='btn btn-info m-1' 
								id='edit_data' 
								data-tooltip='View/Edit Content'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Add Content'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_hrsetscores.php'
								data-backendrequest='viewchildlevel'
								data-datavalue='" . htmlspecialchars($level["col_id"]) . "'>
							<i class='fa-solid fa-eye'></i>
						</button>";
					}
						
					  echo "</td>";

				echo "</tr>";
			}
		echo '	</tbody>
			</table>
		  </div>';
		
	break;
	
	
	case "addlevel":
		$title = "Add Category";
		if($datavalue > 0){
		$title = "Add Content";
		}
		echo '
		<div class="p-3">
		  <div class="form-group">
			<label for="">Category:</label>
			<input hidden type="text" class="form-control field-input" id="field1" value="'. $datavalue.'"> 
			<input type="text" class="form-control field-input" id="field2" placeholder="e.g. Potential">
			
		  </div>

		  <div class="form-group">
			<label for="">Points:</label>
			<input type="text" class="form-control field-input" id="field3" placeholder="0...">
		  </div> 
		  
		  <div class="form-group">
			<label for="">Status (0/1)</label>
			<input type="text" class="form-control field-input" id="field4" placeholder="0...">
		  </div>

		  <div class="form-group pt-2 d-flex justify-content-center">
			<button type="submit" 
					id="save_data"
					data-openmodal="#addeditmodal"
					data-operator="add"
					data-backendrequest="savelevel"
					data-backendurl="backend/bk_hrsetscores.php"
					data-tableid="#tblvieweduclevels"
					data-tablerequest="vieweduclevels"
					class="btn btn-success">'.$title.' </button>
		  </div>
		</div>';
		
		
	break;
	
	case "editlevel":

		$queryedit = execsqlSRS("
			SELECT [col_id]
			  ,[mothercol_id]
			  ,[rating_col]
			  ,[max_value]
			  ,[IsActive]
			FROM [tbl_SnapshotSBCol]
			WHERE col_id = :col_id", 
			"Select", [
						":col_id" => $datavalue
					  ]); 
					  
			foreach ($queryedit as $edit) {

			echo '<div class="p-3">';
			echo '<div class="form-group">
					<label for="">Category:</label>
					<input hidden type="text" class="form-control field-input" id="field1" value="0"> 
					<input type="text" class="form-control field-input" id="field2"  value="' . $edit["rating_col"] . '">
				</div>';
				
			echo '<div class="form-group">
					<label for="">Points:</label>
					<input type="text" class="form-control field-input" id="field3" value=' . htmlspecialchars($edit["max_value"]) . '>
				  </div> ';
				  
			echo '<div class="form-group">
					<label for="">Status (0/1)</label>
					<input type="text" class="form-control field-input" id="field4" value=' . htmlspecialchars($edit["IsActive"]) . '>
				  </div> ';
				
				
		
				echo    "<div class='form-group d-flex justify-content-center pt-2'>
				<button 
						type='submit' 
						class='btn btn-success' 
						id='save_data'
						data-openmodal='#addeditmodal'
						data-operator='edit'
						data-backendrequest='savelevel'
						data-backendurl='backend/bk_hrsetscores.php'
						data-tableid='#tblvieweduclevels'
						data-tablerequest='vieweduclevels'
						data-datavalue='" . htmlspecialchars($edit["col_id"]) . "'>
						Save Changes
				</button>
              </div>
			  </div>";
			}

	break;


	case "savelevel":

		if ($operator == "edit") {
			
			$querysave = execsqlSRS("
				UPDATE tbl_SnapshotSBCol
				SET rating_col = :rating_col, max_value = :max_value, IsActive = :IsActive
				WHERE col_id = :datavalue", 
				"Update", [
							":rating_col" => $fields["field2"], 
							":max_value" => intval($fields["field3"]), 
							":IsActive" => intval($fields["field4"]), 
							":datavalue" => intval($datavalue)
						  ]);
						  
			echo json_encode (['status' => 'success', 'message' => 'System: Changes have been Saved.']);

		}

		else if ($operator == "add") {
			
			$sql = "[mothercol_id], [rating_col], [max_value], [IsActive]";
			
			$params = ":mothercol_id, :rating_col, :max_value, :IsActive";
			
			$querysave = execsqlSRS("
					INSERT INTO [tbl_SnapshotSBCol] ($sql)
					VALUES ($params)", 
					"Insert", [
								":mothercol_id" => $fields["field1"], 
								":rating_col" => $fields["field2"],
								":max_value" =>  intval($fields["field3"]), 
								":IsActive" => intval($fields["field4"])
							  ]);

			echo json_encode (['status' => 'success', 'message' => 'System: Education Level Added!']);
		}
		
		else {
			echo json_encode (['status' => 'error', 'message' => 'System: Error Fetching. No Operator']);
			return;
		}

	break;

}

?>