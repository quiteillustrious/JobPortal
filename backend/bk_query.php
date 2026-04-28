<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$input = isset($_POST["input"]) ? $_POST["input"] : "";

switch ($request) {

	case "executequery":
	
		if(!$input){
			echo "Type Something...";
			break;
		}
		
		$spacesremoved = ltrim($input);
		$firstword = strtoupper(strtok($spacesremoved, " "));
		$formatted = ucfirst(strtolower($firstword));
		
		preg_match('/SELECT\s+(.*?)\s+FROM/i', $spacesremoved, $implodeparameter);
		$columns = $implodeparameter[1];
	
		$query = execsqlSRS("$input",
		"$formatted",
		array()
		);
		
 		if (!$query){
			echo "There's an error in the Query...";
		}
		
		else {
			echo "
				<div class='table-responsive'>
					<table class='table table-hover table-striped'>
						<thead class='thead-dark'>
							<tr>";
							
							$string = explode(",",$columns);
							foreach ($string as $column) {
								echo "<th>" . htmlspecialchars($column) . "</th>";
							}
							
			echo 		"</tr>
						</thead>
						<tbody>";
						
							foreach ($query as $row) {
								echo "<tr>";
								foreach ($string as $param) {
									
									$letter = preg_replace('/\s+/', '', $param);
									$trimmed = substr(strrchr(trim($letter), '.'), 1);
									
									if (!$trimmed){
										echo "<td>" . htmlspecialchars($row[$letter]) . "</td>";
									}
									
									else{
										echo "<td>" . htmlspecialchars($row[$trimmed]) . "</td>";
									}		
								}
								echo "</tr>";
							}
							
			echo 		"</tbody>
					</table>
				</div>";
		} 

	break;

}

?>