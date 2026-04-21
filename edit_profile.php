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
	if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != '') { 
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
				// Handle Profile Image Upload
				if(isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0) {
					$url = wc_upload_image_return_url($_FILES['profile_image'], 'users');
					if(is_string($url)) $pr_img = $url;
				}

				// Convert Date for DB
				if(!empty($date_of_birth)) $date_of_birth = date("Y-m-d", strtotime($date_of_birth));
				$message = $new_user->edit_profile($_SESSION['user_id'], $first_name, $last_name, $gender, $date_of_birth, $address1, $address2, $city, $state, $country, $zip_code, $mobile, $phone, $username, $email, $password_set, $pr_img, $description, $bank_name, $account_holder, $account_number, $iban_no, $branch_name, $branch_code);
				
				$user_id = $_SESSION['user_id'];
				
				// Handle NIC image uploads
				if(isset($_FILES['nic_front']) && $_FILES['nic_front']['size'] > 0) {
					$url = wc_upload_image_return_url($_FILES['nic_front'], 'users');
					if(is_string($url)) $new_user->update_user_row($user_id, 'nic_front', $url);
				}
				if(isset($_FILES['nic_back']) && $_FILES['nic_back']['size'] > 0) {
					$url = wc_upload_image_return_url($_FILES['nic_back'], 'users');
					if(is_string($url)) $new_user->update_user_row($user_id, 'nic_back', $url);
				}

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

	$new_user->set_user($_SESSION['user_id'], $_SESSION['user_type'], $_SESSION['user_id']);

	$page_title = _("Edit your profile"); //You can edit this to change your page title.
	require_once('lib/includes/header.php');

	$_fields_array = array( 'first_name', 'last_name', 'gender', 'date_of_birth', 'address1', 'address2', 'city', 'state', 'zip_code', 'country', 'mobile', 'phone', 'profile_image', 'description' );

	$_fieldarr = array();
	foreach ( $_fields_array as $field ) {
		$_fieldarr[$field]['label']  = get_option( "accountform_setting_". $field ."_field_label" );
		$_fieldarr[$field]['status'] = get_option( "accountform_setting_". $field ."_edit_profile" );
	}
?>
<style>
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

	.image-upload-zone { border: 2px dashed #dcdcdc; border-radius: 10px; padding: 15px; text-align: center; background: #fafafa; transition: all 0.3s; cursor: pointer; margin-bottom: 20px; }
	.image-upload-zone:hover { border-color: #5d5386; background: #f4f4f9; }
	.preview-box { height: 110px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }
	.preview-box img { max-height: 100%; max-width: 100%; object-fit: contain; border-radius: 6px; }
	
	.referral-widget { background: #fff; padding: 20px; border-radius: 10px; border: 1px solid #eee; box-shadow: 0 2px 10px rgba(0,0,0,0.03); margin-bottom: 30px; }
	.copy-btn { border-radius: 4px !important; background: #ECAD3D !important; border-color: #ECAD3D !important; padding: 12px 30px !important; font-weight: 700 !important; color: #fff !important; }
	.referral-input { border-radius: 4px !important; border-color: #eee !important; font-weight: 600; background: #fafafa !important; font-size: 13px !important; }
</style>

<div class="row flex-row">
	<div class="col-12">
		<div class="widget has-shadow">
			<div class="widget-body">
				<!-- Referral Link Section -->
				<div class="referral-widget">
					<div class="row align-items-center">
						<div class="col-lg-4 text-lg-right">
							<label style="font-weight: 800; color: #333; margin: 0;">
								<i class="la la-share-alt-square mr-2" style="font-size: 24px; color: #ECAD3D;"></i> <?php _e("YOUR REFERRAL LINK"); ?>
							</label>
						</div>
						<div class="col-lg-7">
							<div class="input-group">
								<input type="text" class="form-control referral-input" id="referral_link" value="<?php echo rtrim(SITEURL, '/') . '/register.php?ref=' . $new_user->username; ?>" readonly />
								<div class="input-group-append">
									<button class="btn btn-primary copy-btn" type="button" onclick="copyReferralLink()">
										<i class="la la-copy"></i> <?php _e("COPY"); ?>
									</button>
								</div>
							</div>
							<small id="copy_msg" class="text-success mt-2 d-none" style="font-weight: 700;"><i class="la la-check-circle"></i> <?php _e("Link copied to clipboard!"); ?></small>
						</div>
					</div>
				</div>

				<form action="<?php $_SERVER['PHP_SELF']?>" id="add_user" name="user" method="post" enctype="multipart/form-data">

					<div class="section-header"><h5>Personal Details</h5></div>
					<div class="row">
						<?php if ( ! isset( $_fieldarr['first_name']['status'] ) || $_fieldarr['first_name']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php echo (isset($_fieldarr['first_name']['label'])) ? $_fieldarr['first_name']['label'] : _('First Name'); ?>*</label>
								<input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo $new_user->first_name; ?>" required="required" />
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['last_name']['status'] ) || $_fieldarr['last_name']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php echo (isset($_fieldarr['last_name']['label'])) ? $_fieldarr['last_name']['label'] : _('Last Name'); ?></label>
								<input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo $new_user->last_name; ?>" required="required" />
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
								<input type="text" name="mobile" id="mobile" class="form-control" value="<?php echo $new_user->mobile; ?>" />
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['phone']['status'] ) || $_fieldarr['phone']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>Phone</label>
								<input type="text" name="phone" id="phone" class="form-control" value="<?php echo $new_user->phone; ?>" />
							</div>
						</div>
						<?php endif; ?>
					</div>

					<div class="section-header"><h5>Location Information</h5></div>
					<div class="row">
						<?php if ( ! isset( $_fieldarr['address1']['status'] ) || $_fieldarr['address1']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>Address 1</label>
								<input type="text" name="address1" id="addOne" class="form-control" value="<?php echo $new_user->address1; ?>" />
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['address2']['status'] ) || $_fieldarr['address2']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>Address 2</label>
								<input type="text" name="address2" id="addTwo" class="form-control" value="<?php echo $new_user->address2; ?>" />
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['city']['status'] ) || $_fieldarr['city']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>City</label>
								<input type="text" name="city" id="city" class="form-control" value="<?php echo $new_user->city; ?>" />
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['state']['status'] ) || $_fieldarr['state']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>State</label>
								<input type="text" name="state" id="state" class="form-control" value="<?php echo $new_user->state; ?>" />
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['zip_code']['status'] ) || $_fieldarr['zip_code']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>Zip Code</label>
								<input type="text" name="zip_code" id="zipcode" class="form-control" value="<?php echo $new_user->zip_code; ?>" />
							</div>
						</div>
						<?php endif; ?>

						<?php if ( ! isset( $_fieldarr['country']['status'] ) || $_fieldarr['country']['status'] != 'hide' ) : ?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label>Country</label>
								<select class="form-control" id="country" name="country">
									<?php countries_dropdown($new_user->country); ?>
								</select>
							</div>
						</div>
						<?php endif; ?>
					</div>

					<div class="section-header"><h5>Account Security</h5></div>
					<div class="row">
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("Username"); ?>*</label>
								<input type="text" class="form-control" id="username" name="username" value="<?php echo $new_user->username; ?>" required="required" />
							</div>
						</div>

						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("Email"); ?>*</label>
								<input type="email" id="email" class="form-control" name="email" value="<?php echo $new_user->email; ?>" required="required" />
							</div>
						</div>

						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("Change Password"); ?></label>
								<div class="pass-wrapper">
									<input type="password" id="password" class="form-control" name="password" placeholder="Leave blank to keep current" />
									<i class="toggle-password fa fa-fw fa-eye-slash"></i>
								</div>
							</div>
						</div>
						
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e("Confirm Password"); ?></label>
								<div class="pass-wrapper">
									<input type="password" id="confirmpassword" class="form-control" name="confirm_password" placeholder="Repeat new password" />
									<i class="toggle-password fa fa-fw fa-eye-slash"></i>
								</div>
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
						foreach($upl_fields as $f_key => $f_label) : 
							$current_val = ($f_key == 'profile_image') ? $new_user->profile_image : $new_user->get_user_info($_SESSION['user_id'], $f_key);
							$display_img = (!empty($current_val)) ? $current_val : 'assets/images/thumb.png';
							$is_nic = ($f_key == 'nic_front' || $f_key == 'nic_back');
							$can_edit_img = ($_SESSION['user_type'] == 'admin' || !$is_nic);
						?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
							<div class="image-upload-zone <?php echo (!$can_edit_img) ? 'readonly-style' : ''; ?>" id="zone_<?=$f_key?>" <?php echo ($can_edit_img) ? 'onclick="document.getElementById(\'input_'.$f_key.'\').click()"' : ''; ?>>
								<label><?php _e($f_label); ?></label>
								<div class="preview-box">
									<img id="preview_<?=$f_key?>" src="<?=$display_img?>">
								</div>
								<?php if($can_edit_img): ?>
								<input type="file" name="<?=$f_key?>" id="input_<?=$f_key?>" class="d-none" accept="image/*" onchange="showPreview(this, 'preview_<?=$f_key?>')">
								<?php endif; ?>
								<?php if($f_key == 'profile_image'): ?>
									<input type="hidden" name="profile_image" id="cropOutput" value="" />
									<?php if(!empty($new_user->profile_image)): ?>
										<input type="hidden" name="already_img" value="<?=$new_user->profile_image?>">
									<?php endif; ?>
								<?php endif; ?>
								<?php if($can_edit_img): ?>
								<button type="button" class="btn btn-sm btn-outline-secondary"><?php _e("Change Photo"); ?></button>
								<?php endif; ?>
							</div>
							</div>
						</div>
						<?php endforeach; ?>
					</div>

					<div class="section-header"><h5>Bank Details</h5></div>
					<div class="row">
						<?php 
						$bank_fields = [
							'bank_name' => 'Bank Name',
							'account_holder' => 'Account Holder Name',
							'account_number' => 'Account Number',
							'iban_no' => 'IBAN No',
							'branch_name' => 'Branch Name',
							'branch_code' => 'Branch Code'
						];
						foreach($bank_fields as $b_key => $b_label): 
						?>
						<div class="col-md-4">
							<div class="form-group admin-form-group">
								<label><?php _e($b_label); ?></label>
								<input type="text" name="<?=$b_key?>" class="form-control <?php echo ($_SESSION['user_type'] != 'admin') ? 'readonly-style' : ''; ?>" value="<?php echo $new_user->$b_key; ?>" <?php echo ($_SESSION['user_type'] != 'admin') ? 'readonly' : ''; ?> />
							</div>
						</div>
						<?php endforeach; ?>

					    <div class="col-md-12">
				            <?php if($_SESSION['user_type'] != 'admin'): ?>
				                <div class="alert alert-warning mt-2 d-flex align-items-center" role="alert">
				                    <i class="fa fa-info-circle me-2"></i>
				                    <small>
				                        <?php _e("Your bank details and NIC are managed by the admin. Please contact the administrator if you need any changes."); ?>
				                    </small>
				                </div>
				            <?php endif; ?>					    	
					        <div class="form-group admin-form-group">
					            <label><?php _e("Additional Details"); ?></label>

                                <textarea name="description" class="form-control bank-textarea" rows="2" placeholder="Enter any other additional details..."></textarea>

					        </div>
					    </div>
					</div>

					<?php echo return_additional_field_options( $_SESSION['user_id'], 'edit' ); ?>

					<div class="form-group mb-5">
						<div class="styled-checkbox">
							<input type="checkbox" id="emailnotification" name="message_email_notification" <?php if($new_user->get_user_meta($_SESSION['user_id'], 'message_email') == '1'){echo 'checked="checked"'; }?> value="1" />				
							<label for="emailnotification"><?php _e("Send Email on new message"); ?></label>
						</div>
					</div>
					
					<div class="text-center mt-5 mb-5">
						<input type="hidden" name="update_user" value="1" /> 
						<button type="submit" class="btn btn-primary btn-lg btn-golden-admin" style="min-width: 250px; border-radius: 30px;">
							<?php _e("Update Profile"); ?>
						</button>
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

function copyReferralLink() {
	var copyText = document.getElementById("referral_link");
	copyText.select();
	copyText.setSelectionRange(0, 99999);
	try {
		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(copyText.value).then(showSuccess);
		} else {
			document.execCommand("copy");
			showSuccess();
		}
	} catch (err) {
		document.execCommand("copy");
		showSuccess();
	}
	function showSuccess() {
		var msg = document.getElementById("copy_msg");
		msg.classList.remove('d-none');
		setTimeout(function() { msg.classList.add('d-none'); }, 3000);
	}
}

jQuery(document).ready(function($) {
	$('.toggle-password').mousedown(function() {
		$(this).toggleClass('fa-eye fa-eye-slash');
		const input = $(this).siblings('input');
		input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
	});
});
</script>

<?php
	require_once("lib/includes/footer.php");