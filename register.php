<?php
	require_once("lib/system_load.php");
	$datepicker = 1;
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
					
					if ( is_integer( $user_id ) ) {
						$message = _("Registration successful please check your mailbox for email activation.");
						
						//Update fields. 
						$_reg_array = array( 'gender', 'date_of_birth', 'address1', 'address2', 'city', 'state', 'zip_code', 'country', 'mobile', 'phone', 'description' );

						foreach( $_reg_array as $reg_field ) {
							if ( isset( $_POST[$reg_field] ) ) {
								$val = $_POST[$reg_field];
								// Convert Date for DB
								if($reg_field == 'date_of_birth' && !empty($val)) {
									$val = date("Y-m-d", strtotime($val));
								}
								$new_user->update_user_row( $user_id, $reg_field, $val );
							}
						}

						// Handle file uploads
						if(isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0) {
							$url = wc_upload_image_return_url($_FILES['profile_image'], 'users');
							if(is_string($url)) $new_user->update_user_row($user_id, 'profile_image', $url);
						}
						if(isset($_FILES['nic_front']) && $_FILES['nic_front']['size'] > 0) {
							$url = wc_upload_image_return_url($_FILES['nic_front'], 'users');
							if(is_string($url)) $new_user->update_user_row($user_id, 'nic_front', $url);
						}
						if(isset($_FILES['nic_back']) && $_FILES['nic_back']['size'] > 0) {
							$url = wc_upload_image_return_url($_FILES['nic_back'], 'users');
							if(is_string($url)) $new_user->update_user_row($user_id, 'nic_back', $url);
						}

						$_additionalarr = return_additionalfields_array( 'registration' );
						if ( ! empty( $_additionalarr ) && is_array( $_additionalarr ) ) {
							foreach( $_additionalarr as $theadditional ) {
								( isset( $_POST[$theadditional] ) && ! empty( $_POST[$theadditional] ) ) ? $new_user->set_usermeta( $user_id, $theadditional, $_POST[$theadditional] ) : '';
							}
						}
						
						HEADER('LOCATION: register.php?message='.$message);
						exit();
					} else {
						$message = $user_id; // This is the error message string
					}
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

			if(strlen($count) == 1) {
				if(($count + 1) == 10){
					$auto_generated_user_name .= '00' . ($count + 1);
				}else{
					$auto_generated_user_name .= '000' . ($count + 1);
				}
			}elseif (strlen($count) == 2 ) {
				if(($count + 1) == 100){
					$auto_generated_user_name .= '0' . ($count + 1);
				}else{
					$auto_generated_user_name .= '00' . ($count + 1);
				}
			}elseif (strlen($count) == 3 ) {
				if(($count + 1) == 1000){
					$auto_generated_user_name .=  ($count + 1);
				}else{
					$auto_generated_user_name .= '0' . ($count + 1);
				}
			}elseif (strlen($count) == 4 ) {
				$auto_generated_user_name .= ($count + 1);
			}
	}	
?>
<style>
	.authentication-form-2 { max-width: 95% !important; width: 100%; padding: 40px; background: #fff; border-radius: 12px; }
	
	.auth-left-panel { background-color: #ECAD3D; min-height: 100vh; height: 100% !important; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding-top: 316px; color: #fff; width: 100%; }
	.auth-left-inner { width: 100%; padding: 0 30px; text-align: center; }
	.auth-logo { margin-bottom: 20px; max-width: 130px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1)); }
	.auth-title { font-weight: 800 !important; color: #fff !important; font-size: 26px; margin-bottom: 5px; }
	.auth-desc { color: rgba(255,255,255,0.95) !important; margin-bottom: 25px; font-size: 14px; line-height: 1.4; }

	@media (max-width: 767px) {
		.auth-left-panel { min-height: auto; padding: 30px 20px; }
		.auth-logo { max-width: 100px; margin-bottom: 15px; }
		.authentication-form-2 { padding: 25px 15px; margin-top: 0; border-radius: 0; }
	}

	.admin-form-group { margin-bottom: 22px; }
	.admin-form-group label { font-weight: 700; color: #333; margin-bottom: 8px; display: block; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
	.admin-form-group .form-control { border-radius: 4px; border: 1px solid #dcdcdc; padding: 10px 12px; height: 42px; background: #fff; transition: all 0.2s; font-size: 14px; width: 100%; }
	.admin-form-group .form-control:focus { border-color: #5d5386; box-shadow: 0 0 0 3px rgba(93, 83, 134, 0.08); outline: none; }
	
	.pass-wrapper { position: relative; width: 100%; }
	.pass-wrapper input { padding-right: 45px !important; }
	.toggle-password { position: absolute; right: 5px; top: 106%; transform: translateY(-50%); cursor: pointer !important; color: #888; z-index: 99; font-size: 16px; pointer-events: auto !important; }
	.toggle-password:hover { color: #5d5386; }

	.section-header { border-bottom: 2px solid #5d5386; margin: 30px 0 20px 0; display: inline-block; padding-bottom: 3px; }
	.section-header h5 { margin: 0; font-weight: 800; color: #5d5386; font-size: 15px; text-transform: uppercase; letter-spacing: 1px; }
	
	.error-msg { color: #dc3545; font-size: 11px; font-weight: 600; margin-top: 4px; display: none; text-transform: none; letter-spacing: 0; }
	.has-error .form-control { border-color: #dc3545 !important; }
	.has-error .error-msg { display: block; }
	
	.image-upload-zone { border: 2px dashed #dcdcdc; border-radius: 10px; padding: 15px; text-align: center; background: #fafafa; transition: all 0.3s; cursor: pointer; margin-bottom: 20px; }
	.image-upload-zone:hover { border-color: #5d5386; background: #f4f4f9; }
	.preview-box { height: 110px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }
	.preview-box img { max-height: 100%; max-width: 100%; object-fit: contain; border-radius: 6px; }
	
	#referrer_name_display { display: block; font-size: 13px; margin-top: 6px; font-weight: 700; min-height: 18px; color: #28a745; }
	.sign-btn .btn-golden-admin { width: auto; padding: 15px 60px; border-radius: 30px; font-size: 16px; min-width: 250px; }
</style>

<!-- Begin Container -->
<div class="container-fluid no-padding h-100">
    <div class="row flex-row h-100 bg-white">
        <!-- Begin Left Content -->
        <div class="col-xl-3 col-lg-5 col-md-5 col-sm-12 col-12 no-padding bg-golden-side">
            <style>.bg-golden-side { background-color: #ECAD3D; }</style>
            <div class="auth-left-panel">
                <div class="auth-left-inner text-center">
                    <img src="<?=SITELOGO;?>" class="auth-logo">
                    <h1 class="auth-title"><?=SITE_NAME;?></h1>
                    <p class="auth-desc">If you are already a member please fill the login form.</p>
                    <a href="<?=SITEURL;?>login.php" class="btn btn-primary btn-lg btn-golden-admin">Sign In</a>
                </div>
            </div>  					
        </div>
        
        <!-- Begin Right Content -->
        <div class="col-xl-9 col-lg-7 col-md-7 col-sm-12 col-12 my-auto no-padding mb-5">
            <div class="authentication-form-2 mx-auto">
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane show active" id="singin">
                        <h3 class="mb-4"><?php _e("Create an account"); ?></h3>
                        <?php 
                            $message = (isset($message)) ? $message : (isset($_GET['message']) ? $_GET['message'] : "");
                            if(!empty($message) && !is_logged_in()) return_info_messages($message); 
                        ?>

                        <form action="" id="register_form" name="register" method="post" enctype="multipart/form-data" novalidate>
                            <div class="section-header"><h5>Personal Details</h5></div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("First Name"); ?>*</label>
                                        <input type="text" name="first_name" class="form-control" placeholder="Enter First Name" />
                                        <div class="error-msg"><?php _e("This field is required"); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("Last Name"); ?>*</label>
                                        <input type="text" name="last_name" class="form-control" placeholder="Enter Last Name" />
                                        <div class="error-msg"><?php _e("This field is required"); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("Gender"); ?></label>
                                        <select class="form-control" name="gender">
                                            <option value=""><?php _e("Select Gender"); ?></option>
                                            <option value="Male"><?php _e("Male"); ?></option>
                                            <option value="Female"><?php _e("Female"); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("Date of Birth"); ?></label>
                                        <input type="text" id="dob" name="date_of_birth" class="form-control" placeholder="Select Date" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("Mobile"); ?>*</label>
                                        <input type="text" name="mobile" class="form-control" placeholder="e.g. +923001234567" />
                                        <div class="error-msg"><?php _e("This field is required"); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("Phone"); ?></label>
                                        <input type="text" name="phone" class="form-control" placeholder="Optional Phone Number" />
                                    </div>
                                </div>
                            </div>

                            <div class="section-header"><h5>Location Information</h5></div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("Address 1"); ?></label>
                                        <input type="text" name="address1" class="form-control" placeholder="Street Address" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("Address 2"); ?></label>
                                        <input type="text" name="address2" class="form-control" placeholder="Apartment, suite, etc." />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("City"); ?></label>
                                        <input type="text" name="city" class="form-control" placeholder="e.g. New York" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("State"); ?></label>
                                        <input type="text" name="state" class="form-control" placeholder="e.g. California" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("Zip Code"); ?></label>
                                        <input type="text" name="zip_code" class="form-control" placeholder="e.g. 10001" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("Country"); ?></label>
                                        <select name="country" class="form-control">
                                            <?php countries_dropdown('Pakistan'); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="section-header"><h5>Account Security</h5></div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("Username"); ?></label>
                                        <input type="text" name="username" class="form-control" value="<?php echo $auto_generated_user_name; ?>" readonly />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("Email Address"); ?>*</label>
                                        <input type="email" name="email" class="form-control" placeholder="example@mail.com" />
                                        <div class="error-msg"><?php _e("This field is required"); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("Referrer Username"); ?></label>
                                        <input type="text" id="referrer_username" class="form-control" autocomplete="off" placeholder="Search Referrer Username" value="<?php echo isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : ''; ?>" <?php echo isset($_GET['ref']) ? 'readonly' : ''; ?> />
                                        <input type="hidden" name="referral_id" id="referral_id" value="0">
                                        <span id="referrer_name_display"></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("Create Password"); ?>*</label>
                                        <div class="pass-wrapper">
                                            <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" />
                                            <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                                        </div>
                                        <div class="error-msg"><?php _e("This field is required"); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("Confirm Password"); ?>*</label>
                                        <div class="pass-wrapper">
                                            <input type="password" name="confirm_password" class="form-control" placeholder="Repeat your password" />
                                            <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                                        </div>
                                        <div class="error-msg" id="confirm_err"><?php _e("This field is required"); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="section-header"><h5>Identification & Media</h5></div>
                            <div class="row">
                                <?php
                                $upl_fields = [
                                    'profile_image' => 'Profile Picture',
                                    'nic_front' => 'NIC Front Side',
                                    'nic_back' => 'NIC Back Side'
                                ];
                                foreach($upl_fields as $f_key => $f_label) : ?>
                                <div class="col-md-4">
                                    <div class="form-group admin-form-group mb-0">
                                    <div class="image-upload-zone" id="zone_<?=$f_key?>" onclick="document.getElementById('input_<?=$f_key?>').click()">
                                        <label><?php _e($f_label); ?></label>
                                        <div class="preview-box">
                                            <img id="preview_<?=$f_key?>" src="assets/images/thumb.png">
                                        </div>
                                        <input type="file" name="<?=$f_key?>" id="input_<?=$f_key?>" class="d-none" accept="image/*" onchange="showPreview(this, 'preview_<?=$f_key?>')">
                                        <button type="button" class="btn btn-sm btn-outline-secondary"><?php _e("Upload Image"); ?></button>
                                    </div>
                                    <div class="error-msg text-center mb-3" style="margin-top:-15px"><?php _e("This photo is required"); ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="section-header"><h5>Extra Information</h5></div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group admin-form-group">
                                        <label><?php _e("Bank Details"); ?></label>
                                        <textarea name="description" class="form-control bank-textarea" rows="6" placeholder="Enter Full Bank Details (Bank Name, Account Number, IBAN, etc.)"></textarea>
                                    </div>
                                </div>
                            </div>

                            <?php echo return_additional_field_options( '', 'registration' ); ?>

                            <div class="row align-items-center mt-3">
                                <div class="col-md-6">
                                    <?php if(get_option('activate_captcha') == '1') : $sitekey = get_option('site_key'); ?>
                                        <script type="text/javascript">
                                            var onloadCallback = function() {
                                                grecaptcha.render('recaptcha_div', { 'sitekey' : '<?=$sitekey?>' });
                                            };
                                        </script>
                                        <div id="recaptcha_div"></div>
                                        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-12 text-right mt-4">
                                   <div class="d-inline-block text-right">
                                       <div class="styled-checkbox mb-1" id="policy_wrap">
                                           <input type="checkbox" id="agreetopolicy" name="privacy_policy" value="1" />    
                                           <label for="agreetopolicy" style="font-weight: 500;"><?php _e("I agree with the privacy policy"); ?></label>
                                           <div class="error-msg"><?php _e("You must agree to proceed"); ?></div>
                                       </div>
                                       <p class="small mb-0" style="font-size: 13px;">
                                           <span style="color: #888;"><?php _e("Forgot password?"); ?></span> 
                                           <a href="forgot.php" style="color: #5d5386; font-weight: 700;"><?php _e("Recover Password"); ?></a>
                                       </p>
                                   </div>
                               </div>
                            </div>

                            <div class="sign-btn text-center mt-5">
                                <input type="hidden" value="1" name="add_user" />
                                <input type="hidden" name="user_type" value="<?php echo get_option('register_user_level'); ?>" />
                                <button type="submit" class="btn btn-primary btn-lg btn-golden-admin"><?php _e("Register Now"); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showPreview(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { document.getElementById(previewId).src = e.target.result; }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
	jQuery(document).ready(function($) {
		$('#register_form').on('submit', function(e) {
			let isValid = true;
			$('.admin-form-group, #policy_wrap').removeClass('has-error');
			
			// Regular Input Check
			const requiredFields = ['first_name', 'last_name', 'email', 'mobile', 'password', 'confirm_password'];
			requiredFields.forEach(name => {
				const el = $(`[name="${name}"]`);
				if(!el.val() || !el.val().trim()) {
					el.closest('.admin-form-group').addClass('has-error');
					isValid = false;
				}
			});

			// Passwords Match check
			const pass = $('[name="password"]').val();
			const confirm = $('[name="confirm_password"]').val();
			if(pass && confirm && pass !== confirm) {
				$('[name="confirm_password"]').closest('.admin-form-group').addClass('has-error');
				$('#confirm_err').text("Passwords do not match").show();
				isValid = false;
			}

			// Image Verification
			const imgFields = ['nic_front', 'nic_back'];
			imgFields.forEach(key => {
				const input = $('#input_'+key)[0];
				if(!input.files || !input.files.length) {
					$('#zone_'+key).closest('.admin-form-group').addClass('has-error');
					isValid = false;
				}
			});

			// Policy Check
			if(!$('#agreetopolicy').is(':checked')) {
				$('#policy_wrap').addClass('has-error');
				isValid = false;
			}

			if(!isValid) {
				e.preventDefault();
				$('html, body').animate({ scrollTop: ($('.has-error').first().offset().top - 100) }, 500);
				return false;
			}
		});

		// Clear error on input
		$('.form-control').on('input change', function() {
			$(this).closest('.admin-form-group').removeClass('has-error');
		});
	});

    var subscribersData = {
    <?php
        $subscribers = $db->query("SELECT first_name, last_name, user_id, username FROM users WHERE user_type LIKE '%subscriber%' ORDER BY username ASC");
        if ($subscribers) {
            $sub_list = [];
            while($u = $subscribers->fetch_assoc()){
                $name = addslashes($u['first_name'] . ' ' . $u['last_name']);
                $sub_list[] = '"' . strtolower($u['username']) . '": {"id": "' . $u['user_id'] . '", "name": "' . $name . '"}';
            }
            echo implode(",\n", $sub_list);
        }
    ?>
    };

    document.getElementById('referrer_username').addEventListener('input', function() {
        var username = this.value.trim().toLowerCase();
        var display = document.getElementById('referrer_name_display');
        var hidden = document.getElementById('referral_id');
        if(username && subscribersData[username]) {
            display.textContent = "VERIFIED: " + subscribersData[username].name;
            display.style.color = '#28a745';
            hidden.value = subscribersData[username].id;
        } else {
            display.textContent = username.length > 0 ? 'USER NOT FOUND' : '';
            display.style.color = '#dc3545';
            hidden.value = 0;
        }
    });

    window.addEventListener('DOMContentLoaded', () => {
        const refInp = document.getElementById('referrer_username');
        if (refInp && refInp.value) refInp.dispatchEvent(new Event('input'));
    });
</script>
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