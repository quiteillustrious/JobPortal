<?php
	include "../db/dbconnection.php";
	include "modals.php";
?>
<div class="content-header">
  <div class="container-fluid">
	<div class="row">

	  <div class="col-sm-6">
		<h1 class="m-0">Privileges Management</h1>
		<ol class="breadcrumb float-sm-left">
		</ol>
	  </div>

	  <div class="col-sm-6">
		<ol class="breadcrumb float-sm-right">
		  <li class="breadcrumb-item"><a href="#">Home</a></li>
		  <li class="breadcrumb-item active">Dashboard v1</li>
		</ol>
	  </div>

	</div>
  </div>
</div>

<div class="row ml-3" id="infomacros">
	
</div>

<div class="dropdown pl-3 d-flex justify-content-center mb-3">
	<label for="destination" class="pr-2" id="">Select a Role:</label>
		<select class="form-control" 
				aria-label="Default select example" 
				style="max-width: 200px;"
				id="topselection"
				data-backendurl="backend/bk_privilegemanagement.php"
				data-backendmethod="POST"
				data-backendrequest="viewprivilege"
				data-backendtarget="#tblviewprivilege">
				<?php
					$roletype = execsqlSRS("
					SELECT RID, Role, IsActive
					FROM Sys_Role
                    ", "Select", array());
					foreach ($roletype as $role) {
							
					            $rid = $role['RID'];
                                $roledesc = $role['Role'];
                                $status = $role['IsActive'];
								
							if (htmlspecialchars($status) == 0) {
								echo "<option value = '$rid'>$roledesc </option>";
							}
							
							else {
								echo "<option value = '$rid' class='bg-danger'>$roledesc</option>";
							}
					}
				?>
		</select>
</div>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card" style="border:2px solid black;">
              <div class="card-header">
                <h3 class="card-title">List of Privileges</h3>

                <div class="card-tools">
                  <div class="input-group input-group-sm" style="max-width: 500px;">
                    <input type="text" name="table_search" class="form-control float-right" placeholder="Search">

                    <div class="input-group-append">
                      <button type="submit" class="btn btn-default">
                        <i class="fas fa-search"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped">
                  <thead class="thead-dark">
                    <tr>
                      <th>Privilege ID</th>
                      <th>Menu</th>
                      <th>Status</th>
                    </tr>
                  </thead>
				  
						<tbody id="tblviewprivilege">
						
						</tbody>
					
                </table>
              </div>
			 
            </div>
          </div>
        </div>
      </div>
    </section>

<script>
  TableLoader.load({
    tableId: "#tblviewprivilege",
    url: "backend/bk_privilegemanagement.php",
    request: { request: "viewprivilege", datavalue: 1}
  });
  
$(document).ready(function() {

    $.ajax({
        url: 'backend/bk_macroloader.php',
        type: 'POST',
        data: {
            request: 'loadmacros',
			datavalue: 2017
        },

        success: function(dataResult) {
            $('#infomacros').html(dataResult);
        },
		
        error: function(xhr, status, error) {

            console.error('AJAX Error:', error);
        }
    });
});
</script>