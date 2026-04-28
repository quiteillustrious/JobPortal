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

    case "fetchpublications":

        $queryselect = execsqlSRS(
            "
            SELECT  pub.[publication_id]
                    ,pub.[pubtitle_name]
                    ,pub.[pubtitle_startdt]
                    ,pub.[pubtitle_enddt]
                    ,stat.[pubstatus_desc]
                    ,c.[color_desc]
                    ,pub.[UserID]
                    ,pub.[created_at]
                    ,pub.[IsActive]

            FROM [tbl_Publication] pub

            LEFT JOIN [tbl_PublicationStatus] stat
            ON stat.pubstatus_id = pub.pubstatus_id

            LEFT JOIN [tbl_Colors] c
            ON c.color_id = stat.color_id

            WHERE pub.[pubstatus_id] = 4

            ORDER BY pub.publication_id DESC",
            "Search",
            array()
        );

        foreach ($queryselect as $publication) {

            $status = htmlspecialchars($publication["pubstatus_desc"]);
            $color = strtolower($publication["color_desc"] ?? 'secondary');

            echo "<tr id='fetchposition_" . htmlspecialchars($publication["publication_id"]) . "'
				  data-datavalue='" . htmlspecialchars($publication["publication_id"]) . "'>";
            echo "<td class='font-weight-bold'>" . htmlspecialchars($publication["pubtitle_name"]) . "</td>";
            echo "<td>" . htmlspecialchars($publication["pubtitle_startdt"]) . " - " . htmlspecialchars($publication["pubtitle_enddt"]) . "</td>";
            echo "<td><span class='badge badge-$color p-2'>$status</span></td>";
            echo "</tr>";
        }

        break;

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

	WHERE pos.publication_id = ?

	ORDER BY pos.position_title",
            "Search",
            array(intval($datavalue))
        );

        foreach ($queryselect as $position) {

            $appoint = htmlspecialchars($position["appoint_desc"]);
            $color = strtolower($position["color_desc"] ?? 'secondary');

            echo "<tr id='fetchapplicants_" . htmlspecialchars($position["pubpos_id"]) . "'
				  data-datavalue='" . htmlspecialchars($position["pubpos_id"]) . "'>";
            echo "<td class='font-weight-bold'>" . htmlspecialchars($position["position_title"]) . "</td>";
            echo "<td class='font-weight-bold'>" . htmlspecialchars($position["office_desc"]) . "</td>";
            echo "<td><span class='badge badge-$color p-2'>$appoint</span></td>";
            echo "<td>
				<button class='btn btn-sm btn-info'
						id='positionsummary_" . htmlspecialchars($position["pubpos_id"]) . "'
						data-datavalue='" . htmlspecialchars($position["pubpos_id"]) . "'
						data-openmodallabel='" . htmlspecialchars($position["position_title"]) . "'
						data-tooltip='View Summary'>
					<i class='fa-solid fa-rectangle-list'></i>
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

        $totalRemarked = 0;

        foreach ($fetchapplicants as $app) {

            $checkforremarked = execsqlSRS("
                SELECT deci.[opdecision_id]
                FROM [tbl_SnapshotOPDecision] deci
                WHERE deci.[IsActive] = 0
                    AND deci.[snap_id] = ?
            ", "Select", array(intval($app['snap_id'])));

            $totalRemarked += count($checkforremarked);
        }

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
            <th style='position: sticky; top: 40px; z-index: 10;' class='text-center'>Competency</th>
            <th style='position: sticky; top: 40px; z-index: 10;' class='text-center'>Other Information (Skills / Hobbies / Performance / etc.)</th>
            <th style='position: sticky; top: 40px; z-index: 10;' class='text-center'>Other Positions being applied for</th>
            <th style='position: sticky; top: 40px; z-index: 10;' class='text-center'>Action<hr>
                <div class='text-nowrap'>Remarked (" . ($totalRemarked ?? 0) . "/" . count($fetchapplicants) . ")</div>
            </th>
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

            /*
            $fetchsectors = execsqlSRS("
                SELECT
                    CASE question_code
                        WHEN 'q40a' THEN 'Indigenous Group'
                        WHEN 'q40b' THEN 'Person with Disability'
                        WHEN 'q40c' THEN 'Solo Parent'
                        WHEN 'q40d' THEN 'Pregnant'
                        WHEN 'q40e' THEN 'Senior Citizen'
                    END AS tag_label
                FROM tbl_SnapshotAnswers
                WHERE snap_id = ?
                    AND UserID = ?
                    AND answer = 'Yes'
                    AND question_code IN ('q40a','q40b','q40c','q40d','q40e')
            ", "Select", array($snap_id, $user_id));
            */

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

            //Sector
            /*
            $sector_html = "";
            foreach ($fetchsectors as $s) {
                $sector_html .= "<span class='badge-tag'>" . $s['tag_label'] . "</span> ";
            }
                */

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
            if ($rid == 1 || $rid == 3) {
                echo "

                    <label style='margin-right:10px; cursor:pointer;'>
                        <input type='radio'
                            name='decision_" . $snap_id . "'
                            value='Pass'
                            class='decision-radio'
                            id='decision_" . $snap_id . "'
                            data-tooltip='Qualified'>
                        Q
                    </label>

                    <label style='cursor:pointer;'>
                        <input type='radio'
                            name='decision_" . $snap_id . "'
                            value='DQ'
                            class='decision-radio'
                            id='decision_" . $snap_id . "'
                            data-tooltip='Disqualified'>
                        DQ
                    </label>

                    <div id='hrremarks_box_" . $snap_id . "' style='display:none; margin-top:6px;'>
                        <textarea
                            name='hrremarks_" . $snap_id . "'
                            class='form-control form-control-sm'
                            rows='3'
                            placeholder='Enter remarks here...'
                            ></textarea>
                    </div>

                    <div>
                        <button
                            type='button'
                            class='btn btn-sm btn-success d-inline-flex align-items-center mt-2'
                            id='hrdelremdecision_" . $snap_id . "'
                            data-pubposid='" . $pubpos_id . "'
                            data-snapid='" . $snap_id . "'
                            data-userid='" . $user_id . "'
                            data-remarksby='" . $userid . "'
                            >
                            <span class='text-nowrap'>Submit Decision</span>
                        </button>
                    </div>";
            } else {
                echo "
                        <div class=''>
                            <label for='commremarks_" . $snap_id . "'>Remarks</label>
                            <textarea
                                class='form-control'
                                id='commremarks_" . $snap_id . "'
                                name='commremarks_" . $snap_id . "'
                                rows='3'
                                placeholder='Enter remarks here...'></textarea>
                        </div>

                        <div>
                            <button
                                class='btn btn-success btn-sm mt-2'
                                type='button'
                                id='commdelremdecision_" . $snap_id . "'
                                data-pubposid='" . $pobpos_id . "'
                                data-snapid='" . $snap_id . "'
                                data-userid='" . $user_id . "'
                                data-remarksby='" . $userid . "'
                                >
                                <span class='text-nowrap'>Submit Remarks</span>
                            </button>
                        </div>
                    ";
            }
            echo "
                </td>
            </tr>
            ";

            $i++;
        }

        echo "</table>
        </div>

        <script>
        document.addEventListener('change', function(e) {

            if (e.target.classList.contains('decision-radio')) {

                let fullId = e.target.id;
                let id = fullId.replace('decision_', '');
                let value = e.target.value;

                let box = document.getElementById('hrremarks_box_' + id);

                if (!box) return;

                if (value === 'DQ') {
                    box.style.display = 'block';
                } else {
                    box.style.display = 'none';

                    let textarea = box.querySelector('textarea');
                    if (textarea) textarea.value = '';
                }
            }
        });

        $(document).off('click', '[id^=\'opdecisiontohr_\']').on('click', '[id^=\'opdecisiontohr_\']', function() {

        var snapid = $(this).data('snapid');
        var pubposid = $(this).data('pubposid');
        var userid = $(this).data('userid');

        var decisionvalue = $('#opdecision_' + snapid).val();

        $.ajax({
            url: 'backend/bk_homepage.php',
            method: 'POST',
            dataType: 'json',

            data: {
            request: 'submitopdecision',
            datavalue: decisionvalue,
            snapid: snapid,
            pubposid: pubposid,
            userid: userid,
            remarksby: UserInfo['UserID']
            },

            beforeSend: function() {
            $('#loadingSpinner').css('display', 'flex').hide().fadeIn(200);
            },

            success: function(dataResult) {

            $('#loadingSpinner').fadeOut(200);

            if (dataResult.status === 'success') {

                Swal.fire({
                title: 'Success!',
                text: dataResult.message,
                icon: 'success',
                confirmButtonText: 'OK',
                scrollbarPadding: false
                });

                $.ajax({
                url: 'backend/bk_homepage.php',
                method: 'POST',
                data: {
                    request: 'fetchapplicants',
                    datavalue: pubposid,
                    userid: UserInfo['UserID'],
                    rid: UserInfo['RID']
                },
                success: function(response) {
                    $('#xlmodalcontent').html(response);
                },
                error: function(xhr) {
                    console.error('Fetch error:', xhr.responseText);
                    Swal.fire('Error', 'Failed to reload applicants', 'error');
                }
                });

            } else {
                Swal.fire({
                title: 'Oops!',
                text: dataResult.message || 'Unknown error occurred',
                icon: 'error',
                confirmButtonText: 'I see!',
                scrollbarPadding: false
                });
            }
            },

            error: function(xhr, status, error) {

            $('#loadingSpinner').fadeOut(200);

            console.error('AJAX Error:', {
                status: status,
                error: error,
                response: xhr.responseText
            });

            Swal.fire({
                title: 'Server Error',
                text: 'Something went wrong while submitting. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK',
                scrollbarPadding: false
            });
            }
        });

        });
        </script>

        ";
        break;
}
