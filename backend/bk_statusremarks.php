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
	
    case "seestatushistory":

        $fetchstatus = execsqlSRS("
            SELECT  hist.[snaphistory_id]
                    ,hist.[snap_id]
                    ,stat.[status_code]
                    ,stat.[status_desc]
                    ,c.[color_desc]
                    ,c.[color_hex]
                    ,hist.[changed_at]

            FROM [tbl_SnapshotHistory] hist

            LEFT JOIN [tbl_SnapshotStatus] stat
                ON stat.[snap_status] = hist.[snap_status]

            LEFT JOIN [tbl_Colors] c
                ON c.[color_id] = stat.[color_id]

            WHERE hist.[snap_id] = ?
            ORDER BY hist.[changed_at] DESC
        ", "Select", array(intval($datavalue)));


        echo '<div style="padding:12px 6px;">';

        if (!empty($fetchstatus)) {

            echo '<div style="position:relative; padding-left:18px;">';

            $isLatest = true;

            foreach ($fetchstatus as $row) {

                $desc = htmlspecialchars($row['status_desc']);
                $code = htmlspecialchars($row['status_code']);

                $colorHex = !empty($row['color_hex']) ? $row['color_hex'] : '#5cb85c';

                $dateFull = date('M d, Y h:i A', strtotime($row['changed_at']));
                $timeOnly = date('h:i A', strtotime($row['changed_at']));

                $accent = '#28a745';

                $isActive = $isLatest ? true : false;

                echo '

                <!-- vertical line -->
                <div style="
                    position:absolute;
                    left:6px;
                    top:0;
                    bottom:0;
                    width:2px;
                    background:#e9f5ee;
                "></div>

                <div style="
                    position:relative;
                    margin-bottom:16px;
                    padding-left:20px;
                ">

                    <!-- DOT -->
                    <div style="
                        position:absolute;
                        left:0;
                        top:8px;
                        width:' . ($isActive ? '12px' : '10px') . ';
                        height:' . ($isActive ? '12px' : '10px') . ';
                        border-radius:50%;
                        background:' . $colorHex . ';
                        box-shadow:0 0 0 3px #fff, 0 0 ' . ($isActive ? '8px rgba(40,167,69,0.4)' : '0') . ';
                    "></div>

                    <!-- CARD -->
                    <div style="
                        background:' . ($isActive ? '#f6fffa' : '#fff') . ';
                        border:1px solid ' . ($isActive ? '#bfe8cc' : '#f1f1f1') . ';
                        border-radius:8px;
                        padding:10px 12px;
                        box-shadow:0 1px 2px rgba(0,0,0,0.04);
                    ">

                        <div style="
                            font-size:12px;
                            color:red;
                            margin-bottom:3px;
                        ">
                            ' . $dateFull . ' • ' . $timeOnly . '
                        </div>

                        <div style="
                            font-size:15px;
                            font-weight:600;
                            color:#2d3436;
                            line-height:1.3;
                        ">
                            ' . $desc . '
                        </div>

                        <div style="
                            font-size:13px;
                            margin-top:3px;
                            color:' . $colorHex . ';
                            font-weight:600;
                            letter-spacing:0.2px;
                        ">
                            ' . $code . '
                        </div>

                        ' . ($isActive ? '
                            <div style="
                                margin-top:6px;
                                font-size:11px;
                                color:#28a745;
                                font-weight:600;
                            ">
                                CURRENT STATUS
                            </div>
                        ' : '') . '

                    </div>

                </div>';

                $isLatest = false;
            }

            echo '</div>';

        } else {
            echo '<div style="color:#999; padding:10px;">No history found.</div>';
        }

        echo '</div>';

    break;

    case "seeremarkshistory":

        $fetchhistory = execsqlSRS("
            SELECT  hist.[snaphistory_id]
                    ,hist.[snap_id]
                    ,stat.[status_code]
                    ,stat.[status_desc]
                    ,c.[color_desc]
                    ,c.[color_hex]
                    ,hist.[changed_at]
                    ,hist.[remarks]

            FROM [tbl_SnapshotHistory] hist

            LEFT JOIN [tbl_SnapshotStatus] stat
                ON stat.[snap_status] = hist.[snap_status]

            LEFT JOIN [tbl_Colors] c
                ON c.[color_id] = stat.[color_id]

            WHERE hist.[snap_id] = ?
            ORDER BY hist.[changed_at] DESC
        ", "Select", array(intval($datavalue)));


        echo '<div style="padding:12px 6px;">';

        if (!empty($fetchhistory)) {

            echo '<div style="position:relative; padding-left:18px;">';

            $isLatest = true;

            foreach ($fetchhistory as $row) {

                $remarks = htmlspecialchars($row['remarks'] ?? '');
                $code = htmlspecialchars($row['status_code']);
                $desc = htmlspecialchars($row['status_desc']);

                $colorHex = !empty($row['color_hex']) ? $row['color_hex'] : '#5cb85c';

                $dateFull = date('M d, Y h:i A', strtotime($row['changed_at']));
                $timeOnly = date('h:i A', strtotime($row['changed_at']));

                $isActive = $isLatest;

                echo '

                <!-- vertical line -->
                <div style="
                    position:absolute;
                    left:6px;
                    top:0;
                    bottom:0;
                    width:2px;
                    background:#e9f5ee;
                "></div>

                <div style="
                    position:relative;
                    margin-bottom:16px;
                    padding-left:20px;
                ">

                    <!-- DOT -->
                    <div style="
                        position:absolute;
                        left:0;
                        top:10px;
                        width:' . ($isActive ? '12px' : '10px') . ';
                        height:' . ($isActive ? '12px' : '10px') . ';
                        border-radius:50%;
                        background:' . $colorHex . ';
                        box-shadow:0 0 0 3px #fff,
                                0 0 ' . ($isActive ? '8px rgba(40,167,69,0.35)' : '0') . ';
                    "></div>

                    <!-- CARD -->
                    <div style="
                        background:' . ($isActive ? '#f6fffa' : '#fff') . ';
                        border:1px solid ' . ($isActive ? '#bfe8cc' : '#f1f1f1') . ';
                        border-radius:8px;
                        padding:10px 12px;
                        box-shadow:0 1px 2px rgba(0,0,0,0.04);
                    ">

                        <!-- TOP META -->
                        <div style="
                            font-size:11px;
                            color:#999;
                            margin-bottom:6px;
                        ">
                            ' . $dateFull . ' • ' . $timeOnly . '
                        </div>

                        <!-- STATUS (secondary) -->
                        <div style="
                            font-size:12px;
                            color:' . $colorHex . ';
                            font-weight:600;
                            margin-bottom:6px;
                        ">
                            ' . $desc . ' <span style="opacity:0.7;">(' . $code . ')</span>
                        </div>

                        <!-- 🔥 MAIN EMPHASIS: REMARKS -->
                        <div style="
                            font-size:15px;
                            font-weight:600;
                            color:#2d3436;
                            line-height:1.4;
                            background:#f4fbf7;
                            border-left:3px solid ' . $colorHex . ';
                            padding:8px 10px;
                            border-radius:6px;
                        ">
                            ' . ($remarks ?: '<span style="color:#aaa;">No remarks provided</span>') . '
                        </div>

                        ' . ($isActive ? '
                            <div style="
                                margin-top:8px;
                                font-size:11px;
                                color:#28a745;
                                font-weight:600;
                            ">
                                CURRENT STATUS
                            </div>
                        ' : '') . '

                    </div>

                </div>';

                $isLatest = false;
            }

            echo '</div>';

        } else {
            echo '<div style="color:#999; padding:10px;">No history found.</div>';
        }

        echo '</div>';

    break;

}
