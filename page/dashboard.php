<?php
include "../db/dbconnection.php";
include "modals.php";
include "../config/config.php";

$EmailAddress = isset($_POST["EmailAddress"]) ? $_POST["EmailAddress"] : "";
$RID = isset($_POST["RID"]) ? $_POST["RID"] : "";
$Name = isset($_POST["Name"]) ? $_POST["Name"] : "";
$Office = isset($_POST["Office_id"]) ? $_POST["Office_id"] : "";
$LastName = isset($_POST["LastName"]) ? $_POST["LastName"] : "";
$FirstName = isset($_POST["FirstName"]) ? $_POST["FirstName"] : "";
$UserID = isset($_POST["UserID"]) ? $_POST["UserID"] : "";
?>


<!-- Announcement Pop-up -->
<div id="announcementpop">

</div>

<!-- Notif Drop Modal -->
<div class="modal fade" id="notifdrop" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-xl" style="min-width:90%">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title text-danger font-weight-bold" id="exampleModalLabel">Notifications</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div id="notifshow">

			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<body class="hold-transition sidebar-mini layout-fixed">
	<div class="wrapper">

		<!-- Navbar -->
		<nav class="main-header navbar navbar-expand navbar-light" id="navbardarkmode">
			<!-- Left navbar links -->
			<ul class="navbar-nav">
				<li class="nav-item">
					<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
				</li>
				<!-- <li class="nav-item d-none d-sm-inline-block">
					<h3>Home</h3>
				</li> -->
				<!-- <li class="nav-item d-none d-sm-inline-block">
					<a href="#" class="nav-link">Contact</a>
				</li> -->
			</ul>

			<!-- Right navbar links -->
			<ul class="navbar-nav ml-auto">
				<!-- Navbar Search -->

				<!-- To-Do
				<li class="nav-item">
					<button class="btn" id="todotask">
						<i class="nav-icon fas fa-solid fa-list-check fa-2x"></i>
						<span class="badge badge-warning badge-secondary" id="">0</span>
					</button>
				</li>
				-->

				<!-- Notifications Dropdown Menu -->
				<li class="nav-item ml-1 mr-1 notif-wrapper">
					<button class="btn notif-btn position-relative" id="notifsee">

						<i class="nav-icon far fa-bell fa-lg text-danger notif-icon"></i>

						<audio id="notif-sound" src="dist/sound/notification-sound.wav"></audio>

						<span class="badge badge-warning notif-badge" id="notifcount"></span>

					</button>
				</li>

			</ul>
			<!-- Start Dark Mode -->
			<div class="custom-control custom-switch">
				<input type="checkbox" class="custom-control-input" id="customSwitch1">
				<label class="custom-control-label" for="customSwitch1">Dark Mode</label>
			</div>
			<!-- End Dark Mode-->

		</nav>
		<!-- /.navbar -->

		<?php include '../page/loading.php' ?>
		<?php include 'sidebar.php'; ?>
		<!-- Content Wrapper. Contains page content -->
		<div class="content-wrapper" id='mainContent'>

			<?php include "homemain.php"; ?>
			<!--
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Dashboard</h1>
						<? //php  echo "Hello ". $FirstName;
						?>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="#">Home</a></li>
							<li class="breadcrumb-item active">Dashboard v1</li>
						</ol>
					</div>
				</div>

				<div id="searchcontent">
				</div>

			</div>
		</div>
		-->

		</div>
		<!-- /.content-wrapper -->

		<footer class="main-footer">
			<strong><?php echo $cpy; ?>.</strong>

			<div class="float-right d-none d-sm-inline-block">
				<b>Version</b> <?php echo $vrs; ?>
			</div>
		</footer>

		<!-- /.control-sidebar -->
	</div>
	<!-- ./wrapper -->
