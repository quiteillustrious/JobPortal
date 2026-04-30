<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$fields = isset($_POST["fields"]) ? $_POST["fields"] : "";
$operator = isset($_POST["operator"]) ? $_POST["operator"] : "";
$datavalue = isset($_POST["datavalue"]) ? $_POST["datavalue"] : "";
$logslocation = isset($_POST["logslocation"]) ? $_POST["logslocation"] : "";
$userid = isset($_POST["userid"]) ? $_POST["userid"] : "";
$rid = isset($_POST["rid"]) ? $_POST["rid"] : "";

$currentdt = date("Y-m-d H:i:s");

switch ($request) {

    case "fetchjobs":

        $fetchpublication = execsqlSRS("
        SELECT
            [publication_id],
            [pubtitle_name],
            [pubtitle_startdt],
            [pubtitle_enddt]
        FROM [tbl_Publication]
        WHERE
            [pubstatus_id] IN ('2', '5')
            AND CAST(? AS DATE) BETWEEN
                CAST([pubtitle_startdt] AS DATE)
                AND
                CAST([pubtitle_enddt] AS DATE)
    ", "Select", array($currentdt));

        echo '
    <div class="col-12 fade-in">
        <div class="card job-card">
            <div class="card-body p-0 table-responsive">
                <table class="table mb-0">
                    <thead style="background:#28a745; color:#fff;">
                        <tr class="">
                            <th>Position Title</th>
                            <th>Office Assignment</th>
                            <!-- <th>Status</th>
                            <th>Salary Grade</th> -->
                            <th>Specialization</th>
                            <th>Publication Deadline</th>
                            <th>';
        if ($rid == 1 || $rid == 5) {
            echo '';
        } else {
            echo 'Action';
        }

        echo '</th>
                        </tr>
                    </thead>
                    <tbody>
    ';

        if (!empty($fetchpublication)) {

            foreach ($fetchpublication as $publication) {

                $publication_id = $publication["publication_id"];
                $pubtitle_enddt = date("F j, Y", strtotime($publication["pubtitle_enddt"]));

                $fetchpositions = execsqlSRS("
                SELECT
                    p.[pubpos_id],
                    p.[position_title],
                    a.[appoint_desc],
                    c.[color_desc],
                    s.[sg_grade],
                    s.[sg_amount],
                    o.[office_desc]

                FROM [tbl_PublicationPosition] p

                LEFT JOIN [tbl_ProfExpAppoint] a
                    ON a.[appoint_id] = p.[appoint_id]

                LEFT JOIN [tbl_SalaryGrade] s
                    ON s.[sg_id] = p.[sg_id]

                LEFT JOIN [tbl_Office] o
                    ON o.[office_id] = p.[office_id]

                LEFT JOIN [tbl_Colors] c
                    ON c.[color_id] = a.[color_id]

                WHERE p.[publication_id] = ?
            ", "Select", array(intval($publication_id)));

                foreach ($fetchpositions as $job) {

                    echo '
                <tr>
                    <td>' . htmlspecialchars($job["position_title"]) . '</td>
                    <td>' . htmlspecialchars($job["office_desc"]) . '</td>
                    <!-- <td>
                        <span class="job-badge bg-' . htmlspecialchars($job["color_desc"]) . '">
                            ' . htmlspecialchars($job["appoint_desc"]) . '
                        </span>
                    </td>
                    <td>
                        ' . htmlspecialchars($job["sg_grade"]) . '
                        (₱' . number_format($job["sg_amount"], 2) . ')
                    </td> -->
                    <td></td>
                    <td>' . htmlspecialchars($pubtitle_enddt) . '</td>
                    <td>
                        <button class="btn btn-success btn-sm"
                            id="view_position_' . htmlspecialchars($job["pubpos_id"]) . '"
                            data-datavalue="' . htmlspecialchars($job["pubpos_id"]) . '"
                            data-backendurl="backend/bk_homepage.php"
                            data-backendrequest="viewpositiondetailsuser"
                            data-openmodal="#attachmentmodal"
                            data-openmodallabel="View Position - ' . htmlspecialchars($job["position_title"]) . '"
                            data-openmodalbody="#attachmentmodalcontent"
                            data-tooltip="View Position"
                        >
                            View Details
                        </button>';

                    if ($rid == 1 || $rid == 5) {

                        echo '
                        <button class="btn btn-success btn-sm"
                                type="button"
                                id="fetchapplicants_' . htmlspecialchars($job['pubpos_id']) . '"
                                data-tooltip="View Applicants"
                                data-datavalue="' . htmlspecialchars($job['pubpos_id']) . '"
                                >
                            View Applicants
                        </button>

                        <button class="btn btn-sm btn-success"
                                id="positionsummary_' . htmlspecialchars($job["pubpos_id"]) . '"
                                data-datavalue="' . htmlspecialchars($job["pubpos_id"]) . '"
                                data-openmodallabel="' . htmlspecialchars($job["position_title"]) . '"
                                data-tooltip="View Summary">
                            Analytics
                        </button>

                        ';
                    } else {
                        echo '
                        <button class="btn btn-success btn-sm"
                                type="button"
                                id="job_apply_' . htmlspecialchars($job['pubpos_id']) . '"
                                data-tooltip="Apply now"
                                data-datavalue="' . htmlspecialchars($job['pubpos_id']) . '"
                                data-backendurl="backend/bk_homepage.php"
                                data-backendrequest="applyposition"
                                data-openmodal="#attachmentmodal"
                                >
                            Apply
                        </button>
                        ';
                    }

                    echo '
                    </td>
                </tr>
                ';
                }
            }
        } else {

            echo '
        <tr>
            <td colspan="6" class="text-center text-danger font-weight-bold">
                No active job postings at the moment. Please check back later.
            </td>
        </tr>';
        }

        echo '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    ';

        break;

    case "viewpositiondetailsuser":

        $positionDetails = execsqlSRS("
            SELECT
                pp.[pubpos_id],
                pp.[publication_id],
                pp.[position_title],
                a.[appoint_desc],
                sg.[sg_grade],
                sg.[sg_step],
                sg.[sg_amount],
                o.[office_desc],
                pp.[educ_qual],
                pp.[exp],
                pp.[training],

                STUFF((
                    SELECT ', ' + pe2.eligibility_desc
                    FROM tbl_PublicationPositionEligibility ppe2
                    LEFT JOIN tbl_ProfEligibilityLibrary pe2
                        ON pe2.eligibility_id = ppe2.eligibility_id
                    WHERE ppe2.pubpos_id = pp.pubpos_id
                    AND ppe2.IsActive = 0
                    FOR XML PATH(''), TYPE).value('.', 'NVARCHAR(MAX)')
                , 1, 2, '') AS eligibility_desc,

                att.[att_filepath],
                pp.[created_at],
                c.[color_desc]

            FROM [tbl_PublicationPosition] pp

            LEFT JOIN [tbl_ProfExpAppoint] a
                ON a.[appoint_id] = pp.[appoint_id]

            LEFT JOIN [tbl_SalaryGrade] sg
                ON sg.[sg_id] = pp.[sg_id]

            LEFT JOIN [tbl_Office] o
                ON o.[office_id] = pp.[office_id]

            LEFT JOIN [tbl_Attachment] att
                ON att.[attach_id] = pp.[attach_id]

            LEFT JOIN [tbl_Colors] c
                ON c.[color_id] = a.[color_id]

            WHERE pp.[pubpos_id] = ?
        ", "Select", [intval($datavalue)]);

        $position_id = $positionDetails[0]['pubpos_id'] ?? 0;

        // Fetch competencies
        $competencies = execsqlSRS(
            "
                SELECT [competency_desc]
                FROM [tbl_PublicationCompetency]
                WHERE [pubpos_id] = ?",
            "Select",
            array(intval($position_id))
        );

        if ($positionDetails) {

            foreach ($positionDetails as $pd) {

                echo '<div class="container-fluid p-3">';

                // ------------------ Position Details Card ------------------
                echo '<div class="card mb-3 shadow-sm">';
                echo '<div class="card-header bg-success text-white">Position Details</div>';
                echo '<div class="card-body">';

                echo '<p class="h3">' . htmlspecialchars($pd['position_title']) . '</p>';
                echo '<p class="mb-1"><strong>Salary Grade:</strong> SG ' . htmlspecialchars($pd['sg_grade']) . ' (₱' . number_format($pd['sg_amount'], 2) . ')</p>';
                echo '<p class="mb-1"><strong>Status of Employment:</strong> <span class="job-badge bg-' . htmlspecialchars($pd['color_desc']) . '">' . htmlspecialchars($pd['appoint_desc']) . '</span></p>';
                echo '<p class="mb-1"><strong>Office Assignment:</strong> ' . htmlspecialchars($pd['office_desc']) . '</p>';
                echo '<p class="mb-1"><strong>Educational Background Required:</strong> ' . htmlspecialchars($pd['educ_qual']) . '</p>';
                echo '<p class="mb-1"><strong>Work Experience Required:</strong> ' . htmlspecialchars($pd['exp']) . '</p>';
                echo '<p class="mb-1"><strong>Training Required:</strong> ' . htmlspecialchars($pd['training']) . '</p>';
                echo '<p class="mb-1"><strong>Eligibility Required:</strong> ' . htmlspecialchars($pd['eligibility_desc']) . '</p>';

                echo '</div>';
                echo '</div>';

                // ------------------ Competencies Card ------------------
                if ($competencies) {
                    echo '<div class="card mb-3 shadow-sm">';
                    echo '<div class="card-header bg-success text-white">Competencies</div>';
                    echo '<ul class="list-group list-group-flush">';
                    foreach ($competencies as $comp) {
                        echo '<li class="list-group-item">' . htmlspecialchars($comp['competency_desc']) . '</li>';
                    }
                    echo '</ul>';
                    echo '</div>'; // card
                }

                // ------------------ Attachment Card ------------------
                if (!empty($pd['att_filepath'])) {

                    echo '<div class="card shadow-sm mb-3">';
                    echo '<div class="card-header bg-success text-white">Attachment</div>';
                    echo '<div class="card-body">';

                    $file1 = $pd['att_filepath'];
                    $file2 = preg_replace('/(\.\w+)$/', '_2$1', $file1);

                    $files = [$file1, $file2];

                    echo '<div class="row">'; // START ROW

                    foreach ($files as $path) {

                        if (!file_exists($path)) {
                            continue;
                        }

                        $fileExt = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        $filename = htmlspecialchars(basename($path));
                        $fileUrl = str_replace('../', '/JobPortal/', $path);

                        echo '<div class="col-md-6 mb-3">'; // EACH ITEM IN COLUMN

                        if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                            echo "<img src='$fileUrl' class='img-fluid rounded w-100' alt='$filename'>";
                        } elseif (in_array($fileExt, ['pdf', 'eps'])) {
                            echo "<iframe src='$fileUrl' width='100%' height='400px' style='border:none;'></iframe>";
                        } else {
                            echo "<a href='$fileUrl' target='_blank' class='btn btn-sm btn-outline-primary w-100'>";
                            echo "<i class='fas fa-download me-1'></i> View/Download Attachment";
                            echo "</a>";
                        }

                        echo '</div>'; // END COLUMN
                    }

                    echo '</div>'; // END ROW

                    echo '</div>';
                    echo '</div>';
                } else {
                    echo "<div class='alert alert-info'>No attachment found for this position.</div>";
                }
            }
        } else {
            echo '<div class="alert alert-warning">No details found for this position.</div>';
        }

        break;

    case "applyposition":

        //Validations
        $user = execsqlSRS("
            SELECT TOP 1 *
            FROM [db_JobPortal].[dbo].[tbl_ProfUserDetails]
            WHERE [UserID] = ?
        ", "Select", array($userid));

        if (!$user || !isset($user[0])) {
            echo json_encode([
                "status" => "error",
                "valid" => false,
                "message" => "Your profile has missing details. Please complete your profile first."
            ]);
            exit;
        }

        $u = $user[0];

        $missinguser = [];

        if (empty($u['MiddleName'])) $missinguser[] = "Middle Name";
        if (empty($u['ExtName'])) $missinguser[] = "Extension Name";
        if (empty($u['DateOfBirth'])) $missinguser[] = "Date of Birth";
        if (empty($u['CivilStatus'])) $missinguser[] = "Civil Status";
        if (empty($u['Sex'])) $missinguser[] = "Sex";
        if (empty($u['Nationality'])) $missinguser[] = "Nationality";
        if (empty($u['MobileNumber'])) $missinguser[] = "Mobile Number";
        if (empty($u['Age'])) $missinguser[] = "Age";
        if (empty($u['Religion'])) $missinguser[] = "Religion";

        if (empty($u['HmCity'])) $missinguser[] = "Home City";
        if (empty($u['HmProvince'])) $missinguser[] = "Home Province";
        if (empty($u['HmZip'])) $missinguser[] = "Home ZIP";

        if (empty($u['CurCity'])) $missinguser[] = "Current City";
        if (empty($u['CurProvince'])) $missinguser[] = "Current Province";
        if (empty($u['CurZip'])) $missinguser[] = "Current ZIP";

        if (empty($u['pds_file'])) $missinguser[] = "Personal Data Sheet";
        if (empty($u['workexp_file'])) $missinguser[] = "Work Experience Sheet";
        if (empty($u['profile_pic'])) $missinguser[] = "Profile Picture";
        //        if (empty($u['perf_file'])) $missinguser[] = "Performance Rating File";

        if (count($missinguser) > 0) {

            echo json_encode([
                "status" => "missing",
                "message" => "Your profile is incomplete.",
                "missing" => $missinguser
            ]);

            exit;
        }

        $education = execsqlSRS("
            SELECT TOP 1 [UserID]
            FROM [db_JobPortal].[dbo].[tbl_ProfEducation]
            WHERE [UserID] = ?
        ", "Select", array($userid));

        if (!$education || !isset($education[0])) {
            echo json_encode([
                "status" => "error",
                "valid" => false,
                "message" => "You have no Educational Background details. Please complete your profile first."
            ]);
            exit;
        }

        $skills = execsqlSRS("
            SELECT TOP 1 [UserID]
            FROM [db_JobPortal].[dbo].[tbl_ProfSkills]
            WHERE [UserID] = ?
        ", "Select", array($userid));

        if (!$skills || !isset($skills[0])) {
            echo json_encode([
                "status" => "error",
                "valid" => false,
                "message" => "You have no Skills entered. Please complete your profile first."
            ]);
            exit;
        }

        $comps = execsqlSRS("
            SELECT TOP 1 [UserID]
            FROM [db_JobPortal].[dbo].[tbl_ProfComp]
            WHERE [UserID] = ?
        ", "Select", array($userid));

        if (!$comps || !isset($comps[0])) {
            echo json_encode([
                "status" => "error",
                "valid" => false,
                "message" => "You have no Competencies entered. Please complete your profile first."
            ]);
            exit;
        }

        $answers = execsqlSRS("
            SELECT TOP 1 [UserID]
            FROM [db_JobPortal].[dbo].[tbl_ProfAnswers]
            WHERE [UserID] = ?
        ", "Select", array($userid));

        if (!$answers || !isset($answers[0])) {
            echo json_encode([
                "status" => "error",
                "valid" => false,
                "message" => "You have not answered the Profile Questions. Please complete your profile first."
            ]);
            exit;
        }

        // Validation if already applied to the same position
        $existingApplication = execsqlSRS(
            "
            SELECT TOP 1 [snap_id]
            FROM [tbl_Snapshot]
            WHERE [UserID] = ? AND [pubpos_id] = ?",
            "Select",
            array($userid, $datavalue)
        );

        if ($existingApplication && isset($existingApplication[0])) {
            echo json_encode([
                "status" => "error",
                "valid" => false,
                "message" => "You have already applied for this position."
            ]);
            exit;
        }

        //Apply the user to the position
        $insertsnap = execsqlSRS("
            INSERT INTO [tbl_Snapshot] (
                [pubpos_id]
                ,[UserID]
                ,[AppliedDate]
                ,[snap_status]
                ,[IsActive]
                ,[UpdatedAt]
            ) VALUES (?, ?, ?, '1', '0', ?)
        ", "Insert", array(
            intval($datavalue),
            intval($userid),
            $currentdt,
            $currentdt
        ));

        $getsnapid = execsqlSRS("
            SELECT TOP 1 [snap_id]
            FROM [tbl_Snapshot]
            WHERE [UserID] = ? AND [pubpos_id] = ?
            ORDER BY [AppliedDate] DESC
        ", "Select", array($userid, $datavalue));

        $snap_id = $getsnapid[0]['snap_id'] ?? 0;

        //Snapshot of user details at time of application
        $selectinitialuserdetails = execsqlSRS("
            SELECT  [EmailAddress]
                    ,[LastName]
                    ,[FirstName]
            FROM [Sys_UserAccount]
            WHERE [UserID] = ?", "Select", array($userid));

        $selectprofiledetails = execsqlSRS("
            SELECT  [UserDetailsID]
                    ,[MiddleName]
                    ,[ExtName]
                    ,[DateOfBirth]
                    ,[CivilStatus]
                    ,[Sex]
                    ,[Nationality]
                    ,[MobileNumber]
                    ,[TelephoneNumber]
                    ,[Age]
                    ,[Religion]
                    ,[HmHouse]
                    ,[HmStreet]
                    ,[HmBarangay]
                    ,[HmCity]
                    ,[HmProvince]
                    ,[HmZip]
                    ,[CurHouse]
                    ,[CurStreet]
                    ,[CurBarangay]
                    ,[CurCity]
                    ,[CurProvince]
                    ,[CurZip]
                    ,[pds_file]
                    ,[workexp_file]
                    ,[perf_file]
                    ,[perf_rating]
                    ,[adj_rating]
                    ,[profile_pic]
            FROM [tbl_ProfUserDetails]
            WHERE [UserID] = ?", "Select", array($userid));

        $userdetails = array_merge($selectinitialuserdetails[0] ?? [], $selectprofiledetails[0] ?? []);

        $insertuserdetailsnap = execsqlSRS("
            INSERT INTO [tbl_SnapshotUser] (
                [snap_id],
                [UserID],
                [Email],
                [LastName],
                [FirstName],
                [MiddleName],
                [ExtName],
                [DateOfBirth],
                [CivilStatus],
                [Sex],
                [Nationality],
                [MobileNumber],
                [TelephoneNumber],
                [Age],
                [Religion],
                [HmHouse],
                [HmStreet],
                [HmBarangay],
                [HmCity],
                [HmProvince],
                [HmZip],
                [CurHouse],
                [CurStreet],
                [CurBarangay],
                [CurCity],
                [CurProvince],
                [CurZip],
                [IsActive],
                [perf_rating],
                [adj_rating]
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '0', ?, ?)
        ", "Insert", array(
            intval($snap_id),
            intval($userid),
            $userdetails['EmailAddress'] ?? '',
            $userdetails['LastName'] ?? '',
            $userdetails['FirstName'] ?? '',
            $userdetails['MiddleName'] ?? '',
            $userdetails['ExtName'] ?? '',
            $userdetails['DateOfBirth'] ?? '',
            $userdetails['CivilStatus'] ?? '',
            $userdetails['Sex'] ?? '',
            $userdetails['Nationality'] ?? '',
            $userdetails['MobileNumber'] ?? '',
            $userdetails['TelephoneNumber'] ?? '',
            $userdetails['Age'] ?? '',
            $userdetails['Religion'] ?? '',
            $userdetails['HmHouse'] ?? '',
            $userdetails['HmStreet'] ?? '',
            $userdetails['HmBarangay'] ?? '',
            $userdetails['HmCity'] ?? '',
            $userdetails['HmProvince'] ?? '',
            $userdetails['HmZip'] ?? '',
            $userdetails['CurHouse'] ?? '',
            $userdetails['CurStreet'] ?? '',
            $userdetails['CurBarangay'] ?? '',
            $userdetails['CurCity'] ?? '',
            $userdetails['CurProvince'] ?? '',
            $userdetails['CurZip'] ?? '',
            $userdetails['perf_rating'] ?? '',
            $userdetails['adj_rating'] ?? ''
        ));

        $selectuserattachments = execsqlSRS("
            SELECT attach_id, att_filename, att_filepath, att_dt
            FROM [tbl_Attachment]
            WHERE [attach_id] = ? OR [attach_id] = ? OR [attach_id] = ? OR [attach_id] = ?
        ", "Select", array(
            $selectprofiledetails[0]['pds_file'] ?? '',
            $selectprofiledetails[0]['workexp_file'] ?? '',
            $selectprofiledetails[0]['perf_file'] ?? '',
            $selectprofiledetails[0]['profile_pic'] ?? ''
        ));

        foreach ($selectuserattachments as $attuser) {

            execsqlSRS("
                    INSERT INTO [tbl_SnapshotAttachment] (
                        [snap_id],
                        [entity_type],
                        [entity_id],
                        [attach_id],
                        [file_name],
                        [file_path],
                        [created_at],
                        [IsActive]
                    ) VALUES (?, 'snapuser_id', ?, ?, ?, ?, ?, 0)
                ", "Insert", array(
                intval($snap_id),
                $selectprofiledetails[0]['UserDetailsID'] ?? 0,
                $attuser['attach_id'] ?? '',
                $attuser['att_filename'] ?? '',
                $attuser['att_filepath'] ?? '',
                $attuser['att_dt'] ?? ''
            ));
        }

        //Snapshot of user education at time of application
        $selectusereducation = execsqlSRS("
            SELECT  edu.[education_id]
                    ,edu.[degree_name]
                    ,edu.[major_name]
                    ,edu.[school_name]
                    ,edu.[school_address]
                    ,edu.[start_date]
                    ,edu.[end_date]
                    ,level.[edu_desc]
            FROM [tbl_ProfEducation] edu

            LEFT JOIN [tbl_ProfEducationLevels] level
            ON level.[edu_id] = edu.[edu_id]

            WHERE edu.[UserID] = ?", "Select", array($userid));

        foreach ($selectusereducation as $edu) {

            execsqlSRS("
                    INSERT INTO [tbl_SnapshotEducation] (
                        [snap_id],
                        [UserID],
                        [degree_name],
                        [major_name],
                        [school_name],
                        [school_address],
                        [start_date],
                        [end_date],
                        [educ_level],
                        [IsActive]
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
                ", "Insert", array(
                intval($snap_id),
                intval($userid),
                $edu['degree_name'] ?? '',
                $edu['major_name'] ?? '',
                $edu['school_name'] ?? '',
                $edu['school_address'] ?? '',
                $edu['start_date'] ?? '',
                $edu['end_date'] ?? '',
                $edu['edu_desc'] ?? ''
            ));

            $snapResult = execsqlSRS("
                    SELECT TOP 1 [snapeduc_id]
                    FROM [tbl_SnapshotEducation]
                    WHERE [snap_id] = ? AND [UserID] = ?
                    ORDER BY [snapeduc_id] DESC
                ", "Select", array(
                intval($snap_id),
                intval($userid)
            ));

            $snapeduc_id = $snapResult[0]['snapeduc_id'] ?? 0;

            $educationattachments = execsqlSRS("
                    SELECT [attach_id]
                    FROM [tbl_ProfEducationAwards]
                    WHERE [education_id] = ?
                ", "Select", array($edu['education_id']));

            foreach ($educationattachments as $eduatt) {

                $attid = $eduatt['attach_id'] ?? 0;

                if ($attid) {

                    $attdetails = execsqlSRS("
                            SELECT [att_filename], [att_filepath], [att_dt]
                            FROM [tbl_Attachment]
                            WHERE [attach_id] = ?
                        ", "Select", array($attid));

                    if (isset($attdetails[0])) {
                        $ad = $attdetails[0];

                        execsqlSRS("
                                INSERT INTO [tbl_SnapshotAttachment] (
                                    [snap_id],
                                    [entity_type],
                                    [entity_id],
                                    [attach_id],
                                    [file_name],
                                    [file_path],
                                    [created_at],
                                    [IsActive]
                                ) VALUES (?, 'snapeduc_id', ?, ?, ?, ?, ?, 0)
                            ", "Insert", array(
                            intval($snap_id),
                            intval($snapeduc_id),
                            intval($attid),
                            $ad['att_filename'] ?? '',
                            $ad['att_filepath'] ?? '',
                            $ad['att_dt'] ?? ''
                        ));
                    }
                }
            }
        }

        // Snapshot of user eligibility at time of application
        $selecteligibility = execsqlSRS("
                SELECT  eli.[eli_id]
                        ,lib.[eligibility_desc]
                        ,eli.[rating]
                        ,eli.[exam_date]
                        ,eli.[exam_place]
                        ,eli.[license_number]
                        ,eli.[license_validity]
                        ,eli.[type]
                        ,eli.[attach_id]
                FROM [tbl_ProfEligibility] eli

                LEFT JOIN [tbl_ProfEligibilityLibrary] lib
                ON lib.[eligibility_id] = eli.[eligibility_id]

                WHERE eli.[UserID] = ?", "Select", array(intval($userid)));

        foreach ($selecteligibility as $eli) {

            $eligid = $eli['eli_id'] ?? 0;

            $eligibility = !empty($eli['eligibility_desc'])
                ? $eli['eligibility_desc']
                : ($eli['type'] ?? '');

            execsqlSRS("
                    INSERT INTO [tbl_SnapshotEligibility] (
                        [snap_id],
                        [UserID],
                        [elig_type],
                        [rating],
                        [exam_date],
                        [exam_place],
                        [license_number],
                        [license_validity],
                        [IsActive]
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)
                ", "Insert", array(
                intval($snap_id),
                intval($userid),
                $eligibility,
                $eli['rating'] ?? '',
                $eli['exam_date'] ?? '',
                $eli['exam_place'] ?? '',
                $eli['license_number'] ?? '',
                $eli['license_validity'] ?? ''
            ));

            $geteligsnapid = execsqlSRS("
                    SELECT TOP 1 [snapelig_id]
                    FROM [tbl_SnapshotEligibility]
                    WHERE [snap_id] = ? AND [UserID] = ?
                    ORDER BY [snapelig_id] DESC
                ", "Select", array(
                intval($snap_id),
                intval($userid)
            ));

            $snapelig_id = $geteligsnapid[0]['snapelig_id'] ?? 0;

            $attid = $eli['attach_id'] ?? 0;

            if ($attid) {

                $attdetails = execsqlSRS("
                        SELECT [att_filename], [att_filepath], [att_dt]
                        FROM [tbl_Attachment]
                        WHERE [attach_id] = ?
                    ", "Select", array($attid));

                if (isset($attdetails[0])) {
                    $ad = $attdetails[0];

                    execsqlSRS("
                            INSERT INTO [tbl_SnapshotAttachment] (
                                [snap_id],
                                [entity_type],
                                [entity_id],
                                [attach_id],
                                [file_name],
                                [file_path],
                                [created_at],
                                [IsActive]
                            ) VALUES (?, 'snapelig_id', ?, ?, ?, ?, ?, 0)
                        ", "Insert", array(
                        intval($snap_id),
                        intval($snapelig_id),
                        intval($attid),
                        $ad['att_filename'] ?? '',
                        $ad['att_filepath'] ?? '',
                        $ad['att_dt'] ?? ''
                    ));
                }
            }
        }

        // Snapshot of user work experience at time of application
        $selectworkexp = execsqlSRS("
                SELECT  exp.[experience_id],
                        exp.[start_date],
                        exp.[end_date],
                        exp.[position],
                        exp.[department],
                        app.[appoint_desc],
                        exp.[appoint_custom],
                        exp.[gov_service],
                        exp.[cert_emp],
                        exp.[service_rec]
                FROM [tbl_ProfExp] exp
                LEFT JOIN [tbl_ProfExpAppoint] app
                    ON app.[appoint_id] = exp.[appoint_id]
                WHERE exp.[UserID] = ?
            ", "Select", array(intval($userid)));

        foreach ($selectworkexp as $we) {

            $gov_service_text = ($we['gov_service'] ?? 0) == 0 ? 'Yes' : 'No';

            execsqlSRS("
                    INSERT INTO [tbl_SnapshotExp] (
                        [snap_id],
                        [UserID],
                        [start_date],
                        [end_date],
                        [position],
                        [department],
                        [appoint_type],
                        [gov_service],
                        [IsActive]
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)
                ", "Insert", array(
                intval($snap_id),
                intval($userid),
                $we['start_date'] ?? '',
                $we['end_date'] ?? '',
                $we['position'] ?? '',
                $we['department'] ?? '',
                $we['appoint_desc'] ?? $we['appoint_custom'] ?? '',
                $gov_service_text
            ));

            $getweid = execsqlSRS("
                    SELECT TOP 1 [snapexp_id]
                    FROM [tbl_SnapshotExp]
                    WHERE [snap_id] = ? AND [UserID] = ?
                    ORDER BY [snapexp_id] DESC
                ", "Select", array(
                intval($snap_id),
                intval($userid)
            ));

            $snapexp_id = $getweid[0]['snapexp_id'] ?? 0;

            $attachment_ids = array_filter([
                $we['cert_emp'] ?? '',
                $we['service_rec'] ?? ''
            ]);

            foreach ($attachment_ids as $attid) {

                $attdetails = execsqlSRS("
                        SELECT [att_filename], [att_filepath], [att_dt]
                        FROM [tbl_Attachment]
                        WHERE [attach_id] = ?
                    ", "Select", array($attid));

                if (!empty($attdetails[0])) {
                    $ad = $attdetails[0];

                    execsqlSRS("
                            INSERT INTO [tbl_SnapshotAttachment] (
                                [snap_id],
                                [entity_type],
                                [entity_id],
                                [attach_id],
                                [file_name],
                                [file_path],
                                [created_at],
                                [IsActive]
                            ) VALUES (?, 'snapexp_id', ?, ?, ?, ?, ?, 0)
                        ", "Insert", array(
                        intval($snap_id),
                        intval($snapexp_id),
                        intval($attid),
                        $ad['att_filename'] ?? '',
                        $ad['att_filepath'] ?? '',
                        $ad['att_dt'] ?? ''
                    ));
                }
            }
        }

        //Snapshot of voluntary work at time of application
        $selectvolwork = execsqlSRS(
            "
            SELECT  [volwork_id]
                    ,[org_name]
                    ,[org_address]
                    ,[start_date]
                    ,[end_date]
                    ,[num_hours]
                    ,[position]
                    ,[attach_id]
            FROM [tbl_ProfVolWork]
            WHERE [UserID] = ?
        ",
            "Select",
            array(intval($userid))
        );

        foreach ($selectvolwork as $vw) {

            execsqlSRS("
                INSERT INTO [tbl_SnapshotVolWork] (
                    [snap_id],
                    [UserID],
                    [org_name],
                    [org_address],
                    [start_date],
                    [end_date],
                    [num_hours],
                    [position],
                    [IsActive]
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)
            ", "Insert", array(
                intval($snap_id),
                intval($userid),
                $vw['org_name'] ?? '',
                $vw['org_address'] ?? '',
                $vw['start_date'] ?? '',
                $vw['end_date'] ?? '',
                $vw['num_hours'] ?? '',
                $vw['position'] ?? ''
            ));

            $getvolworkid = execsqlSRS("
                SELECT TOP 1 [snapvolwork_id]
                FROM [tbl_SnapshotVolWork]
                WHERE [snap_id] = ? AND [UserID] = ?
                ORDER BY [snapvolwork_id] DESC
            ", "Select", array(
                intval($snap_id),
                intval($userid)
            ));

            $snapvolwork_id = $getvolworkid[0]['snapvolwork_id'] ?? 0;

            $attid = $vw['attach_id'] ?? 0;

            if ($attid) {

                $attdetails = execsqlSRS("
                    SELECT [att_filename], [att_filepath], [att_dt]
                    FROM [tbl_Attachment]
                    WHERE [attach_id] = ?
                ", "Select", array($attid));

                if (!empty($attdetails[0])) {

                    $ad = $attdetails[0];

                    execsqlSRS("
                        INSERT INTO [tbl_SnapshotAttachment] (
                            [snap_id],
                            [entity_type],
                            [entity_id],
                            [attach_id],
                            [file_name],
                            [file_path],
                            [created_at],
                            [IsActive]
                        ) VALUES (?, 'snapvolwork_id', ?, ?, ?, ?, ?, 0)
                    ", "Insert", array(
                        intval($snap_id),
                        intval($snapvolwork_id),
                        intval($attid),
                        $ad['att_filename'] ?? '',
                        $ad['att_filepath'] ?? '',
                        $ad['att_dt'] ?? ''
                    ));
                }
            }
        }

        //Snapshot of learning and development at time of application
        $selectld = execsqlSRS("
            SELECT  ld.[ld_id]
                    ,ld.[ld_title]
                    ,ld.[start_date]
                    ,ld.[end_date]
                    ,ld.[num_hours]
                    ,type.[ldtype_desc]
                    ,ld.[ldtype_custom]
                    ,ld.[sponsor]
                    ,ld.[cert_attendance]
                    ,ld.[skills_certificate]
            FROM [tbl_ProfLD] ld

            LEFT JOIN [tbl_ProfLDType] type
                ON type.[ldtype_id] = ld.[ldtype_id]

            WHERE ld.[UserID] = ?
        ", "Select", array(intval($userid)));

        foreach ($selectld as $ld) {

            $ldtype = !empty($ld['ldtype_desc'])
                ? $ld['ldtype_desc']
                : ($ld['ldtype_custom'] ?? '');

            execsqlSRS("
                INSERT INTO [tbl_SnapshotLD] (
                    [snap_id],
                    [UserID],
                    [ld_title],
                    [start_date],
                    [end_date],
                    [num_hours],
                    [ldtype],
                    [sponsor],
                    [IsActive]
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)
            ", "Insert", array(
                intval($snap_id),
                intval($userid),
                $ld['ld_title'] ?? '',
                $ld['start_date'] ?? '',
                $ld['end_date'] ?? '',
                $ld['num_hours'] ?? '',
                $ldtype,
                $ld['sponsor'] ?? ''
            ));

            $getldid = execsqlSRS("
                SELECT TOP 1 [snapld_id]
                FROM [tbl_SnapshotLD]
                WHERE [snap_id] = ? AND [UserID] = ?
                ORDER BY [snapld_id] DESC
            ", "Select", array(
                intval($snap_id),
                intval($userid)
            ));

            $snapld_id = $getldid[0]['snapld_id'] ?? 0;

            $attachment_ids = array_filter([
                $ld['cert_attendance'] ?? '',
                $ld['skills_certificate'] ?? ''
            ]);

            foreach ($attachment_ids as $attid) {

                $attdetails = execsqlSRS("
                    SELECT [att_filename], [att_filepath], [att_dt]
                    FROM [tbl_Attachment]
                    WHERE [attach_id] = ?
                ", "Select", array($attid));

                if (!empty($attdetails[0])) {

                    $ad = $attdetails[0];

                    execsqlSRS("
                        INSERT INTO [tbl_SnapshotAttachment] (
                            [snap_id],
                            [entity_type],
                            [entity_id],
                            [attach_id],
                            [file_name],
                            [file_path],
                            [created_at],
                            [IsActive]
                        ) VALUES (?, 'snapld_id', ?, ?, ?, ?, ?, 0)
                    ", "Insert", array(
                        intval($snap_id),
                        intval($snapld_id),
                        intval($attid),
                        $ad['att_filename'] ?? '',
                        $ad['att_filepath'] ?? '',
                        $ad['att_dt'] ?? ''
                    ));
                }
            }
        }

        // Snapshot of user skills at time of application
        $selectskills = execsqlSRS("
            SELECT  [skills_id]
                    ,[skills_desc]
                    ,[UserID]
                    ,[IsActive]
            FROM [tbl_ProfSkills]
            WHERE [UserID] = ?", "Select", array(intval($userid)));

        foreach ($selectskills as $comp) {
            execsqlSRS("
                    INSERT INTO [tbl_SnapshotSkills] (
                        [snap_id]
                        ,[UserID]
                        ,[skills_desc]
                        ,[IsActive]
                    ) VALUES (?, ?, ?, 0)
                ", "Insert", array(
                intval($snap_id),
                intval($userid),
                $comp['skills_desc'] ?? ''
            ));
        }

        // Snapshot of user competencies at time of application
        $selectcomp = execsqlSRS("
            SELECT  [comp_id]
                    ,[comp_desc]
                    ,[UserID]
                    ,[IsActive]
            FROM [tbl_ProfComp]
            WHERE [UserID] = ?", "Select", array(intval($userid)));

        foreach ($selectcomp as $compe) {
            execsqlSRS("
                    INSERT INTO [tbl_SnapshotComp] (
                        [snap_id]
                        ,[UserID]
                        ,[comp_desc]
                        ,[IsActive]
                    ) VALUES (?, ?, ?, 0)
                ", "Insert", array(
                intval($snap_id),
                intval($userid),
                $compe['comp_desc'] ?? ''
            ));
        }

        // Snapshot of user membership in organizations at time of application
        $selectorgs = execsqlSRS("
            SELECT  [orgassoc_id]
                    ,[orgassoc_desc]
                    ,[attach_id]
                    ,[IsActive]
            FROM [tbl_ProfOrgAssoc]
            WHERE [UserID] = ?
        ", "Select", array(intval($userid)));

        foreach ($selectorgs as $org) {

            execsqlSRS("
                INSERT INTO [tbl_SnapshotOrgAssoc] (
                    [snap_id],
                    [UserID],
                    [orgassoc_desc],
                    [IsActive]
                ) VALUES (?, ?, ?, 0)
            ", "Insert", array(
                intval($snap_id),
                intval($userid),
                $org['orgassoc_desc'] ?? ''
            ));

            $getorgid = execsqlSRS("
                SELECT TOP 1 [snaporgassoc_id]
                FROM [tbl_SnapshotOrgAssoc]
                WHERE [snap_id] = ? AND [UserID] = ?
                ORDER BY [snaporgassoc_id] DESC
            ", "Select", array(
                intval($snap_id),
                intval($userid)
            ));

            $snaporgassoc_id = $getorgid[0]['snaporgassoc_id'] ?? 0;

            $attid = $org['attach_id'] ?? 0;

            if ($attid) {

                $attdetails = execsqlSRS("
                    SELECT [att_filename], [att_filepath], [att_dt]
                    FROM [tbl_Attachment]
                    WHERE [attach_id] = ?
                ", "Select", array($attid));

                if (!empty($attdetails[0])) {

                    $ad = $attdetails[0];

                    execsqlSRS("
                        INSERT INTO [tbl_SnapshotAttachment] (
                            [snap_id],
                            [entity_type],
                            [entity_id],
                            [attach_id],
                            [file_name],
                            [file_path],
                            [created_at],
                            [IsActive]
                        ) VALUES (?, 'snaporgassoc_id', ?, ?, ?, ?, ?, 0)
                    ", "Insert", array(
                        intval($snap_id),
                        intval($snaporgassoc_id),
                        intval($attid),
                        $ad['att_filename'] ?? '',
                        $ad['att_filepath'] ?? '',
                        $ad['att_dt'] ?? ''
                    ));
                }
            }
        }

        // Snapshot of user non-academic distinctions/recognitions at time of application
        $selectdistinctions = execsqlSRS("
            SELECT  [nonacad_id]
                    ,[nonacad_desc]
                    ,[attach_id]
                    ,[IsActive]
            FROM [tbl_ProfNonAcad]
            WHERE [UserID] = ?
        ", "Select", array(intval($userid)));

        foreach ($selectdistinctions as $dist) {

            execsqlSRS("
                INSERT INTO [tbl_SnapshotNonAcad] (
                    [snap_id],
                    [UserID],
                    [nonacad_desc],
                    [IsActive]
                ) VALUES (?, ?, ?, 0)
            ", "Insert", array(
                intval($snap_id),
                intval($userid),
                $dist['nonacad_desc'] ?? ''
            ));

            $getnonacadid = execsqlSRS("
                SELECT TOP 1 [snapnonacad_id]
                FROM [tbl_SnapshotNonAcad]
                WHERE [snap_id] = ? AND [UserID] = ?
                ORDER BY [snapnonacad_id] DESC
            ", "Select", array(
                intval($snap_id),
                intval($userid)
            ));

            $snapnonacad_id = $getnonacadid[0]['snapnonacad_id'] ?? 0;

            $attid = $dist['attach_id'] ?? 0;

            if ($attid) {

                $attdetails = execsqlSRS("
                    SELECT [att_filename], [att_filepath], [att_dt]
                    FROM [tbl_Attachment]
                    WHERE [attach_id] = ?
                ", "Select", array($attid));

                if (!empty($attdetails[0])) {

                    $ad = $attdetails[0];

                    execsqlSRS("
                        INSERT INTO [tbl_SnapshotAttachment] (
                            [snap_id],
                            [entity_type],
                            [entity_id],
                            [attach_id],
                            [file_name],
                            [file_path],
                            [created_at],
                            [IsActive]
                        ) VALUES (?, 'snapnonacad_id', ?, ?, ?, ?, ?, 0)
                    ", "Insert", array(
                        intval($snap_id),
                        intval($snapnonacad_id),
                        intval($attid),
                        $ad['att_filename'] ?? '',
                        $ad['att_filepath'] ?? '',
                        $ad['att_dt'] ?? ''
                    ));
                }
            }
        }

        // Snapshot of user answers at time of application
        $selectanswers = execsqlSRS("
            SELECT  [answer_id]
                    ,[question_code]
                    ,[answer]
                    ,[answer_details]
            FROM [tbl_ProfAnswers]
            WHERE [UserID] = ?", "Select", array(intval($userid)));

        foreach ($selectanswers as $ans) {
            execsqlSRS("
                    INSERT INTO [tbl_SnapshotAnswers] (
                        [snap_id]
                        ,[UserID]
                        ,[question_code]
                        ,[answer]
                        ,[answer_details]
                        ,[IsActive]
                    ) VALUES (?, ?, ?, ?, ?, 0)
                ", "Insert", array(
                intval($snap_id),
                intval($userid),
                $ans['question_code'] ?? '',
                $ans['answer'] ?? '',
                $ans['answer_details'] ?? ''
            ));
        }

        //Add log into the history
        $addsnaphistory = execsqlSRS("
            INSERT INTO [tbl_SnapshotHistory] (
                [snap_id]
                ,[snap_status]
                ,[changed_at]
                ,[changed_by]
                ,[remarks]
                ,[IsActive]
            ) VALUES (?, '1', ?, ?, 'Applied for Position', '0')
            ", "Insert", array(
            intval($snap_id),
            $currentdt,
            intval($userid)
        ));

        //Final success
        echo json_encode([
            "status" => "success",
            "message" => "Your application has been submitted."
        ]);

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
            <th style='position: sticky; top: 40px; z-index: 10;' class='text-center'>Competency/ies</th>
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

            $checkfordecision = execsqlSRS("
                    SELECT  deci.[opdecision_id]
                            ,deci.[opdecisionlib_id]
                            ,decilib.[opdecisionlib_desc]
                            ,deci.[recomm_at]
                    FROM [tbl_SnapshotOPDecision] deci

                    LEFT JOIN [tbl_SnapshotOPDecisionLibrary] decilib
                    ON decilib.[opdecisionlib_id] = deci.[opdecisionlib_id]

                    WHERE deci.[IsActive] = 0
                        AND deci.[snap_id] = ?
                        AND deci.[UserID] = ?
                ", "Select", array(
                intval($snap_id),
                intval($user_id)
            ));

            if ($checkfordecision) {
                $locker = "disabled";
                $tooltip = "Recommendation already provided";
                $color = "danger";
            } else {
                $locker = "";
                $tooltip = "Submit";
                $color = "success";
            }

            echo "<select id='opdecision_" . $snap_id . "'
                              class='form-control'
                              " . $locker . ">";

            echo "<option value='"
                . (isset($checkfordecision[0]['opdecisionlib_id'])
                    ? $checkfordecision[0]['opdecisionlib_id']
                    : '')
                . "'>"
                . (isset($checkfordecision[0]['opdecisionlib_desc'])
                    ? $checkfordecision[0]['opdecisionlib_desc']
                    : 'Select Remark')
                . "</option><hr>";

            $opdecision_list = execsqlSRS("
                    SELECT [opdecisionlib_id], [opdecisionlib_desc]
                    FROM [tbl_SnapshotOPDecisionLibrary]
                    WHERE [IsActive] = 0
                ", "Select", array());

            foreach ($opdecision_list as $item) {
                echo "<option value='" . $item['opdecisionlib_id'] . "'>
                            " . $item['opdecisionlib_desc'] . "
                        </option>";
            }

            echo "</select>

                    <div class='mt-2'>
                        <button class='btn btn-sm btn-" . $color . "'
                                type='button'
                                id='opdecisiontohr_" . $snap_id . "'
                                data-userid='" . $user_id . "'
                                data-snapid='" . $snap_id . "'
                                data-pubposid='" . $pubpos_id . "'
                                data-tooltip='" . $tooltip . "'
                                " . $locker . "
                        >
                            <span class='text-nowrap'>Submit to HRMU</span>
                        </button>
                    </div>
                ";


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

    case "submitopdecision":

        $snapid = isset($_POST["snapid"]) ? $_POST["snapid"] : "";
        $pubposid = isset($_POST["pubposid"]) ? $_POST["pubposid"] : "";
        $remarksby = isset($_POST["remarksby"]) ? $_POST["remarksby"] : "";

        if (empty($datavalue)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'You did not choose a remark.'
            ]);
            exit;
        }

        $submitdecision = execsqlSRS("
            INSERT INTO [tbl_SnapshotOPDecision] (
                [opdecisionlib_id]
                ,[snap_id]
                ,[UserID]
                ,[recomm_by]
                ,[recomm_at]
                ,[IsActive]
            )
            VALUES (
                ?, ?, ?, ?, ?, '0'
            )
            ", "Insert", array(
            intval($datavalue),
            intval($snapid),
            intval($userid),
            intval($remarksby),
            $currentdt
        ));

        $updatemainsnap = execsqlSRS("
            UPDATE [tbl_Snapshot]
            SET [snap_status] = '2',
                [UpdatedAt] = ?
            WHERE [snap_id] = ?
                AND [pubpos_id] = ?
                AND [UserID] = ?
            ", "Update", array(
            $currentdt,
            intval($snapid),
            intval($pubposid),
            intval($userid)
        ));

        $insertintologs = execsqlSRS("
            INSERT INTO [tbl_SnapshotHistory] (
                [snap_id]
                ,[snap_status]
                ,[changed_at]
                ,[changed_by]
                ,[remarks]
                ,[IsActive]
            )
            VALUES (
                ?, '2', ?, ?, 'Application has been accepted', '0'
            )
            ", "Insert", array(
            intval($snapid),
            $currentdt,
            $remarksby
        ));

        $insertintonotifs = execsqlSRS("
            INSERT INTO [tbl_Notifications] (
                [notif_title]
                ,[notif_message]
                ,[color_id]
                ,[UserID]
                ,[target_url]
                ,[IsRead]
                ,[ReadAt]
                ,[IsActive]
            )
            VALUES (
                'Accepted', 'Application (TAU-APP-000{$snapid}) has been Accepted', '2', ?, 'applicationstatus.php', '1', NULL, '0'
            )
            ", "Insert", array(
            intval($userid)
        ));

        echo json_encode([
            'status' => 'success',
            'message' => 'Application has been submitted.'
        ]);

        break;
}
