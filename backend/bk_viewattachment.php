<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

$request   = isset($_POST["request"]) ? $_POST["request"] : "";
$datavalue = isset($_POST["datavalue"]) ? $_POST["datavalue"] : "";

$attach_id = intval($datavalue);

switch ($request) {

    case "viewattachment":

        if ($attach_id <= 0) {
            echo "<p>No valid attachment selected.</p>";
            break;
        }

        $attachment = execsqlSRS("
            SELECT [attach_id], [att_filename], [att_filepath]
            FROM [tbl_Attachment]
            WHERE [attach_id] = ?
        ", "Select", array($attach_id));

        if (!empty($attachment)) {

            $file = $attachment[0];
            $filename = htmlspecialchars($file['att_filename']);

            $filepath = str_replace('../', '/JobPortal/', $file['att_filepath']);

            $allowed_types = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'pdf' => 'application/pdf',
                'eps' => 'image/x-eps'
            ];

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (!array_key_exists($ext, $allowed_types)) {
                echo "<p>File type not supported.</p>";
                break;
            }

            switch ($ext) {

                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                    echo "<img src='$filepath' class='img-fluid' alt='$filename' />";
                    break;

                case 'pdf':
                case 'eps':
                    echo "<iframe src='$filepath' width='100%' height='600px' style='border:none;'></iframe>";
                    break;
            }
        } else {
            echo "<p>Attachment not found.</p>";
        }

        break;

    case "viewprofile":

        $files = execsqlSRS("
		SELECT snapattach_id, snap_id, entity_type, entity_id,
			attach_id, file_name, file_path, created_at, checked
		FROM tbl_SnapshotAttachment
		WHERE snap_id = ?
	", "Select", [intval($datavalue)]);

        $getpubpos = execsqlSRS("
		SELECT TOP 1 pubpos_id
		FROM tbl_Snapshot
		WHERE snap_id = ?
		", "", array(intval($datavalue)));

        $user = execsqlSRS("
		SELECT TOP 1 *
		FROM tbl_SnapshotUser
		WHERE snap_id = ?
	", "Select", [intval($datavalue)]);

        $user = $user[0] ?? [];

        $typeLabels = [
            'snapuser_id'     => 'PDS/Exp/PR',
            'snapeduc_id'     => 'Education',
            'snapelig_id'     => 'Eligibility',
            'snapexp_id'      => 'Work Experience',
            'snapvolwork_id'  => 'Volunteer Work',
            'snapld_id'       => 'Learning & Development',
            'snaporgassoc_id' => 'Organization Association',
            'snapnonacad_id'  => 'Non-Academic Recognition'
        ];

        $tableMap = [
            'snapeduc_id' => [
                'table' => 'tbl_SnapshotEducation',
                'key'   => 'snapeduc_id',
                'label' => 'degree_name'
            ],
            'snapelig_id' => [
                'table' => 'tbl_SnapshotEligibility',
                'key'   => 'snapelig_id',
                'label' => 'elig_type'
            ],
            'snapexp_id' => [
                'table' => 'tbl_SnapshotExp',
                'key'   => 'snapexp_id',
                'label' => 'position'
            ],
            'snapvolwork_id' => [
                'table' => 'tbl_SnapshotVolWork',
                'key'   => 'snapvolwork_id',
                'label' => 'org_name'
            ],
            'snapld_id' => [
                'table' => 'tbl_SnapshotLD',
                'key'   => 'snapld_id',
                'label' => 'ld_title'
            ],
            'snaporgassoc_id' => [
                'table' => 'tbl_SnapshotOrgAssoc',
                'key'   => 'snaporgassoc_id',
                'label' => 'orgassoc_desc'
            ],
            'snapnonacad_id' => [
                'table' => 'tbl_SnapshotNonAcad',
                'key'   => 'snapnonacad_id',
                'label' => 'nonacad_desc'
            ]
        ];

        echo "
	<div class='card border border-success mb-3'>
	<div class='card-header bg-success text-white'>
		<h5 class='mb-0'>Applicant Profile</h5>
	</div>

	<div class='card-body'>
		<div class='row'>

		<div class='col-md-6'>
			<p><strong>Full Name:</strong> "
            . htmlspecialchars(($user['FirstName'] ?? '') . ' ' . ($user['MiddleName'] ?? '') . ' ' . ($user['LastName'] ?? '') . ' ' . ($user['ExtName'] ?? '')) . "
			</p>

			<p><strong>Email:</strong> " . htmlspecialchars($user['Email'] ?? '') . "</p>
			<p><strong>Mobile:</strong> " . htmlspecialchars($user['MobileNumber'] ?? '') . "</p>
			<p><strong>Telephone:</strong> " . htmlspecialchars($user['TelephoneNumber'] ?? '') . "</p>

			<p><strong>Date of Birth:</strong> " . (!empty($user['DateOfBirth'])
                ? date('F d, Y', strtotime($user['DateOfBirth']))
                : '') . "</p>
			<p><strong>Age:</strong> " . htmlspecialchars($user['Age'] ?? '') . "</p>

			<p><strong>Sex:</strong> " . htmlspecialchars($user['Sex'] ?? '') . "</p>
			<p><strong>Civil Status:</strong> " . htmlspecialchars($user['CivilStatus'] ?? '') . "</p>
			<p><strong>Nationality:</strong> " . htmlspecialchars($user['Nationality'] ?? '') . "</p>
			<p><strong>Religion:</strong> " . htmlspecialchars($user['Religion'] ?? '') . "</p>
		</div>

		<div class='col-md-6'>
			<p><strong>Home Address:</strong><br>
				" . htmlspecialchars(
                ($user['HmHouse'] ?? '') . ' ' .
                    ($user['HmStreet'] ?? '') . ', ' .
                    ($user['HmBarangay'] ?? '') . ', ' .
                    ($user['HmCity'] ?? '') . ', ' .
                    ($user['HmProvince'] ?? '') . ' ' .
                    ($user['HmZip'] ?? '')
            ) . "
			</p>

			<p><strong>Current Address:</strong><br>
				" . htmlspecialchars(
                ($user['CurHouse'] ?? '') . ' ' .
                    ($user['CurStreet'] ?? '') . ', ' .
                    ($user['CurBarangay'] ?? '') . ', ' .
                    ($user['CurCity'] ?? '') . ', ' .
                    ($user['CurProvince'] ?? '') . ' ' .
                    ($user['CurZip'] ?? '')
            ) . "
			</p>
		</div>

		</div>
	</div>
	</div>
	";

        if (empty($files)) {
            echo "<div class='alert alert-warning'>No attachments found for this snapshot.</div>";
            break;
        }

        echo "
	<div class='card border border-success'>
	<div class='card-header bg-success text-white'>
		<h5 class='mb-0'>Attachments</h5>
	</div>

	<div class='card-body p-0 table-responsive'>
		<table class='table table-hover mb-0'>
		<thead class='table-success'>
			<tr>
			<th>#</th>
			<th>Reference</th>
			<th>Type</th>
			<th>Uploaded</th>
			</tr>
		</thead>
		<tbody>
	";

        $i = 1;

        foreach ($files as $f) {

            $id       = $f['snapattach_id'];
            $type     = $f['entity_type'] ?? '';
            $entityId = $f['entity_id'] ?? 0;
            $date = '';

            if (!empty($f['created_at'])) {
                try {
                    $dt = new DateTime($f['created_at']);
                    $date = $dt->format('l, F j, Y • g:i A');
                } catch (Exception $e) {
                    $date = htmlspecialchars($f['created_at']);
                }
            }

            $typeLabel = $typeLabels[$type] ?? $type;

            $reference = 'Unknown';

            if ($type === 'snapuser_id') {
                $reference = 'PDS/Work Experience/Performance Rating';
            } elseif (!empty($tableMap[$type])) {

                $tbl = $tableMap[$type]['table'];
                $key = $tableMap[$type]['key'];
                $col = $tableMap[$type]['label'];

                $res = execsqlSRS("
				SELECT TOP 1 $col AS label
				FROM $tbl
				WHERE $key = ?
			", "Select", [$entityId]);

                if (!empty($res[0]['label'])) {
                    $reference = $res[0]['label'];
                }
            }

            $path = $f['file_path'] ?? '';
            $url  = !empty($path) ? str_replace('../', '/JobPortal/', $path) : '';

            echo '
		<tr onclick="togglePreview(' . $id . ', \'' . $url . '\')" style="cursor:pointer;">
			<td class="text-success font-weight-bold">' . $i . '</td>
			<td>' . $reference . '</td>
			<td>' . $typeLabel . '</td>
			<td>' . $date . '</td>
		</tr>

		<tr id="preview-' . $id . '" style="display:none;">
			<td colspan="5">
				<iframe src="" style="width:100%;height:400px;border:1px solid #ddd;"></iframe>
			</td>
		</tr>
		';

            $i++;
        }

        echo "
		</tbody>
		</table>
	</div>
	</div>

	<script>
	function togglePreview(id, url) {
		const row = document.getElementById('preview-' + id);
		const iframe = row.querySelector('iframe');

		const isOpen = row.style.display === 'table-row';

		if (!isOpen) {
			iframe.src = url;
			row.style.display = 'table-row';
		} else {
			iframe.src = '';
			row.style.display = 'none';
		}
	}

	function toggleChecklist(el, event = null) {

		if (event) event.stopPropagation();

		const id = el.getAttribute('data-id');
		const current = el.getAttribute('data-value');

		const newValue = (current == '0') ? 1 : 0;

		fetch('backend/bk_amreviewattachments.php', {
			method: 'POST',
			body: new URLSearchParams({
				request: 'update_checklist',
				id: id,
				checked: newValue
			})
		})
		.then(res => res.json())
		.then(res => {
			if (res.success) {

				if (newValue == 0) {
					el.classList.remove('fa-toggle-off', 'text-danger');
					el.classList.add('fa-toggle-on', 'text-success');
					el.setAttribute('data-value', '0');
				} else {
					el.classList.remove('fa-toggle-on', 'text-success');
					el.classList.add('fa-toggle-off', 'text-danger');
					el.setAttribute('data-value', '1');
				}

			} else {
				alert('Update failed');
			}
		})
		.catch(() => {
			alert('Error updating checklist');
		});
	}
	</script>";

        break;
}
