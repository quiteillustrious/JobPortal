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
          <h1>Summary Profile of Applicants</h1>
        </div>
      </div>
    </div>
  </section>

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
            <div style="max-height:280px; overflow-y:auto;">
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

    <!-- ROW: Applicants Container -->
    <div class="row mt-3">
      <div class="col-12">
        <div id="applicants-container">
          <h5 class="text-success">Applicants will appear here</h5>
          <p>Select a publication and position to load applicants.</p>
        </div>
      </div>
    </div>

  </div>

  <script>
    DivLoader(
      'positionstableloader',
      'backend/bk_amdeliberation.php', {
        request: 'fetchpositions'
      }
    );

    $(document).off('click', '[id^="fetchposition_"]').on('click', '[id^="fetchposition_"]', function() {

      $('#publicationstableloader tr').removeClass('table-selected');
      $(this).addClass('table-selected');

      $.ajax({
        url: 'backend/bk_amdeliberation.php',
        method: "POST",
        data: {
          request: "fetchpositions",
          datavalue: $(this).data("datavalue")
        },

        beforeSend: function() {
          $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },

        success: function(response) {
          $("#loadingSpinner").fadeOut(200, function() {
            $("#loadingSpinner").css("display", "none");
          });

          $("#positionstableloader").html(response);
        }
      });

    });

    $(document).off('click', '[id^="fetchapplicants_"]').on('click', '[id^="fetchapplicants_"]', function() {

      $('#positionstableloader tr').removeClass('table-selected');
      $(this).addClass('table-selected');

      $.ajax({
        url: 'backend/bk_amdeliberation.php',
        method: "POST",
        data: {
          request: "fetchapplicants",
          datavalue: $(this).data("datavalue"),
          userid: UserInfo["UserID"],
          rid: UserInfo["RID"]
        },

        beforeSend: function() {
          $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
        },

        success: function(response) {
          $("#loadingSpinner").fadeOut(200, function() {
            $("#loadingSpinner").css("display", "none");
          });

          $("#applicants-container").html(response);
        }
      });

    });

    $(document).off('click', '#delibdecision_submit').on('click', '#delibdecision_submit', function(e) {

      e.stopPropagation();

      var fetchdata = $(this);
      var datavalue = fetchdata.data("datavalue");

      Swal.fire({
        title: "Submit decisions?",
        text: "This will save all deliberation decisions of this position.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, submit!",
        cancelButtonText: "Cancel",
        reverseButtons: true,
        scrollbarPadding: false
      }).then((result) => {

        if (result.isConfirmed) {

          let payload = [];

          $('.decision-radio:checked').each(function() {

            let fullId = $(this).attr('id');
            let snap_id = fullId.replace('decision_q_', '').replace('decision_dq_', '');

            let decision = $(this).val();

            let remarks = $("select[name='hrremarks_" + snap_id + "']").val() || '';

            let user_id = $(this).data('userid');

            payload.push({
              snap_id: snap_id,
              decision: decision,
              remarks: remarks,
              user_id: user_id
            });
          });

          //alert(JSON.stringify(payload, null, 2));

          $.ajax({
            url: "backend/bk_amdeliberation.php",
            method: "POST",
            data: {
              request: "save_delib_decision",
              payload: JSON.stringify(payload),
              datavalue: datavalue,
              userid: UserInfo["UserID"]
            },

            beforeSend: function() {
              $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
            },

            success: function(dataResult) {
              $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
              });

              let res;
              try {
                res = typeof dataResult === "object" ? dataResult : JSON.parse(dataResult);
              } catch (e) {
                console.error("Invalid JSON:", dataResult);
                Swal.fire("Error", "Invalid server response.", "error");
                return;
              }

              if (res.status === 'success') {
                Swal.fire({
                  title: "Saved!",
                  text: res.message,
                  icon: "success",
                  confirmButtonText: "OK",
                  scrollbarPadding: false
                });

                $.ajax({
                  url: 'backend/bk_amdeliberation.php',
                  method: "POST",
                  data: {
                    request: "fetchapplicants",
                    datavalue: datavalue,
                    userid: UserInfo["UserID"],
                    rid: UserInfo["RID"]
                  },

                  success: function(response) {
                    $("#applicants-container").html(response);
                  }
                });

              } else {
                let errorText = res.message;

                if (res.errors && res.errors.length > 0) {
                  errorText += "\n\n" + res.errors.join("\n");
                }

                Swal.fire({
                  title: "Oops!",
                  text: errorText,
                  icon: "error",
                  confirmButtonText: "I see!",
                  scrollbarPadding: false
                });
              }
            },

            error: function(xhr, status, error) {
              $("#loadingSpinner").fadeOut(200, function() {
                $("#loadingSpinner").css("display", "none");
              });
              console.error("Error occurred:", error);
            }
          });

        }
      });
    });
  </script>
