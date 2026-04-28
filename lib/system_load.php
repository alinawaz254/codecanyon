<?php
	session_start();
	ob_start();
	
	define("ROOT_DIR", dirname(__DIR__, 1));

	/*This file loads system to do basic functions on the site, Please do not change anything here if you dont know what you are doing.*/
	require_once 'includes/db_connect.php';
	require_once "notifications_helper.php";
	
	define('ADMIN_ID', 1);

	// Ensure upload directories exist (especially since they are gitignored)
	$dirs_to_check = [
		ROOT_DIR . '/assets/upload',
		ROOT_DIR . '/assets/upload/proofs'
	];
	foreach($dirs_to_check as $dir) {
		if(!is_dir($dir)) {
			@mkdir($dir, 0777, true);
		}
	}
	//Redirecting to installation wizard
	//If not installed already.
	global $db;

	//Checks if options exist and installation is complete.
	if ( if_table_exists("notes") == FALSE ) {
		HEADER("LOCATION: ".dirname($_SERVER['PHP_SELF'])."/install.php");
	}
	
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	use PHPMailer\PHPMailer\SMTP;
	date_default_timezone_set('Etc/UTC');

	require_once "includes/vendors/phpmailer/Exception.php";
	require_once "includes/vendors/phpmailer/PHPMailer.php";
	require_once "includes/vendors/phpmailer/SMTP.php";

	require_once('includes/localize.php');
	require_once('includes/functions.php');

	if ( get_option( 'smtp_activation' ) == '1' ) {
		$mail = new PHPMailer();
	}

	require_once('includes/update.php');

	$site_logo = get_option("site_logo");
	$site_logo = (empty($site_logo)) ? "assets/images/logo.png" : $site_logo;

	$site_url = get_option("site_url");
	$site_url = (empty($site_url)) ? "" : $site_url;

	$site_name = get_option('site_name');
	$site_name = (empty($site_name)) ? _("Login Script") : $site_name;

	define("SITELOGO", $site_url.$site_logo);
	define("SITEURL", $site_url);
	define("SITE_NAME", $site_name);

	//Session signout after session timeout.
	if(isset($_SESSION['timeout'])) {
		if ($_SESSION['timeout'] + get_option('session_timeout') * 60 < time()) {
		 	session_destroy();
			setcookie("loginCredentials", "I do something!", time() - 2592000);
			HEADER('LOCATION: '.get_option('redirect_on_logout'));
			exit();
		}
	}

	//Match Salt for Security
	if(isset($_SESSION['user_id'])) {
		$system_salt = get_option("system_salt");
		empty($system_salt) ? generate_salt(): "";
		if($_SESSION['salt'] != $system_salt) {
			$_SESSION = array();
			session_destroy();
			setcookie("loginCredentials", "", time() - 2592000);
			HEADER('LOCATION: '.get_option('redirect_on_logout'));
			exit();
		}
	}

	//Adding Language.
	require_once('classes/users.php');
	require_once('classes/userlevel.php');
	require_once('classes/notes.php');
	require_once('classes/messages.php');
	require_once('classes/notifications.php');
	require_once('classes/announcements.php');
	require_once('classes/investments.php');
	require_once('classes/forms.php');
	require_once('classes/forms_submission.php');
	require_once('classes/transactions.php');
	require_once('classes/video.php');
	require_once('classes/images.php');

	$new_user 	= new Users;

	if(isset($_COOKIE['loginCredentials'])) {
		$cookie_info = unserialize($_COOKIE['loginCredentials']);
		
		if(!isset($_SESSION["user_id"])) {
			$current_ip = get_client_ip();//Function is inside function.php to get ip
			$last_ip 	= $new_user->get_user_meta($cookie_info["user_id"], "last_login_ip");
			
			if($current_ip == $last_ip) {
				//IP Match
				$new_user->set_user($cookie_info["user_id"], "normal", $cookie_info["user_id"]);
				
				$_SESSION['user_id'] 	= $new_user->user_id;
				$_SESSION['first_name'] = $new_user->first_name;
				$_SESSION['last_name'] 	= $new_user->last_name;
				$_SESSION['username'] 	= $new_user->username;
				$_SESSION['email'] 		= $new_user->email;
				$_SESSION['status'] 	= $new_user->status;
				$_SESSION['salt']		= get_option("system_salt");
				$_SESSION['user_type'] 	= $new_user->user_type;
				$_SESSION['timeout'] 	= time();
				
				//Setting user meta information.
				$new_user->set_user_meta($_SESSION['user_id'], 'last_login_time', date("Y-m-d H:i:s")); //setting last login time.
				$new_user->set_user_meta($_SESSION['user_id'], 'login_lock', 'No'); //setting last login time.
			} else {
				$message = "You cant stay loged in your last login ip is different from Current IP relogin please.";
			}
		}
	}
	
	if(isset($_SESSION['user_id'])):
		$new_user 		= new Users;
		$user_status 	= $new_user->get_user_info($_SESSION['user_id'], 'status');
	
		if($user_status == 'ban' || $user_status == 'deactivate' || $user_status == 'suspend') { 
			session_destroy();
			setcookie("loginCredentials", "", time() - 2592000);
			HEADER('LOCATION: ../index.php');
		}
		
		$message_obj      = new Messages;
		$notification_obj = new Notifications;
		$new_level        = new Userlevel;
		$notes_obj        = new Notes;
		$announcement_obj = new Announcements;
		$investment_obj   = new Investments;
		$forms_obj        = new FORMS;
		$transaction_obj  = new Transactions;
		$video_obj        = new Video;
		$image_obj        = new Images;
		
		// Expire old withdrawal requests (older than 36 hours)
		$transaction_obj->expire_requests();

		if($new_user->get_user_info($_SESSION['user_id'], 'profile_image') == '') { 
			$profile_img = 'assets/images/user-4.png';
		} else { 
			$profile_img = $new_user->get_user_info($_SESSION['user_id'], 'profile_image');
		}
	endif;