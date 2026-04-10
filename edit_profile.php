<?php
	require_once("lib/system_load.php");
	//Including this file we load system.
	
	//This loads system.
	authenticate_user('all');

	$datepicker = 1;
	$croppic 	= 1;
	
	$first_name = $last_name = $gender = $date_of_birth = $address1 = $address2 = $city = $state = $country = $zip_code = $mobile = $phone = $username = $email = $password = $profile_image = $description = $status = $user_type = '';

	if(isset($_POST['profile_image']) && $_POST['profile_image'] != '') { 
		$pr_img = $_POST['profile_image'];
	}

	//User update submission image processing edit user password setting if not changed.
	if(isset($_GET['user_id']) && $_GET['user_id'] != '') { 
		if(isset($pr_img)) { 
			$pr_img = $pr_img;
		} else { 
			if(isset($_POST['already_img'])) { 
				$pr_img = $_POST['already_img'];
			} else { 
				$pr_img = '';
			}
		}
		if(isset($_POST['password']) && $_POST['password'] != '') { 
			if($_POST['password'] == $_POST['confirm_password']) { 
				$password_set = $_POST['password'];
			} else { 
				$message = _("Password do not match");
			}
		} else { 
			$password_set = '';
		}
		if(isset($_POST['update_user']) && $_POST['update_user'] == '1') {
			extract($_POST);
			if($password != $confirm_password){ 
				$message = _("Password do not match");
			} else {
				$message = $new_user->edit_profile($_SESSION['user_id'], $first_name, $last_name, $gender, $date_of_birth, $address1, $address2, $city, $state, $country, $zip_code, $mobile, $phone, $username, $email, $password_set, $pr_img, $description);
				if(isset($_POST['message_email_notification']) && $_POST['message_email_notification'] == '1') {
					$new_user->set_user_meta($_SESSION['user_id'], 'message_email', $_POST['message_email_notification']);
				} else { 
					$new_user->set_user_meta($_SESSION['user_id'], 'message_email', '0');
				}

				//Additional fields update.
				$_additionalarr = return_additionalfields_array( 'edit' );
				if ( ! empty( $_additionalarr ) && is_array( $_additionalarr ) ) {
					foreach( $_additionalarr as $theadditional ) {
						( isset( $_POST[$theadditional] ) && ! empty( $_POST[$theadditional] ) ) ? $new_user->set_usermeta( $_SESSION['user_id'], $theadditional, $_POST[$theadditional] ) : '';
					}
				}
			}
		}
	}//update user submission.

	if(isset($_GET['user_id']) && $_GET['user_id'] != '') { 
		$new_user->set_user($_GET['user_id'], $_SESSION['user_type'], $_SESSION['user_id']);
	}//setting user data if editing.

	$page_title = _("Edit your profile"); //You can edit this to change your page title.
	require_once('lib/includes/header.php');

	$_fields_array = array( 'first_name', 'last_name', 'gender', 'date_of_birth', 'address1', 'address2', 'city', 'state', 'zip_code', 'country', 'mobile', 'phone', 'profile_image', 'description' );

	$_fieldarr = array();
	foreach ( $_fields_array as $field ) {
		$_fieldarr[$field]['label']  = get_option( "accountform_setting_". $field ."_field_label" );
		$_fieldarr[$field]['status'] = get_option( "accountform_setting_". $field ."_edit_profile" );
	}
?>
<div class="row flex-row">
	<div class="col-12">
		<!-- Form -->
		<div class="widget has-shadow">
			<div class="widget-body">
				<!-- Referral Link Section -->
				<div class="form-group row d-flex align-items-center mb-5" style="background: #f8f9fa; padding: 20px 0; border-radius: 8px; border: 1px dashed #ECAD3D; margin: 0 15px 30px 15px;">
					<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" style="font-weight: 700; color: #333;">
						<i class="la la-share-alt-square mr-2" style="font-size: 20px; color: #ECAD3D;"></i> <?php _e("Your Referral Link"); ?>
					</label>
					<div class="col-lg-6">
						<div class="input-group">
							<input type="text" class="form-control" id="referral_link" value="<?php echo rtrim(SITEURL, '/') . '/register.php?ref=' . $new_user->username; ?>" readonly style="background: #fff; border-color: #ECAD3D; font-weight: 500;" />
							<button class="btn btn-primary btn-golden-admin" type="button" onclick="copyReferralLink()">
								<i class="la la-copy"></i> <?php _e("Copy Link"); ?>
							</button>
						</div>
						<small id="copy_msg" class="text-success" style="display:none; margin-top: 5px; font-weight: 600;"><i class="la la-check-circle"></i> <?php _e("Link copied to clipboard!"); ?></small>
					</div>
				</div>

				<script>
				function copyReferralLink() {
					var copyText = document.getElementById("referral_link");
					copyText.select();
					copyText.setSelectionRange(0, 99999); // For mobile devices

					try {
						// Use modern clipboard API if available and in secure context
						if (navigator.clipboard && window.isSecureContext) {
							navigator.clipboard.writeText(copyText.value).then(showSuccess);
						} else {
							// Fallback for HTTP/local development
							document.execCommand("copy");
							showSuccess();
						}
					} catch (err) {
						// Final fallback if everything else fails
						document.execCommand("copy");
						showSuccess();
					}

					function showSuccess() {
						var msg = document.getElementById("copy_msg");
						msg.style.display = "block";
						setTimeout(function() {
							msg.style.display = "none";
						}, 3000);
					}
				}
				</script>
				<form action="<?php $_SERVER['PHP_SELF']?>" id="add_user" name="user" method="post" enctype="multipart/form-data">

					<?php if ( ! isset( $_fieldarr['first_name']['status'] ) || $_fieldarr['first_name']['status'] != 'hide' ) : ?>
					<div class="form-group row d-flex align-items-center mb-5">
						<?php $_label = ( isset( $_fieldarr['first_name']['label'] ) && ! empty( $_fieldarr['first_name']['label'] ) ) ? $_fieldarr['first_name']['label'] : _( 'First Name' ); ?>
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="first_name"><?php echo $_label ?>*</label>
						<div class="col-lg-5">
							<input type="text" name="first_name" id="first_name" class="form-control" placeholder="<?php _e("Enter First Name"); ?>" value="<?php echo $new_user->first_name; ?>" required="required" />
						</div>
					</div>
					<?php endif; ?>
					
					<?php if ( ! isset( $_fieldarr['last_name']['status'] ) || $_fieldarr['last_name']['status'] != 'hide' ) : ?>
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="last_name">
						<?php $_label = ( isset( $_fieldarr['last_name']['label'] ) && ! empty( isset( $_fieldarr['last_name']['label'] ) ) ) ? $_fieldarr['last_name']['label'] : _( 'Last Name' ); ?>
						<?php echo $_label ?>*
						</label>
						<div class="col-lg-5">
							<input type="text" id="last_name" name="last_name" class="form-control" placeholder="<?php _e("Enter Last Name"); ?>" value="<?php echo $new_user->last_name; ?>" required="required" />
						</div>
					</div>
					<?php endif; ?>
					
					<?php if ( ! isset( $_fieldarr['gender']['status'] ) || $_fieldarr['gender']['status'] != 'hide' ) : ?>
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="gender">
						<?php $_label = ( isset( $_fieldarr['gender']['label'] ) && ! empty( $_fieldarr['gender']['label'] ) ) ? $_fieldarr['gender']['label'] : _( 'Gender' ); ?>
						<?php echo $_label ?>
						</label>
						<div class="col-lg-5">
							<select class="custom-select form-control" id="gender" name="gender">
								<option vale=''><?php _e("Select Gender"); ?></option>
								<option value="<?php _e("Male"); ?>" <?php if($new_user->gender == _("Male")) { echo 'selected="selected"'; } ?>><?php _e("Male"); ?></option>
								<option value="<?php _e("Female"); ?>" <?php if($new_user->gender == _("Female")) { echo 'selected="selected"'; } ?>><?php _e("Female"); ?></option>
							</select>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['date_of_birth']['status'] ) || $_fieldarr['date_of_birth']['status'] != 'hide' ) : ?>
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="dob">
						<?php $_label = ( isset( $_fieldarr['date_of_birth']['label'] ) && ! empty( $_fieldarr['date_of_birth']['label'] ) ) ? $_fieldarr['date_of_birth']['label'] : _( 'Date of birth' ); ?>
						<?php echo $_label ?>
						</label>
						<div class="col-lg-5">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="la la-calendar"></i>
									</span>
									<input type="text" id="dob" name="date_of_birth" class="form-control" 
									placeholder="<?php _e("Date of Birth"); ?> 2013-12-03" 
									value="<?php 
												$dob = $new_user->date_of_birth; 
												echo date("m/d/Y", strtotime($dob));
										   ?>" />
								</div>
							</div>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['address1']['status'] ) || $_fieldarr['address1']['status'] != 'hide' ) : ?>
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="addOne">
						<?php $_label = ( isset( $_fieldarr['address1']['label'] ) && ! empty( $_fieldarr['address1']['label'] ) ) ? $_fieldarr['address1']['label'] : _( 'Address' ); ?>
						<?php echo $_label ?> 1
						</label>
						<div class="col-lg-5">
							<textarea name="address1" id="addOne" class="form-control" placeholder="<?php _e("Address"); ?> 1"><?php echo $new_user->address1; ?></textarea>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['address2']['status'] ) || $_fieldarr['address2']['status'] != 'hide' ) : ?>
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="addTwo">
						<?php $_label = ( isset( $_fieldarr['address2']['label'] ) && ! empty( $_fieldarr['address2']['label'] ) ) ? $_fieldarr['address2']['label'] : _( 'Address' ); ?>
						<?php echo $_label ?> 2
						</label>
						<div class="col-lg-5">
							<textarea name="address2" id="addTwo" class="form-control" placeholder="<?php _e("Address"); ?> 2"><?php echo $new_user->address2; ?></textarea>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['city']['status'] ) || $_fieldarr['city']['status'] != 'hide' ) : ?>
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="city">
						<?php $_label = ( isset( $_fieldarr['city']['label'] ) && ! empty( $_fieldarr['city']['label'] ) ) ? $_fieldarr['city']['label'] : _( 'City' ); ?>
						<?php echo $_label ?>
						</label>
						<div class="col-lg-5">
							<input type="text" name="city" id="city" class="form-control" placeholder="<?php _e("City"); ?>" value="<?php echo $new_user->city; ?>" />
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['state']['status'] ) || $_fieldarr['state']['status'] != 'hide' ) : ?>
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="state">
						<?php $_label = ( isset( $_fieldarr['state']['label'] ) && ! empty( $_fieldarr['state']['label'] ) ) ? $_fieldarr['state']['label'] : _( 'State' ); ?>
						<?php echo $_label ?>
						</label>
						<div class="col-lg-5">
							<input type="text" id="state" name="state" 
							class="form-control" placeholder="<?php _e("State OR Province"); ?>" value="<?php echo $new_user->state; ?>" />
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['zip_code']['status'] ) || $_fieldarr['zip_code']['status'] != 'hide' ) : ?>
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="zipcode">
						<?php $_label = ( isset( $_fieldarr['zip_code']['label'] ) && ! empty( $_fieldarr['zip_code']['label'] ) ) ? $_fieldarr['zip_code']['label'] : _( 'Zip Code' ); ?>
						<?php echo $_label ?>
						</label>
						<div class="col-lg-5">
							<input type="text" name="zip_code" id="zipcode" class="form-control" placeholder="<?php _e("Zip Code"); ?>" value="<?php echo $new_user->zip_code; ?>" />
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['country']['status'] ) || $_fieldarr['country']['status'] != 'hide' ) : ?>
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="country">
						<?php $_label = ( isset( $_fieldarr['country']['label'] ) && ! empty( $_fieldarr['country']['label'] ) ) ? $_fieldarr['country']['label'] : _( 'Country' ); ?>
						<?php echo $_label ?>
						</label>
						<div class="col-lg-5">
							<select class="custom-select form-control" id="country" name="country">
								<?php countries_dropdown($new_user->country); ?>
							</select>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['mobile']['status'] ) || $_fieldarr['mobile']['status'] != 'hide' ) : ?>
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="mobile">
						<?php $_label = ( isset( $_fieldarr['mobile']['label'] ) && ! empty( $_fieldarr['mobile']['label'] ) ) ? $_fieldarr['mobile']['label'] : _( 'Mobile' ); ?>
						<?php echo $_label ?>
						</label>
						<div class="col-lg-5">
							<input type="text" name="mobile" id="mobile" class="form-control" placeholder="<?php _e("Mobile"); ?>" value="<?php echo $new_user->mobile; ?>" />
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['phone']['status'] ) || $_fieldarr['phone']['status'] != 'hide' ) : ?>
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="phone">
						<?php $_label = ( isset( $_fieldarr['phone']['label'] ) && ! empty( $_fieldarr['phone']['label'] ) ) ? $_fieldarr['phone']['label'] : _( 'Phone' ); ?>
						<?php echo $_label ?>
						</label>
						<div class="col-lg-5">
							<input type="text" name="phone" id="phone" class="form-control" placeholder="<?php _e("Phone"); ?>" value="<?php echo $new_user->phone; ?>" />
						</div>
					</div>
					<?php endif; ?>

					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="username">
							<?php _e("Username"); ?>*
						</label>
						<div class="col-lg-5">
							<input type="text" class="form-control" id="username" name="username" placeholder="<?php _e("Username"); ?>" value="<?php echo $new_user->username; ?>" required="required" />
						</div>
					</div>


					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="email">
							<?php _e("Email"); ?>*
						</label>
						<div class="col-lg-5">
							<input type="email" id="email" class="form-control" name="email" placeholder="<?php _e("Email"); ?>" value="<?php echo $new_user->email; ?>" required="required" />
						</div>
					</div>

					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="password">
							<?php _e("Password"); ?>
						</label>
						<div class="col-lg-5">
							<input type="password" id="password" class="form-control" name="password" placeholder="<?php _e("Password"); ?>" value="" /><small><?php _e("Leave blank if you don't want to change password"); ?></small>
						</div>
					</div>

					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="confirmpassword">
							<?php _e("Confirm Password"); ?>
						</label>
						<div class="col-lg-5">
							<input class="form-control" id="confirmpassword" type="password" name="confirm_password" placeholder="<?php _e("Confirm Password"); ?>" value="" />
						</div>
					</div>

					<?php if ( ! isset( $_fieldarr['profile_image']['status'] ) || $_fieldarr['profile_image']['status'] != 'hide' ) : ?>
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="cropContaineroutput">
						<?php $_label = ( isset( $_fieldarr['profile_image']['label'] ) && ! empty( $_fieldarr['profile_image']['label'] ) ) ? $_fieldarr['profile_image']['label'] : _( 'Profile Image' ); ?>
						<?php echo $_label ?>
						</label>
						<div class="col-lg-5">
							<div class="clearfix"></div>
							<div class="clearfix"></div>
							<div class="col-lg-4 ">
								<div id="cropContaineroutput"></div>
								<input type="hidden" name="profile_image" id="cropOutput" value="" />
							</div>
							<?php
								if(isset($new_user->profile_image) && $new_user->profile_image != '') {
									echo '<a href="'.$new_user->profile_image.'" target="_blank"><img src="'.$new_user->profile_image.'" height="80" class="pull-left img-thumbnail" style="height:80px;" /></a><input type="hidden" name="already_img" value="'.$new_user->profile_image.'">';	
								}
							?>
							<div class="clearfix"></div>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['description']['status'] ) || $_fieldarr['description']['status'] != 'hide' ) : ?>
					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="description">
						<?php $_label = ( isset( $_fieldarr['description']['label'] ) && ! empty( $_fieldarr['description']['label'] ) ) ? $_fieldarr['description']['label'] : _( 'Description' ); ?>
						<?php echo $_label ?>
						</label>
						<div class="col-lg-5">
							<textarea name="description" id="description" class="form-control" placeholder="<?php _e("Description"); ?>"><?php echo $new_user->description; ?></textarea>
						</div>
					</div>
					<?php endif; ?>
					<?php echo return_additional_field_options( $_SESSION['user_id'], 'edit' ); ?>

					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="emailnotification">
							<?php _e("Send Email on new message"); ?>:
						</label>
						<div class="col-lg-5">
							<input type="checkbox" id="emailnotification" name="message_email_notification" <?php if($new_user->get_user_meta($_SESSION['user_id'], 'message_email') == '1'){echo 'checked="checked"'; }?> value="1" />				
						</div>
					</div>
					
					<div class="text-center">
						<input type="hidden" name="update_user" value="1" /> 
						<input type="submit" value="<?php _e("Update User"); ?>" class="btn btn-primary align-items-center" />
					</div>
				</form>
			</div>
		</div>
		<!-- End Form -->
	</div>
</div>

<?php
	require_once("lib/includes/footer.php");