<?php
	require_once("lib/system_load.php");
	//Including this file we load system.
	
	//user Authentication.
	authenticate_user('admin');
	
	//installation form processing when submits.
	if(isset($_POST['settings_submit']) && $_POST['settings_submit'] == 'Yes') {
	//validation to check if fields are empty!
	if($_POST['site_url'] == '') { 
		$message = _("Site URL Required");
	} else if($_POST['email_from'] == '') { 
		$message = _("Email From Required");
	} else if($_POST['email_to'] == '') { 
		$message = _("Reply To Required");
	} else {
		//adding site url
		set_option('site_url', 					$_POST['site_url']);
		set_option('site_name', 				$_POST['site_name']);
		set_option('email_from',				$_POST['email_from']);
		set_option('email_to', 					$_POST['email_to']);
		set_option('smtp_host', 				$_POST['smtp_host']);
		set_option('smtp_port', 				$_POST['smtp_port']);
		set_option('smtp_username', 			$_POST['smtp_username']);
		set_option('smtp_password', 			$_POST['smtp_password']);
		set_option('site_key', 					$_POST['site_key']);
		set_option('secret_key', 				$_POST['secret_key']);
		set_option('redirect_on_logout', 		$_POST['redirect_on_logout']);
		set_option('password_hash', 			$_POST["password_hash"]);
		set_option('language', 					$_POST['language']);
		set_option('skin', 						$_POST['skin']);
		set_option('maximum_login_attempts', 	$_POST['maximum_login_attempts']);
		set_option('wrong_attempts_time', 		$_POST['wrong_attempts_time']);
		set_option('session_timeout', 			$_POST['session_timeout']);
		set_option('register_user_level', 		$_POST['register_user_level']);
		set_option('facebook_api_key', 			$_POST['facebook_api_key']);
		set_option('google_redirect_uri', 		$_POST['google_redirect_uri']);
		set_option('google_client_secret', 		$_POST['google_client_secret']);
		set_option('google_client_id', 			$_POST['google_client_id']);
		
		//Unset Selected LAnguage in case its user selected.
		unset($_SESSION["language"]);
		
		if(isset($_POST['activate_captcha'])) {
			set_option('activate_captcha', $_POST['activate_captcha']);
		} else { 
			set_option('activate_captcha', '0');
		}
		
		$language_array = array( "language_english", "language_spanish", "language_dutch", "language_german", "language_french", "language_italian", "language_thai" );
		
		foreach($language_array as $language) {
			if(isset($_POST[$language])) {
				set_option($language, $_POST[$language]);	
			} else {
				set_option($language, "0");
			}
		}
	
		if(isset($_POST['notify_user_group'])) {
			set_option('notify_user_group', $_POST['notify_user_group']);
		} else { 
			set_option('notify_user_group', '0');
		}
		if(isset($_POST['register_verification'])) {
			set_option('register_verification', $_POST['register_verification']);
		} else { 
			set_option('register_verification', '0');
		}
		if ( isset( $_POST['smtp_activation'] ) ) {
			set_option('smtp_activation', $_POST['smtp_activation']);
		} else { 
			set_option('smtp_activation', '0');
		}
		if(isset($_POST['facebook_login'])) {
			set_option('facebook_login', $_POST['facebook_login']);
		} else { 
			set_option('facebook_login', '0');
		}
		if(isset($_POST['google_login'])) {
			set_option('google_login', $_POST['google_login']);
		} else { 
			set_option('google_login', '0');
		}
		if(isset($_POST['disable_login'])) {
			set_option('disable_login', $_POST['disable_login']);
		} else { 
			set_option('disable_login', '0');
		}
		if(isset($_POST['disable_registration'])) {
			set_option('disable_registration', $_POST['disable_registration']);
		} else { 
			set_option('disable_registration', '0');
		}
		$message = _("Settings saved.");
		HEADER('LOCATION: general_settings.php?message='.$message); 
		}//form validations
	}//form processing.

	//Page display settings.
	$page_title = _("General Settings"); //You can edit this to change your page title.
	require_once("lib/includes/header.php"); //including header file.
?>
<div class="row flex-row">
	<div class="col-12">
		<!-- Form -->
		<div class="widget has-shadow">
			<div class="widget-body">
				<div class="form-group">
					<a href="general_settings.php" class="btn btn-primary mr-1 mb-2"><?php _e( 'General Settings' ); ?></a>
					<a href="general_settings_userforms.php" class="btn btn-secondary mr-1 mb-2"><?php _e( 'User Account Settings' ); ?></a>
					<a href="general_settings_user_additionalfields.php" class="btn btn-secondary mr-1 mb-2"><?php _e( 'Additional User Fields' ); ?></a>
				</div>
			</div>
			<div class="widget-body">

				<form name="set_install" id="set_install" action="<?php $_SERVER['PHP_SELF']; ?>" method="post">

					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="site_url"><?php _e("Site URL"); ?>*:</label>
						<div class="col-lg-5">
							<input type="text" name="site_url" id="site_url" class="form-control" value="<?php echo get_option('site_url'); ?>" required /><small><?php _e("Please include / at end of site url e.g http://localhost/"); ?></small>
						</div>
					</div>
				
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="site_name"><?php _e("Site Name"); ?>:</label>
						<div class="col-lg-5">
							<input type="text" name="site_name" id="site_name" class="form-control" value="<?php echo get_option('site_name'); ?>" />
						</div>
					</div>
				
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="redirect_on_logout"><?php _e("Page to redirect on logout"); ?>:</label>
						<div class="col-lg-5">
							<input type="text" name="redirect_on_logout" id="redirect_on_logout" class="form-control" value="<?php echo get_option('redirect_on_logout'); ?>" />
						</div>
					</div>
				
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="language"><?php _e("Site Language"); ?>:</label>
						<div class="col-lg-5">
							<select name="language" id="language" class="custom-select form-control">
								<option <?php if ( get_option('language') == 'english' ) { echo "selected='selected'"; } ?> value="english"><?php _e("English"); ?></option>
								<option <?php if ( get_option('language') == 'spanish' ) { echo "selected='selected'"; } ?> value="spanish"><?php _e("Spanish"); ?></option>
								<option <?php if ( get_option('language') == 'dutch' ) { echo "selected='selected'"; } ?> value="dutch"><?php _e("Dutch"); ?></option>
								<option <?php if ( get_option('language') == 'french' ) { echo "selected='selected'"; } ?> value="french"><?php _e("French"); ?></option>
								<option <?php if ( get_option('language') == 'german' ) { echo "selected='selected'"; } ?> value="german"><?php _e("German"); ?></option>
								<option <?php if ( get_option('language') == 'italian' ) { echo "selected='selected'"; } ?> value="italian"><?php _e("Italian"); ?></option>
								<option <?php if ( get_option('language') == 'thai' ) { echo "selected='selected'"; } ?> value="thai"><?php _e("Thai"); ?></option>
							</select>
							<small><?php _e("You can easily add more languages if they ar enot available. By updating .po files we are available for support."); ?></small>
						</div>
					</div>
				
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for=""><?php _e("Enable Multilanguage"); ?>:</label>
						<div class="col-lg-5">
							<input type="checkbox" name="language_english" <?php if(get_option('language_english') == '1'){echo 'checked="checked"'; }?> value="1" title="<?php _e("Enable English"); ?>"> <?php _e("English"); ?>
							<input type="checkbox" name="language_french" <?php if(get_option('language_french') == '1'){echo 'checked="checked"'; }?> value="1" title="<?php _e("Enable French"); ?>"> <?php _e("French"); ?>
							<input type="checkbox" name="language_spanish" <?php if(get_option('language_spanish') == '1'){echo 'checked="checked"'; }?> value="1" title="<?php _e("Enable Spanish"); ?>"> <?php _e("Spanish"); ?>
							<input type="checkbox" name="language_dutch" <?php if(get_option('language_dutch') == '1'){echo 'checked="checked"'; }?> value="1" title="<?php _e("Enable Dutch"); ?>"> <?php _e("Dutch"); ?>
							<input type="checkbox" name="language_german" <?php if(get_option('language_german') == '1'){echo 'checked="checked"'; }?> value="1" title="<?php _e("Enable German"); ?>"> <?php _e("German"); ?>
							<input type="checkbox" name="language_italian" <?php if(get_option('language_italian') == '1'){echo 'checked="checked"'; }?> value="1" title="<?php _e("Enable Italian"); ?>"> <?php _e("Italian"); ?>
							<input type="checkbox" name="language_thai" <?php if(get_option('language_thai') == '1'){echo 'checked="checked"'; }?> value="1" title="<?php _e("Enable Thai"); ?>"> <?php _e( "Thai" ); ?>
						</div>
					</div>
				
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="password_hash"><?php _e("Password Hash"); ?>:</label>
						<div class="col-lg-5">
							<select name="password_hash" id="password_hash" class="custom-select form-control">
								<option <?php if(get_option('password_hash') == 'md5'){ echo "selected='selected'"; } ?> value="md5">MD5</option>
								<option <?php if(get_option('password_hash') == 'argon2'){ echo "selected='selected'"; } ?> value="argon2">Argon2</option>
							</select>
							<small><?php _e("Please note if you change password hashing all users would require to reset their password. As we do not save original passwords so script cannot auto generate new passwords."); ?></small>
						</div>
					</div>
				
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="disable_registration"><?php _e("Disable Registration"); ?>:</label>
						<div class="col-lg-5">
							<input type="checkbox" name="disable_registration" id="disable_registration" <?php if(get_option('disable_registration') == '1'){echo 'checked="checked"'; }?> value="1" title="<?php _e("Disable Registration"); ?>" />
						</div>
					</div>
				
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="disable_login"><?php _e("Disable Login"); ?>:</label>
						<div class="col-lg-5">
							<input type="checkbox" name="disable_login" id="disable_login" <?php if(get_option('disable_login') == '1'){echo 'checked="checked"'; }?> value="1" title="<?php _e("Disable Login"); ?>" />
						</div>
					</div>
				
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="notify_user_group"><?php _e("Notify User Group On New Registration"); ?>:</label>
						<div class="col-lg-5">
							<input type="checkbox" name="notify_user_group" id="notify_user_group" <?php if(get_option('notify_user_group') == '1'){echo 'checked="checked"'; }?> value="1" title="<?php _e("Notify User Group On New Registration"); ?>" />
						</div>
					</div>
				
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="register_user_level"><?php _e("Default Register User Type"); ?></label>
						<div class="col-lg-5">
							<select name="register_user_level" id="register_user_level" class="custom-select form-control">
								<option value=""><?php _e("Select Default User Type"); ?></option>
								<?php $new_level->userlevel_options(get_option('register_user_level')); ?>
							</select>
						</div>
					</div>
				
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="session_timeout"><?php _e("Session Timeout in Minutes"); ?>:</label>
						<div class="col-lg-5">
							<input type="text" name="session_timeout" id="session_timeout" class="form-control" value="<?php echo get_option('session_timeout'); ?>" />
						</div>
					</div>
				
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="maximum_login_attempts"><?php _e("Maximum Wrong Login Attempts"); ?>:</label>
						<div class="col-lg-5">
							<input type="text" name="maximum_login_attempts" id="maximum_login_attempts" class="form-control" value="<?php echo get_option('maximum_login_attempts'); ?>" />
						</div>
					</div>
				
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="wrong_attempts_time"><?php _e("Lock time on Wrong Login Attempts in minutes"); ?>:</label>
						<div class="col-lg-5">
							<input type="text" name="wrong_attempts_time" id="wrong_attempts_time" class="form-control" value="<?php echo get_option('wrong_attempts_time'); ?>" />
						</div>
					</div>
					
					<div class="row">
						<div class="offset-xl-3 col-xl-6">
							<!-- Begin Basic Accordion -->
							<div id="accordion" class="accordion-icon icon-01">

								<a class="card-header collapsed d-flex align-items-center" data-toggle="collapse" href="#googleSettings">
									<div class="card-title"><?php _e("Google Login Settings"); ?></div>
								</a>
								<div id="googleSettings" class="card-body collapse pt-0" data-parent="#accordion">
									<div class="form-group">
										<label class="control-label"><?php _e("Activate Google Login"); ?>:</label>
										<input type="checkbox" name="google_login" <?php if(get_option('google_login') == '1'){echo 'checked="checked"'; }?> value="1" title="<?php _e("Activate Google Login"); ?>" />
									</div>
									<p>Please <a href="https://www.webfulcreations.com/setting-up-google-project-php-login-script/" target="_blank">click here</a> to create google api details.</p>

									<div class="form-group">
										<label class="control-label">
											<?php _e("Google Client ID"); ?>:
										</label>
										<input type="text" name="google_client_id" class="form-control" value="<?php echo get_option('google_client_id'); ?>" />
									</div>

									<!--<div class="form-group">
										<label class="control-label">
											<?php _e("Google Client Secret"); ?>:
										</label>
										<input type="text" name="google_client_secret" class="form-control" value="<?php echo get_option('google_client_secret'); ?>" />
									</div>-->

									<div class="form-group">
										<label class="control-label">
											<?php _e("Enter Redirect URI. Should be your Login page login.php full URL With http:"); ?>:
										</label>
										<?php
											$base_url = get_option('google_redirect_uri'); 
										
											if(empty($base_url)) {
												$base_url = return_base_url().'/login.php';
											}
										?>
										<input type="text" name="google_redirect_uri" class="form-control" value="<?php echo $base_url; ?>" />
									</div>
								</div>

								<a class="card-header collapsed d-flex align-items-center" data-toggle="collapse" href="#facebookSettings">
									<div class="card-title"><?php _e("Facebook Login Settings"); ?></div>
								</a>
								<div id="facebookSettings" class="card-body collapse pt-0" data-parent="#accordion">
									<div class="form-group">
										<label class="control-label"><?php _e("Activate Facebook Login"); ?>:</label>
										<input type="checkbox" name="facebook_login" <?php if(get_option('facebook_login') == '1'){echo 'checked="checked"'; }?> value="1" />
									</div>
									<div class="form-group">
										<label class="control-label"><?php _e("Facebook API Key"); ?>:</label>
										<input type="text" name="facebook_api_key" class="form-control" value="<?php echo get_option('facebook_api_key'); ?>" /><small><?php _e("Please go to http://facebook.com/developers/ Register new app add your website url to its Web URL make your app live. And get your API key from dashboard."); ?></small>
									</div>
								</div>
									
								<a class="card-header collapsed d-flex align-items-center" data-toggle="collapse" href="#emailSettings">
									<div class="card-title"><?php _e("Email Settings"); ?></div>
								</a>
								<div id="emailSettings" class="card-body collapse pt-0" data-parent="#accordion">
									<p><?php _e("Email settings used for the system will send Emails to users."); ?></p>
				
									<div class="form-group">
										<label class="control-label"><?php _e("Email From"); ?>*:</label>
										<input type="text" name="email_from" class="form-control" value="<?php echo get_option('email_from'); ?>"  />
									</div>
									
									<div class="form-group">
										<label class="control-label"><?php _e("Reply To"); ?>*:</label>
										<input type="text" name="email_to" class="form-control" value="<?php echo get_option('email_to'); ?>" required />
									</div>
									<div class="form-group">
										<label class="control-label"><?php _e("Activate User Without email Verification?"); ?>:</label>
										<input type="checkbox" name="register_verification" <?php if(get_option('register_verification') == '1'){echo 'checked="checked"'; }?> value="1" title="<?php _e("You can check this box to disable verification of email for registration."); ?>" />
									</div>
								</div>


								<a class="card-header collapsed d-flex align-items-center" data-toggle="collapse" href="#smtpSettings">
									<div class="card-title"><?php _e("SMTP Settings"); ?></div>
								</a>
								<div id="smtpSettings" class="card-body collapse pt-0" data-parent="#accordion">
									<p><?php _e("Activate and add accurate SMTP details to send email through SMTP instead of using local mail server."); ?></p>

									<div class="form-group">
										<label class="control-label"><?php _e("Activate SMTP?"); ?>:</label>
										<input type="checkbox" name="smtp_activation" <?php if(get_option('smtp_activation') == '1'){echo 'checked="checked"'; }?> value="1" title="<?php _e("Emails will be sent through SMTP if checked."); ?>" />
									</div>
				
									<div class="form-group">
										<label class="control-label"><?php _e("SMTP Host"); ?>:</label>
										<input type="text" name="smtp_host" class="form-control" value="<?php echo get_option('smtp_host'); ?>"  />
									</div>

									<div class="form-group">
										<label class="control-label"><?php _e("SMTP Port e.g 25 or 587"); ?>:</label>
										<input type="number" name="smtp_port" class="form-control" value="<?php echo get_option('smtp_port'); ?>"  />
									</div>
									
									<div class="form-group">
										<label class="control-label"><?php _e("SMTP Username"); ?>:</label>
										<input type="text" name="smtp_username" class="form-control" value="<?php echo get_option('smtp_username'); ?>" />
									</div>

									<div class="form-group">
										<label class="control-label"><?php _e("SMTP Password"); ?>:</label>
										<input type="password" name="smtp_password" class="form-control" value="<?php echo get_option('smtp_password'); ?>" />
									</div>
									<?php if ( ! empty( get_option( 'last_smtp_response' ) ) ) : 
										echo '<h2>' . _( 'Last SMTP Response' ) . '</h2>';	
									?>
									<pre><?php echo get_option( 'last_smtp_response' ); ?></pre>
									<?php endif; ?>
								</div>


								<a class="card-header collapsed d-flex align-items-center" data-toggle="collapse" href="#captchaSettings">
									<div class="card-title"><?php _e("Captcha Settings"); ?></div>
								</a>
								<div id="captchaSettings" class="card-body collapse pt-0" data-parent="#accordion">
									<p><?php _e("Captcha Settings Please"); ?> <a href="https://www.google.com/recaptcha/admin/create" target="_blank"><?php _e("Sign up to google reCaptcha"); ?></a> <?php _e("Get your site key and secret key."); ?></p>
									<div class="form-group">
										<label class="control-label"><?php _e("Activate Captcha"); ?>:</label>
										<input type="checkbox" name="activate_captcha" <?php if(get_option('activate_captcha') == '1'){echo 'checked="checked"'; }?> value="1" />
									</div>
									
									<div class="form-group">
										<label class="control-label"><?php _e("Site Key"); ?>:</label>
										<input type="text" class="form-control" name="site_key" value="<?php echo get_option('site_key'); ?>" />
									</div>
									
									<div class="form-group">
										<label class="control-label"><?php _e("Secret Key"); ?>:</label>
										<input type="text" class="form-control" name="secret_key" value="<?php echo get_option('secret_key'); ?>" />
									</div>
								</div>
							</div>
							<!-- End Basic Accordion -->
						</div>
					</div>
					<hr />

					<div class="text-center">
						<input type="hidden" name="settings_submit" value="Yes" />
						<input type="submit" value="<?php _e("Save"); ?>" class="btn btn-primary" />
					</div>
				</form>

			</div><!-- Widget body /-->
		</div><!-- Widget /-->
	</div><!-- Column /-->
</div><!-- Row /-->

<?php
	require_once("lib/includes/footer.php");