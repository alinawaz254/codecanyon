<?php
	//messages processings
	require_once('../system_load.php');
	//loading system.

	if(isset($_POST['form_type']) && $_POST['form_type'] == 'new_message') {
		extract($_POST);
		if($message == '') { 
			_e("Message is empty!");
			exit();
		}
		$message = $message_obj->new_message( $message_to, $subject, $message );
		echo $message;	
	} //new message processing ends here.
	
	if(isset($_POST['single_form']) && $_POST['single_form'] == '1') { 
		extract($_POST);
		if($message == '') { 
			_e("Message is empty!");
			exit();
		}
		$message = $message_obj->single_user_msg( $user_id, $subject, $message );
		echo $message;
	}//single form msg ends here.
	
	if(isset($_POST['level_form']) && $_POST['level_form'] == '1') { 
		extract($_POST);
		if($message == '') { 
			_e("Message is empty!");
			exit();
		}
		$message = $message_obj->level_message( $level_name, $subject, $message );
		echo $message;
	}
	
	if(isset($_POST['all_users']) && $_POST['all_users'] == '1') { 
		extract($_POST);
		if($message == '') { 
			_e("Message is empty!");
			exit();
		}
		$message = $message_obj->message_all( $subject, $message );
		echo $message;
	}