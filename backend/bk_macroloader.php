<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$datavalue = isset($_POST["datavalue"]) ? $_POST["datavalue"] : "";

switch ($request) {

	case "loadmacros":

		$loadmacros = execsqlSRS(
			"
			SELECT m.macro_id, m.macro_title, m.macro_desc, c.color_desc, m.macro_icon
			FROM tbl_Macros m

			LEFT JOIN Sys_Menu mm
			ON mm.MenID = m.MenID

			LEFT JOIN tbl_Colors c
			ON c.color_id = m.color_id
			WHERE (
					(mm.MenID = :datavalue)
					AND
					(m.IsActive = '0' AND c.IsActive ='0')
				  )
			ORDER BY macro_id",
			"Select",
			[
				":datavalue" => $datavalue
			]
		);

		echo "<div class='row mb-2 font-weight-bold'>";

		foreach ($loadmacros as $load) {

			echo "
			<div class='col-auto mb-1'>
				<div class='card border border-" . htmlspecialchars($load["color_desc"]) . " shadow h-100' style='max-width:40rem;'>
					<div class='card-body pb-1'>

						<div class='d-flex align-items-center mb-1'>
							<i class='" . htmlspecialchars($load["macro_icon"]) . " text-" . htmlspecialchars($load["color_desc"]) . " mr-2'></i>

							<div class='font-weight-bold text-" . htmlspecialchars($load["color_desc"]) . "'>
								" . htmlspecialchars($load["macro_title"]) . "
							</div>
						</div>

						<div style='text-align: justify;'>
							" . htmlspecialchars($load["macro_desc"]) . "
						</div>

					</div>
				</div>
			</div>";
		}

		echo "</div>";

		break;
}
