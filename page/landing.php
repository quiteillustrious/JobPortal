<?php
include 'landingmodal.php';
include 'modals.php';

?>
<html>
<head>
  
</head>
<body >
<div id="contentbody">
		<div >
			<div class="card-header bg-white">
			
				<div class="align-items-center d-flex " style="width: 100%;">
					
					   <img src="dist/img/tau-logo.png" alt="logo" width="35" class="float-left" >
					   <span style=" margin-left: 10px; width: 90%;">TAU JOB PORTAL</span>
					
					
						<button class=' float-right float-end badge badge-success p-2 flex-end' id="openloginmodal">Login / Sign Up</button>
					
				</div>
			</div>

		</div>
		<div class="container-fluid py-5">

		<div class="card border-0 shadow">

			<div class="card-body p-5">

				<div class="row align-items-center">

					<div class="col-lg-7">

						<span class="badge bg-success-subtle text-success mb-3 px-3 py-2">
							<i class="fas fa-seedling me-2"></i>
							Official Recruitment Portal
						</span>

						<h1 class="display-5 fw-bold text-dark">
							Welcome to the
							<span class="text-success">
								Tarlac Agricultural University
							</span>
							Job Portal
						</h1>

						<p class="lead text-secondary mt-4">
							Discover career opportunities, submit applications online,
							and become part of the TAU community through a
							transparent and efficient recruitment process.
						</p>

						<div class="d-flex mt-4">

							<button class=" m-1 btn btn-success btn-lg rounded-pill px-4"
									id="exploreJobs">
								<i class="fas fa-search me-2"></i>
								Explore Jobs
							</button>

							<button class=" m-1 btn btn-outline-success btn-lg rounded-pill px-4"
									id="openloginmodal">
								<i class="fas fa-user me-2"></i>
								Login
							</button>

						</div>

					</div>

					<div class="col-lg-5 text-center">

						<div class="hero-circle">

							<i class="fas fa-briefcase hero-icon"></i>

						</div>

					</div>

				</div>

			</div>

		</div>

	</div>
	<div id="landingidcontent" class="card-body"></div>
	
	<div class="container-fluid mb-5">

		<div class="row g-4">

			<div class="col-md-6 col-xl-3">

				<div class="card feature-card h-100">

					<div class="card-body text-center">

						<div class="feature-icon bg-success">
							<i class="fas fa-search"></i>
						</div>

						<h5 class="mt-3">
							Find Jobs
						</h5>
						
						<p class="text-muted">
							Browse available vacancies across the university.
						</p>

					</div>

				</div>

			</div>

			<div class="col-md-6 col-xl-3">

				<div class="card feature-card h-100">

					<div class="card-body text-center">

						<div class="feature-icon bg-primary">
							<i class="fas fa-file-upload"></i>
						</div>

						<h5 class="mt-3">
							Apply Online
						</h5>

						<p class="text-muted">
							Upload your documents securely.
						</p>

					</div>

				</div>

			</div>

			<div class="col-md-6 col-xl-3">

				<div class="card feature-card h-100">

					<div class="card-body text-center">

						<div class="feature-icon bg-warning">
							<i class="fas fa-user-check"></i>
						</div>

						<h5 class="mt-3">
							Qualified Hiring
						</h5>

						<p class="text-muted">
							Merit-based recruitment process.
						</p>

					</div>

				</div>

			</div>

			<div class="col-md-6 col-xl-3">

				<div class="card feature-card h-100">

					<div class="card-body text-center">

						<div class="feature-icon bg-danger">
							<i class="fas fa-users"></i>
						</div>

						<h5 class="mt-3">
							Join TAU
						</h5>

						<p class="text-muted">
							Build your future with us.
						</p>

					</div>

				</div>

			</div>

		</div>

	</div>
	
	<footer class="main-footer-item bg-white border-top mt-5">

    <div class="container-fluid py-4">

        <div class="row">

            <!-- University Info -->
            <div class="col-lg-5 mb-4 mb-lg-0">

                <div class="d-flex align-items-center mb-3">
                    <img src="dist/img/tau-logo.png" alt="TAU Logo" width="45">
                    <div class="ms-3">
                        <h5 class="fw-bold mb-0 text-success">
                            Tarlac Agricultural University
                        </h5>
                        <small class="text-muted">
                            Human Resource Management Office
                        </small>
                    </div>
                </div>

                <p class="text-muted mb-0">
                    The official recruitment portal of Tarlac Agricultural University.
                    Our mission is to provide a transparent, fair, and efficient hiring
                    process for qualified applicants.
                </p>

            </div>

            <!-- Quick Links -->
            <div class="col-md-3 col-lg-2">

                <h6 class="fw-bold text-success mb-3">
                    Quick Links
                </h6>

                <ul class="list-unstyled">

                    <li class="mb-2">
                        <a href="#" class="text-decoration-none text-muted">
                            <i class="fas fa-angle-right me-2"></i>
                            Home
                        </a>
                    </li>

                    <li class="mb-2">
                        <a href="#landingidcontent" class="text-decoration-none text-muted">
                            <i class="fas fa-angle-right me-2"></i>
                            Job Vacancies
                        </a>
                    </li>

                    <li class="mb-2">
                        <a href="#" class="text-decoration-none text-muted">
                            <i class="fas fa-angle-right me-2"></i>
                            Login
                        </a>
                    </li>

                </ul>

            </div>

            <!-- Contact -->
            <div class="col-md-4 col-lg-5 d-flex">
				<div style="width: 50%;">
					<h6 class="fw-bold text-success mb-3">
						Contact Information
					</h6>

					<p class="mb-2 text-muted">
						<i class="fas fa-map-marker-alt text-success me-2"></i>
						Camiling, Tarlac, Philippines
					</p>

					<p class="mb-2 text-muted">
						<i class="fas fa-envelope text-success me-2"></i>
						hrmo@tau.edu.ph
					</p>

					<p class="mb-3 text-muted">
						<i class="fas fa-phone text-success me-2"></i>
						(+63) XXX-XXX-XXXX
					</p>
				</div>
				
				<div>

						<a href="#" class="btn btn-outline-success btn-sm rounded-circle me-2">
							<i class="fab fa-facebook-f"></i>
						</a>

						<a href="#" class="btn btn-outline-success btn-sm rounded-circle me-2">
							<i class="fas fa-globe"></i>
						</a>

						<a href="#" class="btn btn-outline-success btn-sm rounded-circle">
							<i class="fas fa-envelope"></i>
						</a>

				</div>

            </div>

        </div>

        <hr>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">

            <small class="text-muted">
                © <?= date('Y'); ?> Tarlac Agricultural University. All Rights Reserved.
            </small>

            <small class="text-muted">
                Developed by the Planning and Development Office / Management Information Systems Office (MIS)
            </small>

        </div>

    </div>

</footer>

</div>


</body>


</html>

<script>

function initializeJobSlider(){

    let track = $(".job-slider-track");

    if(track.length === 0){
        return;
    }

    let items = $(".job-slide-item");

    let position = 0;

    function slideWidth(){
        return $(".job-slide-item").outerWidth(true);
    }

    function nextSlide(){
        position++;
        if(position > items.length - 3){
            position = 0;
        }

        track.css(
            "transform",
            "translateX(-" + 
            (position * slideWidth()) +
            "px)"
        );
    }

    function prevSlide(){
        position--;
        if(position < 0){

            position = items.length - 3;

        }
        track.css(
            "transform",
            "translateX(-" + 
            (position * slideWidth()) +
            "px)"
        );
    }

    $(".job-slider-btn.next").off().on("click",function(){
        nextSlide();
    });
	
    $(".job-slider-btn.prev").off().on("click",function(){
        prevSlide();
    });

    setInterval(function(){
        nextSlide();
    },4000);


}

$(document).ready( function (){
	
	$.ajax({
		method:"POST",
		url: "backend/bk_homepage.php",
		data: {request: "landingjobs", rid: UserInfo["RID"] },
		success: function (response){
			$("#landingidcontent").html(response);
			initializeJobSlider();
		},
		error: function (){
			Swal.fire({
				toast: true,
				position: "top-end",
				icon: "error",
				title: "Error for postings",
				showConfirmButton: false,
				timer: 3000
			});
		},
	});
	
	$(document).off("click", "#openloginmodal").on("click", "#openloginmodal", function(){
		
		$.ajax({
			method:"GET",
			url:"page/login.php",
			success: function (result){
				$("#landingmodal").modal("show");
				$("#landingmodalcontent").html(result);
			},
			error: function (){
				Swal.fire({
						toast: true,
						position: "top-end",
						icon: "error",
						title: "Error for postings",
						showConfirmButton: false,
						timer: 3000
				});
			},
		});
		
	});
	
	$("#exploreJobs").click(function(){
		$('html,body').animate({
			scrollTop: $("#landingidcontent").offset().top-20
		},700);
	});
	$(function(){
		setTimeout(function(){
			Swal.fire({
				toast:true,
				position:'top-end',
				icon:'info',
				title:'Welcome to the TAU Job Portal',
				showConfirmButton:false,
				timer:2500
			});
		},600);
	});
});
</script>