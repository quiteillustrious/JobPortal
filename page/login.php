<?php 
	include "../config/config.php";
	include "modals.php";
	include "loading.php";
?>

<div class="d-flex justify-content-center align-items-center animated-bg" style="height: 100vh; background-image: linear-gradient(#80EF80, white);">
  <div class="login-box mx-2 my-5" style="max-width: 25rem; width: 100%;">
    <div class="card card-outline card-primary">
      <div class="card-header text-center" style="border: transparent;">
        <div class="text-center my-2">
          <img src="dist/img/tau-logo.png" alt="logo" width="100">
        </div>
        <h1><b>Welcome Back!</b></h1>
		<h3><b>TAU Job Portal</b></h3>
      </div>

      <div class="card-body" style="border: transparent;">
        <p class="login-box-msg">Log in to your account</p>

        <div class="input-group mb-3">
          <input type="email" class="form-control" placeholder="Username" id="lgtxtusername" value="admin">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fa-solid fa-circle-user"></span>
            </div>
          </div>
        </div>

        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Password" id="lgtxtpassword" value="systemsuperadmin">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-key"></span>
            </div>
          </div>
        </div>
		
        <div class="row">
		  <div class="col-8">
			<div class="icheck-primary">
			  <input type="checkbox" id="showPasswordCheckbox">
			  <label for="showPasswordCheckbox">
				Show Password
			  </label>
			</div>
		  </div>

		  <div class="col-4">
			<button type="submit" 
					class="btn btn-success btn-block" 
					style="border:2px solid black"
					id='btnLogin'>Log In</button>
		  </div>

		</div>
		
		<div class="text-center mt-3">
		  <span class="text-muted">
			No account yet?
			<button type="button" 
					class="btn btn-link text-primary font-weight-bold p-0 align-baseline"
					id="add_data"
					data-openmodal="#addeditmodal"
					data-openmodallabel="Account Registration"
					data-openmodalbody="#addeditcontent"
					data-backendurl="backend/bk_registeraccount.php"
					data-backendrequest="registeraccount"
					>
			  Register here
			</button>
		  </span>
		</div>
		
		<div class="text-center mt-2">
			<button type="button" 
					class="btn btn-link text-primary font-weight-bold p-0 font-italic"
					id=""
					>
			  Forgot Password or Can't Login?
			</button>
		</div>

      </div>

    </div>

	
    <div class="text-center mt-3 text-muted">
      <?php echo $cpy; ?>
    </div>

	<div class="text-center text-muted">
		PDO &mdash; MIS - v<?php echo $vrs; ?>
	</div>
  </div>
</div>