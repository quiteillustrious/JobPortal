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

    case "changeprofilepic":
        echo '
<div class="container-fluid p-0">
  <div class="card border-0 shadow-none m-0">

    <!-- Header -->
    <div class="card-header bg-success text-center">
      <h5 class="mb-0 text-white">
        <i class="fas fa-user-circle"></i> Change Profile Picture
      </h5>
    </div>

    <!-- Body -->
    <div class="card-body text-center">

      <!-- Profile Preview -->
      <div class="mb-3 position-relative d-inline-block">
        <img id="profilePreview"
             src="dist/img/default-avatar.png"
             class="img-circle elevation-2"
             style="width:130px; height:130px; object-fit:cover; border:3px solid #007bff;">

        <!-- Camera Button -->
        <label for="uploadPic"
               class="btn btn-success btn-sm position-absolute"
               style="bottom:0; right:0; border-radius:50%; width:35px; height:35px; display:flex; align-items:center; justify-content:center;">
          <i class="fas fa-camera"></i>
        </label>
      </div>

      <!-- File Input -->
      <input type="file" id="uploadPic" accept="image/*" hidden>

      <p class="text-muted mb-3">Upload a new profile picture</p>

      <!-- Buttons -->
      <div class="d-flex justify-content-center">
        <button id="saveProfilePic"
                class="btn btn-success m-1"
                data-backendurl="backend/bk_changeprofilepic.php"
                data-backendrequest="saveprofilepic"
                >
          <i class="fas fa-save"></i> Save
        </button>

        <button id="cancelProfilePic" class="btn btn-secondary m-1" data-dismiss="modal">
          Cancel
        </button>
      </div>

    </div>
  </div>
</div>
';
        break;

    case "saveprofilepic":

        if (!isset($_POST['datavalue']) || !isset($_FILES['profile_pic'])) {
            echo json_encode(["status" => "error", "message" => "Missing data"]);
            exit;
        }

        $file   = $_FILES['profile_pic'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(["status" => "error", "message" => "Upload failed"]);
            exit;
        }

        if ($file['size'] > (5 * 1024 * 1024)) {
            echo json_encode(["status" => "error", "message" => "File must not exceed 5MB"]);
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($mime, $allowed)) {
            echo json_encode(["status" => "error", "message" => "File must be an image"]);
            exit;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = 'profile_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;

        $uploadDir = '../uploads/profile_pictures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fullPath = $uploadDir . $newName;

        $dbPath = '../uploads/profile_pictures/' . $newName;

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            echo json_encode(["status" => "error", "message" => "Failed to save file"]);
            exit;
        }

        execsqlSRS("
        INSERT INTO [tbl_Attachment]
        (att_filename, att_filepath, att_dt, UserID, IsActive)
        VALUES (?, ?, ?, ?, 0)
    ", "Insert", array($newName, $dbPath, $currentdt, intval($datavalue)));

        $getAttach = execsqlSRS("
        SELECT TOP 1 attach_id
        FROM [tbl_Attachment]
        WHERE UserID = ?
        ORDER BY attach_id DESC
    ", "Select", array(intval($datavalue)));

        if (!$getAttach || count($getAttach) == 0) {
            echo json_encode(["status" => "error", "message" => "Failed to retrieve attachment"]);
            exit;
        }

        $attachId = $getAttach[0]['attach_id'];

        execsqlSRS("
        UPDATE [tbl_ProfUserDetails]
        SET profile_pic = ?
        WHERE UserID = ?
    ", "Update", array(intval($attachId), intval($datavalue)));

        echo json_encode([
            "status" => "success",
            "message" => "Profile picture updated",
            "new_image" => $dbPath
        ]);

        break;
}
