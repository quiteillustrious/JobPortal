<?php
	include "../db/dbconnection.php";
	include "modals.php";
?>
<!-- Content Header (Page header) -->
    <div class="content-header">
	
      <div class="container-fluid">
        <div class="row">

          <div class="col-sm-6">
            <h1>Application Status</h1>		
          </div>
		  
        </div>
      </div>
    </div>
	
<div class="row ml-3" id="infomacros">
	
</div>

    <div id="applicationstatusloader" class="ml-3 mr-3">
    </div>

	
<script>
$(document).ready(function() {

	DivLoader(
		'applicationstatusloader',
		'backend/bk_applicationstatus.php',
		{ request: 'viewapplicationstatus', userid: UserInfo['UserID'] }
	);

});

/*
$(document).ready(function() {

    $.ajax({
        url: 'backend/bk_macroloader.php',
        type: 'POST',
        data: {
            request: 'loadmacros',
			datavalue: 2
        },

        success: function(dataResult) {
            $('#infomacros').html(dataResult);
        },
		
        error: function(xhr, status, error) {

            console.error('AJAX Error:', error);
        }
    });
});	
*/
</script>