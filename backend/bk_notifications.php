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

    case "fetchnotif":

        $fetchnotif = execsqlSRS("
            SELECT [notif_id]
            FROM [tbl_Notifications]
            WHERE [UserID] = ?
            AND [IsRead] = 1
            AND [IsActive] = 0
        ", "Select", [intval($userid)]);

        $count = count($fetchnotif);
        echo $count;

        break;

    case "seenotif":

        $fetchnotif = execsqlSRS("
            SELECT
                [notif_id],
                [notif_title],
                [notif_message],
                [target_url],
                [IsRead]
            FROM [tbl_Notifications]
            WHERE [UserID] = ?
            AND [IsActive] = 0
            AND [IsRead] = 1
            ORDER BY [notif_id] DESC
        ", "Select", [intval($userid)]);

        if (!$fetchnotif) {
            echo "<div class='text-center text-danger font-weight-bold'>No new notifications...</div>";
            exit;
        } else {

            foreach ($fetchnotif as $n) {

                $isUnread = ($n['IsRead'] == 1);

                $class = $isUnread ? "notif-unread" : "notif-read";

                $badge = $isUnread
                    ? "<span class='badge badge-success ml-2'>NEW</span>"
                    : "";

                echo "
                <div class='notif-item $class'
                    id='readnotif'
                    data-datavalue='" . $n['notif_id'] . "'
                    data-targeturl='" . $n['target_url'] . "'
                    data-dataisread='" . $n['IsRead'] . "'
                    style='padding:10px; margin-bottom:6px; border-radius:5px; cursor:pointer;'>
                    <strong>{$n['notif_title']} $badge</strong><br>
                    <small>{$n['notif_message']}</small>
                </div>
            ";
            }
        }

        break;

    case "readnotif":

        $dataisread = isset($_POST["dataisread"]) ? $_POST["dataisread"] : "";

        if ($dataisread == 1) {

            execsqlSRS("
                UPDATE tbl_Notifications
                SET IsRead = 0,
                    ReadAt = ?
                WHERE notif_id = ?
            ", "Update", array($currentdt, intval($datavalue)));
        }

        break;
}
