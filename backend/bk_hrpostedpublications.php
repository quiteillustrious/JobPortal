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
                pub.[publication_id],
                pub.[pubtitle_name],
                pub.[pubtitle_startdt],
                pub.[pubtitle_enddt],
                pub.[pubstatus_id],
                color.[color_desc],

                pos.[pubpos_id],
                pos.[position_title],

                pubstatus.[pubstatus_desc]

            FROM [tbl_Publication] pub

            LEFT JOIN [tbl_PublicationStatus] pubstatus
                ON pubstatus.[pubstatus_id] = pub.[pubstatus_id]

            LEFT JOIN [tbl_Colors] color 
                ON color.[color_id] = pubstatus.[color_id]

            LEFT JOIN [tbl_PublicationPosition] pos
                ON pos.[publication_id] = pub.[publication_id]

            WHERE (
                    (pub.[pubstatus_id] = '2' 
                        OR pub.[pubstatus_id] = '5')
                   )

            ORDER BY pub.[publication_id] DESC
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

    case "cancelpublication":

        $update = execsqlSRS("UPDATE [tbl_Publication] SET [pubstatus_id] = '3' WHERE [publication_id] = ?", "Update", [$datavalue]);

        echo json_encode(["status" => "success", "message" => "Publication cancelled successfully."]);

    break;
}

?>