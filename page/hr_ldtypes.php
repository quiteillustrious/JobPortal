<?php
	include "../db/dbconnection.php";
	include "modals.php";
?>
<!-- Content Header (Page header) -->
    <div class="content-header">
	
      <div class="container-fluid">
        <div class="row">

          <div class="col-sm-6">
            <h1>Learning and Development Types Management</h1>		
			<ol class="breadcrumb float-sm-left mt-2">
				<button 
					type="button" 
					class="btn btn-success" 
					id="add_data"
					data-openmodal="#addeditmodal"
					data-openmodallabel="Add L&D Type"
					data-openmodalbody="#addeditcontent"
					data-backendurl="backend/bk_hrldtypes.php"
					data-backendrequest="addldtype">
				  Add L&D Type <i class="fa-solid fa-plus"></i>
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

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card" style="border:2px solid black;">
              <div class="card-header">
                <h3 class="card-title">List of L&D Types</h3>

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
                      <th>ID</th>
                      <th>Description</th>
					  <th>Status</th>
					  <th>Action</th>
                    </tr>
                  </thead>
				  
					<tbody id="tblviewldtypes">

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
    tableId: "#tblviewldtypes",
    url: "backend/bk_hrldtypes.php",
    request: { request: "viewldtypes" },
  });

var passwordToggler = new PasswordToggler();

$(document).ready(function() {

    $.ajax({
        url: 'backend/bk_macroloader.php',
        type: 'POST',
        data: {
            request: 'loadmacros',
//			datavalue: 2
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