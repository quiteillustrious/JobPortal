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

	case "viewmenus":

		$queryViewMenus = execsqlSRS("
			SELECT MenID, Menu, MotherMenID, Description, Menucode, MenuLink, Arrangement, MenIcon, IsActive
			FROM Sys_Menu
			ORDER BY MotherMenID, Arrangement, MenID", 
			"Search", 
			array()
		);

		$menuTree = [];
		foreach ($queryViewMenus as $menu) {
			$parentId = $menu["MotherMenID"] ?? 0;
			$menuTree[$parentId][] = $menu;
		}

		function renderMenuTree($menus, $menuTree, $depth = 0) {
			foreach ($menus as $menu) {
				
				if (htmlspecialchars($menu["MotherMenID"]) == 0) {
					echo "<tr class='bg-secondary'>";
				}
				
				else {
					echo "<tr>";
				}
				
				echo "<td>" . htmlspecialchars($menu["MenID"]) . "</td>";
				echo "<td>" . htmlspecialchars($menu["Menu"]) . "</td>";
				echo "<td>" . htmlspecialchars($menu["MotherMenID"]) . "</td>";
				echo "<td>" . htmlspecialchars($menu["Description"]) . "</td>";
				echo "<td>" . htmlspecialchars($menu["Menucode"]) . "</td>";
				echo "<td>" . htmlspecialchars($menu["MenuLink"]) . "</td>";
				echo "<td>" . htmlspecialchars($menu["Arrangement"]) . "</td>";
				echo "<td> <i class='" . htmlspecialchars($menu["MenIcon"]) . " fa-2x'></i></td>";

				if (htmlspecialchars($menu["IsActive"]) == 0) {
					echo "<td><i class='fa-solid fa-toggle-on fa-2x text-success'
								 data-statuscontent='". htmlspecialchars($menu["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($menu["MenID"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='Sys_Menu'
								 data-databaseparam='MenID'
								 data-tableid='#tblviewmenus'
								 data-tablebackendurl='backend/bk_menumanagement.php'
								 data-tablerequest='viewmenus'
								 ></i></td>";
				}

				else if (htmlspecialchars($menu["IsActive"]) == 1) {
					echo "<td><i class='fa-solid fa-toggle-off fa-2x text-danger'
								 data-statuscontent='". htmlspecialchars($menu["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($menu["MenID"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='Sys_Menu'
								 data-databaseparam='MenID'
								 data-tableid='#tblviewmenus'
								 data-tablebackendurl='backend/bk_menumanagement.php'
								 data-tablerequest='viewmenus'
								 ></i></td>";
				}

				echo "<td>
						<button type='button' 
								class='btn btn-warning m-1' 
								id='edit_data' 
								data-tooltip='View/Edit'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Edit Menu'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_menumanagement.php'
								data-backendrequest='editmenu'
								data-datavalue='" . htmlspecialchars($menu["MenID"]) . "'>
							<i class='fa-solid fa-pencil'></i>
						</button>
						<button type='button' 
								class='btn btn-danger m-1' 
								id='delete_data' 
								data-tooltip='Delete'
								data-openmodal='#addeditmodal'
								data-openmodallabel='Delete Menu'
								data-openmodalbody='#addeditcontent'
								data-backendurl='backend/bk_menumanagement.php'
								data-backendrequest='deletemenu'
								data-datavalue='" . htmlspecialchars($menu["MenID"]) . "'>
							<i class='fa-solid fa-trash'></i>
					  </td>";
				echo "</tr>";

				if (isset($menuTree[$menu["MenID"]])) {
					renderMenuTree($menuTree[$menu["MenID"]], $menuTree, $depth + 1);
				}
			}
		}
		
		if (isset($menuTree[0])) {
			renderMenuTree($menuTree[0], $menuTree);
		}
	break;
	
	case "addmenu":

		echo '
		<div class="p-3">
		  <div class="form-group">
			<label for="">Menu</label>
			<input type="text" class="form-control field-input" id="field1" placeholder="Menu...">
		  </div>

		  <div class="form-group">
			<label for="">Mother Menu ID</label>
			<input type="text" class="form-control field-input" id="field2" placeholder="0 if Mother or enter the ID of a Mother Menu...">
		  </div>
		  
		  <div class="form-group">
			<label for="">Description</label>
			<input type="text" class="form-control field-input" id="field3" placeholder="Description...">
		  </div>
		  
		  <div class="form-group">
			<label for="">Menucode</label>
			<input type="text" class="form-control field-input" id="field4" placeholder="example: SysMngt...">
		  </div>

		  <div class="form-group">
			<label for="">Menulink</label>
			<input type="text" class="form-control field-input" id="field5" placeholder="example: menu.php...">
		  </div>
		  
		  <div class="form-group">
			<label for="">Arrangement</label>
			<input type="text" class="form-control field-input" id="field6" placeholder="Arrangement...">
		  </div>
		  
		  <div class="form-group">
			<label for="">Status</label>
			<input type="text" class="form-control field-input" id="field7" placeholder="Status...">
		  </div>

		  <div class="form-group">
			<label for="">Icon</label>
			<input type="text" class="form-control field-input" id="field8" placeholder="example: nav-icon fas fa-solid fa-computer...">
		  </div>

		  <div class="form-group pt-2 d-flex justify-content-center">
			<button type="submit" 
					id="save_data"
					data-openmodal="#addeditmodal"
					data-operator="add"
					data-backendrequest="savemenu"
					data-backendurl="backend/bk_menumanagement.php"
					data-tableid="#tblviewmenus"
					data-tablerequest="viewmenus"
					class="btn btn-success">Add Menu</button>
		  </div>
		</div>';

	break;

	case "editmenu":

		$queryeditrole = execsqlSRS("
			SELECT MenID, Menu, MotherMenID, Description, Menucode, MenuLink, Arrangement, IsActive, MenIcon
			FROM [Sys_Menu]
			WHERE MenID = :datavalue", 
			"Select", [
						":datavalue" => $datavalue
					  ]); 
					  
			foreach ($queryeditrole as $edit) {

			echo "
				<div class='p-3'>
				<div class='form-group'>
					<label for=''>Menu</label>
                    <input type='text' class='form-control field-input' id='field1' value='" . htmlspecialchars($edit["Menu"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for=''>Mother Menu ID</label>
                    <input type='text' class='form-control field-input' id='field2' value='" . htmlspecialchars($edit["MotherMenID"]) . "'>
				  </div>";

			echo "<div class='form-group'>
					<label for=''>Description</label>
                    <input type='text' class='form-control field-input' id='field3' value='" . htmlspecialchars($edit["Description"]) . "'>
				  </div>";
				  
			echo "<div class='form-group'>
					<label for=''>Menu Code</label>
                    <input type='text' class='form-control field-input' id='field4' value='" . htmlspecialchars($edit["Menucode"]) . "'>
				  </div>";

			echo "<div class='form-group'>
					<label for=''>Menu Link</label>
                    <input type='text' class='form-control field-input' id='field5' value='" . htmlspecialchars($edit["MenuLink"]) . "'>
				  </div>";
				  
			echo "<div class='form-group'>
					<label for=''>Arrangement</label>
                    <input type='text' class='form-control field-input' id='field6' value='" . htmlspecialchars($edit["Arrangement"]) . "'>
				  </div>";
				  
			echo "<div class='form-group'>
					<label for=''>Status</label>
                    <input type='text' class='form-control field-input' id='field7' value='" . htmlspecialchars($edit["IsActive"]) . "'>
				  </div>";
				  
			echo "<div class='form-group'>
					<label for=''>Icon</label>
                    <input type='text' class='form-control field-input' id='field8' value='" . htmlspecialchars($edit["MenIcon"]) . "'>
				  </div>";
				  

				echo    "<div class='form-group d-flex justify-content-center pt-2'>
				<button 
						type='submit' 
						class='btn btn-success' 
						id='save_data'
						data-openmodal='#addeditmodal'
						data-operator='edit'
						data-backendrequest='savemenu'
						data-backendurl='backend/bk_menumanagement.php'
						data-tableid='#tblviewmenus'
						data-tablerequest='viewmenus'
						data-datavalue='" . htmlspecialchars($edit["MenID"]) . "'>
						Save Changes
				</button>
              </div>
			  </div>";
			}

	break;
	
	case "deletemenu":
	
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
						data-backendrequest='savemenu'
						data-backendurl='backend/bk_menumanagement.php'
						data-logslocation='Menu Management'
						data-tableid='#tblviewmenus'
						data-tablerequest='viewmenus'
						data-datavalue='" . htmlspecialchars($datavalue) . "'>
					Confirm Deletion
				</button>
				</div>
			</div>
	";

	break;

	case "savemenu":
	
	if ($operator == "delete") {
		
		$getolddata = execsqlSRS("
				SELECT *
				FROM Sys_Menu
				WHERE MenID = :datavalue", 
				"Select", [
							":datavalue" => $datavalue
						  ]); 
						  
		$string = implode (' | ', $getolddata[0]);

		$querysave = execsqlSRS("
				INSERT INTO [tbl_Logs] (logs_details, logs_dt, logs_location, logs_operation, logs_doneby, IsActive)
				VALUES (:string, :currentdt, :logslocation, :operator, :userid, '0')", 
				"Insert", [
							":string" => $string, 
							":currentdt" => $currentdt, 
							":logslocation" => $logslocation, 
							":operator" => $operator, 
							":userid" => $userid
						  ]); 
						  
		$querydelete = execsqlSRS("
				DELETE FROM Sys_Menu
				WHERE MenID = :datavalue", 
				"Delete", [
							":datavalue" => $datavalue
						  ]); 

		echo json_encode (['status' => 'success', 'message' => 'System: Menu has been Moved to the Logs.']);

		break;
	}

	if ( empty($fields["field1"]) || empty($fields["field3"]) || empty($fields["field4"]) || empty($fields["field6"]) || empty($fields["field8"]) ){
		echo json_encode (['status' => 'error', 'message' => 'System: Some fields are empty.']);
		return;
	}

	if (!is_numeric($fields["field2"]) || !is_numeric($fields["field7"])){
		echo json_encode (['status' => 'error', 'message' => 'System: Status should either be 0 or 1. Mother ID should have a Numeric Value.']);
		return;
	}

		if ($operator == "edit") {
			
			$querysave = execsqlSRS("
				UPDATE Sys_Menu
				SET Menu = :menu, MotherMenID = :mother, Description = :desc, Menucode = :code, MenuLink = :link, Arrangement = :arra, 
				IsActive = :status, MenIcon = :icon
				WHERE MenID = :datavalue", 
				"Update", [
							":menu" => $fields["field1"], 
							":mother" => intval($fields["field2"]), 
							":desc" => $fields["field3"], 
							":code" => $fields["field4"], 
							":link" => $fields["field5"], 
							":arra" => $fields["field6"], 
							":status" => intval($fields["field7"]), 
							":icon" => $fields["field8"], 
							":datavalue" => intval($datavalue)
						  ]);

			echo json_encode (['status' => 'success', 'message' => 'System: Changes have been saved.']);
			
		}

		else if ($operator == "add") {

			$querysave = execsqlSRS("
					INSERT INTO [Sys_Menu] (Menu, MotherMenID, Description, Menucode, MenuLink, Arrangement, IsActive, MenIcon)
					VALUES (:menu, :mother, :description, :menucode, :menulink, :arrangement, :status, :icon)", 
					"Insert", [
								":menu" => $fields["field1"], 
								":mother" => intval($fields["field2"]), 
								":description" => $fields["field3"], 
								":menucode" => $fields["field4"], 
								":menulink" => $fields["field5"], 
								":arrangement" => $fields["field6"], 
								":status" => intval($fields["field7"]), 
								":icon" => $fields["field8"]
							  ]); 
							  
			$getmenu = execsqlSRS("
					SELECT TOP 1 MenID
					FROM Sys_Menu
					WHERE (
							(Menu = :menu AND MotherMenID = :mother AND Description = :description AND
							 Menucode = :menucode AND MenuLink = :menulink AND Arrangement = :arrangement AND
							 IsActive = :status AND MenIcon = :icon)
						  )
					ORDER BY MenID DESC", 
					"Select", [
								":menu" => $fields["field1"], 
								":mother" => intval($fields["field2"]), 
								":description" => $fields["field3"], 
								":menucode" => $fields["field4"], 
								":menulink" => $fields["field5"], 
								":arrangement" => $fields["field6"], 
								":status" => intval($fields["field7"]), 
								":icon" => $fields["field8"]
							  ]); 
							  
			$menid = isset($getmenu[0]["MenID"]) ? $getmenu[0]["MenID"] : "";
			
				if ($menid){
					
					$getroles = execsqlSRS("
							SELECT RID
							FROM Sys_Role
							", 
							"Select", []); 

					foreach ($getroles as $roles) {
						
						$queryloop = execsqlSRS("
								INSERT INTO Sys_RoleMenu (RID, MenID, IsActive)
								VALUES (:rid, :menid, '1')", 
								"Insert", [
											":rid" => $roles["RID"], 
											":menid" => $menid
										  ]); 
										  
						
					}
					
					echo json_encode (['status' => 'success', 'message' => 'System: Menu Added and Looped.']);
					
				}
				
				else {
					echo json_encode (['status' => 'error', 'message' => 'System: Error Fetching. Query Failed.']);
					return;
				} 
		}
		
		else {
			echo json_encode (['status' => 'error', 'message' => 'System: Error Fetching. No Operator']);
			return;
		}

	break;
}
?>
