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
$rid = isset($_POST["rid"]) ? $_POST["rid"] : "";

$currentdt = date("Y-m-d H:i:s");

switch ($request) {

    case "fetchpositions":

        $queryselect = execsqlSRS(
            "
	SELECT  pos.[pubpos_id]
			,pos.[publication_id]
			,pos.[position_title]
			,app.[appoint_desc]
			,c.[color_desc]
            ,office.[office_desc]

	FROM [tbl_PublicationPosition] pos

	LEFT JOIN [tbl_ProfExpAppoint] app
	ON app.appoint_id = pos.appoint_id

	LEFT JOIN [tbl_Colors] c
	ON c.color_id = app.color_id

	LEFT JOIN [tbl_Attachment] att
	ON att.attach_id = pos.attach_id

    LEFT JOIN [tbl_Office] office
    ON office.[office_id] = pos.[office_id]

	ORDER BY pos.position_title, pos.publication_id",
            "Search",
            array(intval($datavalue))
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

    case "fetchapplicants":

        $fetchapplicants = execsqlSRS("
            SELECT
                snap.snap_id,
                snap.pubpos_id,
                stat.status_code,
                snap.UserID,
                userdet.LastName,
                userdet.FirstName,
                userdet.MiddleName,
                snap.AppliedDate
            FROM tbl_Snapshot snap

            OUTER APPLY (
                SELECT TOP 1
                    u.LastName,
                    u.FirstName,
                    u.MiddleName
                FROM tbl_SnapshotUser u
                WHERE u.UserID = snap.UserID
            ) userdet

            OUTER APPLY (
                SELECT TOP 1
                    s.status_code,
                    s.color_id
                FROM tbl_SnapshotStatus s
                WHERE s.snap_status = snap.snap_status
            ) stat

            WHERE snap.pubpos_id = ?

            ORDER BY snap.AppliedDate
        ", "Select", array(intval($datavalue)));

        $fetchposition = execsqlSRS("
            SELECT  pos.[pubpos_id]
                    ,pos.[position_title]
            FROM [tbl_PublicationPosition] pos

            WHERE pos.[pubpos_id] = ?
            ", "Select", array(intval($datavalue)));

        echo "
        <style>
        .app-table {
            width: 100%;
            border-collapse: collapse;
        }

        .app-table th {
            background: #16a34a;
            color: white;
            padding: 10px;
            text-align: left;
        }

        .app-table td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            vertical-align: top;
        }

        .section-title {
            font-weight: bold;
            color: #065f46;
        }

        ul.clean-list {
            margin: 0;
            padding-left: 18px;
        }

        ul.clean-list li {
            margin-bottom: 6px;
        }

        .attach-row {
            display:flex;
            gap:5px;
            flex-wrap:wrap;
            margin-top:4px;
        }
        </style>
        ";

        $applicantcount = count($fetchapplicants);

        echo "<div class='table-responsive' style='max-height: 800px; overflow-y: auto;' id='applicantscontent'>";
        echo "<table class='app-table'>";

        echo "

        <tr>
            <th colspan='10' style='position: sticky; top: 0; z-index: 20;'>
                <div class='font-weight-bold ml-2'>
                    <span>
                        Applicants for " . $fetchposition[0]['position_title'] . "
                    </span>
                </div>
            </th>
        </tr>

        <tr>
            <th style='position: sticky; top: 40px; z-index: 10;' class='text-center'>#</th>
            <th style='position: sticky; top: 40px; z-index: 10;' class='text-center'>Personal Information</th>
            <th style='position: sticky; top: 40px; z-index: 10;' class='text-center'>Educational Qualification</th>
            <th style='position: sticky; top: 40px; z-index: 10;' class='text-center'>Work Experience</th>
            <th style='position: sticky; top: 40px; z-index: 10;' class='text-center'>Trainings</th>
            <th style='position: sticky; top: 40px; z-index: 10;' class='text-center'>Eligibility / NC</th>
            <th style='position: sticky; top: 40px; z-index: 10;' class='text-center'>Competency/ies</th>
            <th style='position: sticky; top: 40px; z-index: 10;' class='text-center'>Other Information (Skills / Hobbies / Performance / etc.)</th>
            <th style='position: sticky; top: 40px; z-index: 10;' class='text-center'>President's Remarks</th>
            <th style='position: sticky; top: 40px; z-index: 10;' class='text-center'>Action</th>
        </tr>
        ";

        $i = 1;

        foreach ($fetchapplicants as $applicants) {

            $snap_id = intval($applicants['snap_id']);
            $user_id = intval($applicants['UserID']);
            $pubpos_id = intval($applicants['pubpos_id']);

            $fullName =
                $applicants['LastName'] . ', ' .
                $applicants['FirstName'] . ' ' .
                $applicants['MiddleName'];

            $fetchedu = execsqlSRS("
                SELECT  [degree_name]
                        ,[major_name]
                        ,[school_name]
                        ,[school_address]
                        ,[start_date]
                        ,[end_date]
                        ,[educ_level]
                FROM [tbl_SnapshotEducation]
                WHERE [snap_id] = ? AND [UserID] = ?
                ORDER BY [end_date]
            ", "Select", array($snap_id, $user_id));

            $fetchexp = execsqlSRS("
                SELECT snapexp_id, position, department, start_date, end_date
                FROM tbl_SnapshotExp
                WHERE snap_id = ? AND UserID = ?
                ORDER BY [end_date]
            ", "Select", array($snap_id, $user_id));

            $fetchld = execsqlSRS("
                SELECT snapld_id, ld_title, num_hours
                FROM tbl_SnapshotLD
                WHERE snap_id = ? AND UserID = ?
                ORDER BY [end_date]
            ", "Select", array($snap_id, $user_id));

            $fetcheli = execsqlSRS("
                SELECT snapelig_id, elig_type
                FROM tbl_SnapshotEligibility
                WHERE snap_id = ? AND UserID = ?
            ", "Select", array($snap_id, $user_id));

            $fetchskills = execsqlSRS("
                SELECT snapskills_id, skills_desc
                FROM tbl_SnapshotSkills
                WHERE snap_id = ? AND UserID = ?
                ORDER BY [skills_desc]
            ", "Select", array($snap_id, $user_id));

            $fetchcomp = execsqlSRS("
                SELECT [snapcomp_id], [comp_desc]
                FROM [tbl_SnapshotComp]
                WHERE [snap_id] = ? AND [UserID] = ?
                ORDER BY [comp_desc]
            ", "Select", array($snap_id, $user_id));

            $personal = "<span class='section-title'>" . $fullName . "</span>";

            //Edu
            $edu_html = "<ul class='clean-list'>";

            $total_hours = 0;

            foreach ($fetchedu as $edu) {


                $degree = trim($edu['degree_name'] ?? '');
                $major  = trim($edu['major_name'] ?? '');

                $edu_html .= "<li>
                    <span class='section-title'>" . $edu['educ_level'] . "</span><br>";

                if (strcasecmp($degree, 'n/a') !== 0 || strcasecmp($major, 'n/a') !== 0) {
                    $parts = [];
                    if (strcasecmp($degree, 'n/a') !== 0) $parts[] = $degree;
                    if (strcasecmp($major, 'n/a') !== 0) $parts[] = $major;

                    $edu_html .= "<small>" . implode(' - ', $parts) . "</small><br>";
                }

                $edu_html .= "<small class='text-danger'>" . $edu['school_name'] . " - " . $edu['school_address'] . "</small>
                ";

                $edu_html .= "</li>";
            }

            $edu_html .= "</ul>";

            //Exp

            if (count($fetchexp) == 0) {
                $exp_html = "<span class='text-danger font-weight-bold'>n/a</span>";
            } else {
                $exp_html = "<ul class='clean-list'>";

                $total_months = 0;

                foreach ($fetchexp as $e) {

                    $start = new DateTime($e['start_date']);
                    $end = !empty($e['end_date']) ? new DateTime($e['end_date']) : new DateTime();

                    $interval = $start->diff($end);

                    $years = $interval->y;
                    $months = $interval->m;

                    $exp_months = ($years * 12) + $months;

                    $points = $exp_months / 12;

                    $total_months += $exp_months;

                    $display_years = floor($exp_months / 12);
                    $display_months = $exp_months % 12;

                    $exp_html .= "<li>
                    <span class='section-title'>" . $e['position'] . "</span> - " . $e['department'] . " - " . date('F j, Y', strtotime($e['start_date'])) . " - " . date('F j, Y', strtotime($e['end_date'])) . "
                    <br>
                    <small class='text-danger font-weight-bold'>
                        " . $display_years . "y " . $display_months . "m | "
                        . number_format($points, 2) . " pts
                    </small>
                ";

                    $exp_html .= "</li>";
                }

                $total_years = floor($total_months / 12);
                $total_remaining_months = $total_months % 12;
                $total_points = $total_months / 12;

                $exp_html .= "<li class='total-exp'>
                <strong>Total Experience:</strong>
                <small class='text-danger font-weight-bold'>
                " . $total_years . "y " . $total_remaining_months . "m | "
                    . number_format($total_points, 2) . " pts
                </small>
            </li>";

                $exp_html .= "</ul>";
            }

            //LD
            if (count($fetchld) == 0) {
                $ld_html = "<span class='text-danger font-weight-bold'>n/a</span>";
            } else {
                $ld_html = "<ul class='clean-list'>";

                $total_hours = 0;

                foreach ($fetchld as $l) {

                    $hours = (float)$l['num_hours'];
                    $total_hours += $hours;

                    $ld_html .= "<li>
                    " . $l['ld_title'] . " -
                    <small class='text-danger font-weight-bold'>
                        " . $hours . " hrs
                    </small>
                ";

                    $ld_html .= "</li>";
                }

                $ld_html .= "<li class='total-exp'>
                <strong>Total Learning Hours:</strong>
                <small class='text-danger font-weight-bold'>
                " . $total_hours . " hrs
                </small>
            </li>";

                $ld_html .= "</ul>";
            }

            //Eligibility
            if (count($fetcheli) == 0) {
                $eli_html = "<span class='text-danger font-weight-bold'>n/a</span>";
            } else {
                $eli_html = "<ul class='clean-list'>";
                foreach ($fetcheli as $e) {

                    $eli_html .= "<li>
                    " . $e['elig_type'] . "<br>";

                    $eli_html .= "</li>";
                }
                $eli_html .= "</ul>";
            }

            //Skills
            $skill_html = "<ul class='clean-list'>";
            foreach ($fetchskills as $ski) {
                $skill_html .= "<li>" . $ski['skills_desc'] . "</li>";
            }
            $skill_html .= "</ul>";

            //Competencies
            $comp_html = "<ul class='clean-list'>";
            foreach ($fetchcomp as $comp) {
                $comp_html .= "<li>" . $comp['comp_desc'] . "</li>";
            }
            $comp_html .= "</ul>";

            echo "
            <tr>
                <td>" . $i . "</td>
                <td id='viewprofile_" . $snap_id . "'
                    data-datavalue='" . $snap_id . "'
                    data-openmodallabel='" . htmlspecialchars($fullName, ENT_QUOTES) . "'
                    data-tooltip='View Profile'
                    style='cursor:pointer;'
                    >"
                . $personal .
                "</td>
                <td>" . $edu_html . "</td>
                <td>" . $exp_html . "</td>
                <td>" . $ld_html . "</td>
                <td>" . $eli_html . "</td>
                <td>" . $skill_html . "</td>
                <td>" . $comp_html . "</td>
                <td></td>
                <td>";

            $delremarks = execsqlSRS("
                SELECT [snap_id]
                        ,[UserID]
                        ,[IsQual]
                        ,[remarks]
                        ,[remarks_by]
                        ,[remarks_at]
                FROM [tbl_SnapshotDelRem]
                WHERE [snap_id] = ?
                ", "Select", array(
                intval($snap_id)
            ));

            $selectedDecision = null;
            $existingRemarks = '';

            if (!empty($delremarks)) {
                $selectedDecision = isset($delremarks[0]['IsQual']) ? intval($delremarks[0]['IsQual']) : null;
                $existingRemarks = $delremarks[0]['remarks'] ?? '';
            }

            if ($rid == 1 || $rid == 3) {
                echo "
                    <label style='margin-right:10px; cursor:pointer;'>
                        <input type='radio'
                            name='decision_" . $snap_id . "'
                            value='0'
                            class='decision-radio'
                            id='decision_q_" . $snap_id . "'
                            data-userid='" . $user_id . "'
                            data-tooltip='Qualified'
                            " . ($selectedDecision === 0 ? "checked" : "") . ">
                        Q
                    </label>

                    <label style='cursor:pointer;'>
                        <input type='radio'
                            name='decision_" . $snap_id . "'
                            value='1'
                            class='decision-radio'
                            id='decision_dq_" . $snap_id . "'
                            data-userid='" . $user_id . "'
                            data-tooltip='Disqualified'
                            " . ($selectedDecision === 1 ? "checked" : "") . ">
                        DQ
                    </label>

                    <div id='hrremarks_box_" . $snap_id . "'
                        style='display:" . ($selectedDecision === 1 ? "block" : "none") . "; margin-top:6px;'>

                        <select
                            name='hrremarks_" . $snap_id . "'
                            class='form-control form-control-sm'>
                            <option value=''>Select remarks...</option><hr>";


                $remarkLib = execsqlSRS("
                    SELECT [snapdelremlib_id]
                            ,[snapdelremlib_desc]

                    FROM [tbl_SnapshotDelRemLib]
                    ", "Select", array());

                foreach ($remarkLib as $lib) {
                    $selected = ($existingRemarks == $lib['snapdelremlib_desc']) ? "selected" : "";
                    echo "<option value='" . htmlspecialchars($lib['snapdelremlib_desc'], ENT_QUOTES) . "' $selected>"
                        . htmlspecialchars($lib['snapdelremlib_desc']) .
                        "</option>";
                }

                echo "
                        </select>
                    </div>
                    ";
            } else {
                echo "
                        <div class='text-center'>
                            <div class='font-weight-bold'>Remarks:</div>
                            <div>";

                if (!empty($delremarks)) {
                    $isQual = $delremarks[0]['IsQual'];
                    $remarks = $delremarks[0]['remarks'];

                    if ($isQual == 0) {
                        echo "<div class='text-success font-weight-bold'>Qualified</div>";
                    } else {
                        echo "<div class='text-danger font-weight-bold'>Disqualified</div>";

                        if (!empty($remarks)) {
                            echo htmlspecialchars($remarks);
                        }
                    }
                } else {
                    echo "<div class='text-danger'>No Remarks</div>";
                }

                echo "
                            </div>
                        </div>
                    ";
            }
            echo "
                </td>
            </tr>

            ";

            $i++;
        }

        echo "

            </table>
        </div>";

        if ($rid == 1 || $rid == 3) {

            echo "
                <div class='d-flex justify-content-center mt-3'>
                    <button
                        type='button'
                        class='btn btn-success d-inline-flex align-items-center mt-2'
                        id='delibdecision_submit'
                        data-datavalue='" . $datavalue . "'
                        >
                        <span class='text-nowrap'>Submit Decisions <i class='fa-solid fa-arrow-right-to-bracket'></i></span>
                    </button>
                </div>";
        }

        echo "
        <script>
        document.addEventListener('change', function(e) {

            if (e.target.classList.contains('decision-radio')) {

                let fullId = e.target.id;
                let id = fullId.replace('decision_q_', '').replace('decision_dq_', '');
                let value = e.target.value;

                let box = document.getElementById('hrremarks_box_' + id);

                if (!box) return;

                if (value === '1') {
                    box.style.display = 'block';
                } else {
                    box.style.display = 'none';

                    let textarea = box.querySelector('textarea');
                    if (textarea) textarea.value = '';
                }
            }
        });
        </script>

        ";
        break;

    case "save_delib_decision":

        $payload = isset($_POST['payload']) ? json_decode($_POST['payload'], true) : [];

        if (empty($payload)) {
            echo json_encode([
                "status" => "error",
                "message" => "No data received."
            ]);
            exit;
        }

        $errors = [];

        foreach ($payload as $row) {

            $snap_id  = intval($row['snap_id'] ?? 0);
            $decision   = isset($row['decision']) ? intval($row['decision']) : null;
            $remarks  = trim($row['remarks'] ?? '');
            $user_id   = intval($row['user_id'] ?? 0);

            if ($decision === null) {
                $errors[] = "Missing decision for Snap ID: $snap_id";
                continue;
            }

            if ($decision === 1 && $remarks === '') {
                $errors[] = "Remarks required for Disqualified Snap ID: $snap_id";
                continue;
            }

            $check = execsqlSRS("
            SELECT snap_id
            FROM tbl_SnapshotDelRem
            WHERE snap_id = ?
        ", "Select", array($snap_id));

            if (!empty($check)) {

                execsqlSRS("
                UPDATE tbl_SnapshotDelRem
                SET IsQual = ?, remarks = ?, remarks_at = ?, UserID = ?, remarks_by = ?, IsActive = '0'
                WHERE snap_id = ?
            ", "Update", array($decision, $remarks, $currentdt, $user_id, $userid, $snap_id));
            } else {

                execsqlSRS("
                INSERT INTO tbl_SnapshotDelRem
                    (snap_id, IsQual, remarks, remarks_at, UserID, remarks_by, IsActive)
                VALUES (?, ?, ?, ?, ?, ?, '0')
            ", "Insert", array($snap_id, $decision, $remarks, $currentdt, $user_id, $userid));
            }
        }

        if (!empty($errors)) {
            echo json_encode([
                "status" => "error",
                "message" => "Validation failed.",
                "errors" => $errors
            ]);
            exit;
        }

        echo json_encode([
            "status" => "success",
            "message" => "All decisions saved successfully."
        ]);

        break;
}
