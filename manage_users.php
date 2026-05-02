<?php
	require_once("lib/system_load.php");
	//This loads system.
	
	//user Authentication.
	authenticate_user('admin');
	
	$first_name = $last_name = $gender = $date_of_birth = $address1 = $address2 = $city = $state = $country = $zip_code = $mobile = $phone = $username = $email = $password = $profile_image = $description = $status = $user_type = $referral_id = '';

	$datepicker = 1;
	$croppic 	= 1;

	if(isset($_POST['profile_image']) && $_POST['profile_image'] != '') { 
		$pr_img = $_POST['profile_image'];
	}

	//User update submission image processing edit user password setting if not changed.
	if(isset($_POST['edit_user']) && $_POST['edit_user'] != '') {


		if(isset($pr_img)) { 
			$pr_img = $pr_img;
		} else { 
			if(isset($_POST['already_profile_image'])) { 
				$pr_img = $_POST['already_profile_image'];
			} else if(isset($_POST['already_img'])) { 
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
				// Handle Profile Image Upload
				if(isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0) {
					$url = wc_upload_image_return_url($_FILES['profile_image'], 'users');
					if(is_string($url)) $pr_img = $url;
				}

				// Handle NIC Uploads
				$nic_front_url = '';
				if(isset($_FILES['nic_front']) && $_FILES['nic_front']['size'] > 0) {
					$url = wc_upload_image_return_url($_FILES['nic_front'], 'users');
					if(is_string($url)) $nic_front_url = $url;
				}
				$nic_back_url = '';
				if(isset($_FILES['nic_back']) && $_FILES['nic_back']['size'] > 0) {
					$url = wc_upload_image_return_url($_FILES['nic_back'], 'users');
					if(is_string($url)) $nic_back_url = $url;
				}

				// Convert Date for DB
				if(!empty($date_of_birth)) $date_of_birth = date("Y-m-d", strtotime($date_of_birth));
				$message = $new_user->update_user( $_POST['edit_user'], $_SESSION['user_type'], $first_name, $last_name, $gender, $date_of_birth, $address1, $address2, $city, $state, $country, $zip_code, $mobile, $phone, $username, $email, $password_set, $pr_img, $description, $status, $user_type,$referral_id, $bank_name, $account_holder, $account_number, $iban_no, $branch_name, $branch_code, $nic_front_url, $nic_back_url);
				$user_id = $_POST['edit_user'];
			}
		}
	}//update user submission.
	
	if(isset($_POST['edit_user']) && $_POST['edit_user'] != '') { 
		$new_user->set_user($_POST['edit_user'], $_SESSION['user_type'], $_SESSION['user_id']);
	}//setting user data if editing. 	
	
	//add user processing.
	if( isset( $_POST['add_user'] ) && $_POST['add_user'] == '1' ) { 
		extract( $_POST ); 
		if($email == '') { 
			$message = _("Email Required");;
		} else if( $password == '' ){ 
			$message = _("Password Required");;
		} else if($password != $confirm_password){ 
			$message = _("Password do not match");;
		} else if($status == '0') { 
			$message = _("User Status Required");;
		} else if($user_type == '0') { 
			$message = _("User Type Required");;
		}  else {
			// Convert Date for DB
			if(!empty($date_of_birth)) $date_of_birth = date("Y-m-d", strtotime($date_of_birth));
			$received = $new_user->add_user( $first_name, $last_name, $gender, $date_of_birth, $address1, $address2, $city, $state, $country, $zip_code, $mobile, $phone, $username, $email, $password, $profile_image, $description, $status, $user_type,$referral_id, $bank_name, $account_holder, $account_number, $iban_no, $branch_name, $branch_code);

			$user_id = ( is_array($received) && isset( $received['user_id'] ) && ! empty( $received['user_id'] ) ) ? $received['user_id'] : '';
			if(is_array($received) && isset($received['message'])) {
				$message = $received['message'];
			} else if(is_string($received)) {
				$message = $received;
			} else {
				$message = 'An error occured while processing the request';
			}

			if(!empty($user_id)) {
				// Handle Image uploads
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
			}
		}
	}//add user processing ends here.
	
	// Handle Image updates on edit (Legacy block removed, now handled in update_user call)
	/*
	if(isset($_POST['edit_user']) && $_POST['edit_user'] != '' && isset($_POST['update_user'])) {
		...
	}
	*/
	
	//Additional fields update.
	if ( isset( $user_id ) && ! empty( $user_id ) ) {
		$_additionalarr = return_additionalfields_array( 'update' );
		if ( ! empty( $_additionalarr ) && is_array( $_additionalarr ) ) {
			foreach( $_additionalarr as $theadditional ) {
				( isset( $_POST[$theadditional] ) && ! empty( $_POST[$theadditional] ) ) ? $new_user->set_usermeta( $user_id, $theadditional, $_POST[$theadditional] ) : '';
			}
		}
	}
	if ( isset( $_POST['edit_user'] ) ) { $page_title = _("Edit User"); } else { $page_title = _( "Add New User" ); } //page title set.
	require_once("lib/includes/header.php"); //including header file.

	$_fields_array = array( 'first_name', 'last_name', 'gender', 'date_of_birth', 'address1', 'address2', 'city', 'state', 'zip_code', 'country', 'mobile', 'phone', 'profile_image', 'description', 'bank_name', 'account_holder', 'account_number', 'iban_no', 'branch_name', 'branch_code' );

	$_fieldarr = array();
	foreach ( $_fields_array as $field ) {
		$_fieldarr[$field]['label']  = get_option( "accountform_setting_". $field ."_field_label" );
		$_fieldarr[$field]['status'] = get_option( "accountform_setting_". $field ."_update_form" );
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
	.admin-form-group { margin-bottom: 22px; }
	.admin-form-group label { font-weight: 700; color: #333; margin-bottom: 8px; display: block; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
	.admin-form-group .form-control { border-radius: 4px; border: 1px solid #dcdcdc; padding: 10px 12px; height: 42px; background: #fff; transition: all 0.2s; font-size: 14px; width: 100%; }
	.admin-form-group .form-control:focus { border-color: #5d5386; box-shadow: 0 0 0 3px rgba(93, 83, 134, 0.08); outline: none; }
	
	/* Password Toggle Fix */
	.pass-wrapper { position: relative; width: 100%; }
	.pass-wrapper input { padding-right: 45px !important; }
	.toggle-password { 
		position: absolute; 
		right: 5px; 
		top: 106%; 
		transform: translateY(-50%); 
		cursor: pointer !important; 
		color: #888; 
		z-index: 99;
		font-size: 16px;
		pointer-events: auto !important;
	}
	.toggle-password:hover { color: #5d5386; }

	.error-msg { color: #dc3545; font-size: 11px; font-weight: 600; margin-top: 4px; display: none; text-transform: none; letter-spacing: 0; }
	.has-error .form-control { border-color: #dc3545 !important; }
	.has-error .error-msg { display: block; }

	.image-upload-zone { border: 2px dashed #dcdcdc; border-radius: 10px; padding: 15px; text-align: center; background: #fafafa; transition: all 0.3s; cursor: pointer; margin-bottom: 20px; }
	.image-upload-zone:hover { border-color: #5d5386; background: #f4f4f9; }
	.preview-box { height: 110px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }
	.preview-box img { max-height: 100%; max-width: 100%; object-fit: contain; border-radius: 6px; }
	#referrer_name_display { display: block; font-size: 12px; margin-top: 6px; font-weight: 700; min-height: 18px; }
	.section-header { border-bottom: 2px solid #5d5386; margin: 30px 0 20px 0; display: inline-block; padding-bottom: 3px; }
	.section-header h5 { margin: 0; font-weight: 800; color: #5d5386; font-size: 15px; text-transform: uppercase; letter-spacing: 1px; }
</style>

<div class="row flex-row">
	<div class="col-12">
		<div class="widget has-shadow">
			<div class="widget-body">
				<form action="<?php $_SERVER['PHP_SELF']?>" id="add_user" name="user" method="post" enctype="multipart/form-data" role="form" novalidate>
					<?php if ( isset( $message ) && !is_logged_in() ) { return_info_messages( $message ); } ?>
					
					<div class="section-header"><h5>Basic Details</h5></div>
					<div class="row">
						<?php if ( ! isset( $_fieldarr['first_name']['status'] ) || $_fieldarr['first_name']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php echo (isset($_fieldarr['first_name']['label'])) ? $_fieldarr['first_name']['label'] : _('First Name'); ?>*</label>
								<input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo $new_user->first_name; ?>" placeholder="Enter First Name" />
								<div class="error-msg"><?php _e("This field is required"); ?></div>
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['last_name']['status'] ) || $_fieldarr['last_name']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php echo (isset($_fieldarr['last_name']['label'])) ? $_fieldarr['last_name']['label'] : _('Last Name'); ?>*</label>
								<input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo $new_user->last_name; ?>" placeholder="Enter Last Name" />
								<div class="error-msg"><?php _e("This field is required"); ?></div>
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['gender']['status'] ) || $_fieldarr['gender']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>Gender</label>
								<select class="form-control" id="gender" name="gender">
									<option value=""><?php _e("Select Gender"); ?></option>
									<option value="Male" <?php if($new_user->gender == 'Male') { echo 'selected="selected"'; } ?>><?php _e("Male"); ?></option>
									<option value="Female" <?php if($new_user->gender == 'Female') { echo 'selected="selected"'; } ?>><?php _e("Female"); ?></option>
								</select>
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['date_of_birth']['status'] ) || $_fieldarr['date_of_birth']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>Date of birth</label>
								<input type="text" id="dob" name="date_of_birth" class="form-control" value="<?php echo (!empty($new_user->date_of_birth)) ? date("m/d/Y", strtotime($new_user->date_of_birth)) : ''; ?>" autocomplete="off" />
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['mobile']['status'] ) || $_fieldarr['mobile']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>Mobile</label>
								<input type="text" name="mobile" id="mobile" class="form-control" value="<?php echo $new_user->mobile; ?>" placeholder="e.g. +923001234567" />
								<div class="error-msg"><?php _e("This field is required"); ?></div>
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['phone']['status'] ) || $_fieldarr['phone']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>Phone</label>
								<input type="text" name="phone" id="phone" class="form-control" value="<?php echo $new_user->phone; ?>" placeholder="Enter Phone Number" />
							</div>
						</div>
						<?php endif; ?>
					</div>

					<div class="section-header"><h5>Location Info</h5></div>
					<div class="row">
						<?php if ( ! isset( $_fieldarr['address1']['status'] ) || $_fieldarr['address1']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>Address 1</label>
								<input type="text" name="address1" id="address1" class="form-control" value="<?php echo $new_user->address1; ?>" placeholder="Street Address, P.O. Box, etc." />
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['address2']['status'] ) || $_fieldarr['address2']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>Address 2</label>
								<input type="text" name="address2" id="address2" class="form-control" value="<?php echo $new_user->address2; ?>" placeholder="Apartment, suite, unit, building, floor, etc." />
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['city']['status'] ) || $_fieldarr['city']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>City</label>
								<input type="text" name="city" id="city" class="form-control" value="<?php echo $new_user->city; ?>" placeholder="e.g. New York" />
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['state']['status'] ) || $_fieldarr['state']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>State</label>
								<input type="text" name="state" id="state" class="form-control" value="<?php echo $new_user->state; ?>" placeholder="e.g. California" />
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['zip_code']['status'] ) || $_fieldarr['zip_code']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>Zip Code</label>
								<input type="text" name="zip_code" id="zip_code" class="form-control" value="<?php echo $new_user->zip_code; ?>" placeholder="e.g. 10001" />
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['country']['status'] ) || $_fieldarr['country']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>Country</label>
								<select name="country" class="form-control">
									<?php countries_dropdown($new_user->country); ?>
								</select>
							</div>
						</div>
						<?php endif; ?>
					</div>

					<div class="section-header"><h5>Account Settings</h5></div>
					<div class="row">
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("Username"); ?>*</label>
								<input type="text" name="username" class="form-control" value="<?php echo isset($_POST['edit_user']) ? $new_user->username : $auto_generated_user_name; ?>"  placeholder="Auto-generated" />
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("Email"); ?>*</label>
								<input type="email" name="email" class="form-control" value="<?php echo $new_user->email; ?>" placeholder="example@mail.com" />
								<div class="error-msg"><?php _e("This field is required"); ?></div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("Status"); ?>*</label>
								<select name="status" class="form-control" required>
									<option <?php if($new_user->status == 'activate'){echo 'selected="selected"';} ?> value="activate"><?php _e("Active"); ?></option>
									<option <?php if($new_user->status == 'deactivate'){echo 'selected="selected"';} ?> value="deactivate"><?php _e("Deactive"); ?></option>
									<option <?php if($new_user->status == 'ban'){echo 'selected="selected"';} ?> value="ban"><?php _e("Ban"); ?></option>
									<option <?php if($new_user->status == 'suspend'){echo 'selected="selected"';} ?> value="suspend"><?php _e("Suspend"); ?></option>
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("Password"); ?><?php echo (!isset($_POST['edit_user'])) ? '*' : ''; ?></label>
								<div class="pass-wrapper">
									<input type="password" name="password" class="form-control" placeholder="Enter Secure Password" />
									<i class="toggle-password fa fa-fw fa-eye-slash"></i>
								</div>
								<div class="error-msg"><?php _e("This field is required"); ?></div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("Confirm Pass"); ?><?php echo (!isset($_POST['edit_user'])) ? '*' : ''; ?></label>
								<div class="pass-wrapper">
									<input type="password" name="confirm_password" class="form-control" placeholder="Repeat Password" />
									<i class="toggle-password fa fa-fw fa-eye-slash"></i>
								</div>
								<div class="error-msg" id="confirm_err_admin"><?php _e("This field is required"); ?></div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("User Type"); ?>*</label>
								<select name="user_type" class="form-control" required>
									<option value="admin" <?php if($new_user->user_type == 'admin'){echo 'selected="selected"';} ?>><?php _e("Admin"); ?></option>
									<?php $new_level->userlevel_options($new_user->user_type); ?>
								</select>
							</div>
						</div>
					</div>

					<div class="section-header"><h5>Referral Information</h5></div>
					<div class="row">
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("Referrer Username"); ?></label>
								<input type="text" id="referrer_username" class="form-control" autocomplete="off" placeholder="Search by Referrer Username" />
								<input type="hidden" name="referral_id" id="referral_id" value="<?php echo isset($new_user->referral_id) ? $new_user->referral_id : '0'; ?>">
								<span id="referrer_name_display" style="color: #28a745;"></span>
							</div>
						</div>
					</div>

					<div class="section-header"><h5>Media & Uploads</h5></div>
					<div class="row">
						<?php
						$images = [
							'profile_image' => ['label' => 'Profile Picture', 'val' => $new_user->profile_image],
							'nic_front' => ['label' => 'NIC Front Side', 'val' => $new_user->get_user_info(@$new_user->user_id, 'nic_front')],
							'nic_back' => ['label' => 'NIC Back Side', 'val' => $new_user->get_user_info(@$new_user->user_id, 'nic_back')]
						];
						foreach ($images as $key => $img) :
						?>
						<div class="col-md-4">
							<div class="form-group admin-form-group mb-0">
							<div class="image-upload-zone" id="zone_<?php echo $key; ?>" onclick="document.getElementById('input_<?php echo $key; ?>').click()">
								<label><?php _e($img['label']); ?></label>
								<div class="preview-box">
									<img id="preview_<?php echo $key; ?>" src="<?php echo ($img['val'] != '') ? $img['val'] : 'assets/images/thumb.png'; ?>">
								</div>
								<input type="file" name="<?php echo $key; ?>" id="input_<?php echo $key; ?>" class="d-none" accept="image/*" onchange="showPreview(this, 'preview_<?php echo $key; ?>')">
								<input type="hidden" name="already_<?php echo $key; ?>" value="<?php echo $img['val']; ?>">
								<span class="btn btn-sm btn-outline-secondary"><?php _e("Change Image"); ?></span>
							</div>
							<div class="error-msg text-center mb-3" style="margin-top:-15px"><?php _e("Identification photo is required"); ?></div>
							</div>
						</div>
						<?php endforeach; ?>
					</div>

					<div class="section-header"><h5>Bank Details</h5></div>
					<div class="row">
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("Bank Name"); ?></label>
								<input type="text" name="bank_name" class="form-control" value="<?php echo $new_user->bank_name; ?>" placeholder="Bank Name" />
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("Account Holder Name"); ?></label>
								<input type="text" name="account_holder" class="form-control" value="<?php echo $new_user->account_holder; ?>" placeholder="Account Holder" />
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("Account Number"); ?></label>
								<input type="text" name="account_number" class="form-control" value="<?php echo $new_user->account_number; ?>" placeholder="Account Number" />
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("IBAN No"); ?></label>
								<input type="text" name="iban_no" class="form-control" value="<?php echo $new_user->iban_no; ?>" placeholder="IBAN" />
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("Branch Name"); ?></label>
								<input type="text" name="branch_name" class="form-control" value="<?php echo $new_user->branch_name; ?>" placeholder="Branch Name" />
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("Branch Code"); ?></label>
								<input type="text" name="branch_code" class="form-control" value="<?php echo $new_user->branch_code; ?>" placeholder="Branch Code" />
							</div>
						</div>
					</div>

					<div class="section-header"><h5>Extra Details</h5></div>
					<div class="row">
						<div class="col-12">
							<div class="form-group admin-form-group">
								<label><?php _e("Additional Details"); ?></label>
								<textarea name="description" class="form-control bank-textarea" rows="3" placeholder="Enter any other additional details..."><?php echo $new_user->description; ?></textarea>
							</div>
						</div>
					</div>

					<?php echo return_additional_field_options( @$new_user->user_id, 'update' ); ?>

					<div class="text-center mt-5 mb-5">
						<?php if ( isset( $_POST['edit_user'] ) ) { ?>
							<input type="hidden" name="edit_user" value="<?php echo $_POST['edit_user']; ?>" />
							<input type="hidden" name="update_user" value="1" /> 
							<button type="submit" class="btn btn-primary btn-md btn-golden"><?php _e("Update User"); ?></button>
						<?php } else { ?>
							<input type="hidden" name="add_user" value="1" />
							<button type="submit" class="btn btn-primary btn-md btn-golden"><?php _e("Create User"); ?></button>
						<?php } ?>
					</div>
				</form>
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
		$('#add_user').on('submit', function(e) {
			let isValid = true;
			$('.admin-form-group').removeClass('has-error');
			const isEdit = $('[name="edit_user"]').length > 0;
			
			// Regular Input Check
			const requiredFields = ['first_name', 'last_name', 'email', 'mobile'];
			requiredFields.forEach(name => {
				const el = $(`[name="${name}"]`);
				if(!el.val() || !el.val().trim()) {
					el.closest('.admin-form-group').addClass('has-error');
					isValid = false;
				}
			});

			// Passwords check
			const pass = $('[name="password"]').val();
			const confirm = $('[name="confirm_password"]').val();
			
			// Required for new users
			if(!isEdit && (!pass || !pass.trim())) {
				$('[name="password"]').closest('.admin-form-group').addClass('has-error');
				isValid = false;
			}

			// Match check if either is filled
			if(pass && confirm && pass !== confirm) {
				$('[name="confirm_password"]').closest('.admin-form-group').addClass('has-error');
				$('#confirm_err_admin').text("Passwords do not match").show();
				isValid = false;
			}

			// Image Verification (Required for new users)
			if(!isEdit) {
				const keys = ['nic_front', 'nic_back'];
				keys.forEach(key => {
					const input = $('#input_'+key)[0];
					if(!input.files || !input.files.length) {
						$('#zone_'+key).closest('.admin-form-group').addClass('has-error');
						isValid = false;
					}
				});
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
		$subArr = [];
		$subs = $db->query("SELECT first_name, last_name, user_id, username FROM users WHERE user_type LIKE '%subscriber%' ORDER BY username ASC");
		if ($subs) {
			while($u = $subs->fetch_assoc()){
				$fname = addslashes($u['first_name'] . ' ' . $u['last_name']);
				$username = strtolower($u['username']);
				$subArr[] = '"' . strtolower($u['username']) . '": {"id": "' . $u['user_id'] . '", "name": "' . $fname . '"}';
			}
			echo implode(",\n", $subArr);
		}
	?>
	};
	// document.getElementById('referrer_username').addEventListener('input', function() {
	// 	var username = this.value.trim().toLowerCase();
	// 	var displaySpan = document.getElementById('referrer_name_display');
	// 	var hiddenId = document.getElementById('referral_id');
	// 	if(username && subscribersData[username]) {
	// 		displaySpan.textContent = "VERIFIED: " + subscribersData[username].name;
	// 		displaySpan.style.color = '#28a745';
	// 		hiddenId.value = subscribersData[username].id;
	// 	} else {
	// 		displaySpan.textContent = username.length > 0 ? 'USER NOT FOUND' : '';
	// 		displaySpan.style.color = '#dc3545';
	// 		hiddenId.value = 0;
	// 	}
	// });
    document.getElementById('referrer_username').addEventListener('input', function() {
        var username = this.value.trim().toLowerCase();
        var display = document.getElementById('referrer_name_display');
        var hidden = document.getElementById('referral_id');
        if(username && subscribersData[username]) {
            display.textContent = username.toUpperCase() + " - " + subscribersData[username].name;
            display.style.color = '#28a745';
            hidden.value = subscribersData[username].id;
        } else {
            display.textContent = username.length > 0 ? 'USER NOT FOUND' : '';
            display.style.color = '#dc3545';
            hidden.value = 0;
        }
    });	

    }

	<?php
	$ref_id = isset($new_user->referral_id) ? $new_user->referral_id : '0';
	if($ref_id != '0') {
		$ref_un = $db->query("SELECT username FROM users WHERE user_id = '$ref_id'")->fetch_assoc();
		if($ref_un) { ?>
			document.getElementById('referrer_username').value = "<?php echo $ref_un['username']; ?>";
			document.getElementById('referrer_username').dispatchEvent(new Event('input'));
		<?php }
	} ?>
</script>

<?php
	require_once("lib/includes/footer.php");
?>

<?php
	exit();
?>