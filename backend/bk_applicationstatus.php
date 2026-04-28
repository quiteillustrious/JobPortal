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

case "viewapplicationstatus":

    $viewapplicationstatus = execsqlSRS("
        SELECT  s.[snap_id]
                ,s.[pubpos_id]
                ,p.[position_title]
                ,s.[AppliedDate]
                ,sta.[status_code]
                ,sta.[status_desc]
                ,c.[color_desc]
        FROM [tbl_Snapshot] s

        LEFT JOIN [tbl_PublicationPosition] p
            ON p.[pubpos_id] = s.[pubpos_id]

        LEFT JOIN [tbl_SnapshotStatus] sta
            ON sta.[snap_status] = s.[snap_status]

        LEFT JOIN [tbl_Colors] c
            ON c.[color_id] = sta.[color_id]

        WHERE s.[UserID] = ?
        ORDER BY s.[AppliedDate] DESC
    ","Select",array($userid));

    echo '
    <div class="card shadow-sm border border-success">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">My Applications</h5>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped align-middle mb-0">
        <thead class="table-success">
            <tr>
            <th>Application ID</th>
            <th>Date Applied</th>
            <th>Position Applied</th>
            <th>Status</th>
            <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
    ';

    if (!empty($viewapplicationstatus)) {
        foreach ($viewapplicationstatus as $status) {

            $date = strtotime($status['AppliedDate']);

            echo '
            <tr>

                <td class="text-success fw-semibold">
                    TAU-APP-' . str_pad($status["snap_id"], 4, "0", STR_PAD_LEFT) . '
                </td>

                <td>
                    <span class="fw-semibold text-dark">
                        ' . date("M d, Y", $date) . '
                    </span>
                    <small class="text-muted ms-2">
                        (' . date("D", $date) . ')
                    </small>
                </td>

                <td>
                    <a href="#" class="text-decoration-none">
                        ' . (!empty($status['position_title']) 
                            ? '<span class="badge bg-success text-success border border-success px-3 py-2"
                                id="view_position_' . $status['pubpos_id'] . '"
                                data-datavalue="' . $status['pubpos_id'] . '"
                                data-backendurl="backend/bk_hrpublications.php"
                                data-backendrequest="viewpositiondetails"
                                data-openmodal="#attachmentmodal"
                                data-openmodallabel="View Position - ' . $status['position_title'] . '"
                                data-openmodalbody="#attachmentmodalcontent"
                                data-tooltip="View Position"
                                >
                                    ' . htmlspecialchars($status['position_title']) . '
                            </span>'
                            : '<span class="text-muted fst-italic">No position selected</span>') . '
                    </a>
                </td>

                <td>
                    <a href="#" class="text-decoration-none">
                        <span class="badge bg-' . htmlspecialchars($status['color_desc']) . ' px-3 py-2"
                                id="view_status_' . $status['pubpos_id'] . '"
                                data-datavalue="' . $status['snap_id'] . '"
                                data-backendurl="backend/bk_statusremarks.php"
                                data-backendrequest="seestatushistory"
                                data-openmodal="#addeditmodal"
                                data-openmodallabel="View Status History - TAU-APP-' .  str_pad($status["snap_id"], 4, "0", STR_PAD_LEFT) . '"
                                data-openmodalbody="#addeditcontent"
                                data-tooltip="See Status History"
                            >
                            ' . htmlspecialchars($status['status_code']) . '
                        </span>
                    </a>
                </td>

                <td>
                    <a href="#" class="text-decoration-none">
                        <span class="badge bg-' . htmlspecialchars($status['color_desc']) . ' bg-opacity-10 text-' . htmlspecialchars($status['color_desc']) . ' border border-' . htmlspecialchars($status['color_desc']) . ' px-3 py-2"
                                id="view_remarks_' . $status['pubpos_id'] . '"
                                data-datavalue="' . $status['snap_id'] . '"
                                data-backendurl="backend/bk_statusremarks.php"
                                data-backendrequest="seeremarkshistory"
                                data-openmodal="#addeditmodal"
                                data-openmodallabel="View Remarks History - TAU-APP-' .  str_pad($status["snap_id"], 4, "0", STR_PAD_LEFT) . '"
                                data-openmodalbody="#addeditcontent"                 
                                data-tooltip="See Remarks History"
                            >
                            ' .  htmlspecialchars($status['status_desc']) . '
                        </span>
                    </a>
                </td>

            </tr>
            ';
        }
    } else {
        echo '
        <tr>
        <td colspan="5" class="text-center text-danger font-weight-bold py-4">
            No applications found.
        </td>
        </tr>
        ';
    }

    echo '
        </tbody>
        </table>
    </div>
    </div>
    ';

break;

}