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

    case "viewpositionsummary":

        $merged = execsqlSRS("
    SELECT
        snap.snap_id,
        snap.pubpos_id,
        snap.UserID,
        userdet.Sex,
        userdet.FirstName,
        userdet.MiddleName,
        userdet.LastName,

        MAX(CASE WHEN ans.question_code = 'q40a' AND ans.answer = 'Yes' THEN 1 ELSE 0 END) AS IsIndigenous,
        MAX(CASE WHEN ans.question_code = 'q40b' AND ans.answer = 'Yes' THEN 1 ELSE 0 END) AS IsPWD,
        MAX(CASE WHEN ans.question_code = 'q40c' AND ans.answer = 'Yes' THEN 1 ELSE 0 END) AS IsSoloParent,
        MAX(CASE WHEN ans.question_code = 'q40d' AND ans.answer = 'Yes' THEN 1 ELSE 0 END) AS IsPregnant,
        MAX(CASE WHEN ans.question_code = 'q40e' AND ans.answer = 'Yes' THEN 1 ELSE 0 END) AS IsSenior

    FROM tbl_Snapshot snap

    LEFT JOIN tbl_SnapshotUser userdet
        ON userdet.UserID = snap.UserID

    LEFT JOIN tbl_SnapshotAnswers ans
        ON ans.snap_id = snap.snap_id
        AND ans.question_code IN ('q40a','q40b','q40c','q40d','q40e')

    WHERE snap.pubpos_id = ?

    GROUP BY
        snap.snap_id,
        snap.pubpos_id,
        snap.UserID,
        userdet.Sex,
        userdet.FirstName,
        userdet.MiddleName,
        userdet.LastName

    ORDER BY userdet.FirstName
", "Select", [intval($datavalue)]);

        // =========================
        // COUNTERS
        // =========================

        $male = 0;
        $female = 0;

        $indigenous = 0;
        $pwd = 0;
        $soloParent = 0;
        $pregnant = 0;
        $senior = 0;

        $maleNames = [];
        $femaleNames = [];

        $indigenousNames = [];
        $pwdNames = [];
        $soloParentNames = [];
        $pregnantNames = [];
        $seniorNames = [];

        $allApplicants = [];

        // =========================
        // LOOP
        // =========================

        foreach ($merged as $row) {

            $sex = strtolower($row["Sex"] ?? '');

            $name = trim(
                ($row["FirstName"] ?? '') . " " .
                    ($row["MiddleName"] ?? '') . " " .
                    ($row["LastName"] ?? '')
            );

            $allApplicants[] = [
                "name" => $name,
                "sex" => $sex
            ];

            // SEX
            if ($sex === "male") {
                $male++;
                $maleNames[] = $name;
            } elseif ($sex === "female") {
                $female++;
                $femaleNames[] = $name;
            }

            // Q40 GROUPS (with lists)
            if (!empty($row["IsIndigenous"])) {
                $indigenous++;
                $indigenousNames[] = $name;
            }

            if (!empty($row["IsPWD"])) {
                $pwd++;
                $pwdNames[] = $name;
            }

            if (!empty($row["IsSoloParent"])) {
                $soloParent++;
                $soloParentNames[] = $name;
            }

            if (!empty($row["IsPregnant"])) {
                $pregnant++;
                $pregnantNames[] = $name;
            }

            if (!empty($row["IsSenior"])) {
                $senior++;
                $seniorNames[] = $name;
            }
        }

        $totalApplicants = count($allApplicants);

        // =========================
        // DASHBOARD
        // =========================

        echo "
<div class='card ml-3 mr-3'>
    <div class='card-header bg-success text-white'>
        <p class='mb-0'>Applicant Analytics Dashboard</p>
    </div>

    <div class='card-body'>

        <div class='row'>

            <div class='col-md-6 text-center'>
                <h6 class='text-success font-weight-bold'>Sex Ratio</h6>
                <canvas id='sexRatioChart'></canvas>
            </div>

            <div class='col-md-6 text-center'>
                <h6 class='text-success font-weight-bold'>Sector Distribution</h6>
                <canvas id='q40Chart'></canvas>
            </div>

        </div>

        <hr>

        <div class='row'>

            <div class='col-md-6'>

                <h6 class='text-primary font-weight-bold'>Male ($male)</h6>
                <ul>";
        foreach ($maleNames as $n) echo "<li>$n</li>";
        echo "</ul>

                <h6 class='text-danger font-weight-bold mt-3'>Female ($female)</h6>
                <ul>";
        foreach ($femaleNames as $n) echo "<li>$n</li>";
        echo "</ul>

            </div>

            <div class='col-md-6'>

                <h6 class='font-weight-bold text-info'>Indigenous ($indigenous)</h6>
                <ul>";
        foreach ($indigenousNames as $n) echo "<li>$n</li>";
        echo "</ul>

                <h6 class='font-weight-bold text-warning'>PWD ($pwd)</h6>
                <ul>";
        foreach ($pwdNames as $n) echo "<li>$n</li>";
        echo "</ul>

                <h6 class='font-weight-bold text-success'>Solo Parent ($soloParent)</h6>
                <ul>";
        foreach ($soloParentNames as $n) echo "<li>$n</li>";
        echo "</ul>

                <h6 class='font-weight-bold text-danger'>Pregnant ($pregnant)</h6>
                <ul>";
        foreach ($pregnantNames as $n) echo "<li>$n</li>";
        echo "</ul>

                <h6 class='font-weight-bold text-secondary'>Senior ($senior)</h6>
                <ul>";
        foreach ($seniorNames as $n) echo "<li>$n</li>";
        echo "</ul>

            </div>

        </div>

    </div>
</div>
";

        // =========================
        // TABLE
        // =========================

        echo "
<div class='card ml-3 mr-3'>
    <div class='card-header bg-success'>
        <p class='mb-0'>Applicants List (Total: $totalApplicants)</p>
    </div>

    <div class='card-body p-0 table-responsive'>
        <table class='table table-hover mb-0'>
            <thead class='thead-light'>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Sex</th>
                </tr>
            </thead>
            <tbody>
";

        $counter = 1;

        foreach ($allApplicants as $a) {

            $badge = ($a["sex"] === "male")
                ? "<span class='badge badge-primary'>M</span>"
                : "<span class='badge badge-danger'>F</span>";

            echo "
        <tr>
            <td>{$counter}</td>
            <td>{$a['name']}</td>
            <td>{$badge}</td>
        </tr>
    ";

            $counter++;
        }

        echo "
            </tbody>
        </table>
    </div>
</div>
";

        echo "
<script>
(function () {

    function renderCharts() {

        if (typeof Chart === 'undefined') {
            console.error('Chart.js not loaded');
            return;
        }

        var sexCanvas = document.getElementById('sexRatioChart');
        var q40Canvas = document.getElementById('q40Chart');

        if (!sexCanvas || !q40Canvas) {
            // Wait until DOM is ready (AJAX-safe)
            setTimeout(renderCharts, 50);
            return;
        }

        // SAFE DESTROY (prevents your error)
        if (window.sexChart && typeof window.sexChart.destroy === 'function') {
            window.sexChart.destroy();
        }

        if (window.q40Chart && typeof window.q40Chart.destroy === 'function') {
            window.q40Chart.destroy();
        }

        // RESET (avoid polluted references)
        window.sexChart = null;
        window.q40Chart = null;

        // CREATE CHARTS
        window.sexChart = new Chart(sexCanvas, {
            type: 'pie',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [$male, $female],
                    backgroundColor: ['#007bff', '#e83e8c']
                }]
            }
        });

        window.q40Chart = new Chart(q40Canvas, {
            type: 'pie',
            data: {
                labels: [
                    'Indigenous',
                    'PWD',
                    'Solo Parent',
                    'Pregnant',
                    'Senior'
                ],
                datasets: [{
                    data: [
                        $indigenous,
                        $pwd,
                        $soloParent,
                        $pregnant,
                        $senior
                    ],
                    backgroundColor: [
                        '#17a2b8',
                        '#ffc107',
                        '#28a745',
                        '#dc3545',
                        '#6c757d'
                    ]
                }]
            }
        });

    }

    // Force execution after AJAX injection
    setTimeout(renderCharts, 10);

})();
</script>
";

        break;
}
