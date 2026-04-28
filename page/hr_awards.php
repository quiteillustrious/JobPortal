<?php
	include "../db/dbconnection.php";
	include "modals.php";
?>
<div class="content-header">

  <div class="container-fluid">
    <div class="row">

      <div class="col-sm-6">
        <h1 class="m-0">Credentials Management</h1>
			<ol class="breadcrumb float-sm-left mt-2">
				<button 
					type="button" 
					class="btn btn-success" 
					id="add_data"
					data-openmodal="#addeditmodal"
					data-openmodallabel="Add Credential Title"
					data-openmodalbody="#addeditcontent"
					data-backendurl="backend/bk_hrawards.php"
					data-backendrequest="addaward">
				  Add Credential <i class="fa-solid fa-plus"></i>
				</button>
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
	<label for="destination" class="pr-2" id="">Select Education Level:</label>
		<select class="form-select" 
				aria-label="Default select example" 
				id="topselection"
				data-backendurl="backend/bk_hrawards.php"
				data-backendmethod="POST"
				data-backendrequest="viewawards"
				data-backendtarget="#tblviewhrawards">

				<option class="" value="">Select...</option>
				<hr>
				<?php
					$levels = execsqlSRS("
					SELECT [edu_id]
						  ,[edu_desc]
						  ,[IsActive]
					FROM [tbl_ProfEducationLevels]
                    ", "Select", array());
					foreach ($levels as $level) {
								
							if (htmlspecialchars($level["IsActive"]) == 0) {
								echo "<option value = '".htmlspecialchars($level["edu_id"])."'>".htmlspecialchars($level["edu_desc"])."</option>";
							}
							
							else {
								echo "<option value = '".htmlspecialchars($level["edu_id"])."' class='bg-danger'>".htmlspecialchars($level["edu_desc"])."</option>";
							}
					}
				?>
		</select>
</div>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
      
        <div class="card">
		
		<div>
          <div class="card-header">
            <h3 class="card-title">List of Credentials</h3>
            <div class="card-tools">
              <div class="input-group input-group-sm" style="max-width: 500px;">
                <!-- <input type="text" name="table_search" class="form-control float-right" placeholder="Search">
                <div class="input-group-append">
                  <button type="submit" class="btn btn-default">
                    <i class="fas fa-search"></i>
                  </button>
                </div> -->
              </div>
            </div>
          </div>

          <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped">
              <thead class="thead-dark">
                <tr>
                  <th>ID</th>
                  <th>Description</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>

              <tbody id="tblviewhrawards">

              </tbody>

            </table>
          </div>
		</div>

        </div>
      </div>
    </div>
  </div>
</section>

<script>
  
$(document).ready(function() {

    $.ajax({
        url: 'backend/bk_macroloader.php',
        type: 'POST',
        data: {
            request: 'loadmacros',
//			datavalue: 2019
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
