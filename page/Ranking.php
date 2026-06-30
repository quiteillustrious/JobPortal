<?php
date_default_timezone_set('Asia/Manila');
include "modals.php";
?>

<style>
    .theme-green .card-header {
        background-color: #28a745;
        color: white;
    }

    .table-hover tbody tr:hover {
        background-color: #e6f4ea;
        cursor: pointer;
    }

    #applicants-container {
        min-height: 200px;
        border: 2px dashed #28a745;
        border-radius: 8px;
        padding: 15px;
        background: #f8fff9;
    }

    .table-hover tbody tr {
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }

    .table-hover tbody tr.table-selected {
        background-color: #c3e6cb !important;
        box-shadow: inset 4px 0 0 #28a745;
    }

    .table-hover tbody tr:hover:not(.table-selected) {
        background-color: #e6f4ea;
    }

    .content-header {
        padding-bottom: 5px !important;
        margin-bottom: 0 !important;
    }

    .content-header h1 {
        margin-bottom: 0 !important;
    }


    .checklist-toggle {
        cursor: pointer;
        transition: transform 0.15s ease-in-out;
    }

    .checklist-toggle:hover {
        transform: scale(1.1);
    }
</style>
</head>

<body class="hold-transition sidebar-mini theme-green">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h1>Ranking of Applicants</h1>
                </div>
            </div>
        </div>
    </section>

    <div id="infomacros" class='ml-3 mt-1'>
    </div>

    <div class="wrapper ml-3 mr-3">

        <!-- ROW: Two Tables -->
        <div class="">

            <!-- Positions Table -->
            <div class="">
                <div class="card border border-success">
                    <div class="card-header bg-success">
                        <h5 class="mb-0">Positions</h5>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <div style="max-height:380px; overflow-y:auto;">
                            <table class="table table-hover mb-0">
                                <thead class="table-success" style="position:sticky; top:0; z-index:2;">
                                    <tr>
                                        <th>Position</th>
                                        <th>Office Assignment</th>
                                        <th>Publication Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="positionstableloader">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script>
	
	var currentPosId = "";
        DivLoader(
            'positionstableloader',
            'backend/bk_Ranking.php', {
                request: 'fetchpositions',
                RID: UserInfo["RID"]
            }
        );



	$(document).off('click', '[id^="fetchapplicants_"]').on('click', '[id^="fetchapplicants_"]', function(e) {
	
			e.stopPropagation();

			currentPosId = $(this).data("datavalue");	
			currenttitle = $(this).data("title");	
            $('#positionstableloader tr').removeClass('table-selected');
            $(this).addClass('table-selected');

            $.ajax({
              url: 'backend/bk_Ranking.php',
                method: "POST",
                data: {
                    request: "fetchapplicants",
                    datavalue: $(this).data("datavalue"),
                    UserID: UserInfo["UserID"],
                    RID: UserInfo["RID"],
                    title: currenttitle,
                },
                beforeSend: function() {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },
                success: function(dataResult) {
					//console.log(dataResult);
                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });

                    $('#attachmentmodallabel').html(currenttitle);
                    $('#attachmentmodalcontent').html(dataResult);
                    $('#attachmentmodal').modal('show');
                },
                error: function(xhr, status, error) {
                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });
                    console.error("Error occurred:", error);
                }
            });
        });      

		$(document).off('click', '[id^="backList"]').on('click', '[id^="backList"]', function(e) {
	
			e.stopPropagation();

			currentPosId = $(this).data("datavalue");	
			currenttitle = $(this).data("title");	
            $('#positionstableloader tr').removeClass('table-selected');
            $(this).addClass('table-selected');

            $.ajax({
              url: 'backend/bk_Ranking.php',
                method: "POST",
                data: {
                    request: "fetchapplicants",
                    datavalue: $(this).data("datavalue"),
                    UserID: UserInfo["UserID"],
                    RID: UserInfo["RID"],
					 title: currenttitle,
                },
                beforeSend: function() {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },
                success: function(dataResult) {
					//console.log(dataResult);
                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });

                    $('#attachmentmodallabel').html(currenttitle);
                    $('#attachmentmodalcontent').html(dataResult);
                    $('#attachmentmodal').modal('show');
                },
                error: function(xhr, status, error) {
                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });
                    console.error("Error occurred:", error);
                }
            });
        });

       $(document).off('click', '[id^="attachmentreviewer_"]').on('click', '[id^="attachmentreviewer_"]', function(e) {

            e.stopPropagation();

            var fetchdata = $(this);
			
            $.ajax({
                url: "backend/bk_Ranking.php",
                method: "POST",
                data: {
                    request: "attachmentreviewer",
                    datavalue: fetchdata.data("datavalue"),
                    pubposid: fetchdata.data("pubposid"),
                    title: fetchdata.data("title"),
                },
                beforeSend: function() {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },
                success: function(dataResult) {
                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });

                    $('#attachmentmodallabel').html(fetchdata.data('openmodallabel'));
                    $('#attachmentmodalcontent').html(dataResult);
                    $('#attachmentmodal').modal('show');
                },
                error: function(xhr, status, error) {
                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });
                    console.error("Error occurred:", error);
                }
            });
        });     


		$(document).off('click', '[id^="SetCummulate"]').on('click', '[id^="SetCummulate"]', function(e) {

            e.stopPropagation();

            var fetchdata = $(this);
			
            $.ajax({
                url: "backend/bk_Ranking.php",
                method: "POST",
                data: {
                    request: "SetCummulate",
                    datavalue: fetchdata.data("datavalue"),
                    pubposid: fetchdata.data("pubposid"),
                    title: fetchdata.data("title"),
                },
                beforeSend: function() {
                    $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
                },
                success: function(dataResult) {
                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });

                    $('#attachmentmodallabel').html(fetchdata.data('openmodallabel'));
                    $('#attachmentmodalcontent').html(dataResult);
                    $('#attachmentmodal').modal('show');
                },
                error: function(xhr, status, error) {
                    $("#loadingSpinner").fadeOut(200, function() {
                        $("#loadingSpinner").css("display", "none");
                    });
                    console.error("Error occurred:", error);
                }
            });
        });

	function getFormData(formSelector) {
		let data = {};
		$(formSelector).find("input, select, textarea").each(function () {
			const id = $(this).attr("id");
			if (id) {
				data[id] = $(this).val();
			}
		});
		return data;
	}
  </script>
