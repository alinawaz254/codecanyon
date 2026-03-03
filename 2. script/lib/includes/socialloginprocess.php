<?php
	//messages processings
	require_once('../system_load.php');
	
	if(empty($_POST['email'])) {
		return;
	}
	//getting inputs
	$first_name 	= $_POST['first_name'];
	$last_name 		= $_POST['last_name'];
	$gender 		= $_POST['gender'];
	$email 			= $_POST['email'];
	$profile_image	= $_POST['profile_image'];

	$user_type 		= get_option('register_user_level');
	
	$new_user 		= new Users;
	$message 		= $new_user->google_facebook_login_register($first_name, $last_name, $gender, $email, $user_type, $profile_image);
	
	if($message == '1') { 
		if(get_option('disable_login') == '1' && $new_user->user_type != 'admin') { 
			$message = _("Login disabled. Please contact site admin.");	
		} else {
		$_SESSION['user_id'] 		= $new_user->user_id;
		$_SESSION['first_name'] 	= $new_user->first_name;
		$_SESSION['last_name'] 		= $new_user->last_name;
		$_SESSION['username'] 		= $new_user->username;
		$_SESSION['email'] 			= $new_user->email;
		$_SESSION['status'] 		= $new_user->status;
		$_SESSION['salt']			= get_option("system_salt");	
		$_SESSION['user_type'] 		= $new_user->user_type;
		$_SESSION['profile_img'] 	= $new_user->profile_image;
		$_SESSION['timeout'] 		= time();
		
		//Setting user meta information.
		$user_ip = get_client_ip();//Function is inside function.php to get ip
		$new_user->set_user_meta($_SESSION['user_id'], 'last_login_time', date("Y-m-d H:i:s")); //setting last login time.
		$new_user->set_user_meta($_SESSION['user_id'], 'last_login_ip', $user_ip); //setting last login IP.
		$new_user->set_user_meta($_SESSION['user_id'], 'login_attempt', '0'); //On login success default loign attempt is 0.
		$new_user->set_user_meta($_SESSION['user_id'], 'login_lock', 'No'); //setting last login time.
		}
	}
	$return_message = array(
		"message" => $message
	);
	echo json_encode($return_message);