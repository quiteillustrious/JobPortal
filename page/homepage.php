<?php
date_default_timezone_set('Asia/Manila');
?>

<style>
  /* Silky smooth card hover effect */
  .job-card {
    border-radius: 12px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }

  .job-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
  }

  /* Card header gradient */
  .job-card .card-header {
    background: linear-gradient(135deg, #28a745, #218838);
    color: #fff;
    font-weight: 600;
    font-size: 1.1rem;
  }

  /* Job badges */
  .job-badge {
    display: inline-block;
    padding: 0.25rem 0.6rem;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 0.5rem;
    margin-right: 0.3rem;
    text-transform: uppercase;
  }

  /* Smooth fade-in animation */
  .fade-in {
    opacity: 0;
    animation: fadeIn 0.5s forwards;
  }

  @keyframes fadeIn {
    to {
      opacity: 1;
    }
  }

  /* Card body spacing */
  .job-card .card-body p {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
  }

  /* View Job button */
  .job-card .btn {
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .job-card .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
  }

  /* Optional company logo */
  .job-logo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 0.75rem;
    border: 2px solid #eee;
  }

  /* Responsive tweaks */
  @media (max-width: 576px) {
    .job-card {
      margin-bottom: 1rem;
    }
  }

  .fade-in-message {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.8s forwards;
    animation-delay: 0.3s;
  }

  @keyframes fadeInUp {
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>

<div class="ml-2 mr-2 pt-2">
  <!-- Header Row -->

  <!-- Content Wrapper -->
  <div class="content">
    <div class="container-fluid">

      <div class="d-flex align-items-center font-weight-bold mb-3">
        <i class="fas fa-briefcase text-success fa-2x mr-2"></i>
        <span class="h4 mb-0">TAU Job Portal v1.00</span>
      </div>

      <div class="mb-3 d-flex justify-content-start">
        <div style="position: relative; max-width: 300px; width: 100%;">

          <input
            type="text"
            id="jobSearch"
            class="form-control"
            placeholder="Search Vacant Positions..."
            style="padding-right: 35px; border: 1px solid #28a745; box-shadow: none;">

          <i class="fas fa-search"
            style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); color: #6c757d;">
          </i>

        </div>
      </div>

      <div id="noResultsRow" style="display:none;" class="ml-1">
        <span colspan="6" class="text-center text-danger font-weight-bold">No matching results found.</span>
      </div>

      <div id="jobsTableWrapper">
        <div class="row" id="jobscontainer">

        </div>
      </div>
    </div>
  </div>
</div>

<script>
  DivLoader(
    'jobscontainer',
    'backend/bk_homepage.php', {
      request: 'fetchjobs',
      rid: UserInfo["RID"]
    }
  );

  $(document).off('click', '[id^="fetchapplicants_"]').on('click', '[id^="fetchapplicants_"]', function() {

    var fetchdata = $(this);

    $.ajax({
      url: 'backend/bk_homepage.php',
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

        $('#xlmodallabel').html(fetchdata.data('openmodallabel'));
        $('#xlmodalcontent').html(response);
        $('#xlmodal').modal('show');
      }
    });

  });

  //Apply for Position
  $(document).off('click', '[id^="job_apply_"]').on('click', '[id^="job_apply_"]', function(e) {

    e.stopPropagation();

    var fetchdata = $(this);

    let now = new Date();

    let formattedDateTime = new Intl.DateTimeFormat('en-PH', {
      timeZone: 'Asia/Manila',
      year: 'numeric',
      month: 'long',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: true
    }).format(now);

    Swal.fire({
      title: "Apply for this position?",
      text: `Your details and file attachments as of ${formattedDateTime} will be submitted. Any modifications to your profile after this point will not be carried over.`,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Yes, apply",
      cancelButtonText: "Cancel",
      reverseButtons: true,
      scrollbarPadding: false
    }).then((result) => {

      if (result.isConfirmed) {

        $.ajax({
          url: fetchdata.data("backendurl"),
          method: "POST",
          data: {
            request: fetchdata.data("backendrequest"),
            datavalue: fetchdata.data("datavalue"),
            userid: UserInfo["UserID"]
          },

          beforeSend: function() {
            $("#loadingSpinner").css("display", "flex").hide().fadeIn(200);
          },
          dataType: "json",

          success: function(dataResult) {

            $("#loadingSpinner").fadeOut(200, function() {
              $("#loadingSpinner").css("display", "none");
            });

            if (dataResult.status === 'success') {

              Swal.fire({
                title: "Applied!",
                text: dataResult.message || "Your application has been submitted.",
                icon: "success",
                confirmButtonText: "OK",
                scrollbarPadding: false
              });

              $(fetchdata.data("openmodal")).modal("hide");

              DivLoader(
                'jobscontainer',
                'backend/bk_homepage.php', {
                  request: 'fetchjobs'
                }
              );

            } else if (dataResult.status === 'missing') {

              Swal.fire({
                title: "Profile Incomplete",
                html: `
                                ${dataResult.message}<br><br>
                                ${(dataResult.missing || []).map(x => `• ${x}`).join("<br>")}
                            `,
                icon: "warning",
                confirmButtonText: "OK",
                scrollbarPadding: false
              });

            } else {

              Swal.fire({
                title: "Oops!",
                text: dataResult.message || "Something went wrong.",
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

            console.log("RAW RESPONSE:", xhr.responseText);
            console.log("STATUS:", status);
            console.log("ERROR:", error);

            Swal.fire({
              title: "Error!",
              text: "Server error occurred.",
              icon: "error"
            });
          }
        });

      }
    });
  });
  //Apply for Position -- End

  $(document).ready(function() {

    $("#jobSearch").on("keyup", function() {
      var value = $(this).val().toLowerCase();
      var visibleCount = 0;

      $("#jobscontainer table tbody tr").not("#noResultsRow").each(function() {
        var isVisible = $(this).text().toLowerCase().indexOf(value) > -1;
        $(this).toggle(isVisible);

        if (isVisible) visibleCount++;
      });

      if (visibleCount === 0) {
        $("#noResultsRow").show();
        $("#jobsTableWrapper table thead").hide();
      } else {
        $("#noResultsRow").hide();
        $("#jobsTableWrapper table thead").show();
      }

    });

  });
</script>
