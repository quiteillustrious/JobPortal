<?php
	include "../db/dbconnection.php";
	include "modals.php";
?>
<!-- Content Header (Page header) -->
    <div class="content-header">
	
      <div class="container-fluid">
        <div class="row">

          <div class="col-sm-6">
            <h1>Score Board Management</h1>		
			<ol class="breadcrumb float-sm-left mt-2">
				<button 
					type="button" 
					class="btn btn-success" 
					id="add_data"
					data-openmodal="#addeditmodal"
					data-openmodallabel="Add Category"
					data-openmodalbody="#addeditcontent"
					data-backendurl="backend/bk_hrsetscores.php"
					data-backendrequest="addlevel">
				  Add Category <i class="fa-solid fa-plus"></i>
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
                <h3 class="card-title">List of Education Levels</h3>

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
                      <th>Value</th>
					  <th>Status</th>
					  <th>Action</th>
                    </tr>
                  </thead>
				  
					<tbody id="tblvieweduclevels">

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
    tableId: "#tblvieweduclevels",
    url: "backend/bk_hrsetscores.php",
    request: { request: "vieweduclevels" },
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
//Add Modal
$(document).off('click', '#add_data2').on('click', '#add_data2', function(e) {

	var fetchdata = $(this);
		console.log(fetchdata);
		$.ajax({
			url:	fetchdata.data('backendurl'),
			method:	"POST",
			data:{
					request: fetchdata.data('backendrequest'),
				},

			beforeSend: function(xhr) {
				$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
			},

			success:function(dataResult){
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});

				$('#addeditlabel').html(fetchdata.data('openmodallabel'));
				$(fetchdata.data('openmodalbody')).html(dataResult);
				$(fetchdata.data('openmodal')).modal('show');

			},

			error: function(xhr, status, error) {
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});
				console.log("Error occurred:", error);
			}

		});
});
</script>