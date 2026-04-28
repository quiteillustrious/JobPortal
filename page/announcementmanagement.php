<?php
	include "../db/dbconnection.php";
	include "modals.php";
?>
<div class="content-header">

  <div class="container-fluid">
    <div class="row">

      <div class="col-sm-6">
        <h1 class="m-0">Announcement Pop-ups Management</h1>
			<ol class="breadcrumb float-sm-left mt-2">
				<button 
					type="button" 
					class="btn btn-success" 
					id="add_data"
					data-openmodal="#addeditmodal"
					data-openmodallabel="Add Announcement"
					data-openmodalbody="#addeditcontent"
					data-backendurl="backend/bk_announcementmanagement.php"
					data-backendrequest="addheader">
				  Add Announcement <i class="fa-solid fa-plus"></i>
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
	<label for="destination" class="pr-2" id="">Select an Announcement:</label>
		<select class="form-select" 
				aria-label="Default select example" 
				id="topselection"
				data-backendurl="backend/bk_announcementmanagement.php"
				data-backendmethod="POST"
				data-backendrequest="viewannouncements"
				data-backendtarget="#tblviewannouncements">

				<option class="" value="">Select...</option>
				<hr>
				<?php
					$announcements = execsqlSRS("
					SELECT announcement_id, announcement_title, IsActive
					FROM tbl_Announcements
                    ", "Select", array());
					foreach ($announcements as $announce) {
							
					            $aid = $announce['announcement_id'];
                                $atitle = $announce['announcement_title'];
                                $status = $announce['IsActive'];
								
							if (htmlspecialchars($status) == 0) {
								echo "<option value = '$aid'>$atitle </option>";
							}
							
							else {
								echo "<option value = '$aid' class='bg-danger'>$atitle</option>";
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
            <h3 class="card-title">Announcement Header</h3>
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
                  <th>Header ID</th>
                  <th>Header Title</th>
                  <th>Start Date</th>
				  <th>End Date</th>
                  <th>Icon</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>

              <tbody id="tblviewannouncements">

              </tbody>

            </table>
          </div>
		</div>
		
		<hr>
		
		<div>
          <div class="card-header">
            <h3 class="card-title">Contents</h3>
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
                  <th>Content ID</th>
                  <th>Title</th>
                  <th>Title Color</th>
				  <th>Title Fontsize</th>
                  <th>Body</th>
                  <th>Body Color</th>
                  <th>Body Fontsize</th>
                  <th>Content Icon</th>
                  <th>Content Arrangement</th>
                  <th>IsActive</th>
                  <th>Action</th>
                </tr>
              </thead>

              <tbody id="tblviewannouncementcontents">

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
  TableLoader.load({
    tableId: "#tblviewannouncements",
    url: "backend/bk_announcementmanagement.php",
    request: { request: "viewannouncements", datavalue: 0 }
  });
  
$(document).ready(function() {

    $.ajax({
        url: 'backend/bk_macroloader.php',
        type: 'POST',
        data: {
            request: 'loadmacros',
			datavalue: 2019
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
