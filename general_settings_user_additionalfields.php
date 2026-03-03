<?php
	require_once( 'lib/system_load.php' );
	//Including this file we load system.
	
	//user Authentication.
	authenticate_user( 'admin' );
	//installation form processing when submits.
	if ( isset ( $_POST['useradditional_settings_submit'] ) && $_POST['useradditional_settings_submit'] == 'Yes' ) {
		//_extra_user_fields
		if ( isset( $_POST['additional_field_label'] ) && ! empty( $_POST['additional_field_label'] ) ) {
			$users_extra_fields = array();
			for ( $i = 0; $i < count( $_POST["additional_field_label"] ); $i++ ) {
				$additional_field_identifier  = ( isset( $_POST["additional_field_identifier"][$i] ) && ! empty( $_POST["additional_field_identifier"][$i] ) ) ? $_POST["additional_field_identifier"][$i] : randomPassword();
				$additional_field_label 	  = ( isset( $_POST["additional_field_label"][$i] ) && ! empty( $_POST["additional_field_label"][$i] ) ) ? $_POST["additional_field_label"][$i] : '';
				$additional_field_type 		  = ( isset( $_POST["additional_field_type"][$i] ) && ! empty( $_POST["additional_field_type"][$i] ) ) ? $_POST["additional_field_type"][$i] : '';
				$additional_registration_form = ( isset( $_POST["additional_registration_form"][$i] ) && ! empty( $_POST["additional_registration_form"][$i] ) ) ? $_POST["additional_registration_form"][$i] : '';
				$additional_update_form 	  = ( isset( $_POST["additional_update_form"][$i] ) && ! empty( $_POST["additional_update_form"][$i] ) ) ? $_POST["additional_update_form"][$i] : '';
				$additional_edit_profile 	  = ( isset( $_POST["additional_edit_profile"][$i] ) && ! empty( $_POST["additional_edit_profile"][$i] ) ) ? $_POST["additional_edit_profile"][$i] : '';

				if ( ! empty( $additional_field_label ) ) : 
					$users_extra_fields[] = array(
						'additional_field_identifier'	=> $additional_field_identifier,
						'additional_field_label'		=> $additional_field_label,
						'additional_field_type'			=> $additional_field_type,
						'additional_registration_form'	=> $additional_registration_form,
						'additional_update_form' 		=> $additional_update_form,
						'additional_edit_profile' 		=> $additional_edit_profile,
					);
				endif;
			}
			$users_extra_fields = serialize( $users_extra_fields );
			set_option( '_extra_user_fields', $users_extra_fields );
		}
		$message = _("Settings saved.");
		HEADER('LOCATION: ?message='.$message); 
	}

	//Page display settings.
	$page_title = _( "User account additional fields" ); //You can edit this to change your page title.
	require_once("lib/includes/header.php"); //including header file.
?>
<div class="row flex-row">
	<div class="col-12">
		<!-- Form -->
		<div class="widget has-shadow">
			<div class="widget-body">
				<div class="form-group">
				<a href="general_settings.php" class="btn btn-secondary mr-1 mb-2"><?php _e( 'General Settings' ); ?></a>
					<a href="general_settings_userforms.php" class="btn btn-secondary mr-1 mb-2"><?php _e( 'User Account Settings' ); ?></a>
					<a href="general_settings_user_additionalfields.php" class="btn btn-primary mr-1 mb-2"><?php _e( 'Additional User Fields' ); ?></a>
				</div>
			</div>
			<div class="widget-body">
				<form name="set_install" id="set_install" action="<?php $_SERVER['PHP_SELF']; ?>" method="post">
					<h2><?php _e( 'Manage additional profile fields' ); ?></h2><hr />
					<?php
						//Start of Additional Fields Devices
						$setting_body = '<div class="wc-rb-payment-methods">';
						$setting_body .= '<div class="wcrb_additional_fields_wrap clearfix">';

						$user_additional_fields = get_option( '_extra_user_fields' );
						$user_additional_fields = unserialize( $user_additional_fields );

						if ( is_array( $user_additional_fields ) && ! empty( $user_additional_fields ) ) {
							$counter = 0;

							foreach( $user_additional_fields as $_additional_field ) {
								$delete_option = ( $counter == 0 ) ? '' : 'delete';
								$setting_body .= return_extra_field_options( $delete_option, $_additional_field );
								$counter++;
							}
						} else {
							$setting_body .= return_extra_field_options( '', '' );
						}
						$setting_body .= '</div><a href="#" class="button-primary alignright addadditionaluserfield btn btn-success">'. _( 'Add device field' ) .'</a>';
						$setting_body .= '</div>';

						//End of Additional Fields Devices

						echo $setting_body;
					?>
					
					 <hr />
					 <div class="text-center">
						<input type="hidden" name="useradditional_settings_submit" value="Yes" />
						<input type="submit" value="<?php _e("Save"); ?>" class="btn btn-primary" />
					</div>
				</form>

			</div><!-- Widget body /-->
		</div><!-- Widget /-->
	</div><!-- Column /-->
</div><!-- Row /-->

<?php
	require_once("lib/includes/footer.php");