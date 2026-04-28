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
    case "viewprivilege":
	
		$queryViewMenus = execsqlSRS("
			SELECT m.MenID, m.Menu, m.MotherMenID, rm.URID, rm.IsActive, r.RID
			FROM Sys_Menu m
			
			LEFT JOIN Sys_RoleMenu rm
			ON rm.MenID = m.MenID
			
			LEFT JOIN Sys_Role r
			ON r.RID = rm.RID

			WHERE (
					(rm.RID = :datavalue)
				  )


			ORDER BY m.MotherMenID, m.Arrangement, m.MenID", 
			"Select", 
			[
				":datavalue" => $datavalue
			]
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
				
				echo "<td>" . htmlspecialchars($menu["URID"]) . "</td>";
				echo "<td>" . htmlspecialchars($menu["Menu"]) . "</td>";

				if (htmlspecialchars($menu["IsActive"]) == 0) {
					echo "<td><i class='fa-solid fa-toggle-on fa-2x text-success'
								 data-statuscontent='". htmlspecialchars($menu["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($menu["URID"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='Sys_RoleMenu'
								 data-databaseparam='URID'
								 data-tableid='#tblviewprivilege'
								 data-tablebackendurl='backend/bk_privilegemanagement.php'
								 data-tablerequest='viewprivilege'
								 data-datavalue='" . htmlspecialchars($menu["RID"]) . "'
								 ></i></td>";
				}

				else if (htmlspecialchars($menu["IsActive"]) == 1) {
					echo "<td><i class='fa-solid fa-toggle-off fa-2x text-danger'
								 data-statuscontent='". htmlspecialchars($menu["IsActive"]) ."'
								 data-statusdata='" . htmlspecialchars($menu["URID"]) . "'
								 id='statustrigger'
								 data-backendurl='backend/bk_statustrigger.php'
								 data-backendmethod='POST'
								 data-backendrequest='statustoggle'
								 data-databasedir='Sys_RoleMenu'
								 data-databaseparam='URID'
								 data-tableid='#tblviewprivilege'
								 data-tablebackendurl='backend/bk_privilegemanagement.php'
								 data-tablerequest='viewprivilege'
								 data-datavalue='" . htmlspecialchars($menu["RID"]) . "'
								 ></i></td>";
				}
			
				if (isset($menuTree[$menu["MenID"]])) {
					renderMenuTree($menuTree[$menu["MenID"]], $menuTree, $depth + 1);
				}
			}
        }
		
		if (isset($menuTree[0])) {
			renderMenuTree($menuTree[0], $menuTree);
		}

    break;
}
?>
