<?php
	include "../db/dbconnection.php";
	include "modals.php";
	include "../config/config.php";
?>
<!-- Content Header (Page header) -->
    <div class="content-header">
	
      <div class="container-fluid">
        <div class="row">

          <div class="col-sm-6">
            <h1>Query</h1>		
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
					Query from Database
              </div>

			  <div class="ml-5 mr-5 mt-2">
			  <div class="form-group">
				<label for="">Type Query:</label>
				<textarea type="text" class="form-control field-input" id="queryinput" placeholder="SELECT [Column Name] FROM [Table Name]"></textarea>
			  </div>
			  </div>

			  <div class="ml-5 mr-5">
				<button type="submit" 
						class="btn btn-success" 
						id="querybutton"
						data-backendurl="backend/bk_query.php"
						data-backendmethod="POST"
						data-backendrequest="executequery"
						data-backendtarget="#queryresults">
						Execute
				</button>
			  </div>

			  <hr>

			  <div class="ml-5 mr-5 mb-5">
				<label for="">Output:</label>
				<div id="queryresults">

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
			datavalue: 2025
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