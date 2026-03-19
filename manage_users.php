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
				$message = $new_user->update_user( $_POST['edit_user'], $_SESSION['user_type'], $first_name, $last_name, $gender, $date_of_birth, $address1, $address2, $city, $state, $country, $zip_code, $mobile, $phone, $username, $email, $password_set, $pr_img, $description, $status, $user_type,$referral_id);
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
			$received = $new_user->add_user( $first_name, $last_name, $gender, $date_of_birth, $address1, $address2, $city, $state, $country, $zip_code, $mobile, $phone, $username, $email, $password, $profile_image, $description, $status, $user_type,$referral_id);

			$user_id = ( isset( $received['user_id'] ) && ! empty( $received['user_id'] ) ) ? $received['user_id'] : '';
			$message = ( isset( $received['message'] ) && ! empty( $received['message'] ) ) ? $received['message'] : 'An error occured while processing the request';
		}
	}//add user processing ends here.
	
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

	$_fields_array = array( 'first_name', 'last_name', 'gender', 'date_of_birth', 'address1', 'address2', 'city', 'state', 'zip_code', 'country', 'mobile', 'phone', 'profile_image', 'description' );

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
<div class="row flex-row">
	<div class="col-12">
		<!-- Form -->
		<div class="widget has-shadow">
			<div class="widget-body">
				<div class="container">
				<form action="<?php $_SERVER['PHP_SELF']?>" id="add_user" name="user" method="post" enctype="multipart/form-data" role="form">
			
					<?php if ( ! isset( $_fieldarr['first_name']['status'] ) || $_fieldarr['first_name']['status'] != 'hide' ) : ?>
					<div class="row">
					<div class="col-md-6 mb-1">											
						<div class="form-group row d-flex align-items-center mb-5">
							<?php $_label = ( isset( $_fieldarr['first_name']['label'] ) && ! empty( $_fieldarr['first_name']['label'] ) ) ? $_fieldarr['first_name']['label'] : _( 'First Name' ); ?>
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="first_name"><?php echo $_label ?>*</label>
							<div class="col-lg-5">
								<input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo $new_user->first_name; ?>" required="required" />
							</div>
						</div>
					</div>					
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['last_name']['status'] ) || $_fieldarr['last_name']['status'] != 'hide' ) : ?>
					<div class="col-md-6 mb-1">						
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="last_name">
							<?php $_label = ( isset( $_fieldarr['last_name']['label'] ) && ! empty( $_fieldarr['last_name']['label'] ) ) ? $_fieldarr['last_name']['label'] : _( 'Last Name' ); ?>
							<?php echo $_label ?>*
							</label>
							<div class="col-lg-5">
								<input type="text" id="last_name" name="last_name" class="form-control" placeholder="<?php _e("Enter Last Name"); ?>" value="<?php echo $new_user->last_name; ?>" required="required" />
							</div>
						</div>
					</div>						
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['gender']['status'] ) || $_fieldarr['gender']['status'] != 'hide' ) : ?>
					<div class="col-md-6 mb-1">						
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
					</div>						
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['date_of_birth']['status'] ) || $_fieldarr['date_of_birth']['status'] != 'hide' ) : ?>
					<div class="col-md-6 mb-1">						
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
													$dob = ( ! empty( $new_user->date_of_birth ) ) ? date( "m/d/Y", strtotime( $new_user->date_of_birth ) ) : ''; 
													echo $dob;
												?>" />
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['address1']['status'] ) || $_fieldarr['address1']['status'] != 'hide' ) : ?>
					<div class="col-md-6 mb-1">						
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="addOne">
							<?php $_label = ( isset( $_fieldarr['address1']['label'] ) && ! empty( $_fieldarr['address1']['label'] ) ) ? $_fieldarr['address1']['label'] : _( 'Address' ); ?>
							<?php echo $_label ?> 1
							</label>
							<div class="col-lg-5">
								<textarea name="address1" id="addOne" class="form-control" placeholder="<?php _e("Address"); ?> 1"><?php echo $new_user->address1; ?></textarea>
							</div>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['address2']['status'] ) || $_fieldarr['address2']['status'] != 'hide' ) : ?>
					<div class="col-md-6 mb-1">						
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="addTwo">
							<?php $_label = ( isset( $_fieldarr['address2']['label'] ) && ! empty( $_fieldarr['address2']['label'] ) ) ? $_fieldarr['address2']['label'] : _( 'Address' ); ?>
							<?php echo $_label ?> 2
							</label>
							<div class="col-lg-5">
								<textarea name="address2" id="addTwo" class="form-control" placeholder="<?php _e("Address"); ?> 2"><?php echo $new_user->address2; ?></textarea>
							</div>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['city']['status'] ) || $_fieldarr['city']['status'] != 'hide' ) : ?>
					<div class="col-md-6 mb-1">						
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="city">
							<?php $_label = ( isset( $_fieldarr['city']['label'] ) && ! empty( $_fieldarr['city']['label'] ) ) ? $_fieldarr['city']['label'] : _( 'City' ); ?>
							<?php echo $_label ?>
							</label>
							<div class="col-lg-5">
								<input type="text" name="city" id="city" class="form-control" placeholder="<?php _e("City"); ?>" value="<?php echo $new_user->city; ?>" />
							</div>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['state']['status'] ) || $_fieldarr['state']['status'] != 'hide' ) : ?>
					<div class="col-md-6 mb-1">						
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
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['zip_code']['status'] ) || $_fieldarr['zip_code']['status'] != 'hide' ) : ?>
					<div class="col-md-6 mb-1">						
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="zipcode">
							<?php $_label = ( isset( $_fieldarr['zip_code']['label'] ) && ! empty( $_fieldarr['zip_code']['label'] ) ) ? $_fieldarr['zip_code']['label'] : _( 'Zip Code' ); ?>
							<?php echo $_label ?>
							</label>
							<div class="col-lg-5">
								<input type="text" name="zip_code" id="zipcode" class="form-control" placeholder="<?php _e("Zip Code"); ?>" value="<?php echo $new_user->zip_code; ?>" />
							</div>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['country']['status'] ) || $_fieldarr['country']['status'] != 'hide' ) : ?>
					<div class="col-md-6 mb-1">						
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
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['mobile']['status'] ) || $_fieldarr['mobile']['status'] != 'hide' ) : ?>
					<div class="col-md-6 mb-1">						
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="mobile">
							<?php $_label = ( isset( $_fieldarr['mobile']['label'] ) && ! empty( $_fieldarr['mobile']['label'] ) ) ? $_fieldarr['mobile']['label'] : _( 'Mobile' ); ?>
							<?php echo $_label ?>
							</label>
							<div class="col-lg-5">
								<input type="text" name="mobile" id="mobile" class="form-control" placeholder="<?php _e("Mobile"); ?>" value="<?php echo $new_user->mobile; ?>" />
							</div>
						</div>
					</div>
					<?php endif; ?>

					<?php if ( ! isset( $_fieldarr['phone']['status'] ) || $_fieldarr['phone']['status'] != 'hide' ) : ?>
					<div class="col-md-6 mb-1">						
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="phone">
							<?php $_label = ( isset( $_fieldarr['phone']['label'] ) && ! empty( $_fieldarr['phone']['label'] ) ) ? $_fieldarr['phone']['label'] : _( 'Phone' ); ?>
							<?php echo $_label ?>
							</label>
							<div class="col-lg-5">
								<input type="text" name="phone" id="phone" class="form-control" placeholder="<?php _e("Phone"); ?>" value="<?php echo $new_user->phone; ?>" />
							</div>
						</div>
					</div>
					<?php endif; ?>

					<!-- <div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="username">
							<?php _e("Username"); ?>*
						</label>
						<div class="col-lg-5">
							<input type="text" class="form-control" id="username" name="username" placeholder="<?php _e("Username"); ?>" value="<?php echo $new_user->username; ?>" required="required" />
						</div>
					</div> -->
					<div class="col-md-6 mb-1">
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="username">
								<?php _e("Username"); ?>*
							</label>
							<div class="col-lg-5">
								<input type="text" class="form-control" id="username" name="username"
								value="<?php echo isset($_POST['edit_user']) ? $new_user->username : $auto_generated_user_name; ?>"
								readonly />							
							</div>
						</div>				
					</div>	

					<div class="col-md-6 mb-1">
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="email">
								<?php _e("Email"); ?>*
							</label>
							<div class="col-lg-5">
								<input type="email" id="email" class="form-control" name="email" placeholder="<?php _e("Email"); ?>" value="<?php echo $new_user->email; ?>" required="required" />
							</div>
						</div>
					</div>

					<?php if(isset($_POST['edit_user'])) { ?> 
					<div class="col-md-6 mb-1">					
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="password">
								<?php _e("Password"); ?>
							</label>
							<div class="col-lg-5">
								<input type="password" id="password" class="form-control" name="password" placeholder="<?php _e("Password"); ?>" value="" /><small><?php _e("Leave blank if you don't want to change password"); ?></small>
							</div>
						</div>
					</div>

					<div class="col-md-6 mb-1">					
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="confirmpassword">
								<?php _e("Confirm Password"); ?>
							</label>
							<div class="col-lg-5">
								<input class="form-control" id="confirmpassword" type="password" name="confirm_password" placeholder="<?php _e("Confirm Password"); ?>" value="" />
							</div>
						</div>
					</div>
					<?php } else {?>
					<div class="col-md-6 mb-1">
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="password">
								<?php _e("Password"); ?>*
							</label>
							<div class="col-lg-5">
								<input type="password" id="password" class="form-control" name="password" placeholder="<?php _e("Password"); ?>" value="" />
							</div>
						</div>
					</div>

					<div class="col-md-6 mb-1">					
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="confirmpassword">
								<?php _e("Confirm Password"); ?>*
							</label>
							<div class="col-lg-5">
								<input class="form-control" id="confirmpassword" type="password" name="confirm_password" placeholder="<?php _e("Confirm Password"); ?>" value="" />
							</div>
						</div>	
					</div>	
					<?php } ?>
					
					<?php if ( ! isset( $_fieldarr['profile_image']['status'] ) || $_fieldarr['profile_image']['status'] != 'hide' ) : ?>
					<div class="col-md-6 mb-1">						
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
					</div>
					<?php endif; ?>
					
					<?php if ( ! isset( $_fieldarr['description']['status'] ) || $_fieldarr['description']['status'] != 'hide' ) : ?>
					<div class="col-md-6 mb-1">						
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end" for="description">
							<?php $_label = ( isset( $_fieldarr['description']['label'] ) && ! empty( $_fieldarr['description']['label'] ) ) ? $_fieldarr['description']['label'] : _( 'Description' ); ?>
							<?php echo $_label ?>
							</label>
							<div class="col-lg-5">
								<textarea name="description" id="description" class="form-control" placeholder="<?php _e("Description"); ?>"><?php echo $new_user->description; ?></textarea>
							</div>
						</div>
					</div>
					<?php endif; ?>

					<?php 
						$userID = ( isset( $_POST['edit_user'] ) ) ? $_POST['edit_user'] : '';
						echo return_additional_field_options( $userID, 'update' ); ?>

					<div class="col-md-6 mb-1">
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end"><?php _e("Status"); ?>*</label>
							<div class="col-lg-5">
								<select name="status" required class="custom-select form-control" id="status" class="required">
									<option value="0"><?php _e("Select User Status"); ?></option>
									<option <?php if($new_user->status == 'activate'){echo 'selected="selected"';} ?> value="activate"><?php _e("Active"); ?></option>
									<option <?php if($new_user->status == 'deactivate'){echo 'selected="selected"';} ?> value="deactivate"><?php _e("Deactive"); ?></option>
									<option <?php if($new_user->status == 'ban'){echo 'selected="selected"';} ?> value="ban"><?php _e("Ban"); ?></option>
									<option <?php if($new_user->status == 'suspend'){echo 'selected="selected"';} ?> value="suspend"><?php _e("Suspend"); ?></option>                           	
								</select>
							</div>
						</div>
					</div>
					
					<div class="col-md-6 mb-1">					
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end"><?php _e("User Type"); ?>*</label>
							<div class="col-lg-5">
								<select name="user_type" class="custom-select form-control" required id="user_type" class="required">
									<option value=""><?php _e("Select User Type"); ?></option>
									<option <?php if($new_user->user_type == 'admin'){echo 'selected="selected"';} ?> value="admin"><?php _e("Admin"); ?></option>
									<?php $new_level->userlevel_options($new_user->user_type); ?>                          	
								</select>
							</div>
						</div>
					</div>

					<div class="col-md-6 mb-1">
						<div class="form-group row d-flex align-items-center mb-5">
							<label class="col-lg-4 form-control-label d-flex justify-content-lg-end">
							Referred By
							</label>
							<div class="col-lg-5">
							<select name="referral_id" id="referral-users" class="form-control" style="width:100%">
							    <option value="0">Select Referrer (Optional)</option>
							    <?php
							    $current_referral_id = isset($new_user->referral_id) ? $new_user->referral_id : '';
							    
							    $result = $db->query("SELECT user_id, username FROM users WHERE user_type LIKE '%subscriber%' ORDER BY username ASC");
							    
							    if ($result && $result->num_rows > 0) {
							        while($u = $result->fetch_assoc()){
							            // Check if this option should be selected
							            $selected = ($current_referral_id == $u['user_id']) ? 'selected="selected"' : '';
							            
							            if (isset($_POST['edit_user']) && $_POST['edit_user'] == $u['user_id']) {
							                continue; 
							            }
							            
							            echo "<option value='" . htmlspecialchars($u['user_id']) . "' $selected>" . 
							                 htmlspecialchars($u['username']) . "</option>";
							        }
							    } else {
							        echo "<option value=''>No subscribers found</option>";
							    }
							    ?>
							</select>
							</div>
						</div>					
					</div>					

					<?php 
					if ( isset( $_POST['edit_user'] ) ) {
						echo '<input type="hidden" name="edit_user" value="'.$_POST['edit_user'].'" />';
						echo '<input type="hidden" name="update_user" value="1" />'; 
					} else { 
						echo '<input type="hidden" name="add_user" value="1" />';
					} ?>
					<div class="text-left">
						<input type="submit" value="<?php if(isset($_POST['edit_user'])){ _e("Update User"); } else { _e("Add User");} ?>" class="btn btn-primary btn-md btn-golden" />
					</div>
				</form>
				</div>
			</div><!-- widget body /-->
		</div><!-- widget /-->
	</div><!-- column /-->
</div><!-- Row /-->
<?php
	require_once("lib/includes/footer.php");