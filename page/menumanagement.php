<?php
	include "../db/dbconnection.php";
	include "modals.php";
?>
<div class="content-header">

  <div class="container-fluid">
	<div class="row">
	
	  <div class="col-sm-6">
		<h1 class="m-0">Menus Management</h1>
		<ol class="breadcrumb float-sm-left mt-2">
			<button 
				type="button" 
				class="btn btn-success" 
				id="add_data"
				data-openmodal="#addeditmodal"
				data-openmodallabel="Add Menu"
				data-openmodalbody="#addeditcontent"
				data-backendurl="backend/bk_menumanagement.php"
				data-backendrequest="addmenu">
			  Add Menu <i class="fa-solid fa-plus"></i>
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
            <div class="card" style="border:2px solid black">
              <div class="card-header">
                <h3 class="card-title">List of Menus</h3>

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
                      <th>Menu ID</th>
                      <th>Menu</th>
                      <th>MotherMenuID</th>
                      <th style="width: 280px;">Description</th>
					  <th>Menucode</th>
					  <th>MenuLink</th>
					  <th>Arrangement</th>
					  <th>Icon</th>
					  <th>Status</th>
					  <th>Action</th>

                    </tr>
                  </thead>
				  
						<tbody id="tblviewmenus">
						
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
    tableId: "#tblviewmenus",
    url: "backend/bk_menumanagement.php",
    request: { request: "viewmenus" }
  });
  
$(document).ready(function() {

    $.ajax({
        url: 'backend/bk_macroloader.php',
        type: 'POST',
        data: {
            request: 'loadmacros',
			datavalue: 5
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