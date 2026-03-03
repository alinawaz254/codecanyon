<?php
	require_once('../system_load.php');

	//Processing of Vendor.
	authenticate_user('all');

	if ( isset( $_POST['form_type'] ) && $_POST['form_type'] == 'return_additional_field' ) {
		authenticate_user( 'admin' );
		$message = return_extra_field_options( 'delete', '' );
		echo json_encode( $message );
	}
	
	if(isset($_POST["wc_forms_presence"]) && $_POST["wc_forms_presence"] == "1") {
		
		//Check and set notes user correctly.
		if(isset($_POST["note_title"]) && isset($_POST["note_detail"])): 
		   $_POST["user_id"]	= $_SESSION["user_id"];
			
			//Restrict Note Update without access.
			if(isset($_POST["table_update"]) && $notes_obj->check_note_access($_POST["table_update_key"], $_SESSION["user_id"]) == 0) {
				HEADER("LOCATION: notes.php?message="._("You are doing something you don't have access to do."));
				exit;
			}
		 endif;
		
		
		//Auto Submit Forms to Class
		$forms_submission_obj = new FORM_SUBMISSIONS($_POST);
		echo json_encode($forms_submission_obj->return_array);
	}