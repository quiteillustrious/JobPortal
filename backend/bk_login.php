<?php
include "../db/dbconnection.php";

// Get the request type from POST data
$request = isset($_POST["request"]) ? $_POST["request"] : "";

switch ($request) {

    case "verifyLogin":

		$lgtxtusername = isset($_POST["lgtxtusername"]) ? $_POST["lgtxtusername"] : "";
        $lgtxtpassword = isset($_POST["lgtxtpassword"]) ? $_POST["lgtxtpassword"] : "";

        $getUA = execsqlSRS(
            "SELECT UserID, Username, Password, RID, EmailAddress, LastName, FirstName
             FROM Sys_UserAccount 
             WHERE Username = :Username AND Password = :Password AND IsActive = '0'",
            "Select",
            array(
                ":Username" => $lgtxtusername,
                ":Password" => $lgtxtpassword
            )
        );

        $ghostaccount = execsqlSRS(
            "SELECT UserID, Username, Password, RID, EmailAddress, LastName, FirstName
            FROM Sys_UserAccount 
            WHERE Username = :Username 
            AND Password = :Password 
            AND RID IS NULL
            AND IsActive IS NULL",
            "Select",
            array(
                ":Username" => $lgtxtusername,
                ":Password" => $lgtxtpassword
            )
        );

        if (!empty($ghostaccount)){
            $email = $ghostaccount[0]['EmailAddress'];

            if (strpos($email, '@') !== false) {
                $email = substr($email, 0, 3) . "***" . strstr($email, "@");
            } else {
                $email = substr($email, 0, 3) . "***";
            }

            echo json_encode(array(
                "status" => "unrecognized",
                "message" => "It seems you've already registered with the email $email. Click 'Forgot Password or Can't Login?' to recover your account"
            ));
            exit;
        }

        $cypher = execsqlSRS(
            "SELECT GPoaT
             FROM tbl_Cypher",
            "Select",
            array()
        );


        if (isset($getUA[0])) {
            echo json_encode(array(
                "status" => "Registered",
                "UserID" => $getUA[0]["UserID"],
                "RID" => $getUA[0]["RID"],
                "EmailAddress" => $getUA[0]["EmailAddress"],
				"Username" => $getUA[0]["Username"],
				"Password" => $getUA[0]["Password"],
				"LastName" => $getUA[0]["LastName"],
				"FirstName" => $getUA[0]["FirstName"],
				"Cypher" => $cypher[0]["GPoaT"]
            ));
        } else {
            echo json_encode(array(
                "status" => "unrecognized",
                "message" => "Uh oh, you've entered the wrong username or password..."
            ));
        }

        break;
}
?>
