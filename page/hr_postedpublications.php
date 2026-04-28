<?php 
	date_default_timezone_set('Asia/Manila');
	require "../db/dbconnection.php";
	include "modals.php";

?>
<style>
.position-clickable {
    cursor: pointer;
    transition: background 0.2s;
}

.position-clickable:hover {
    background-color: #c3c3c3;
}
</style>
  <!-- Page Header -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h1>Posted Publications</h1>
        </div>
      </div>
    </div>
  </section>

  <!-- Main content -->
  <section class="content">
    <div id="listofpublications" class="row ml-1 mr-1">

    </div>
  </section>


<script>
function loadPublications() {

    $.ajax({
        url: 'backend/bk_hrpostedpublications.php',
        method: 'POST',
        data: {
            request: 'viewlistofpublications',
            userid: UserInfo['UserID']
        },
        dataType: 'json',
        success: function (data) {

            let html = '';

            data.forEach(pub => {

                let positionsHtml = '';

                if (pub.positions && pub.positions.length > 0) {

                    pub.positions.forEach(pos => {
                        positionsHtml += `
                        <div class="border rounded p-2 mb-1 d-flex justify-content-between align-items-center position-clickable"
                            id="view_position_${pos.position_id}"
                            data-datavalue="${pos.position_id}"
                            data-backendurl="backend/bk_hrpublications.php"
                            data-backendrequest="viewpositiondetails"
                            data-openmodal="#attachmentmodal"
                            data-openmodallabel="View Position - ${pos.position_name}"
                            data-openmodalbody="#attachmentmodalcontent"
                            data-tooltip="View Position"
                            >

                            <div>
                                ${pos.position_name}
                            </div>

                            <div class="d-flex gap-1">
                            <!--
                                <button class="btn btn-sm btn-warning mr-1"
                                        type="button"
                                        id="edit_position_${pos.position_id}"
                                        data-datavalue="${pos.position_id}"
                                        data-backendurl="backend/bk_hrpublications.php"
                                        data-backendrequest="editposition"
                                        data-openmodal="#addeditmodal"
                                        data-openmodallabel="Edit Position"
                                        data-openmodalbody="#addeditcontent"
                                        data-tooltip="Edit Position">
                                    <i class="fas fa-edit"></i>
                                </button>
                            -->

                                <button class="btn btn-sm btn-danger"
                                        type="button"
                                        id="delete_position_${pos.position_id}"
                                        data-datavalue="${pos.position_id}"
                                        data-backendurl="backend/bk_hrpublications.php"
                                        data-backendrequest="deleteposition"
                                        data-tooltip="Delete Position">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                        </div>
                        `;
                    });

                } else {

                    positionsHtml = `
                        <div class="text-danger text-center font-weight-bold">
                            No Positions yet. Click 'Add Positions'
                        </div>
                    `;
                }

                html += `
                <div class="col-md-4 d-flex">
                    <div class="card card-outline card-success flex-fill d-flex flex-column">

                        <div class="card-header d-flex align-items-start">
                            <div class="card-title flex-grow-1 mb-0">
                                <span class="badge badge-${pub.color_desc} mr-1 p-2">
                                    ${pub.pubstatus_desc}
                                </span>
                                <strong>${pub.pubtitle_name}</strong><br>
                                <small>${pub.pubtitle_startdt} — ${pub.pubtitle_enddt}</small>
                            </div>

                            <div class="card-tools d-flex ml-2">
                                <button class="btn btn-warning btn-sm mr-1"
                                        id="edit_publication"
                                        data-datavalue="${pub.publication_id}"
                                        data-backendurl="backend/bk_hrpublications.php"
                                        data-backendrequest="editpublication"
                                        data-openmodal="#addeditmodal"
                                        data-openmodallabel="Edit Publication"
                                        data-openmodalbody="#addeditcontent"
                                        data-tooltip="Edit Publication"
                                        >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger 
                                        btn-sm"
                                        id="delete_publication"
                                        data-datavalue="${pub.publication_id}"
                                        data-backendurl="backend/bk_hrpublications.php"
                                        data-backendrequest="deletepublication"
                                        data-tooltip="Delete Publication"
                                        >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body flex-grow-1">
                            ${positionsHtml}
                        </div>

                        <div class="card-footer text-center">
                            <button class="btn btn-sm btn-success add-position" 
                                    id="add_position"
                                    data-datavalue="${pub.publication_id}"
                                    data-backendurl="backend/bk_hrpublications.php"
                                    data-backendrequest="addposition"
                                    data-openmodal="#addeditmodal"
                                    data-openmodallabel="Add Position"
                                    data-openmodalbody="#addeditcontent"
                                    >
                                <i class="fas fa-plus"></i> Add Positions
                            </button>
                            <button class="btn btn-sm btn-warning add-position" 
                                    id="cancel_publication"
                                    data-datavalue="${pub.publication_id}"
                                    data-backendurl="backend/bk_hrpostedpublications.php"
                                    data-backendrequest="cancelpublication"
                                    >
                                <i class="fa-solid fa-newspaper"></i> Cancel Publication
                            </button>
                        </div>

                    </div>
                </div>
                `;
            });

            $('#listofpublications').html(html);
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
            console.log("Response Text:", xhr.responseText);
        }
    });
}

$(document).ready(function () {
    loadPublications();
});

$(document).off('click', '#save_datapublication').on('click', '#save_datapublication', function(e) {

	var fetchdata = $(this);

	var fields = {};

	for (let i = 1; i <= $('.field-input').length; i++) {
		let value = $(`#field${i}`).val();
		fields[`field${i}`] = value;
	}

		$.ajax({
			url:	fetchdata.data('backendurl'),
			method:	"POST",
			data:{
					fields: fields,
					datavalue: fetchdata.data('datavalue'),
					operator: fetchdata.data('operator'),
					logslocation: fetchdata.data('logslocation'),
					request: fetchdata.data('backendrequest'),
					userid: UserInfo['UserID']
				},
            dataType: "json",
			beforeSend: function(xhr) {
				$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
			},

			success:function(dataResult){
				if(dataResult.status == 'success'){			
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});
					
						Swal.fire({
						  title: "Nice!",
						  text: dataResult.message,
						  icon: "success",
						  confirmButtonText: "Cool!",
						  scrollbarPadding: false
						})
					
					$(fetchdata.data('openmodal')).modal('hide');
                    loadPublications();
				} 
				  
				else if (dataResult.status == 'error'){
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});
						Swal.fire({
						  title: "Nope!",
						  text: dataResult.message,
						  icon: "error",
						  confirmButtonText: "I see!",
						  scrollbarPadding: false
						})		
				}

			},

			error: function(xhr, status, error) {
				$("#loadingSpinner").fadeOut(200, function() {
					$("#loadingSpinner").css("display", "none");
				});
				console.log("Error occurred:", error);
			}

		});
});

$(document).off('click', '#delete_publication').on('click', '#delete_publication', function(e) {

    e.preventDefault();

    var fetchdata = $(this);

    Swal.fire({
        title: "Are you sure?",
        text: "This publication will be permanently deleted.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        scrollbarPadding: false,
        reverseButtons: true
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: fetchdata.data('backendurl'),
                method: "POST",
                data: {
                    datavalue: fetchdata.data('datavalue'),
                    request: fetchdata.data('backendrequest'),
                    userid: UserInfo['UserID']
                },

                dataType: "json",

                beforeSend: function() {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },

                success: function(dataResult) {

                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });

                    if (dataResult.status === 'success') {

                        Swal.fire({
                            title: "Deleted!",
                            text: dataResult.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });

                        loadPublications();

                    } else {

                        Swal.fire({
                            title: "Error!",
                            text: dataResult.message,
                            icon: "error",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });
                    }
                },

                error: function(xhr, status, error) {
                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });

                    console.log("Error occurred:", error);

                    Swal.fire({
                        title: "Oops!",
                        text: "Something went wrong.",
                        icon: "error"
                    });
                }
            });

        }
    });
});

$(document).off('click', '#edit_publication').on('click', '#edit_publication', function(e) {
	
	var fetchdata = $(this);

	$.ajax({
			url:	fetchdata.data('backendurl'),
			method:	"POST",
			data:{
					datavalue: fetchdata.data('datavalue'),
					request: fetchdata.data('backendrequest'),
				},
			beforeSend: function(xhr) {
				$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
			},

			success:function(dataResult){
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});
					$(fetchdata.data('openmodalbody')).html(dataResult);
					$('#addeditlabel').html(fetchdata.data('openmodallabel'));
					$(fetchdata.data('openmodal')).modal('show');
			}
	});
});

$(document).off('click', '#add_position').on('click', '#add_position', function(e) {
	
	var fetchdata = $(this);

	$.ajax({
			url:	fetchdata.data('backendurl'),
			method:	"POST",
			data:{
					datavalue: fetchdata.data('datavalue'),
					request: fetchdata.data('backendrequest'),
				},
			beforeSend: function(xhr) {
				$("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
			},

			success:function(dataResult){
					$("#loadingSpinner").fadeOut(200, function() {
						$("#loadingSpinner").css("display", "none");
					});
					$(fetchdata.data('openmodalbody')).html(dataResult);
					$('#addeditlabel').html(fetchdata.data('openmodallabel'));
					$(fetchdata.data('openmodal')).modal('show');
			}
	});
});

$(document).off('click', '#save_dataposition').on('click', '#save_dataposition', function(e) {

    var fetchdata = $(this);

    var position_title = $('#position_title').val();
    var appoint_id = $('#appoint_id').val();
    var sg_id = $('#sg_id').val();
    var office_id = $('#office_id').val();
    var educ_qual = $('#educ_qual').val();
    var exp = $('#exp').val();
    var training = $('#training').val();
    var eligibility_id = $('#eligibility_id').val();
    var competencies = $('#competencies_hidden').val();

    Swal.fire({
        title: "Are you sure?",
        text: "Have you reviewed everything before saving?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, save it!",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        scrollbarPadding: false
    }).then((result) => {
        if (result.isConfirmed) {

            $.ajax({
                url: "backend/bk_hrpublications.php",
                method: "POST",
                data: {
                    position_title: position_title,
                    appoint_id: appoint_id,
                    sg_id: sg_id,
                    office_id: office_id,
                    educ_qual: educ_qual,
                    exp: exp,
                    training: training,
                    eligibility_id: eligibility_id,
                    competencies: competencies,
                    datavalue: fetchdata.data('datavalue'),
                    request: "saveposition",
                    userid: UserInfo['UserID']
                },
                dataType: "json",
                beforeSend: function(xhr) {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },
                success: function(dataResult) {
                    $("#loadingSpinner").fadeOut(200, function() {
                        $(this).css("display", "none");
                    });

                    if (dataResult.status == 'success') {
                        Swal.fire({
                            title: "Nice!",
                            text: dataResult.message,
                            icon: "success",
                            confirmButtonText: "Cool!",
                            scrollbarPadding: false
                        });
                        $("#addeditmodal").modal("hide");
                        loadPublications();
                    } else if (dataResult.status == 'error') {
                        Swal.fire({
                            title: "Nope!",
                            text: dataResult.message,
                            icon: "error",
                            confirmButtonText: "I see!",
                            scrollbarPadding: false
                        });
                    }
                },
                error: function(xhr, status, error) {
                    $("#loadingSpinner").fadeOut(200, function() {
                        $(this).css("display", "none");
                    });
                    console.group("AJAX Error Debug");
                    console.log("Status:", status);
                    console.log("HTTP Status:", xhr.status);
                    console.log("Error thrown:", error);
                    console.log("Response text:", xhr.responseText);
                    console.groupEnd();
                }
            });

        }
    });

});

$(document).off('click', '#cancel_publication').on('click', '#cancel_publication', function(e) {

    e.preventDefault();

    var fetchdata = $(this);

    Swal.fire({
        title: "Are you sure?",
        text: "This publication will be cancelled.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#f0ad4e",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, cancel it!",
        cancelButtonText: "Back",
        scrollbarPadding: false,
        reverseButtons: true
    }).then((result) => {

        if (result.isConfirmed) {

            $.ajax({
                url: fetchdata.data('backendurl'),
                method: "POST",
                data: {
                    datavalue: fetchdata.data('datavalue'),
                    request: fetchdata.data('backendrequest'),
                    userid: UserInfo['UserID']
                },

                dataType: "json",

                beforeSend: function() {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },

                success: function(dataResult) {

                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });

                    if (dataResult.status === 'success') {

                        Swal.fire({
                            title: "Cancelled!",
                            text: dataResult.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });

                        loadPublications();

                    } else {

                        Swal.fire({
                            title: "Error!",
                            text: dataResult.message,
                            icon: "error",
                            confirmButtonText: "OK",
                            scrollbarPadding: false
                        });
                    }
                },

                error: function(xhr, status, error) {

                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });

                    console.log("Error occurred:", error);

                    Swal.fire({
                        title: "Oops!",
                        text: "Something went wrong.",
                        icon: "error"
                    });
                }
            });

        }
    });
});
</script>