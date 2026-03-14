<?php
	require_once 'lib/system_load.php';
	//This loads system.	
    
    if(isset($_GET["logout"]) && $_GET["logout"] == "1") {
        session_destroy();
    }

	//Activation of user confirm email id
	if(isset($_GET['confirmation_code']) && $_GET['confirmation_code'] != '' && $_GET['user_id'] != '') {
		/**
		* Code Handles Account Activation
		*
		* If not present accounts cannot be enabled.
		* @Since 1.0
		*/
		$confirmation_code 	= $_GET['confirmation_code'];
		$user_id 			= $_GET['user_id'];
		$message 			= $new_user->match_confirm_code($confirmation_code,$user_id);
	}
	
	if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != '') { 
		HEADER('LOCATION: dashboard.php');
	} //If user is loged in redirect to specific page.
	
	if(isset($_POST['login_identity']) && $_POST['login_identity'] == '1') { 
		extract($_POST);
        if(get_option('activate_captcha')) {    
            
            if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
                $captcha    = $_POST['g-recaptcha-response'];
                $secret_key = get_option('secret_key');
                
                $url    = 'https://www.google.com/recaptcha/api/siteverify';
                $data   = ['secret'   => $secret_key,
                          'response' => $captcha,
                          'remoteip' => $_SERVER['REMOTE_ADDR']];
                $options = [
                    'http' => [
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'POST',
                        'content' => http_build_query($data) 
                    ]
                ];
                $context    = stream_context_create($options);
                $result     = file_get_contents($url, false, $context);
                $response   = json_decode($result, true);
            
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
            $message = _("Captcha is not correct");
        }
		elseif(empty($email)) { 
			$message = _("Email required");
		} elseif(empty($password)) { 
			$message = _("Password Required");
		} else {
			$message = $new_user->login_user($email, $password);
		} //validation ends here.
		
		
		if($message == 1) {
			if(get_option('disable_login') == '1' && $new_user->user_type != 'admin') { 
				$message = _("Login disabled. Please contact site admin.");
			} else {
			$_SESSION['user_id'] 	= $new_user->user_id;
			$_SESSION['first_name'] = $new_user->first_name;
			$_SESSION['last_name'] 	= $new_user->last_name;
			$_SESSION['username'] 	= $new_user->username;
			$_SESSION['email'] 		= $new_user->email;
			$_SESSION['status'] 	= $new_user->status;
			$_SESSION['salt']		= get_option("system_salt");
			$_SESSION['user_type'] 	= $new_user->user_type;
			$_SESSION['timeout'] 	= time();
			
			if(isset($_POST["keep_login"]) && $_POST["keep_login"] == "yes") {
				$user_credentials = array(
										"user_id"	=> $new_user->user_id,
										"email"		=> $new_user->email
									);
				setcookie("loginCredentials", serialize($user_credentials), time() + 2592000);
			}	
				
			//Setting user meta information.
			$user_ip = get_client_ip();//Function is inside function.php to get ip
			$new_user->set_user_meta($_SESSION['user_id'], 'last_login_time', date("Y-m-d H:i:s")); //setting last login time.
			$new_user->set_user_meta($_SESSION['user_id'], 'last_login_ip', $user_ip); //setting last login IP.
			$new_user->set_user_meta($_SESSION['user_id'], 'login_attempt', '0'); //On login success default loign attempt is 0.
			$new_user->set_user_meta($_SESSION['user_id'], 'login_lock', 'No'); //setting last login time.
			
			$message = _("Login Success");
			redirect_user($new_user->user_type); //Checks authentication and redirect user as per his/her level.
			exit();
			}
		} else { 
				if(!empty($message)) {
					//Original Message would display
				} else {
					$message = _("Password or Email doesn't Match");	
				}
		}//setting Session variables if user loged in successful!
	}//login process ends here if form submits

	$page_title = _("Login to your account"); //You can edit this to change your page title.
	require_once('lib/includes/header.php');

	//adding facebook if activate.
	if(get_option('facebook_login') == '1') { 
		include('lib/includes/add_facebook.php');
		echo '<div id="fb_return_msg"></div>';
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

                             <a href="<?=SITEURL;?>register.php" class="btn btn-primary btn-lg btn-golden-admin">
                                Sign Up
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
                                <h3><?php _e("Sign In"); ?></h3>
                                <?php 
                                    $message = (isset($message)) ? $message : "";
                                    return_info_messages($message); ?>
                                <div id="success_message_admin"></div>

                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-signin" method="post">
                                    <div class="group material-input">
                                        <input type="text" name="email" id="emailOrUser" class="form-control" required="required" />
                                        <label for="emailOrUser"><?php _e("Email or Username"); ?>*</label>
                                    </div>
                                    <div class="group material-input">
                                        <input type="password" id="passWord" name="password" class="form-control" required="required"/>
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
                                        <div id="html_element" class="mb-5"></div>
                                        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
                                            async defer>
                                        </script>
                                    <?php } ?>
                                    <div class="row mt-3">
                                        <div class="col text-left">
                                            <div class="styled-checkbox">
                                                <input type="checkbox" id="remeber" name="keep_login" value="yes" />    
                                                <label for="remeber"><?php _e("Remember me"); ?></label>
                                            </div>
                                        </div>
                                        <div class="col text-right">
                                            <?php _e("Forgot password?"); ?> <a href="forgot.php"><?php _e("Recover Password"); ?></a>
                                        </div>
                                    </div>
                                    <div class="sign-btn text-center">
                                        <input type="hidden" value="1" name="login_identity" />
                                        <input type="submit" class="btn btn-primary btn-lg btn-golden-admin" value="<?php _e("Login"); ?>" />
                                    </div>
                                </form>

                                <ul class="mt-5 justify-content-center text-center">
                                    <li>
                                <?php if(get_option('facebook_login') == '1') { ?>
                                    <fb:login-button scope="public_profile,email" size="large" onlogin="checkLoginState();">
                                        <?php _e("Login with Facebook"); ?>
                                    </fb:login-button>
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
                                </li>
                                </ul>

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