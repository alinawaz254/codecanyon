<?php
	require_once("lib/system_load.php");
	//This loads system.	
    
	if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != '') { 
		HEADER('LOCATION: dashboard.php');
	} //If user is loged in redirect to specific page.
	
	if(isset($_POST['forgot_pass'])) {
		$forgot_pass = $_POST['forgot_pass'];
		if($forgot_pass == 1){
			extract($_POST);
			$message = $new_user->forgot_user($email);
		}//processing forgot password Email sending.
	}//if isset forgot pass
	
	if(isset($_POST['reset_form'])) {
		$reset_form = $_POST['reset_form'];
		if($reset_form == 1){
			extract($_POST);
			if($password!=$match_password){
				$message = _("Password Doesn't Match");	
			} else {
			   $confirmation_code = $_GET['confirmation_code'];
			   $message = $new_user->reset_pass_user($_GET['user_id'],$confirmation_code,$password);
			   if($message == 1) {
				   header("Location: login.php?message=" . urlencode(_("Your Password has been reset please use new password to login.")));
				   exit();
			   }
			}
		}//reset Form reset password processing.
	}//isset reset_ form
	
	$page_title = _("Forgot Password!"); //You can edit this to change your page title.
	require_once('lib/includes/header.php');
?>
       <!-- Begin Container -->
        <div class="container-fluid no-padding h-100">
            <div class="row flex-row h-100 bg-white">
                <!-- Begin Left Content -->
                <div class="col-xl-3 col-lg-5 col-md-5 col-sm-12 col-12 no-padding">

                    <div class="auth-left-panel">
                        <div class="auth-left-inner text-center">
                            <img src="<?=SITELOGO;?>" class="auth-logo">

                            <h1 class="auth-title"><?=SITE_NAME;?></h1>

                            <p class="auth-desc">
                                If you are already a member please fill the login form.
                            </p>

                             <a href="<?=SITEURL;?>register.php" class="btn btn-primary btn-lg btn-golden-admin">
                                Sign Up
                            </a> 
                            <a href="<?=SITEURL;?>login.php" class="btn btn-primary btn-lg btn-golden-admin">
                                Sign In
                            </a> 
                        </div>
                    </div>                    
                </div>
                <!-- End Left Content -->
                <!-- Begin Right Content -->
                <div class="col-xl-9 col-lg-7 col-md-7 col-sm-12 col-12 my-auto no-padding">
                    <!-- Begin Form -->
                    <div class="authentication-form-2 mx-auto">
                        <div class="tab-content" id="animate-tab-content">
                            <!-- Begin Sign In -->
                            <div role="tabpanel" class="tab-pane show active" id="singin" aria-labelledby="singin-tab">
                                <h3><?php _e("Reset your password"); ?></h3>
                                <?php 
                                    $message = (isset($message)) ? $message : "";
                                    return_info_messages($message); ?>
                                <div id="success_message_admin"></div>
								<?php if(!isset($_GET['confirmation_code']) || $_GET['confirmation_code'] == '') : ?>   
									<p><?php _e("Please enter your email address or username to recover your password."); ?></p>
									<form action="<?php $_SERVER['PHP_SELF']?>" class="form-signin" id="forgot_form" name="forgot" method="post">
										<div class="group material-input">
											<input type="text" class="form-control mt-5" id="eMail" name="email" required="required" />
											<label for="eMail"><?php _e("Email or Username"); ?></label>
										</div>
										
										<div class="sign-btn text-center">
											<input type="submit" class="btn btn-primary btn-lg btn-golden-admin" value="<?php _e("Reset Password"); ?>" />
										</div>
										<input type="hidden" name="forgot_pass" value="1" />
									</form>
								<?php else: ?>
									<form action="<?php $_SERVER['PHP_SELF']?>" class="form-signin" id="reset_form" name="reset_form" method="post">
										<div class="group material-input pass-wrapper">
											<input type="password" name="password" class="form-control" placeholder="<?php _e("New Password"); ?>" required="required" /><i class="toggle-password fa fa-fw fa-eye-slash"></i>
										</div>
										
										<div class="group material-input pass-wrapper">
											<input type="password" name="match_password" class="form-control" placeholder="<?php _e("Confirm Password"); ?>" required="required"/><i class="toggle-password fa fa-fw fa-eye-slash"></i>
										</div>
										
										<div class="sign-btn text-center">
											<input type="hidden" value="1" name="reset_form" />
											<input type="submit" class="btn btn-primary btn-lg btn-golden-admin" value="<?php _e("Reset Password"); ?>" />
										</div>	
									</form>
								<?php endif; ?>
                            </div>
                            <!-- End Sign In -->
                        </div>
                    </div>
                    <!-- End Form -->
                </div>
                <!-- End Right Content -->
            </div>
            <!-- End Row -->
        </div>
        <!-- End Container -->
        <?php require_once("lib/includes/footer_bar.php"); ?>
<?php
	require_once("lib/includes/footer.php");