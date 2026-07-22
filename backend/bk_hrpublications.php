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

    case "viewlistofpublications":

        $rows = execsqlSRS("
            SELECT
                pub.publication_id,
                pub.pubtitle_name,
                pub.pubtitle_startdt,
                pub.pubtitle_enddt,
                pub.pubstatus_id,
                color.color_desc,

                pos.pubpos_id,
                pos.position_title,

                pubstatus.pubstatus_desc

            FROM tbl_Publication pub

            LEFT JOIN tbl_PublicationStatus pubstatus
                ON pubstatus.pubstatus_id = pub.pubstatus_id

            LEFT JOIN tbl_Colors color
                ON color.color_id = pubstatus.color_id

            LEFT JOIN tbl_PublicationPosition pos
                ON pos.publication_id = pub.publication_id

            WHERE (
                    (pub.pubstatus_id = '1'
                        OR pub.pubstatus_id = '3')
                  )

            ORDER BY pub.publication_id DESC
        ", "Search", []);

        $grouped = [];

        foreach ($rows as $row) {
            $pubId = $row['publication_id'];

            if (!isset($grouped[$pubId])) {
                $grouped[$pubId] = [
                    'publication_id' => $pubId,
                    'pubtitle_name' => $row['pubtitle_name'],
                    'pubtitle_startdt' => $row['pubtitle_startdt'],
                    'pubtitle_enddt' => $row['pubtitle_enddt'],
                    'pubstatus_id' => $row['pubstatus_id'],
                    'pubstatus_desc' => $row['pubstatus_desc'],
                    'color_desc' => $row['color_desc'],
                    'positions' => []
                ];
            }

            if (!empty($row['pubpos_id'])) {
                $grouped[$pubId]['positions'][] = [
                    'position_id' => $row['pubpos_id'],
                    'position_name' => $row['position_title']
                ];
            }
        }

        echo json_encode(array_values($grouped));
        break;

    case "addpublication":

        echo '
		<div class="p-3">
            <div class="form-group">
            <label for="field1">Publication Title</label>
            <input type="text"
                    class="form-control field-input"
                    id="field1"
                    placeholder="e.g. Publication of Vacant Positions...">
            </div>

            <div class="form-group">
            <label for="field2">Publication Start Date</label>
            <input type="date"
                    class="form-control field-input"
                    id="field2">
            </div>

            <div class="form-group">
            <label for="field3">Publication End Date</label>
            <input type="date"
                    class="form-control field-input"
                    id="field3">
            </div>

		  <div class="form-group pt-2 d-flex justify-content-center">
			<button type="submit"
					id="save_datapublication"
					data-openmodal="#addeditmodal"
					data-operator="add"
					data-backendrequest="savepublication"
					data-backendurl="backend/bk_hrpublications.php"
					class="btn btn-success">Add Publication <i class="fa-solid fa-plus"></i>
            </button>
		  </div>
		</div>';

        break;

    case "editpublication":

        $queryedit = execsqlSRS(
            "
			SELECT [publication_id]
				  ,[pubtitle_name]
				  ,[pubtitle_startdt]
				  ,[pubtitle_enddt]
			FROM [tbl_Publication]
			WHERE publication_id = :publication_id",
            "Select",
            [
                ":publication_id" => $datavalue
            ]
        );

        foreach ($queryedit as $edit) {

            echo "
				<div class='p-3'>
				<div class='form-group'>
					<label for=''>Description</label>
                    <input type='text' class='form-control field-input' id='field1' value='" . htmlspecialchars($edit["pubtitle_name"]) . "'>
				  </div>";
            echo "<div class='form-group'>
					<label for=''>Publication Start Date</label>
                    <input type='date' class='form-control field-input' id='field2' value='" . htmlspecialchars($edit["pubtitle_startdt"]) . "'>
				  </div>";

            echo "<div class='form-group'>
					<label for=''>Publication End Date</label>
                    <input type='date' class='form-control field-input' id='field3' value='" . htmlspecialchars($edit["pubtitle_enddt"]) . "'>
				  </div>";

            echo    "<div class='form-group d-flex justify-content-center pt-2'>
				<button
						type='submit'
						class='btn btn-success'
						id='save_datapublication'
						data-openmodal='#addeditmodal'
						data-operator='edit'
						data-backendrequest='savepublication'
						data-backendurl='backend/bk_hrpublications.php'
						data-tableid='#tblviewpublications'
						data-tablerequest='viewpublications'
						data-datavalue='" . htmlspecialchars($edit["publication_id"]) . "'>
						Save Changes
				</button>
              </div>
			  </div>";
        }

        break;

    case "deletepublication":

        $querydelete = execsqlSRS(
            "
            DELETE FROM [tbl_Publication]
            WHERE publication_id = :publication_id",
            "Delete",
            [
                ":publication_id" => $datavalue
            ]
        );

        echo json_encode(['status' => 'success', 'message' => 'System: Publication Deleted!']);

        break;

    case "savepublication":

        if ($operator == "edit") {

            $querysave = execsqlSRS(
                "
				UPDATE [tbl_Publication]
				SET pubtitle_name = :pubtitle_name, pubtitle_startdt = :pubtitle_startdt, pubtitle_enddt = :pubtitle_enddt
				WHERE publication_id = :datavalue",
                "Update",
                [
                    ":pubtitle_name" => $fields["field1"],
                    ":pubtitle_startdt" => $fields["field2"],
                    ":pubtitle_enddt" => $fields["field3"],
                    ":datavalue" => intval($datavalue)
                ]
            );

            echo json_encode(['status' => 'success', 'message' => 'System: Changes have been Saved.']);
        } else if ($operator == "add") {

            $querysave = execsqlSRS(
                "
					INSERT INTO [tbl_Publication] (  [pubtitle_name]
                                                    ,[pubtitle_startdt]
                                                    ,[pubtitle_enddt]
                                                    ,[pubstatus_id]
                                                    ,[UserID]
                                                    ,[created_at]
                                                    ,[IsActive]
                                                )
					VALUES (:pubtitle_name, :pubtitle_startdt, :pubtitle_enddt, '1', :UserID, :created_at, '0')",
                "Insert",
                [
                    ":pubtitle_name" => $fields["field1"],
                    ":pubtitle_startdt" => $fields["field2"],
                    ":pubtitle_enddt" => $fields["field3"],
                    ":UserID" => intval($userid),
                    ":created_at" => $currentdt,
                ]
            );

            echo json_encode(['status' => 'success', 'message' => 'System: Publication Added!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'System: Error Fetching. No Operator']);
            return;
        }

        break;

    case "addposition":

        $queryAppointments = execsqlSRS(
            "
            SELECT appoint_id, appoint_desc
            FROM tbl_ProfExpAppoint
            WHERE IsActive = 0
            ORDER BY appoint_desc",
            "Search",
            array()
        );

        $querySalaryGrades = execsqlSRS(
            "
            SELECT sg_id, sg_grade, sg_step, sg_amount
            FROM tbl_SalaryGrade
            WHERE IsActive = 0
            ORDER BY CAST(sg_grade AS INT), CAST(sg_step AS INT)",
            "Search",
            array()
        );

        $queryOffices = execsqlSRS(
            "
            SELECT office_id, office_desc, office_code, officemother_id
            FROM tbl_Office
            WHERE IsActive = 0
            ORDER BY office_desc",
            "Search",
            array()
        );

        $queryEligibility = execsqlSRS(
            "
            SELECT eligibility_id, eligibility_desc
            FROM tbl_ProfEligibilityLibrary
            WHERE IsActive = 0
            ORDER BY eligibility_desc",
            "Search",
            array()
        );

        echo '<div class="p-3">

        <div class="addposition_div">

          <!-- Position Type -->
          <div class="form-group">
            <label for="position_title">Position Type</label>
			<select id="job_type" class="form-control">
			<option value="0" selected>Faculty</option>
			<option value="1">Non - Teaching</option>
			</select>
            </div>
		  <!-- Position Title -->
          <div class="form-group">
            <label for="position_title">Position Title</label>
            <input type="text" class="form-control" id="position_title" name="position_title" placeholder="e.g. Administrative Officer I" required>
          </div>

          <!-- Appointment Dropdown -->
          <div class="form-group">
            <label for="appoint_id">Employment Status</label>
            <select class="form-control" id="appoint_id" name="appoint_id" >
              <option value="">-- Select Employment Status --</option><hr>';
        foreach ($queryAppointments as $a) {
            echo "<option value='" . $a['appoint_id'] . "'>" . htmlspecialchars($a['appoint_desc']) . "</option>";
        }
        echo '
            </select>
          </div>

          <!-- Salary Grade Dropdown -->
          <div class="form-group">
            <label for="sg_id">Salary Grade</label>
            <select class="form-control" id="sg_id" name="sg_id" >
              <option value="">-- Select Salary Grade --</option><hr>';
        foreach ($querySalaryGrades as $sg) {
            echo "<option value='" . $sg['sg_id'] . "'>" . htmlspecialchars($sg['sg_grade']) . " - " . htmlspecialchars($sg['sg_step']) . " (" . number_format($sg['sg_amount'], 2) . ")</option>";
        }
        echo '
            </select>
          </div>

          <!-- Office Dropdown -->
          <div class="form-group">
            <label for="office_id">Office Assignment</label>
            <select class="form-control" id="office_id" name="office_id" >
              <option value="">-- Select Office --</option><hr>';
        foreach ($queryOffices as $o) {
            echo "<option value='" . $o['office_id'] . "'>" . htmlspecialchars($o['office_desc']) . "</option>";
        }
        echo '
            </select>
          </div>

          <!-- Educational Qualification -->
          <div class="form-group">
            <label for="educ_qual">Educational Qualification</label>
            <textarea type="text" class="form-control" id="educ_qual" name="educ_qual" placeholder="e.g. Bachelor\'s Degree in Public Administration" required></textarea>
          </div>

          <!-- Experience -->
          <div class="form-group">
            <label for="exp">Experience</label>
            <textarea class="form-control" id="exp" name="exp" placeholder="e.g. 5 years" ></textarea>
          </div>

          <!-- Training -->
          <div class="form-group">
            <label for="training">Training</label>
            <textarea class="form-control" id="training" name="training" placeholder="e.g. 40 hours of relevant training" ></textarea>
          </div>

          <!-- Eligibility Dropdown -->
          <div class="form-group">
            <label for="eligibility_id">Eligibility</label>
            <select class="form-control" id="eligibility_id" name="eligibility_id" >
              <option value="">-- Select Eligibility --</option><hr>';
        foreach ($queryEligibility as $e) {
            echo "<option value='" . $e['eligibility_id'] . "'>" . htmlspecialchars($e['eligibility_desc']) . "</option>";
        }
        echo '
            </select>
                <ul id="eligibility_list" class="list-group mt-2"></ul>

                <input type="hidden" id="eligibility_hidden" name="eligibility_ids">
          </div>

          <!-- Competencies List -->
          <div class="form-group">
            <label for="competency_input">Competencies Required</label>
            <div class="input-group mb-2">
              <input type="text" class="form-control" id="competency_input" placeholder="e.g. Proficiency in MS Office">
              <button type="button" class="btn btn-success" id="add_competency_btn">Add</button>
            </div>
            <ul class="list-group" id="competency_list"></ul>

            <input type="hidden" id="competencies_hidden" name="competencies">
          </div>


                <div class="form-group d-flex justify-content-center pt-2">
                <button type="button"
                        class="btn btn-success mt-2"
                        id="save_dataposition"
                        data-datavalue="' . $datavalue . '"
                        >
                        Add Position <i class="fa-solid fa-plus"></i>
                </button>
                </div>
            </div>
        </div>

        <script>
        $(document).off("click", "#add_competency_btn").on("click", "#add_competency_btn", function() {
            const input = $("#competency_input");
            const list = $("#competency_list");
            const hiddenInput = $("#competencies_hidden");

            let competencies = hiddenInput.data("competencies") || [];

            const value = input.val().trim();
            if (value && !competencies.includes(value)) {
                competencies.push(value);
                hiddenInput.data("competencies", competencies);
                updateList();
                input.val("").focus();
            }

            function updateList() {
                list.html("");
                competencies.forEach((comp, index) => {
                    const li = $("<li>").addClass("list-group-item d-flex justify-content-between align-items-center").text(comp);
                    const removeBtn = $("<button>").addClass("btn btn-sm btn-danger").text("Remove").on("click", function() {
                        competencies.splice(index, 1);
                        hiddenInput.data("competencies", competencies);
                        updateList();
                    });
                    li.append(removeBtn);
                    list.append(li);
                });
                hiddenInput.val(JSON.stringify(competencies));
            }
        });

        $(document).off("change", "#eligibility_id").on("change", "#eligibility_id", function () {
            const select = $("#eligibility_id");
            const list = $("#eligibility_list");
            const hiddenInput = $("#eligibility_hidden");

            let eligibilities = hiddenInput.data("eligibilities") || [];

            const value = select.val();
            const text = $("#eligibility_id option:selected").text();

            if (value && !eligibilities.some(e => e.id === value)) {
                eligibilities.push({ id: value, text: text });

                hiddenInput.data("eligibilities", eligibilities);
                updateList();

                select.val("");
            }

            function updateList() {
                list.html("");

                eligibilities.forEach((item, index) => {
                    const li = $("<li>")
                        .addClass("list-group-item d-flex justify-content-between align-items-center")
                        .text(item.text);

                    const removeBtn = $("<button>")
                        .addClass("btn btn-sm btn-danger")
                        .text("Remove")
                        .on("click", function () {
                            eligibilities.splice(index, 1);
                            hiddenInput.data("eligibilities", eligibilities);
                            updateList();
                        });

                    li.append(removeBtn);
                    list.append(li);
                });

                hiddenInput.val(JSON.stringify(eligibilities.map(e => e.id)));
            }
        });
        </script>
        ';

        break;

    case "saveposition":

        // Collect inputs
        $job_type = $_POST['job_type'] ?? 0;
        $position_title = trim($_POST['position_title'] ?? '');
        $appoint_id     = trim($_POST['appoint_id'] ?? '');
        $sg_id          = trim($_POST['sg_id'] ?? '');
        $office_id      = trim($_POST['office_id'] ?? '');
        $educ_qual      = trim($_POST['educ_qual'] ?? '');
        $exp            = trim($_POST['exp'] ?? '');
        $training       = trim($_POST['training'] ?? '');
        $eligibilities  = $_POST['eligibilities'] ?? '[]';
        $competencies   = $_POST['competencies'] ?? '[]';
        $userid         = $_POST['userid'] ?? '';

        $competenciesArr = json_decode($competencies, true);

        if (!is_array($competenciesArr) || count($competenciesArr) == 0) {
            echo json_encode([
                "status" => "error",
                "message" => "At least one competency is required."
            ]);
            exit;
        }

        $competenciesFlat = array_filter(array_map('trim', $competenciesArr));
        $competenciesFlat = array_unique($competenciesFlat);

        if (count($competenciesFlat) == 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid competencies data."
            ]);
            exit;
        }

        $eligibilitiesArr = json_decode($eligibilities, true);

        if (!is_array($eligibilitiesArr)) {
            $eligibilitiesArr = [];
        }

        // Validations
        if (empty($userid)) {
            echo json_encode(["status" => "error", "message" => "User ID is required."]);
            exit;
        }
        if ($job_type == "") {
            echo json_encode(["status" => "error", "message" => "Position job type is required."]);
            exit;
        } if (empty($position_title)) {
            echo json_encode(["status" => "error", "message" => "Position Title is required."]);
            exit;
        }
        if (empty($appoint_id)) {
            echo json_encode(["status" => "error", "message" => "Employment Status is required."]);
            exit;
        }
        if (empty($sg_id)) {
            echo json_encode(["status" => "error", "message" => "Salary Grade is required."]);
            exit;
        }
        if (empty($office_id)) {
            echo json_encode(["status" => "error", "message" => "Office Assignment is required."]);
            exit;
        }
        if (empty($educ_qual)) {
            echo json_encode(["status" => "error", "message" => "Educational Qualification is required."]);
            exit;
        }
        if (empty($exp)) {
            echo json_encode(["status" => "error", "message" => "Experience is required."]);
            exit;
        }
        if (empty($training)) {
            echo json_encode(["status" => "error", "message" => "Training is required."]);
            exit;
        }
        /*
        if(empty($eligibility_id)){
            echo json_encode(["status"=>"error","message"=>"Eligibility is required."]);
            exit;
        }
        */

        // SAVE DATA
        $saveposition = execsqlSRS(
            "
            INSERT INTO [tbl_PublicationPosition] (
                [publication_id]
                ,[job_type]
                ,[position_title]
                ,[appoint_id]
                ,[sg_id]
                ,[office_id]
                ,[educ_qual]
                ,[exp]
                ,[training]
                ,[created_at]
                ,[UserID]
                ,[IsActive]
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
        ",
            "Insert",
            array(
                intval($datavalue),
                $job_type,
                $position_title,
                intval($appoint_id),
                intval($sg_id),
                intval($office_id),
                $educ_qual,
                $exp,
                $training,
                $currentdt,
                intval($userid)
            )
        );

        // Get inserted ID
        $res = execsqlSRS("
            SELECT TOP 1 pubpos_id
            FROM [tbl_PublicationPosition]
            WHERE UserID = ?
            ORDER BY pubpos_id DESC
        ", "Select", array(intval($userid)));

        $position_id = $res[0]['pubpos_id'] ?? 0;

        // Insert competencies
        foreach ($competenciesFlat as $comp) {
            $savecomp = execsqlSRS(
                "
                INSERT INTO [tbl_PublicationCompetency]
                ([competency_desc]
                ,[pubpos_id]
                ,[UserID]
                ,[IsActive])
                VALUES (?, ?, ?, 0)
            ",
                "Insert",
                array($comp, intval($position_id), intval($userid))
            );
        }

        // Insert eligibilities
        foreach ($eligibilitiesArr as $eli) {
            $savecomp = execsqlSRS(
                "
                INSERT INTO [tbl_PublicationPositionEligibility]
                (
                    [pubpos_id]
                    ,[eligibility_id]
                    ,[UserID]
                    ,[created_at]
                    ,[IsActive]
                )
                VALUES (?, ?, ?, ?, 0)
            ",
                "Insert",
                array(intval($position_id), intval($eli), intval($userid), $currentdt)
            );
        }

        // Image Generation using GD Library
        $dir = '../uploads/publications/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $timestamp = time();

        //First Page
        $image = imagecreatefromjpeg('../dist/img/publicationtemplate.jpg');
        $imagePath = $dir . 'generated_position_' . $position_id . '_' . $timestamp . '.jpg';

        // Colors
        $black = imagecolorallocate($image, 0, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        $yellow = imagecolorallocate($image, 255, 255, 0);

        // Fonts
        $fontBold = '../dist/fonts/ARIALBD.ttf';
        $fontRegular = '../dist/fonts/arial.ttf';

        // Image width
        $imageWidth = imagesx($image);

        function drawTextBlock($image, $text, $font, $size, $angle, $startX, $startY, $maxWidth, $lineHeight, $color, $align = 'left')
        {
            $words = explode(' ', $text);
            $lines = [];
            $currentLine = '';

            foreach ($words as $word) {
                $testLine = $currentLine === '' ? $word : $currentLine . ' ' . $word;
                $bbox = imagettfbbox($size, $angle, $font, $testLine);
                $width = $bbox[2] - $bbox[0];

                if ($width > $maxWidth) {
                    if ($currentLine !== '') {
                        $lines[] = $currentLine;
                    }
                    $currentLine = $word;
                } else {
                    $currentLine = $testLine;
                }
            }

            if ($currentLine !== '') {
                $lines[] = $currentLine;
            }

            $y = $startY;
            $imgW = imagesx($image);

            foreach ($lines as $index => $line) {
                $bbox = imagettfbbox($size, $angle, $font, $line);
                $lineWidth = $bbox[2] - $bbox[0];

                if ($align === 'center') {
                    $x = ($imgW - $lineWidth) / 2;
                } else {
                    $x = $startX;
                }

                imagettftext($image, $size, $angle, $x, $y, $color, $font, $line);
                $y += $lineHeight;
            }

            return $y;
        }

        function getTextLines($text, $font, $size, $angle, $maxWidth)
        {
            $words = explode(' ', $text);
            $lines = [];
            $currentLine = '';

            foreach ($words as $word) {
                $testLine = $currentLine === '' ? $word : $currentLine . ' ' . $word;
                $bbox = imagettfbbox($size, $angle, $font, $testLine);
                $width = $bbox[2] - $bbox[0];

                if ($width > $maxWidth) {
                    if ($currentLine !== '') {
                        $lines[] = $currentLine;
                    }
                    $currentLine = $word;
                } else {
                    $currentLine = $testLine;
                }
            }

            if ($currentLine !== '') {
                $lines[] = $currentLine;
            }

            return $lines;
        }

        $getimagedetails = execsqlSRS("
            SELECT
                a.appoint_desc,
                sg.sg_grade,
                sg.sg_step,
                sg.sg_amount,
                o.office_desc,
                pp.educ_qual,
                pp.exp,
                pp.training,
                STUFF((
                    SELECT ', ' + pe2.eligibility_desc
                    FROM tbl_PublicationPositionEligibility ppe2
                    LEFT JOIN tbl_ProfEligibilityLibrary pe2
                        ON pe2.eligibility_id = ppe2.eligibility_id
                    WHERE ppe2.pubpos_id = pp.pubpos_id
                    AND ppe2.IsActive = 0
                    FOR XML PATH(''), TYPE).value('.', 'NVARCHAR(MAX)')
                , 1, 2, '') AS eligibility_desc

            FROM tbl_PublicationPosition pp

            LEFT JOIN tbl_ProfExpAppoint a
                ON a.appoint_id = pp.appoint_id

            LEFT JOIN tbl_SalaryGrade sg
                ON sg.sg_id = pp.sg_id

            LEFT JOIN tbl_Office o
                ON o.office_id = pp.office_id

            WHERE pp.pubpos_id = ?
        ", "Select", [$position_id]);

        $getpublicationid = execsqlSRS("
            SELECT TOP 1 [pubtitle_startdt], [pubtitle_enddt]
            FROM [tbl_Publication]
            WHERE [publication_id] = ?
            ORDER BY [publication_id] DESC
        ", "Select", array(intval($datavalue)));

        $publicationdate = $getpublicationid[0]['pubtitle_startdt'] ?? date("Y-m-d");

        //Current D/T
        $currendtformat = date("F j, Y", strtotime($publicationdate));

        $fontSizeCurrendt = 25;
        $angle = 0;
        $paddingRight = 140;
        $paddingTop = 236;

        // Get text bounding box
        $bbox = imagettfbbox($fontSizeCurrendt, $angle, $fontRegular, $currendtformat);
        $textWidth = $bbox[2] - $bbox[0];
        $textHeight = $bbox[1] - $bbox[7];

        $x = imagesx($image) - $textWidth - $paddingRight;
        $y = $paddingTop + $textHeight;

        imagettftext($image, $fontSizeCurrendt, $angle, $x, $y, $white, $fontRegular, $currendtformat);

        // Position Header
        $headerY = 360;
        $fontSize = 75;
        $maxWidth = $imageWidth * 0.9; // maximum text width

        // Split text into lines that fit the width
        $lines = getTextLines($position_title, $fontBold, $fontSize, 0, $maxWidth);

        $lineHeight = $fontSize + 10;
        $y = $headerY;

        foreach ($lines as $line) {
            $bbox = imagettfbbox($fontSize, 0, $fontBold, $line);
            $lineWidth = $bbox[2] - $bbox[0];
            $x = ($imageWidth - $lineWidth) / 2; // center each line horizontally

            imagettftext($image, $fontSize, 0, $x, $y, $black, $fontBold, $line);
            $y += $lineHeight;
        }


        // Employment Details
        $valueX = 650;
        $valueMaxWidth = 600;

        $statusY = 535;
        $sgY     = 600;
        $salaryY = 660;
        $officeY = 715;

        $statusValue = $getimagedetails[0]['appoint_desc'] ?? "";
        $sgValue = $getimagedetails[0]['sg_grade'] ?? "";
        $salaryValue = isset($getimagedetails[0]['sg_amount'])
            ? '₱' . number_format($getimagedetails[0]['sg_amount'], 2)
            : '';
        $officeValue = $getimagedetails[0]['office_desc'] ?? "";

        drawTextBlock($image, $statusValue, $fontRegular, 30, 0, $valueX, $statusY, $valueMaxWidth, 40, $white);
        drawTextBlock($image, $sgValue, $fontRegular, 30, 0, $valueX, $sgY, $valueMaxWidth, 40, $white);
        drawTextBlock($image, $salaryValue, $fontRegular, 30, 0, $valueX, $salaryY, $valueMaxWidth, 40, $white);
        drawTextBlock($image, $officeValue, $fontRegular, 30, 0, $valueX, $officeY, $valueMaxWidth, 40, $white);

        // 3. Qualification details.
        $qualX = 650;
        $qualMaxWidth = 700;

        $educY = 975;
        $expY  = 1090;
        $trainY = 1150;
        $eligY = 1210;

        $educValue = $getimagedetails[0]['educ_qual'] ?? "";
        $expValue = $getimagedetails[0]['exp'] ?? "";
        $trainValue = $getimagedetails[0]['training'] ?? "";
        $eligValue = $getimagedetails[0]['eligibility_desc'] ?? "";

        drawTextBlock($image, $educValue, $fontRegular, 28, 0, $qualX, $educY, $qualMaxWidth, 40, $white);
        drawTextBlock($image, $expValue, $fontRegular, 28, 0, $qualX, $expY, $qualMaxWidth, 40, $white);
        drawTextBlock($image, $trainValue, $fontRegular, 28, 0, $qualX, $trainY, $qualMaxWidth, 40, $white);
        drawTextBlock($image, $eligValue, $fontRegular, 28, 0, $qualX, $eligY, $qualMaxWidth, 40, $white);


        // Competencies
        $compX = 110;
        $compY = 1400;
        $bulletIndent = 40;
        $compWidth = 1200;

        foreach ($competenciesFlat as $comp) {
            imagettftext(
                $image,
                26,
                0,
                $compX,
                $compY,
                $white,
                $fontRegular,
                "•"
            );

            $compY = drawTextBlock(
                $image,
                $comp,
                $fontRegular,
                26,
                0,
                $compX + $bulletIndent,
                $compY,
                $compWidth - $bulletIndent,
                35,
                $white,
                'left'
            );

            //Extra spacing between bullets
            $compY += 10;
        }

        // Save Page 1
        imagejpeg($image, $imagePath);

        // Second Page text
        $image2 = imagecreatefromjpeg('../dist/img/publicationtemplate2.jpg');
        $imagePath2 = $dir . 'generated_position_' . $position_id . '_' . $timestamp . '_2.jpg';

        $publicationdate2 = $getpublicationid[0]['pubtitle_enddt'] ?? date("Y-m-d");
        $currendtformat2 = date("F j, Y", strtotime($publicationdate2));

        $fontSizeCurrendt2 = 25;
        $angle2 = 0;
        $paddingRight2 = 140;
        $paddingTop2 = 236;

        // Get text bounding box
        $bbox = imagettfbbox($fontSizeCurrendt2, $angle2, $fontRegular, $currendtformat2);
        $textWidth = $bbox[2] - $bbox[0];
        $textHeight = $bbox[1] - $bbox[7];

        $x = imagesx($image2) - $textWidth - $paddingRight2;
        $y = $paddingTop2 + $textHeight;

        imagettftext($image2, $fontSizeCurrendt2, $angle2, $x, $y, $white, $fontRegular, $currendtformat);

        imagettftext($image2, 40, $angle2, 180, 510, $yellow, $fontBold, $currendtformat2 . ".");

        // Save Page 2
        imagejpeg($image2, $imagePath2);

        //Insert the attachment record with the generated image path
        $filename = basename($imagePath);
        $saveattachment = execsqlSRS(
            "
            INSERT INTO [tbl_Attachment] (
                [att_filename]
                ,[att_filepath]
                ,[att_dt]
                ,[UserID]
                ,[IsActive]
            )
            VALUES (?, ?, ?, ?, 0)
        ",
            "Insert",
            array(
                $filename,
                $imagePath,
                $currentdt,
                intval($userid)
            )
        );

        //Get the id of the inserted attachment record and update attach_id in tbl_PublicationPosition
        $resAttachment = execsqlSRS(
            "
            SELECT TOP 1 [attach_id]
            FROM [tbl_Attachment]
            WHERE [UserID] = ?
            ORDER BY [attach_id] DESC
        ",
            "Select",
            array(
                intval($userid)
            )
        );

        $attach_id = $resAttachment[0]['attach_id'] ?? 0;

        $updatePosition = execsqlSRS(
            "
            UPDATE [tbl_PublicationPosition]
            SET attach_id = ?
            WHERE pubpos_id = ?",
            "Update",
            array(
                intval($attach_id),
                intval($position_id)
            )
        );

        echo json_encode([
            "status" => "success",
            "message" => "Position successfully saved."
        ]);

        break;

    case "viewpositiondetails":

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
                echo '<p class="mb-1"><strong>Status of Employment:</strong> <span class="border rounded bg-' . htmlspecialchars($pd['color_desc']) . ' p-2">' . htmlspecialchars($pd['appoint_desc']) . '</span></p>';
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

                        echo '<div class="col-md-6 mb-3">'; // COLUMN

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

                echo '</div>';
            }
        } else {
            echo '<div class="alert alert-warning">No details found for this position.</div>';
        }

        break;

    case "deleteposition":

        $querydelete = execsqlSRS(
            "
            DELETE FROM [tbl_PublicationPosition]
            WHERE [pubpos_id] = :pubpos_id",
            "Delete",
            [
                ":pubpos_id" => $datavalue
            ]
        );

        echo json_encode(['status' => 'success', 'message' => 'System: Position Deleted!']);

        break;

    case "publishpublication":

        $querypublish = execsqlSRS(
            "
            UPDATE [tbl_Publication]
            SET [pubstatus_id] = 2
            WHERE [publication_id] = :publication_id",
            "Update",
            [
                ":publication_id" => intval($datavalue)
            ]
        );

        echo json_encode(['status' => 'success', 'message' => 'System: Publication Published!']);

        break;
}
