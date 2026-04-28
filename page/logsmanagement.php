<?php
	include "../db/dbconnection.php";
	include "modals.php";
?>
<!-- Content Header (Page header) -->
    <div class="content-header">
	
      <div class="container-fluid">
        <div class="row">

          <div class="col-sm-6">
            <h1 class="m-0">Logs</h1>
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

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Logs</h3>

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
                      <th>Logs ID</th>
                      <th style="width: 280px;">Details</th>
                      <th>D/T</th>
                      <th>Location</th>
                      <th>Operation</th>
					  <th>User</th>
					  <th style="width: 280px;">New Details</th>
					  <th>Status</th>
                    </tr>
                  </thead>
				  
					<tbody id="tblviewlogs">

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
    tableId: "#tblviewlogs",
    url: "backend/bk_logsmanagement.php",
    request: { request: "viewlogs" },
  });
  
$(document).ready(function() {

    $.ajax({
        url: 'backend/bk_macroloader.php',
        type: 'POST',
        data: {
            request: 'loadmacros',
			datavalue: 12
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