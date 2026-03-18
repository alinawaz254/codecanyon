<?php
	require_once( 'lib/system_load.php' );
	//Including this file we load system.
	
	//user Authentication.
	authenticate_user( 'admin' );
	
	//installation form processing when submits.
	if ( isset ( $_POST['useraccount_settings_submit'] ) && $_POST['useraccount_settings_submit'] == 'Yes' ) {
		//validation to check if fields are empty!
		$_fields_array = array( 'first_name', 'last_name', 'gender', 'date_of_birth', 'address1', 'address2', 'city', 'state', 'zip_code', 'country', 'mobile', 'phone', 'profile_image', 'description' );

		foreach ( $_fields_array as $field ) {
			$_field_label 		= "accountform_setting_". $field ."_field_label";
			$_registration_form = "accountform_setting_". $field ."_registration_form";
			$_update_form   	= "accountform_setting_". $field ."_update_form";
			$_edit_profile 		= "accountform_setting_". $field ."_edit_profile";

			if ( isset( $_POST[$_field_label] ) && ! empty( $_POST[$_field_label] ) ) {
				set_option( $_field_label, $_POST[$_field_label] );
			}
			if ( isset( $_POST[$_registration_form] ) && ! empty( $_POST[$_registration_form] ) ) {
				set_option( $_registration_form, $_POST[$_registration_form] );
			}
			if ( isset( $_POST[$_update_form] ) && ! empty( $_POST[$_update_form] ) ) {
				set_option( $_update_form, $_POST[$_update_form] );
			}
			if ( isset( $_POST[$_edit_profile] ) && ! empty( $_POST[$_edit_profile] ) ) {
				set_option( $_edit_profile, $_POST[$_edit_profile] );
			}
		}

		$message = _("Settings saved.");
		HEADER('LOCATION: general_settings_userforms.php?message='.$message); 
	}

	//Page display settings.
	$page_title = _( "User account settings" ); //You can edit this to change your page title.
	require_once("lib/includes/header.php"); //including header file.
?>
<div class="row flex-row">
	<div class="col-12">
		<!-- Form -->
		<div class="widget has-shadow">
			<div class="widget-body">
				<div class="form-group">
					<a href="general_settings.php" class="btn btn-secondary mr-1 mb-2"><?php _e( 'General Settings' ); ?></a>
					<a href="general_settings_userforms.php" class="btn btn-primary mr-1 mb-2"><?php _e( 'User Account Settings' ); ?></a>
					<a href="general_settings_user_additionalfields.php" class="btn btn-secondary mr-1 mb-2"><?php _e( 'Additional User Fields' ); ?></a>
				</div>
			</div>
			<div class="widget-body">

				<form name="set_install" id="set_install" action="<?php $_SERVER['PHP_SELF']; ?>" method="post">
					<h2><?php _e( 'Manage user profile fields' ); ?></h2><hr />
					<?php
						$fieldsarray = array();

						//First name
						$fieldsarray[] = array(
							'fieldName' => 'first_name',
							'fieldHead' => _('First Name'),
							'fieldLabel' => _('First Name'),
							'registrationForm' => 'show',
							'updateForm' => 'show',
							'editProfile' => 'show',
						);
						//Last name
						$fieldsarray[] = array(
							'fieldName' => 'last_name',
							'fieldHead' =>  _( 'Last Name' ),
							'fieldLabel' => _('Last Name'),
							'registrationForm' => 'show',
							'updateForm' => 'show',
							'editProfile' => 'show',
						);
						//Gender
						$fieldsarray[] = array(
							'fieldName' => 'gender',
							'fieldHead' =>  _( 'Gender' ),
							'fieldLabel' => _('Gender'),
							'registrationForm' => 'show',
							'updateForm' => 'show',
							'editProfile' => 'show',
						);
						//Date of Birth
						$fieldsarray[] = array(
							'fieldName' => 'date_of_birth',
							'fieldHead' =>  _( 'Date of Birth' ),
							'fieldLabel' => _('Date of Birth'),
							'registrationForm' => 'show',
							'updateForm' => 'show',
							'editProfile' => 'show',
						);
						//Address 1
						$fieldsarray[] = array(
							'fieldName' => 'address1',
							'fieldHead' =>  _( 'Address 1' ),
							'fieldLabel' => _('Address 1'),
							'registrationForm' => 'show',
							'updateForm' => 'show',
							'editProfile' => 'show',
						);
						//Address 2
						$fieldsarray[] = array(
							'fieldName' => 'address2',
							'fieldHead' =>  _( 'Address 2' ),
							'fieldLabel' => _('Address 2'),
							'registrationForm' => 'show',
							'updateForm' => 'show',
							'editProfile' => 'show',
						);
						//City
						$fieldsarray[] = array(
							'fieldName' => 'city',
							'fieldHead' =>  _( 'City' ),
							'fieldLabel' => _('City'),
							'registrationForm' => 'show',
							'updateForm' => 'show',
							'editProfile' => 'show',
						);
						//State
						$fieldsarray[] = array(
							'fieldName' => 'state',
							'fieldHead' =>  _( 'State Or Province' ),
							'fieldLabel' => _('State Or Province'),
							'registrationForm' => 'show',
							'updateForm' => 'show',
							'editProfile' => 'show',
						);
						//Zip/Postal Code
						$fieldsarray[] = array(
							'fieldName' => 'zip_code',
							'fieldHead' =>  _( 'Zip/Postal Code' ),
							'fieldLabel' => _('Zip/Postal Code'),
							'registrationForm' => 'show',
							'updateForm' => 'show',
							'editProfile' => 'show',
						);
						//Country
						$fieldsarray[] = array(
							'fieldName' => 'country',
							'fieldHead' =>  _( 'Country' ),
							'fieldLabel' => _( 'Country' ),
							'registrationForm' => 'show',
							'updateForm' => 'show',
							'editProfile' => 'show',
						);
						//Mobile
						$fieldsarray[] = array(
							'fieldName' => 'mobile',
							'fieldHead' =>  _( 'Mobile' ),
							'fieldLabel' => _( 'Mobile' ),
							'registrationForm' => 'show',
							'updateForm' => 'show',
							'editProfile' => 'show',
						);
						//Phone
						$fieldsarray[] = array(
							'fieldName' => 'phone',
							'fieldHead' =>  _( 'Phone' ),
							'fieldLabel' => _( 'Phone' ),
							'registrationForm' => 'show',
							'updateForm' => 'show',
							'editProfile' => 'show',
						);
						//profile image
						$fieldsarray[] = array(
							'fieldName' => 'profile_image',
							'fieldHead' =>  _( 'Profile Image' ),
							'fieldLabel' => _( 'Profile Image' ),
							'registrationForm' => 'show',
							'updateForm' => 'show',
							'editProfile' => 'show',
						);
						//Description
						$fieldsarray[] = array(
							'fieldName' => 'description',
							'fieldHead' =>  _( 'Description' ),
							'fieldLabel' => _( 'Description' ),
							'registrationForm' => 'show',
							'updateForm' => 'show',
							'editProfile' => 'show',
						);

						$output = '<div class="form-group row">';
						foreach( $fieldsarray as $field ) {
							$output .= '<label class="col-lg-3 form-control-label">' . $field['fieldHead'] . '</label>';

							$output .= '<div class="col-sm-9"><div class="row">';

							$fieldIdentifier = "accountform_setting_". $field['fieldName'] ."_field_label";
							$value = get_option( $fieldIdentifier );
							$value = ( empty( $value ) ) ? $field['fieldLabel'] : $value;
							$output .= '<div class="col-lg-3">';
							$output .= '<label for="'. $fieldIdentifier .'">' . _( 'Field label' );
							$output .= '<input id="' . $fieldIdentifier . '" name="' . $fieldIdentifier . '" type="text" placeholder="" value="' . $value . '" class="form-control">';
							$output .= '</label>';
							$output .= '</div>';

							$fieldIdentifier = "accountform_setting_". $field['fieldName'] ."_registration_form";
							$value = get_option( $fieldIdentifier );
							$value = ( empty( $value ) ) ? $field['registrationForm'] : $value;
							$_showselected = ( $value == 'show' ) ? 'selected' : '';
							$_hideselected = ( $value == 'hide' ) ? 'selected' : '';
							$output .= '<div class="col-lg-3">';
							$output .= '<label for="'. $fieldIdentifier .'">' . _( 'In registration form' );
							$output .= '<select id="' . $fieldIdentifier . '" name="'. $fieldIdentifier .'" class="custom-select form-control">';
							$output .= '<option '. $_showselected .' value="show">' . _( 'Show' ) . '</option>';
							$output .= '<option '. $_hideselected .' value="hide">' . _( 'Hide' ) . '</option>';
							$output .= '</select>';
							$output .= '</label>';
							$output .= '</div>';

							$fieldIdentifier = "accountform_setting_". $field['fieldName'] ."_update_form";
							$value = get_option( $fieldIdentifier );
							$value = ( empty( $value ) ) ? $field['updateForm'] : $value;
							$_showselected = ( $value == 'show' ) ? 'selected' : '';
							$_hideselected = ( $value == 'hide' ) ? 'selected' : '';
							$output .= '<div class="col-lg-3">';
							$output .= '<label for="'. $fieldIdentifier .'">' . _( 'Add & update user by admin' );
							$output .= '<select id="' . $fieldIdentifier . '" name="'. $fieldIdentifier .'" class="custom-select form-control">';
							$output .= '<option '. $_showselected .' value="show">' . _( 'Show' ) . '</option>';
							$output .= '<option '. $_hideselected .' value="hide">' . _( 'Hide' ) . '</option>';
							$output .= '</select>';
							$output .= '</label>';
							$output .= '</div>';

							$fieldIdentifier = "accountform_setting_". $field['fieldName'] ."_edit_profile";
							$value = get_option( $fieldIdentifier );
							$value = ( empty( $value ) ) ? $field['editProfile'] : $value;
							$_showselected = ( $value == 'show' ) ? 'selected' : '';
							$_hideselected = ( $value == 'hide' ) ? 'selected' : '';
							$output .= '<div class="col-lg-3">';
							$output .= '<label for="'. $fieldIdentifier .'">' . _( 'Update profile by user' );
							$output .= '<select id="' . $fieldIdentifier . '" name="'. $fieldIdentifier .'" class="custom-select form-control">';
							$output .= '<option '. $_showselected .' value="show">' . _( 'Show' ) . '</option>';
							$output .= '<option '. $_hideselected .' value="hide">' . _( 'Hide' ) . '</option>';
							$output .= '</select>';
							$output .= '</label>';
							$output .= '</div>';

							$output .= '</div></div>';
						}
						$output .= '</div>';

						echo $output;
					?>
					
					<hr />
					<div class="text-center">
						<input type="hidden" name="useraccount_settings_submit" value="Yes" />
						<input type="submit" value="<?php _e("Save"); ?>" class="btn btn-primary" />
					</div>
				</form>

			</div><!-- Widget body /-->
		</div><!-- Widget /-->
	</div><!-- Column /-->
</div><!-- Row /-->

<?php
	require_once("lib/includes/footer.php");