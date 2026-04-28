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

$currentdt = date("Y-m-d H:i:s");

switch ($request) {
	
	case "registeraccount":

	echo '
		<div id="registerAlert"></div>

		<div class="form-group">
		  <label for="field1">Username <span class="text-danger">*</span></label>
		  <div class="input-group">
			<div class="input-group-prepend">
			  <span class="input-group-text"><i class="fas fa-user"></i></span>
			</div>
			<input type="text" class="form-control" id="field1" placeholder="Ex. user123">
		  </div>
		</div>

		<div class="form-group">
		  <label for="field2">Password <span class="text-danger">*</span></label>
		  <div class="input-group">
			<div class="input-group-prepend">
			  <span class="input-group-text"><i class="fas fa-lock"></i></span>
			</div>
			<input type="password" class="form-control" id="field2" placeholder="Password">
			<div class="input-group-append">
			  <button type="button" class="btn btn-outline-secondary" id="togglePassword1">
				<i class="fas fa-eye"></i>
			  </button>
			</div>
		  </div>
		</div>

		<div class="form-group">
		  <label for="field3">Confirm Password <span class="text-danger">*</span></label>
		  <div class="input-group">
			<div class="input-group-prepend">
			  <span class="input-group-text"><i class="fas fa-lock"></i></span>
			</div>
			<input type="password" class="form-control" id="field3" placeholder="Confirm Password">
			<div class="input-group-append">
			  <button type="button" class="btn btn-outline-secondary" id="togglePassword2">
				<i class="fas fa-eye"></i>
			  </button>
			</div>
		  </div>
		</div>

		<div class="form-group">
		  <label for="field4">Last Name <span class="text-danger">*</span></label>
		  <input type="text" class="form-control" id="field4" placeholder="Ex. Dela Cruz">
		</div>

		<div class="form-group">
		  <label for="field5">First Name <span class="text-danger">*</span></label>
		  <input type="text" class="form-control" id="field5" placeholder="Ex. Juan">
		</div>

		<div class="form-group">
		  <label for="field6">Email Address <span class="text-danger">*</span></label>
		  <div class="input-group">
			<input type="email" class="form-control" id="field6" placeholder="Ex. user123@tau.edu.ph">
			<div class="input-group-append">
			  <button class="btn btn-success" type="button" id="sendOTP">
				<i class="fas fa-envelope"></i> Send OTP
			  </button>
			</div>
		  </div>
		  <small class="form-text text-muted">Click "Send OTP" to receive a one-time code in your email.</small>
		</div>

		<div class="form-group">
		  <label for="field7">One-Time Password <span class="text-danger">*</span></label>
		  <input type="text" class="form-control" id="field7" placeholder="Enter OTP">
		</div>

		<div class="form-group mt-3">
		  <button type="button"
				  id="finalregistration"
				  class="btn btn-success btn-block font-weight-bold">
			Register Account <i class="fas fa-user-plus"></i>
		  </button>
		</div>

		<script>
		$("#togglePassword1").on("click", function() {
			let input = $("#field2");
			let icon = $(this).find("i");
			input.attr("type", input.attr("type") === "password" ? "text" : "password");
			icon.toggleClass("fa-eye fa-eye-slash");
		});

		$("#togglePassword2").on("click", function() {
			let input = $("#field3");
			let icon = $(this).find("i");
			input.attr("type", input.attr("type") === "password" ? "text" : "password");
			icon.toggleClass("fa-eye fa-eye-slash");
		});
		
		$("#field2, #field3").on("input", function() {
			const pwd = $("#field2").val();
			const confirm = $("#field3").val();

			if(confirm.length > 0){
				if(pwd !== confirm){
					showRegisterAlert("danger", "Passwords do not match!");
				} else {
					showRegisterAlert("success", "Passwords match!");
				}
			} else {
				$("#registerAlert").html(""); // Clear alert if confirm is empty
			}
		});

		function showRegisterAlert(type, message) {
		  $("#registerAlert").html(
			`<div class="alert alert-${type} alert-dismissible fade show" role="alert">
			  ${message}
			  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			  </button>
			</div>`
		  );
		}
		';

	break;

	case "sendotp":

	$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$otp = '';

	$username 	= $fields["field1"];
	$password 	= $fields["field2"];
	$firstname = ucwords(strtolower($fields['field5']));
	$lastname  = ucwords(strtolower($fields['field4']));
	$email 		= $fields["field6"];
	
	$checkexisting = execsqlSRS("
    SELECT EmailAddress
    FROM Sys_UserAccount
    WHERE EmailAddress = :email
	", "Select", [":email"=>$email]);

	if (!empty($checkexisting)) {
		echo json_encode([
			'status' => 'error',
			'message' => 'Email address is already registered with an account.'
		]);
		exit;
	}

	for ($i = 0; $i < 6; $i++) {
		$otp .= $characters[random_int(0, strlen($characters) - 1)];
	}

	$mail = new PHPMailer(true);

	try {
		
		$mail->isSMTP();
		$mail->SMTPDebug = 0;
		$mail->Host = 'smtp.gmail.com';
		$mail->SMTPAuth = true;
		$mail->Username = 'tau_hrmo-rsp@tau.edu.ph';
		$mail->Password = 'quec zbkq ntka efhp';
		$mail->SMTPSecure = 'tls';
		$mail->Port = 587;
		
		$mail->setFrom('tau_hrmo-rsp@tau.edu.ph', 'Tarlac Agricultural University - Human Resource Management Unit (Recruitment, Selection, and Placement)');
		$mail->addAddress($email, "$firstname $lastname");

		$mail->isHTML(true);
		$mail->Subject = 'TAU Job Portal Verification Code';
		$mail->Body = "Hello, <b>$firstname $lastname</b><br>";
		$mail->Body .= "Your verification code is: <b>$otp</b>";

		$mail->send();
		
		$insertinitial = execsqlSRS("
			INSERT INTO Sys_UserAccount (
						Username
						,Password
						,LastName
						,FirstName
						,EmailAddress
						,VerificationCode
						,AccountRegDate
						)
			VALUES (
						:username
						,:password
						,:lastname
						,:firstname
						,:email
						,:otp
						,:regdate
					)
		", "Insert", [
			":username" => $username,
			":password" => $password,
			":lastname" => $lastname,
			":firstname"   => $firstname,
			":email"   => $email,
			":otp"   => $otp,
			":regdate"   => $currentdt
		]);

		echo json_encode([
			'status' => 'success',
			'message' => "OTP sent successfully!"
		]);

	} catch (Exception $e) {
		echo json_encode([
			'status' => 'error',
			'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
		]);
	}

	break;

	case "finalregistration":
	
	$otp = isset($_POST["otp"]) ? $_POST["otp"] : "";
	$email = isset($_POST["email"]) ? $_POST["email"] : "";
		
		$getotp = execsqlSRS("
				SELECT [VerificationCode]
				FROM Sys_UserAccount
				WHERE EmailAddress = :email", 
				"Select", [
							":email" => $email
						  ]); 
						  
		if (!$getotp) {
			echo json_encode(['status'=>'error','message'=>"Email not found."]);
			exit;
		}
						  
		else if ($getotp[0]["VerificationCode"] == $otp){
			
			$changeuser = execsqlSRS("
					UPDATE Sys_UserAccount
					SET RID = '2', IsActive = '0', VerificationCode = NULL
					WHERE EmailAddress = :email", 
					"Update", [
								":email" => $email
							  ]); 
							  
			echo json_encode([
				'status' => 'success',
				'message' => "Registration successful."
			]);
		}
		else {
			echo json_encode([
				'status' => 'error',
				'message' => "Invalid OTP."
			]);
		}

	break;

}

?>