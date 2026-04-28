<?php 

include "../db/dbconnection.php";
date_default_timezone_set('Asia/Manila');
$currentdt = date("Y-m-d H:i:s");

$request = isset($_POST["request"]) ? $_POST["request"] : "";

switch ($request){

    case "closepublications":

    $affected = execsqlSRS("
        UPDATE [tbl_Publication]
        SET [pubstatus_id] = '4'
        WHERE 
            [pubstatus_id] IN ('2', '5')
            AND CAST([pubtitle_enddt] AS DATE) < CAST(? AS DATE)
    ", "Update", array($currentdt));

    echo json_encode([
        "status" => "success",
        "updated" => $affected
    ]);

    break;

}