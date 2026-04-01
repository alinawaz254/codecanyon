<?php
	require_once("lib/system_load.php");
	//This loads system.
	
	if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != '') { 
		HEADER('LOCATION: dashboard.php');
	} //If user is loged in redirect to specific page.
	
	$first_name = $last_name = $gender = $date_of_birth = $address1 = $address2 = $city = $state = $country = $zip_code = $mobile = $phone = $username = $email = $password = $profile_image = $description = $status = $user_type = $referral_id = '';

	if ( isset( $_POST['add_user'] ) ) {
		$add_user = $_POST['add_user'];
		
		if($add_user == 1){
			extract($_POST);
			
			if(get_option('activate_captcha')) {	
				
				if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
					$captcha 	= $_POST['g-recaptcha-response'];
					$secret_key = get_option('secret_key');
					
					$url 	= 'https://www.google.com/recaptcha/api/siteverify';
					$data 	= ['secret'   => $secret_key,
							  'response' => $captcha,
							  'remoteip' => $_SERVER['REMOTE_ADDR']];
					$options = [
						'http' => [
							'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
							'method'  => 'POST',
							'content' => http_build_query($data) 
						]
					];
					$context  	= stream_context_create($options);
					$result 	= file_get_contents($url, false, $context);
					$response 	= json_decode($result, true);
				
					if($response['success'] == false) {
						// What happens when the CAPTCHA was entered incorrectly
						$captcha = '1';
					} else {
						//Captcha Compared and worked well! Go ahead.
					}
				} else {
					// What happens when the CAPTCHA was entered incorrectly
					$captcha = '1';
				}
			}
		
			if(isset($captcha) && $captcha == '1') { 
				///captcha is not correct.
				$message = _("Captcha is not correct");
			} elseif( isset( $_POST['first_name'] ) && empty( $first_name ) ) { 
				$message = _("First name required");
			} elseif( $username == '' ) {
				$message = _("Username required");
			} elseif($email == '') { 
				$message = _("Email required");
			} elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    			$message = _("Incorrect email format");
			} elseif($password == ''){ 
				$message = _("Password required");
			} elseif($_POST['privacy_policy'] != '1') { 
				$message = _("You didn't agree with our privacy policy.");
			} elseif($user_type == '') { 
				$message = _("User type is empty");
			} else {
				if(get_option('disable_registration') == '1') { 
					$message = _("Registration is disabled please contact site admin.");
				} else {
					$user_id = $new_user->register_user( $first_name, $last_name, $user_type, $username, $email, $password,$referral_id );
					$message = _("Registration successful please check your mailbox for email activation.");

					//Update fields. 
					if ( is_integer( $user_id ) ) {
						$_reg_array = array( 'gender', 'date_of_birth', 'address1', 'address2', 'city', 'state', 'zip_code', 'country', 'mobile', 'phone', 'profile_image', 'description' );

						foreach( $_reg_array as $reg_field ) {
							if ( isset( $_POST[$reg_field] ) ) {
								$new_user->update_user_row( $user_id, $reg_field, $_POST[$reg_field] );
							}
						}

						$_additionalarr = return_additionalfields_array( 'registration' );
						if ( ! empty( $_additionalarr ) && is_array( $_additionalarr ) ) {
							foreach( $_additionalarr as $theadditional ) {
								( isset( $_POST[$theadditional] ) && ! empty( $_POST[$theadditional] ) ) ? $new_user->set_usermeta( $user_id, $theadditional, $_POST[$theadditional] ) : '';
							}
						}
					}
					HEADER('LOCATION: register.php?message='.$message);
					exit();
				}
			}//validation ends here.
		}//form processing ends here.
	}//isset user register add user.
	
	$page_title = _("Register Yourself!"); //You can edit this to change your page title.
	require_once('lib/includes/header.php');
	
	if ( get_option('facebook_login') == '1' ) { 
		include('lib/includes/add_facebook.php');
		echo '<div id="fb_return_msg"></div>';
	}

	$_fields_array = array( 'first_name', 'last_name', 'gender', 'date_of_birth', 'address1', 'address2', 'city', 'state', 'zip_code', 'country', 'mobile', 'phone', 'profile_image', 'description' );

	$_fieldarr = array();
	foreach ( $_fields_array as $field ) {
		$_fieldarr[$field]['label']  = get_option( "accountform_setting_". $field ."_field_label" );
		$_fieldarr[$field]['status'] = get_option( "accountform_setting_". $field ."_registration_form" );
	}

	$auto_generated_user_name = '';

	if(!isset($_POST['edit_user'])){

		$all_users = "SELECT * FROM users WHERE user_type LIKE '%subscriber%'";
		$result    = $db->query($all_users);
		$count     = $result->num_rows;

		$auto_generated_user_name = 'BIZ';

		if(strlen($count) == 1){
			$auto_generated_user_name .= '000' . ($count + 1);
		}elseif(strlen($count) == 2){
			$auto_generated_user_name .= '00' . ($count + 1);
		}elseif(strlen($count) == 3){
			$auto_generated_user_name .= '0' . ($count + 1);
		}else{
			$auto_generated_user_name .= ($count + 1);
		}
	}	
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

							<a href="<?=SITEURL;?>login.php" class="btn btn-primary btn-lg btn-golden-admin">
							Sign In
							</a>
                        </div>
                    </div>  					
                </div>
                <!-- End Left Content -->
                <!-- Begin Right Content -->
                <div class="col-xl-9 col-lg-7 col-md-7 col-sm-12 col-12 my-auto no-padding mb-5">
                    <!-- Begin Form -->
                    <div class="authentication-form-2 mx-auto">
                        <div class="tab-content" id="animate-tab-content">
                            <!-- Begin Sign In -->
                            <div role="tabpanel" class="tab-pane show active" id="singin" aria-labelledby="singin-tab">
                                <h3><?php _e("Create an account"); ?></h3>
                                <?php 
                                    $message = (isset($message)) ? $message : "";
                                    return_info_messages($message); ?>
                                <div id="success_message_admin"></div>

								<form action="<?php $_SERVER['PHP_SELF']?>" class="form-signin" id="register_form" name="register" method="post">
									<?php
							    		$subscribers = $db->query("SELECT first_name,last_name,user_id, username FROM users WHERE user_type LIKE '%subscriber%' ORDER BY username ASC");

										foreach( $_fields_array as $_thefield ) {
											$_fieldarr[$_thefield]['label']  = get_option( "accountform_setting_". $_thefield ."_field_label" );
											$_fieldarr[$_thefield]['status'] = get_option( "accountform_setting_". $_thefield ."_registration_form" );

											if ( $_fieldarr[$_thefield]['status'] != 'hide' ) {
												?>
												<div class="group material-input">
													<input type="text" name="<?=$_thefield;?>" id="<?=$_thefield.'_id';?>" class="form-control" />
													<label for="<?=$_thefield.'_id';?>"><?=$_fieldarr[$_thefield]['label'];?></label>
												</div>
												<?php
											}
										}
									?>
									<?php echo return_additional_field_options( '', 'registration' ); ?>
									<!-- <div class="group material-input">	
										<input type="text" name="username" id="userName" class="form-control" required="required"/>
										<label for="userName"><?php _e("Username"); ?>*</label>
									</div> -->
									<div class="group material-input">	
										<input type="text" class="form-control" id="username" name="username"
										value="<?php echo isset($_POST['edit_user']) ? $new_user->username : $auto_generated_user_name; ?>"
										readonly />
										<!-- <label for="userName"><?php _e("Username"); ?>*</label> -->
									</div>									

																			
									<div class="group material-input">	
										<input type="text" name="email" id="email" class="form-control" required="required"/>
										<label for="email"><?php _e("Email"); ?>*</label>
									</div>
									
									<div class="group material-input">	
										<input type="password" id="passWord" name="password" class="form-control" required="required"/><i class="toggle-password fa fa-fw fa-eye-slash"></i>
										<label for="passWord"><?php _e("Password"); ?>*</label>
									</div>
									<?php 
										//This is captcha code please do not remove it you can deactivate captcha by going admin section general settings. Else form will not work . on other page.
										if(get_option('activate_captcha') == '1') { 
												$sitekey = get_option('site_key'); // you got this from the signup page
									?>
										<script type="text/javascript">
											var onloadCallback = function() {
											grecaptcha.render('html_element', {
												'sitekey' : '<?php echo $sitekey; ?>'
											});
											};
										</script>
										<div id="html_element" class="mb-3"></div>
										<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
											async defer>
										</script>
									<?php } ?>

									<select name="referral_id" id="referral-users" class="form-control mb-5" style="width:100%;margin-bottom: 20px;">
									    <option value="0">Select Referrer (Optional)</option>
									    <?php
										    if ($subscribers && $subscribers->num_rows > 0) {
										        while($u = $subscribers->fetch_assoc()){	
										        $user_full_name = htmlspecialchars($u['first_name']) .' ' .htmlspecialchars($u['last_name']);

										            echo "<option data-user-name ='".htmlspecialchars($u['username']). "' data-user-full-name='".$user_full_name."' value='" . htmlspecialchars($u['user_id']) . "'>" . 
										                 htmlspecialchars($u['username']) .' - '.$user_full_name ."</option>";
										        }
										    } else {
										        echo "<option value=''>No subscribers found</option>";
										    }
									    ?>
									</select>

									<div class="row mt-2">
										<div class="col text-left">
											<div class="styled-checkbox">
											<input type="checkbox" id="agreetopolicy" name="privacy_policy" value="1" required="required" />    
												<label for="agreetopolicy"><?php _e("You agree with our privacy policy"); ?></label>
											</div>
										</div>
										<div class="col text-right">
											<?php _e("Forgot password?"); ?> <a href="forgot.php"><?php _e("Recover Password"); ?></a>
										</div>
									</div>

									<div class="sign-btn text-center">
										<input type="hidden" value="1" name="add_user" />
										<!--Default register user is subscriber, you can change it to any other level you have created-->
										<input type="hidden" name="user_type" value="<?php echo get_option('register_user_level'); ?>" />
										<input type="submit" class="btn btn-primary btn-lg btn-golden-admin" value="<?php _e("Register"); ?>" />
									</div>
									<!--user type registration ends here.-->
								</form>
								<?php if(get_option('facebook_login') == '1') { ?>
									<center><fb:login-button scope="public_profile,email" size="xlarge" onlogin="checkLoginState();">
										<?php _e("Register With Facebook"); ?>
									</fb:login-button></center>
								<?php } ?>
								<?php 
                                    if(get_option('google_login') == '1') {
                                        //Producing Google Button
                                        if(!isset($_SESSION["wc_google_logout"])) { ?>
                                            <style type="text/css">#gSignIn .abcRioButton {margin:auto;}</style>
                                            <div id="gSignIn"></div>
                                            <div id="google_return_msg"></div>
                                <?php } elseif(isset($_SESSION["wc_google_logout"]) && $_SESSION["wc_google_logout"] == 'confirm') { 
                                        unset($_SESSION["wc_google_logout"]); ?>
                                        <script>
                                            function signout_now() {
                                                gapi.load('auth2', signout_process);	
                                            }
                                            function signout_process() {
                                                gapi.auth2.init({
                                                    client_id: '<?php echo get_option('google_client_id'); ?>'
                                                }).then(function (authInstance) {
                                                    // now auth2 is fully initialized
                                                    var auth2 = gapi.auth2.getAuthInstance();
                                                    auth2.signOut().then(function () {
                                                        console.log("Loged out from Google!");
                                                    });
                                                    auth2.disconnect();
                                                    //reload to get button back
                                                    location.reload();
                                                });
                                            }
                                        </script>
                                <?php } } ?>
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