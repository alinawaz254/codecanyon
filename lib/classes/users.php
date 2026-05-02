<?php
//users Class
require_once __DIR__ . '/WordPressService.php';
class Users
{
	public $user_id;
	public $first_name;
	public $last_name;
	public $gender;
	public $date_of_birth;
	public $address1;
	public $address2;
	public $city;
	public $state;
	public $country;
	public $zip_code;
	public $mobile;
	public $phone;
	public $username;
	public $email;
	public $profile_image;
	public $description;
	public $status;
	public $user_type;
	public $referral_id;
	public $bank_name;
	public $account_holder;
	public $account_number;
	public $iban_no;
	public $branch_name;
	public $branch_code;

	function update_user_row($user_id, $term, $value)
	{
		global $db;

		if (empty($user_id) || empty($term)) {
			return;
		}

		// Convert Date for DB if it's DOB
		if($term == 'date_of_birth' && !empty($value)) {
			$value = date("Y-m-d", strtotime($value));
		}

		//We have to update existing record. 
		$query = 'UPDATE `users` SET
				' . $term . ' = "' . $value . '"
		WHERE user_id="' . $user_id . '"';

		$result = $db->query($query) or die($db->error);
	}//set user meta information.

	function get_usermeta($recordID, $term)
	{
		global $db;

		if (empty($recordID) && empty($term)) {
			return "";
		}
		$query = "SELECT * from `usermeta` WHERE `user_id`='{$recordID}' AND `meta_key`='{$term}'";
		$result = $db->query($query) or die($db->error);
		$row = $result->fetch_array();

		if (!empty($row)) {
			return $row["meta_value"];
		} else {
			return "";
		}
	}//get user email ends here.

	function set_usermeta($recordID, $term, $value)
	{
		global $db;

		if (empty($recordID) && empty($term)) {
			return "";
		}

		$query = "SELECT * from `usermeta` WHERE `user_id`='" . $recordID . "' AND `meta_key`='" . $term . "'";
		$result = $db->query($query) or die($db->error);
		$rows = $result->num_rows;

		if ($rows > 0) {
			//We have to update existing record. 
			$query = "UPDATE `usermeta` SET
   	    			`meta_value` = '" . $value . "'
			WHERE `user_id`='" . $recordID . "' AND `meta_key`='" . $term . "'";
		} else {
			//we have to add new record.
			$query = "INSERT into `usermeta`(`meta_id`, `user_id`, `meta_key`, `meta_value`) 
						VALUES(NULL, '" . $recordID . "', '" . $term . "', '" . $value . "')";
		}
		$result = $db->query($query) or die($db->error);
	}//set user meta information.

	function set_user_meta($user_id, $term, $value)
	{
		global $db;
		$query = "SELECT * from user_meta WHERE user_id='" . $user_id . "'";
		$result = $db->query($query) or die($db->error);
		$rows = $result->num_rows;

		if ($rows > 0) {
			//We have to update existing record. 
			$query = 'UPDATE user_meta SET
   	    			' . $term . ' = "' . $value . '"
			WHERE user_id="' . $user_id . '"';
		} else {
			//we have to add new record.
			$query = 'INSERT into user_meta(user_meta_id, user_id, ' . $term . ') VALUES(NULL, "' . $user_id . '", "' . $value . '")';
		}
		$result = $db->query($query) or die($db->error);
	}//set user meta information.

	function get_user_meta($user_id, $term)
	{
		global $db;
		$query = "SELECT * from user_meta WHERE user_id='" . $user_id . "'";
		$result = $db->query($query) or die($db->error);
		$row = $result->fetch_array();
		return isset($row[$term]) ? $row[$term] : "";
	}//get user email ends here.

	function get_user_info($user_id, $term)
	{
		global $db;
		$query = "SELECT * from users WHERE user_id='" . $user_id . "'";
		$result = $db->query($query) or die($db->error);
		$row = $result->fetch_array();
		return isset($row[$term]) ? $row[$term] : "";
	}//get user email ends here.

	function register_user($first_name, $last_name, $user_type, $username, $email, $password, $referral_id, $bank_name = '', $account_holder = '', $account_number = '', $iban_no = '', $branch_name = '', $branch_code = '')
	{
		global $db;
		
		$bank_name = $db->real_escape_string($bank_name);
		$account_holder = $db->real_escape_string($account_holder);
		$account_number = $db->real_escape_string($account_number);
		$iban_no = $db->real_escape_string($iban_no);
		$branch_name = $db->real_escape_string($branch_name);
		$branch_code = $db->real_escape_string($branch_code);

		if ($referral_id == 0) {
			$search = $db->query("SELECT user_id FROM users WHERE username LIKE '%BIZ0000%'");
			$result = $search ? $search->fetch_assoc() : null;

			if ($result == null) {
				$db->query("INSERT INTO `users`( `first_name`, `last_name`, `gender`, `date_of_birth`, `address1`, `address2`, `city`, `state`, `country`, `zip_code`, `mobile`, `phone`, `username`, `email`, `password`, `profile_image`, `description`, `status`, `activation_key`, `date_register`, `user_type`, `referral_id`) VALUES (null,null,null,null,null,null,null,null,null,null,null,null,'BIZ0000',null,null,null,null,null,null,null,'admin',null)");
				$referral_id = $db->insert_id;
			} else {
				$referral_id = $result['user_id'];
			}
		}

		//Check if user already exist
		$query = "SELECT * from users WHERE email='" . $email . "'";
		$result = $db->query($query);

		$num_user = $result->num_rows;

		if ($num_user > 0) {
			return _("Email cannot be added ") . ' <strong>' . $email . '</strong> ' . _("already exists.");
			exit();
		}
		//username validation
		$query = "SELECT * from users WHERE username='" . $username . "'";
		$result = $db->query($query);

		$num_user = $result->num_rows;

		if ($num_user > 0) {
			return _("Username cannot be added") . ' <strong>' . $username . '</strong> ' . _("Already exists.");
			exit();
		}
		$registration_date = date('Y-m-d');

		$plain_password = $password; // Capture for WP Sync
		$password_hash = get_option('password_hash');

		if ($password_hash == "argon2") {
			$options = ['cost' => 12];
			$password = password_hash($password, PASSWORD_DEFAULT, $options);
		} else {
			$password = md5($password);
		}

		$activation_key = substr(md5(uniqid(rand(), true)), 16, 16);

		if (get_option('register_verification') != '1') {
			$status = "deactivate";
		} else {
			$status = "activate";
		}

		$user_type = get_option('register_user_level');
		//adding user into database
		$query = "INSERT INTO users(first_name,last_name,username,email,password,activation_key,date_register,user_type,status,referral_id, bank_name, account_holder, account_number, iban_no, branch_name, branch_code) VALUES ('$first_name', '$last_name', '$username', '$email', '$password', '$activation_key', '$registration_date', '$user_type', '$status','$referral_id', '$bank_name', '$account_holder', '$account_number', '$iban_no', '$branch_name', '$branch_code')";
		$result = $db->query($query) or die($db->error);
		$user_id = $db->insert_id;
		//Email to user
		$site_url = get_option('site_url');

		$email_message = _("Thank you for joining us.") . "<br />";
		$email_message .= _("Your username is") . ": <strong> " . $username . '</strong><br>';
		$email_message .= _("Your Password is") . ": <strong> " . $plain_password . '</strong><br>';
		$email_message .= _("Please click the link below to verify your email address and activate your account.") . "<br />";
		$email_message .= "<a href='" . $site_url . "login.php?confirmation_code=" . $activation_key . "&user_id=" . $user_id . "'>" . _("Confirm Email Address") . "</a>";
		$email_message .= "<br><br>" . _("Thank you again. Please contact us if you need any assistance.");

		$message = $email_message;
		$mailto = $email;
		$subject = _("Please verify your email address");

		send_email($mailto, $subject, $message);
		//Notify other users of same level on new registration.
		if (get_option('notify_user_group') == '1'):
			//message object.
			$subject = _("New user registration");
			$message = "<h2>" . _("New user in your user group.") . "</h2>";
			$message .= "<p><strong>" . _("Name") . ": </strong>" . $first_name . " " . $last_name . "</p>";
			$message .= "<p><strong>" . _("Email") . ": </strong>" . $email . "</p>";
			$message .= "<p><strong>" . _("Username") . ": </strong>" . $username . "</p>";

			if (!isset($_SESSION["user_id"])) {
				$_SESSION["user_id_sender"] = $user_id;
			}

			$message_obj = new Messages;
			$message_obj->level_message($user_type, $subject, $message);
		endif;

		// WordPress Sync: User Signup
		try {
			$wpService = new WordPressService();
			$wpService->syncUser($user_id, $email, $plain_password, $username, $first_name, $last_name, '', $status);
		} catch (Exception $e) {
			// Silent fail for main application flow
		}

		return $user_id;
	}//register_user ends here.

	function google_facebook_login_register($first_name, $last_name, $gender, $email, $user_type, $profile_image)
	{
		global $db; //starting database object.

		$query = "SELECT * from users WHERE email='" . $email . "' OR username='" . $email . "'";
		$result = $db->query($query) or die($db->error);
		$num_rows = $result->num_rows;

		$registration_date = date('Y-m-d');
		$pass = randomPassword();

		$plain_password = $pass; // Capture for WP Sync
		$password_hash = get_option('password_hash');

		if ($password_hash == "argon2") {
			$options = ['cost' => 12];
			$password = password_hash($pass, PASSWORD_DEFAULT, $options);
		} else {
			$password = md5($pass);
		}

		$status = 'activate';

		if ($user_type == 'admin') {
			$user_type = get_option('register_user_level');
		}

		if ($num_rows == 0) {
			$query = "INSERT INTO users(first_name,last_name,gender,username,email,profile_image,password,date_register,user_type,status) VALUES('$first_name', '$last_name', '$gender', '$email', '$email', '$profile_image', '$password', '$registration_date', '$user_type', '$status')";
			$result = $db->query($query) or die($mysqli->error);
			$user_id = $db->insert_id;

			$email_msg = '<h1>' . _("Thank you for registration") . '.</h1>';
			$email_msg .= '<p>' . _("You can use facebook login or following login information to access our system.") . '</p>';
			$email_msg .= '<p>' . _("Email") . ':' . $email . '<br>';
			$email_msg .= _("Password") . ':' . $pass . '</p>';
			$email_msg .= get_option('site_url');

			$subject = _("Thank you for registration") . ' | ' . get_option('site_name');

			send_email($email, $subject, $email_msg);
			//Notification to user group on new registration.
			if (get_option('notify_user_group') == '1'):
				//message object.
				$subject = _("New user registration.");
				$message = "<h2>" . _("New user in your user group.") . "</h2>";
				$message .= "<p><strong>" . _("Name") . ": </strong>" . $first_name . " " . $last_name . "</p>";
				$message .= "<p><strong>" . _("Email") . ": </strong>" . $email . "</p>";
				$message .= "<p><strong>" . _("Username") . ": </strong>" . $username . "</p>";

				$message_obj = new Messages;
				$message_obj->level_message($user_type, $subject, $message);
			endif;

			// WordPress Sync: Social Registration
			try {
				$wpService = new WordPressService();
				$wpService->syncUser($user_id, $email, $plain_password, $email, $first_name, $last_name);
			} catch (Exception $e) {
				// Silent fail
			}

			$query = "SELECT * from users WHERE email='" . $email . "' OR username='" . $email . "'";
			$result = $db->query($query) or die($db->error);
			$num_rows = 1;
		}//if user do not exist register user.

		if ($num_rows > 0) {
			$row = $result->fetch_array();

			if ($row['status'] == 'deactivate') {
				$message = _("Your account is not activated yet please confirm your Email address to activate your account!");
			} else if ($row['status'] == 'activate') {
				extract($row);
				$this->user_id = $user_id;
				$this->first_name = $first_name;
				$this->last_name = $last_name;
				$this->username = $username;
				$this->email = $email;
				$this->status = $status;
				$this->user_type = $user_type;
				if ($profile_image != '') {
					$this->profile_image = $profile_image;
				} else {
					$this->profile_image = 'assets/images/thumb.png';
				}

				$message = 1;
			} else {
				$message = _("Your account is ban or suspend. Please contact site admin.");
			}

		} else {
			$message = _("Couldn't find email.");
		}
		return $message;
	}//login func ends here.

	function login_user($email, $password)
	{
		global $db;

		if (empty($email) || empty($password)) {
			return _("Email and password both are required fields.");
		}

		$password_submission = $password;

		// $query 	= "SELECT * FROM `users` WHERE `email`='{$email}' OR `username`='{$email}'";

		$select_query = $db->query("SELECT * FROM users WHERE email LIKE '%{$email}%' OR username LIKE '%{$email}%' LIMIT 1");

		if ($select_query->num_rows > 0) {
			$row = $select_query->fetch_assoc();

			$lock_account = $this->get_user_meta($row['user_id'], 'login_lock');

			$lock_account = (empty($lock_account)) ? 0 : $lock_account;

			if ($lock_account != 'No') {
				if ($lock_account + get_option('wrong_attempts_time') * 60 > time()) {
					return _("You are locked cause of maximum login attempts.");
					exit();
				} else {
					$this->set_user_meta($row['user_id'], 'login_attempt', '0');
					$this->set_user_meta($row['user_id'], 'login_lock', 'No'); //setting last login time.
				}
			}

			$password_hash = get_option('password_hash');

			if ($password_hash == "argon2") {
				// This is the hash of the password in example above.
				$hash = $row['password'];

				if (password_verify($password_submission, $hash)) {
					$status = "valid";
				} else {
					$status = "invalid";
				}
			} else {
				$password_submission = md5($password_submission);

				if ($row['password'] == $password_submission) {
					$status = "valid";
				} else {
					$status = "invalid";
				}
			}

			if ($status == "valid") {
				if ($row['status'] == 'deactivate') {
					$message = _("Your account is not activated yet please confirm your Email address to activate your account!");
				} else if ($row['status'] == 'activate') {
					extract($row);
					$this->user_id = $user_id;
					$this->first_name = $first_name;
					$this->last_name = $last_name;
					$this->username = $username;
					$this->email = $email;
					$this->status = $status;
					$this->user_type = $user_type;

					if ($profile_image != '') {
						$this->profile_image = $profile_image;
					} else {
						$this->profile_image = 'assets/images/thumb.png';
					}

					$message = 1;

					// WordPress Sync: User Login check removed as per requirement "automatic login nahi huga"
				} else {
					$message = _("You cannot login your account is ban or suspend. Contact site admin.");
				}
			} else {
				$message = _("Password does not match.");
				$login_attempt = $this->get_user_meta($row['user_id'], 'login_attempt');
				$login_attempt = (empty($login_attempt)) ? 1 : $login_attempt + 1;

				$this->set_user_meta($row['user_id'], 'login_attempt', $login_attempt);

				if ($login_attempt >= get_option('maximum_login_attempts')) {
					$this->set_user_meta($row['user_id'], 'login_lock', time());
				}
			}

		} else {
			$message = _("Couldn't find email.");
		}
		return $message;
	}//login func ends here.

	function delete_user($user_type, $user_id)
	{
		global $db;

		if ($user_type == 'admin') {
			$query = 'DELETE from users WHERE user_id="' . $user_id . '"';
			$result = $db->query($query) or die($db->error);

			// if ($result) {
			// 	// Re-sequence subscriber usernames
			// 	$all_users = "SELECT user_id FROM users WHERE user_type LIKE '%subscriber%' ORDER BY user_id ASC";
			// 	$users_result = $db->query($all_users);
				
			// 	if ($users_result && $users_result->num_rows > 0) {
			// 		$count = 0;
			// 		while ($row = $users_result->fetch_assoc()) {
			// 			$auto_generated_user_name = 'BIZ';

			// 			if (strlen($count) == 1) {
			// 				if (($count + 1) == 10) {
			// 					$auto_generated_user_name .= '00' . ($count + 1);
			// 				} else {
			// 					$auto_generated_user_name .= '000' . ($count + 1);
			// 				}
			// 			} elseif (strlen($count) == 2) {
			// 				if (($count + 1) == 100) {
			// 					$auto_generated_user_name .= '0' . ($count + 1);
			// 				} else {
			// 					$auto_generated_user_name .= '00' . ($count + 1);
			// 				}
			// 			} elseif (strlen($count) == 3) {
			// 				if (($count + 1) == 1000) {
			// 					$auto_generated_user_name .= ($count + 1);
			// 				} else {
			// 					$auto_generated_user_name .= '0' . ($count + 1);
			// 				}
			// 			} elseif (strlen($count) == 4) {
			// 				$auto_generated_user_name .= ($count + 1);
			// 			}
						
			// 			$update_query = "UPDATE users SET username = '" . $auto_generated_user_name . "' WHERE user_id = '" . $row['user_id'] . "'";
			// 			$db->query($update_query);
						
			// 			$count++;
			// 		}
			// 	}
			// }

			$message = _("User deleted successfuly");

			// WordPress Sync: Delete User
			try {
				$wpService = new WordPressService();
				$wpService->deleteUser($user_id);
			} catch (Exception $e) {
				// Silent fail
			}

		} else {
			$message = _("Cannot delete user");
		}
		return $message;
	}//delete level ends here.

	function list_users($user_type)
	{
		global $db;
		$content = '';
		$modals = '';
		if ($user_type == 'admin') {
			$query = 'SELECT * from users ORDER by first_name ASC';
			$result = $db->query($query) or die($db->error);
			$content = '';
			$count = 0;

			while ($row = $result->fetch_array()) {
				extract($row);
				$user_id = intval($user_id);
				$referral_id = intval($row['referral_id']);
				$referal = $db->query("SELECT username, first_name, last_name FROM users WHERE user_id = " . $referral_id . " LIMIT 1");
				$ref_row = $referal ? $referal->fetch_assoc() : null;
				/* Total Investment */
				$investment = $db->query("
				SELECT 
				COALESCE(SUM(amount),0) AS total
				FROM user_investments
				WHERE user_id = $user_id
				");

				$investment = $investment->fetch_assoc();
				$total_investment = $investment['total'];

				/* Total Commission */

				$commission = $db->query("
				SELECT
				COALESCE(SUM(
				CASE 
				WHEN uid.comission_expiry_date <= CURDATE() 
				THEN uid.comission 
				END),0) AS total_commission,

				COALESCE(SUM(
				CASE 
				WHEN uid.is_claimed = 0 
				AND uid.comission_expiry_date <= CURDATE()
				THEN uid.comission 
				END),0) AS available_commission,

				COALESCE(SUM(
				CASE 
				WHEN uid.is_claimed = 1 
				AND uid.comission_expiry_date <= CURDATE()
				THEN uid.comission 
				END),0) AS claimed_commission

				FROM user_investment_details uid
				JOIN user_investments ui
				ON uid.investment_id = ui.investment_id
				WHERE uid.user_id = $user_id
				");

				$commission = $commission->fetch_assoc();

				$total_commission = $commission['total_commission'];
				$available_commission = $commission['available_commission'];
				$claimed_commission = $commission['claimed_commission'];

				/* Commission Dates */

				$commission_dates = $db->query("
				SELECT 
				p.plan_name,
				uid.comission,
				uid.comission_expiry_date,
				CASE WHEN uid.user_id = ui.user_id THEN 'ROI' ELSE 'Referral' END AS comission_type
				FROM user_investment_details uid
				JOIN user_investments ui 
				ON uid.investment_id = ui.investment_id
				JOIN investment_plans p
				ON ui.plan_id = p.plan_id
				WHERE uid.user_id = $user_id
				ORDER BY uid.comission_expiry_date ASC
				");

				/* REWARDS CALCULATION */

				$unit_value = 30000;

				// $total_query = $db->query("
				// SELECT SUM(amount) as total_investment
				// FROM user_investments
				// WHERE user_id = $user_id
				// ");
				$total_query = $db->query("
					SELECT SUM(ui.amount) as total_investment
					FROM user_investments ui
					JOIN users u ON u.user_id = ui.user_id
					WHERE u.referral_id = '$user_id'
				");

				$reward_row = $total_query->fetch_assoc();
				$total_investment_reward = $reward_row['total_investment'] ? $reward_row['total_investment'] : 0;

				$units_achieved = floor($total_investment_reward / $unit_value);

				$levels = [
					1 => 3,
					2 => 9,
					3 => 27,
					4 => 81,
					5 => 243,
					6 => 729
				];
				/* Packages */
				$packages = $db->query("
				SELECT 
				p.plan_name,
				SUM(ui.amount) AS total_investment
				FROM user_investments ui
				JOIN investment_plans p 
				ON ui.plan_id = p.plan_id
				WHERE ui.user_id = $user_id
				GROUP BY p.plan_id
				");
				$count++;
				if ($count % 2 == 0) {
					$class = 'even';
				} else {
					$class = 'odd';
				}
				$content .= '<tr class="' . $class . '">';
				$content .= '<td>';
				$content .= $first_name . ' ' . $last_name;
				$content .= '</td><td>';
				if ($city != '') {
					$content .= $city . ', ';
				}
				if ($state != '') {
					$content .= $state . ', ';
				}
				$content .= $country;
				$content .= '</td><td>';
				$content .= wc_get_user_display_name($username, $first_name, $last_name);
				$content .= '</td><td>';
				$content .= $email;
				$content .= '</td><td>';
				$content .= isset($ref_row) && !empty($ref_row) ? wc_get_user_display_name($ref_row['username'], $ref_row['first_name'], $ref_row['last_name']) : 'N\A';
				$content .= '</td><td>';
				$content .= $status ? ucfirst($status) : null;
				$content .= '</td><td>';
				$content .= ucfirst($user_type);
				$content .= '</td><td><div class="action-btn-container">';
				if ($user_type == 'subscriber') {
					$content .= '<button class="action-btn btn-details" data-toggle="modal" data-target="#details_' . $user_id . '">' . _("Details") . '</button>';
				}
				$content .= '<button class="action-btn btn-message" data-toggle="modal" data-target="#modal_' . $user_id . '">' . _("Message") . '</button>';
				$content .= '<!-- Modal -->
<script type="text/javascript">
$(function(){
$("#message_form_' . $user_id . '").on("submit", function(e){
  e.preventDefault();
  tinyMCE.triggerSave();
  $.post("lib/includes/messageprocess.php", 
	 $("#message_form_' . $user_id . '").serialize(), 
	 function(data, status, xhr){
	   $("#success_message_' . $user_id . '").html("<div class=\'alert alert-success\'>"+data+"</div>");
	 });
});
});
</script>				
<div class="modal fade" id="modal_' . $user_id . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="message_form_' . $user_id . '" method="post" name="send_message">
	<div class="modal-content investment-modal">
      <div class="modal-header investment-header">
        <h4 class="modal-title" id="myModalLabel">' . _("Send Message") . '</h4>
		<button type="button" class="btn-close-investment" data-dismiss="modal" aria-hidden="true">&times;</button>
      </div>
	  
      <div class="modal-body">
      		<div id="success_message_' . $user_id . '"></div>
	   		<div class="form-group">
 				<label class="control-label">' . _("To") . '</label>
 				<input type="text" class="form-control" name="message_to" value="' . _("Email") . ':(' . $email . ') ' . _("User") . ': (' . wc_get_user_display_name($username, $first_name, $last_name) . ')" readonly="readonly" />
 			</div>
			
			<div class="form-group">
				<label class="control-label">' . _("Subject") . '</label>
				<input type="text" class="form-control" name="subject" value="" />
			</div>
			
			<div class="form-group">
				<label class="control-label">' . _("Message") . '</label>
				<textarea class="tinyst form-control" name="message"></textarea>
			</div>
      </div>
	  <input type="hidden" name="from" value="' . $_SESSION['user_id'] . '" />
	  <input type="hidden" name="user_id" value="' . $user_id . '" />
	  <input type="hidden" name="single_form" value="1" />
      <div class="modal-footer investment-footer mb-5">
        <button type="button" class="btn btn-default" data-dismiss="modal">' . _("Close") . '</button>
		<input type="submit" value="' . _("Send Message") . '" class="btn btn-golden btn-md" />
      </div>
    </div><!-- /.modal-content -->
   </form>
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->';
				if ($user_type == 'subscriber') {
					$modals .= '

				<div class="modal fade" id="details_' . $user_id . '" tabindex="-1">
				<div class="modal-dialog modal-lg">
				<div class="modal-content investment-modal">

				<div class="modal-header investment-header">
 				<h5 class="modal-title">
 					User ' . wc_get_user_display_name($username, $first_name, $last_name) . ' Details
 				</h5>				
				<button type="button" class="btn-close-investment" data-dismiss="modal">&times;</button>
				</div>

				<div class="modal-body">

 				<b>User:</b> ' . wc_get_user_display_name($username, $first_name, $last_name) . '<br>
 				<b>Email:</b> ' . $email . '<br>
 				<b>Joined:</b> ' . (!empty($date_register) ? date("d M Y", strtotime($date_register)) : "N/A") . '<br>
 				<b>Referred By:</b> ' . (isset($ref_row) ? wc_get_user_display_name($ref_row['username'], $ref_row['first_name'], $ref_row['last_name']) : 'N/A') . '

				<hr>

				<b>Total Investment:</b> PKR ' . number_format($total_investment, 2) . '<br>
				<b>Total Claimed Commission:</b> PKR ' . number_format($claimed_commission, 2) . '<br><br>
							
				<b>Investment Packages</b>
				<ul>
				';

					if ($packages && $packages->num_rows > 0) {
						while ($pkg = $packages->fetch_assoc()) {
							$modals .= '<li>' . $pkg['plan_name'] . ' - PKR ' . number_format($pkg['total_investment'], 2) . '</li>';
						}
					} else {
						$modals .= '<li>No Packages</li>';
					}

					$modals .= '

				</ul>

				<hr>
				<b>Commission Summary</b>
				<ul>
				';
					if ($commission_dates && $commission_dates->num_rows > 0) {
						while ($c = $commission_dates->fetch_assoc()) {
							$type = $c['comission_type'];
							$badge_class = ($type == 'ROI') ? 'badge text-bg-success' : 'badge text-light bg-info';
							$badge_label = ($type == 'ROI') ? 'ROI' : 'Referral';
							
							$modals .= "<li>" . $c['plan_name'] . " — PKR " . number_format($c['comission'], 2) . " - " . date("d M Y", strtotime($c['comission_expiry_date'])) . " <span class='" . $badge_class . "'>" . $badge_label . "</span></li>";
						}
					} else {
						$modals .= "<li>No Commissions</li>";
					}
					$modals .= '

				</ul>

				</div>

				<div class="modal-footer investment-footer">
				<button class="btn btn-golden btn-md mb-5" data-dismiss="modal">Close</button>
				</div>

				</div>
				</div>
				</div>

			';
				}
				$content .= '<a href="view_profile.php?user_id=' . $user_id . '" class="action-btn btn-details">' . _("View") . '</a>';
				$content .= '<form method="post" name="edit" action="manage_users.php" class="d-inline">';
				$content .= '<input type="hidden" name="edit_user" value="' . $user_id . '">';
				$content .= '<input type="submit" class="action-btn btn-edit" value="' . _("Edit") . '">';
				$content .= '</form>';
				$content .= '<form method="post" name="delete" onsubmit="return confirm_delete();" action="" class="d-inline">';
				$content .= '<input type="hidden" name="delete_user" value="' . $user_id . '">';
				$content .= '<input type="submit" class="action-btn btn-delete" value="' . _("Delete") . '">';
				$content .= '</form>';
				$content .= '</div></td>';
				$content .= '</tr>';
				unset($class);
			}//loop ends here.
		} else {
			$content = _("You cannot view list of users.");
		}
		echo $content;
		echo $modals;
	}//list_levels ends here.

	function get_total_users($condition)
	{
		global $db;

		if ($_SESSION['user_type'] == 'admin') {
			if ($condition == 'all') {
				$query = "SELECT * from users";
			} else {
				$query = "SELECT * from users WHERE status='" . $condition . "'";
			}
			$result = $db->query($query) or die($db->error);
			$num_rows = $result->num_rows;
			echo $num_rows;
		} else {
			echo _("You are not allowed to view this list.");
		}
	}//prints total registered users.

	function edit_profile($user_id, $first_name, $last_name, $gender, $date_of_birth, $address1, $address2, $city, $state, $country, $zip_code, $mobile, $phone, $username, $email, $password, $profile_image, $description, $bank_name = '', $account_holder = '', $account_number = '', $iban_no = '', $branch_name = '', $branch_code = '')
	{
		global $db;

		$current_email = $this->get_user_info($user_id, 'email');
		$current_username = $this->get_user_info($user_id, 'username');

		$first_name = trim($first_name);
		$last_name = trim($last_name);
		$email = trim($email);
		$username = trim($username);
		$mobile = trim($mobile);
		$phone = trim($phone);
		
		$bank_name = $db->real_escape_string($bank_name);
		$account_holder = $db->real_escape_string($account_holder);
		$account_number = $db->real_escape_string($account_number);
		$iban_no = $db->real_escape_string($iban_no);
		$branch_name = $db->real_escape_string($branch_name);
		$branch_code = $db->real_escape_string($branch_code);
		
		$address1 = $db->real_escape_string(trim($address1));
		$address2 = $db->real_escape_string(trim($address2));

		$description = $db->real_escape_string(trim($description));

		if ($email != $current_email) {
			$query = "SELECT * from users WHERE email='" . $email . "'";
			$result = $db->query($query);

			$num_user = $result->num_rows;

			if ($num_user > 0) {
				return _("Email cannot be added") . ' <strong>' . $email . '</strong> ' . _("Already exists.");
				exit();
			}
		}

		if ($current_username != $username) {
			//username validation
			$query = "SELECT * from users WHERE username='" . $username . "'";
			$result = $db->query($query);

			$num_user = $result->num_rows;

			if ($num_user > 0) {
				return _("Username couldn't be added") . ' <strong>' . $username . '</strong> ' . _("Already exists");
				exit();
			}
		}
		$date_of_birth = (!empty($date_of_birth)) ? date('Y-m-d', strtotime(str_replace('-', '/', $date_of_birth))) : "000-00-00";
		// Before update, get current data to see what changes
		$old_data_query = $db->query("SELECT * FROM users WHERE user_id='$user_id'");
		$old_data = $old_data_query->fetch_assoc();

		if ($password == '') {
			$query = 'UPDATE users SET
   	    			first_name = "' . $first_name . '",
					last_name = "' . $last_name . '",
					gender = "' . $gender . '",
					date_of_birth = "' . $date_of_birth . '",
					address1 = "' . $address1 . '",
					address2 = "' . $address2 . '",
					city = "' . $city . '",
					state = "' . $state . '",
					country = "' . $country . '",
					zip_code = "' . $zip_code . '",
					mobile = "' . $mobile . '",
					phone = "' . $phone . '",
					username = "' . $username . '",
					email = "' . $email . '",
					profile_image = "' . $profile_image . '",
					description = "' . $description . '",
					bank_name = "' . $bank_name . '",
					account_holder = "' . $account_holder . '",
					account_number = "' . $account_number . '",
					iban_no = "' . $iban_no . '",
					branch_name = "' . $branch_name . '",
					branch_code = "' . $branch_code . '"
			WHERE user_id="' . $user_id . '"';
		} else {

			$password_hash = get_option('password_hash');
			$plain_password = $password;

			if ($password_hash == "argon2") {
				$options = ['cost' => 12];
				$password = password_hash($password, PASSWORD_DEFAULT, $options);
			} else {
				$password = md5($password);
			}

			$query = 'UPDATE users SET
   	    			first_name = "' . $first_name . '",
					last_name = "' . $last_name . '",
					gender = "' . $gender . '",
					date_of_birth = "' . $date_of_birth . '",
					address1 = "' . $address1 . '",
					address2 = "' . $address2 . '",
					city = "' . $city . '",
					state = "' . $state . '",
					country = "' . $country . '",
					zip_code = "' . $zip_code . '",
					mobile = "' . $mobile . '",
					phone = "' . $phone . '",
					username = "' . $username . '",
					email = "' . $email . '",
					password = "' . $password . '",
					profile_image = "' . $profile_image . '",
					description = "' . $description . '",
					bank_name = "' . $bank_name . '",
					account_holder = "' . $account_holder . '",
					account_number = "' . $account_number . '",
					iban_no = "' . $iban_no . '",
					branch_name = "' . $branch_name . '",
					branch_code = "' . $branch_code . '"
			WHERE user_id="' . $user_id . '"';
		}
		$result = $db->query($query) or die($db->error);

		// Email Notification Logic
		$changes = array();
		if (trim($old_data['first_name']) != $first_name) $changes[] = _("First Name");
		if (trim($old_data['last_name']) != $last_name) $changes[] = _("Last Name");
		if (trim($old_data['email']) != $email) $changes[] = _("Email Address");
		if (trim($old_data['username']) != $username) $changes[] = _("Username");
		if (trim($old_data['mobile']) != $mobile) $changes[] = _("Mobile");
		if (trim($old_data['phone']) != $phone) $changes[] = _("Phone");
		if (trim($old_data['gender']) != $gender) $changes[] = _("Gender");
		if (isset($plain_password) && $plain_password != '') $changes[] = _("Password");
		if (trim($old_data['address1']) != $address1 || trim($old_data['city']) != $city || trim($old_data['country']) != $country) $changes[] = _("Address Details");
		
		// Bank fields check
		if (trim($old_data['bank_name']) != $bank_name || trim($old_data['account_number']) != $account_number) $changes[] = _("Bank details");

		if (!empty($changes)) {
			$subject = _("Notification: Your account profile has been updated");
			$message = _("Hello") . " " . $first_name . ",<br /><br />";
			$message .= _("We are writing to inform you that your profile has been successfully updated.") . "<br />";
			$message .= _("The following fields were updated") . ": <strong>" . implode(", ", $changes) . "</strong><br /><br />";
			
			$message .= "<strong>" . _("Updated Bank Details:") . "</strong><br />";
			$message .= _("Bank Name") . ": " . $bank_name . "<br />";
			$message .= _("Account Holder") . ": " . $account_holder . "<br />";
			$message .= _("Account Number") . ": " . $account_number . "<br />";
			$message .= _("IBAN") . ": " . $iban_no . "<br />";

			$message .= "<br />" . _("If you did not make these changes, please contact our support team immediately.");
			$message .= "<br /><br />" . _("Thank you.");
			
			send_email($email, $subject, $message);
		}

		// WordPress Sync: Profile and Password Update
		try {
			$wpService = new WordPressService();
			// Use the captured $plain_password from line 797
			$wpService->syncUser($user_id, $email, (isset($plain_password) ? $plain_password : ''), $username, $first_name, $last_name, $description);
		} catch (Exception $e) {
			// Silent fail
		}

		return _("User updated successfuly.");
	}//update user ends here.

	function set_user($user_id, $user_type, $login_user)
	{
		global $db;

		if ($user_type == 'admin') {
			$query = 'SELECT * from users WHERE user_id="' . $user_id . '"';
		} else if ($user_id == $login_user) {
			$query = 'SELECT * from users WHERE user_id="' . $user_id . '"';
		} else {
			echo _("You are trying to do something which you are not allowed to do.");
		}
		$result = $db->query($query) or die($db->error);
		$row = $result->fetch_array();
		//results ends here.
		$this->user_id = $row['user_id'] ?? 'N\A';
		$this->first_name = $row['first_name'] ?? 'N\A';
		$this->last_name = $row['last_name'] ?? 'N\A';
		$this->gender = $row['gender'] ?? 'N\A';
		$this->date_of_birth = $row['date_of_birth'] ?? 'N\A';
		$this->address1 = $row['address1'] ?? 'N\A';
		$this->address2 = $row['address2'] ?? 'N\A';
		$this->city = $row['city'] ?? 'N\A';
		$this->state = $row['state'] ?? 'N\A';
		$this->country = $row['country'] ?? 'N\A';
		$this->zip_code = $row['zip_code'] ?? 'N\A';
		$this->mobile = $row['mobile'] ?? 'N\A';
		$this->phone = $row['phone'] ?? 'N\A';
		$this->username = $row['username'] ?? 'N\A';
		$this->email = $row['email'] ?? 'N\A';
		$this->profile_image = $row['profile_image'] ?? 'N\A';
		$this->description = $row['description'] ?? 'N\A';
		$this->status = $row['status'] ?? 'N\A';
		$this->user_type = $row['user_type'] ?? 'N\A';
		$this->referral_id = $row['referral_id'] ?? 'N\A';
		$this->bank_name = $row['bank_name'] ?? '';
		$this->account_holder = $row['account_holder'] ?? '';
		$this->account_number = $row['account_number'] ?? '';
		$this->iban_no = $row['iban_no'] ?? '';
		$this->branch_name = $row['branch_name'] ?? '';
		$this->branch_code = $row['branch_code'] ?? '';
	}//level set ends here.

	function update_user($user_id, $user_type_ses, $first_name, $last_name, $gender, $date_of_birth, $address1, $address2, $city, $state, $country, $zip_code, $mobile, $phone, $username, $email, $password, $profile_image, $description, $status, $user_type, $referral_id, $bank_name = '', $account_holder = '', $account_number = '', $iban_no = '', $branch_name = '', $branch_code = '', $nic_front = '', $nic_back = '')
	{
		global $db;

		if ($referral_id == 0) {
			$search = $db->query("SELECT user_id FROM users WHERE username LIKE '%BIZ0000%'");
			$result = $search ? $search->fetch_assoc() : null;

			if ($result == null) {
				$db->query("INSERT INTO `users`( `first_name`, `last_name`, `gender`, `date_of_birth`, `address1`, `address2`, `city`, `state`, `country`, `zip_code`, `mobile`, `phone`, `username`, `email`, `password`, `profile_image`, `description`, `status`, `activation_key`, `date_register`, `user_type`, `referral_id`) VALUES (null,null,null,null,null,null,null,null,null,null,null,null,'BIZ0000',null,null,null,null,null,null,null,'subscriber',null)");
				$referral_id = $db->insert_id;
			} else {
				$referral_id = $result['user_id'];
			}
		}

		$current_email = $this->get_user_info($user_id, 'email');
		$current_username = $this->get_user_info($user_id, 'username');

		$address1 = $db->real_escape_string($address1);
		$address2 = $db->real_escape_string($address2);
		$description = $db->real_escape_string($description);

		if ($email != $current_email) {
			$query = "SELECT * from users WHERE email='" . $email . "'";
			$result = $db->query($query);

			$num_user = $result->num_rows;

			if ($num_user > 0) {
				return _("This email cannot be used") . ' <strong>' . $email . '</strong> ' . _("already exists in our system.");
				exit();
			}
		}

		if ($current_username != $username) {
			//username validation
			$query = "SELECT * from users WHERE username='" . $username . "'";
			$result = $db->query($query);

			$num_user = $result->num_rows;

			if ($num_user > 0) {
				return _("Username couldn't be added") . ' <strong>' . $username . '</strong> ' . _("username already exists.");
				exit();
			}
		}

		$date_of_birth = (!empty($date_of_birth)) ? date('Y-m-d', strtotime(str_replace('-', '/', $date_of_birth))) : "000-00-00";
		// Before update, get current data
		$old_data_query = $db->query("SELECT * FROM users WHERE user_id='$user_id'");
		$old_data = $old_data_query->fetch_assoc();

		//updating user info.
		if ($user_type_ses == 'admin') {
			if ($password == '') {

				$query = 'UPDATE users SET
   	    			first_name = "' . $first_name . '",
					last_name = "' . $last_name . '",
					gender = "' . $gender . '",
					date_of_birth = "' . $date_of_birth . '",
					address1 = "' . $address1 . '",
					address2 = "' . $address2 . '",
					city = "' . $city . '",
					state = "' . $state . '",
					country = "' . $country . '",
					zip_code = "' . $zip_code . '",
					mobile = "' . $mobile . '",
					phone = "' . $phone . '",
					username = "' . $username . '",
					email = "' . $email . '",
					profile_image = "' . $profile_image . '",
					description = "' . $description . '",
					bank_name = "' . $bank_name . '",
					account_holder = "' . $account_holder . '",
					account_number = "' . $account_number . '",
					iban_no = "' . $iban_no . '",
					branch_name = "' . $branch_name . '",
					branch_code = "' . $branch_code . '",
					nic_front = "' . (!empty($nic_front) ? $nic_front : $old_data['nic_front']) . '",
					nic_back = "' . (!empty($nic_back) ? $nic_back : $old_data['nic_back']) . '",
					status = "' . $status . '",
					user_type = "' . $user_type . '",
					referral_id = "' . $referral_id . '"
			WHERE user_id="' . $user_id . '"';
			} else {

				$password_hash = get_option('password_hash');
				$plain_password = $password;

				if ($password_hash == "argon2") {
					$options = ['cost' => 12];
					$password = password_hash($password, PASSWORD_DEFAULT, $options);
				} else {
					$password = md5($password);
				}

				$query = 'UPDATE users SET
   	    			first_name = "' . $first_name . '",
					last_name = "' . $last_name . '",
					gender = "' . $gender . '",
					date_of_birth = "' . $date_of_birth . '",
					address1 = "' . $address1 . '",
					address2 = "' . $address2 . '",
					city = "' . $city . '",
					state = "' . $state . '",
					country = "' . $country . '",
					zip_code = "' . $zip_code . '",
					mobile = "' . $mobile . '",
					phone = "' . $phone . '",
					username = "' . $username . '",
					email = "' . $email . '",
					password = "' . $password . '",
					profile_image = "' . $profile_image . '",
					description = "' . $description . '",
					bank_name = "' . $bank_name . '",
					account_holder = "' . $account_holder . '",
					account_number = "' . $account_number . '",
					iban_no = "' . $iban_no . '",
					branch_name = "' . $branch_name . '",
					branch_code = "' . $branch_code . '",
					nic_front = "' . (!empty($nic_front) ? $nic_front : $old_data['nic_front']) . '",
					nic_back = "' . (!empty($nic_back) ? $nic_back : $old_data['nic_back']) . '",
					status = "' . $status . '",
					user_type = "' . $user_type . '",
					referral_id = "' . $referral_id . '"
			WHERE user_id="' . $user_id . '"';
			}
			$result = $db->query($query) or die($db->error);

			// Email Notification Logic for Admin Update
			$changes = array();
			if (trim($old_data['first_name']) != trim($first_name)) $changes[] = _("First Name");
			if (trim($old_data['last_name']) != trim($last_name)) $changes[] = _("Last Name");
			if (trim($old_data['email']) != trim($email)) $changes[] = _("Email Address");
			if (isset($plain_password) && $plain_password != '') $changes[] = _("Password");
			if (trim($old_data['status']) != trim($status)) $changes[] = _("Account Status");
			if (trim($old_data['bank_name']) != $bank_name || trim($old_data['account_number']) != $account_number) $changes[] = _("Bank Details");
			if (!empty($nic_front) || !empty($nic_back)) $changes[] = _("Identification Documents (NIC)");
			if (trim($old_data['user_type']) != trim($user_type)) $changes[] = _("User Access Level");

			if (!empty($changes)) {
				$subject = _("Security Notification: Your account details have been updated");
				$message = _("Hello") . " " . $first_name . ",<br /><br />";
				$message .= _("This is an official notification that an administrator has updated your account profile information.") . "<br />";
				$message .= _("Changes detected in") . ": <strong>" . implode(", ", $changes) . "</strong><br /><br />";
				
				if(isset($plain_password) && $plain_password != '') {
					$message .= "<strong>" . _("New Security Credentials:") . "</strong><br />";
					$message .= _("New Password") . ": <code style='background:#f4f4f4;padding:2px 5px;'>" . $plain_password . "</code><br /><br />";
				}

				$message .= "<strong>" . _("Updated Bank Details:") . "</strong><br />";
				$message .= _("Bank Name") . ": " . $bank_name . "<br />";
				$message .= _("Account Holder") . ": " . $account_holder . "<br />";
				$message .= _("Account Number") . ": " . $account_number . "<br />";
				$message .= _("IBAN") . ": " . $iban_no . "<br /><br />";
				
				$message .= "<strong>" . _("Updated Identification Documents:") . "</strong><br />";
				$cf = !empty($nic_front) ? $nic_front : $old_data['nic_front'];
				$cb = !empty($nic_back) ? $nic_back : $old_data['nic_back'];
				$message .= _("NIC Front");
				$message .= _("NIC Back");

				$message .= "<br />" . _("Please log in to your dashboard to review all changes.");
				$message .= "<br /><br />" . _("Regards,") . "<br />" . get_option('site_name');

				send_email($email, $subject, $message);
			}

			// WordPress Sync: Profile, Password, and Status Update
			try {
				$wpService = new WordPressService();

				// Use the captured $plain_password from line 951
				$wpService->syncUser($user_id, $email, (isset($plain_password) ? $plain_password : ''), $username, $first_name, $last_name, $description, $status);

				// Sync Status
				$wpService->updateStatus($user_id, $status);

			} catch (Exception $e) {
				// Silent fail
			}

			return _("User updated successful.");
		} else {
			return _("You are trying to do something you are not allowed for.");
		}
	}//update user ends here.

	function reset_pass_user($user_id, $confirmation_code, $new_pass)
	{
		global $db;

		$query = "SELECT * from users WHERE user_id='" . $user_id . "'";
		$result = $db->query($query) or die($db->error);
		$row = $result->fetch_array();

		$password_hash = get_option('password_hash');
		$plain_password = $new_pass;

		if ($password_hash == "argon2") {
			$options = ['cost' => 12];
			$new_pass = password_hash($new_pass, PASSWORD_DEFAULT, $options);
		} else {
			$new_pass = md5($new_pass);
		}

		if ($confirmation_code == $row['activation_key']) {
			$query = 'UPDATE users SET password="' . $new_pass . '",activation_key="" WHERE user_id="' . $user_id . '"';
			$row_db = $db->query($query) or die($db->error);

			// WordPress Sync: Password Update
			try {
				$wpService = new WordPressService();
				$wpService->syncUser($user_id, $row['email'], $plain_password);
			} catch (Exception $e) {
				// Silent fail
			}

			// Send Email Notification
			$mailto = $row['email'];
			$subject = _("Your password has been successfully reset");
			$email_message = _("Hello") . " " . $row['first_name'] . ",<br /><br />";
			$email_message .= _("This is a confirmation that your password for your account has been successfully reset.") . "<br />";
			$email_message .= _("You can now log in using your new password.") . "<br /><br />";
			$email_message .= _("If you did not make this change, please contact our support team immediately.") . "<br /><br />";
			$email_message .= _("Thank you.");
			
			send_email($mailto, $subject, $email_message);

			$message = 1;
		} else {
			$message = _("Your activation key is expired and password cannot be reset.");
		}
		return $message;
	}//reset password function ends here.	

	function match_confirm_code($confirmation_code, $user_id)
	{
		global $db;

		//Getting Confirmation Code from database.
		$query = "SELECT * from users WHERE user_id='" . $user_id . "'";
		$result = $db->query($query) or die($db->error);
		$row = $result->fetch_array();

		if ($row['activation_key'] == $confirmation_code) {
			if ($row['status'] == 'suspend' || $row['status'] == 'ban') {
				$message = _("Your account has been suspended. Please contact the administrator for help.");
			} else {
				$status = 'activate';
				$query = 'UPDATE users SET status="' . $status . '",activation_key="" WHERE user_id="' . $user_id . '"';
				$row = $db->query($query) or die($db->error);

				// WordPress Sync: Status Update on Confirmation
				try {
					$wpService = new WordPressService();
					$wpService->updateStatus($user_id, $status);
				} catch (Exception $e) {
					// Silent fail
				}

				$message = _("Congratulations! You are activated successfully now you can use email and password to login and use our services.");
			}
		} else {
			$message = _("Your account cannot be activated.");
		}
		return $message;
	}//function  close

	function forgot_user($email)
	{
		global $db;
		$query = "SELECT * from users WHERE email='" . $email . "' OR username='" . $email . "'";
		$result = $db->query($query) or die($db->error);
		$num_rows = $result->num_rows;

		if ($num_rows > 0) {
			$row = $result->fetch_array();
			$user_id = $row['user_id'];
			$email = $row['email'];
		} else {
			return _("Email is not in our system.");
			exit();
		}
		$activation_key = substr(md5(uniqid(rand(), true)), 16, 16);
		$query = 'UPDATE users SET activation_key="' . $activation_key . '" WHERE user_id="' . $user_id . '"';
		$result = $db->query($query) or die($db->error);

		$site_url = get_option('site_url');
		$email_message = _("We received a request to reset the password associated with your account.") . "<br />";
		$email_message .= _("To proceed with the reset, please click the secure link below:") . "<br />";
		$email_message .= "<a href='" . $site_url . "forgot.php?confirmation_code=" . $activation_key . "&user_id=" . $user_id . "'>" . _("Reset My Password") . "</a>";
		$email_message .= "<br><br>" . _("If you did not request this, you can safely ignore this email. Your password will not change until you access the link above.");
		$message = $email_message;
		$mailto = $email;
		$subject = _("Information regarding your password reset request");

		send_email($mailto, $subject, $message);

		return _("Password recovery email sent please check mail for details.");
	}//forgot password function endsh ere.

	function add_user($first_name, $last_name, $gender, $date_of_birth, $address1, $address2, $city, $state, $country, $zip_code, $mobile, $phone, $username, $email, $password, $profile_image, $description, $status, $user_type, $referral_id, $bank_name = '', $account_holder = '', $account_number = '', $iban_no = '', $branch_name = '', $branch_code = '')
	{
		global $db;

		if ($referral_id == 0) {
			$search = $db->query("SELECT user_id FROM users WHERE username LIKE '%BIZ0000%'");
			$result = $search ? $search->fetch_assoc() : null;

			if ($result == null) {
				$db->query("INSERT INTO `users`( `first_name`, `last_name`, `gender`, `date_of_birth`, `address1`, `address2`, `city`, `state`, `country`, `zip_code`, `mobile`, `phone`, `username`, `email`, `password`, `profile_image`, `description`, `status`, `activation_key`, `date_register`, `user_type`, `referral_id`) VALUES (null,null,null,null,null,null,null,null,null,null,null,null,'BIZ0000',null,null,null,null,null,null,null,'admin',null)");
				$referral_id = $db->insert_id;
			} else {
				$referral_id = $result['user_id'];
			}
		}

		$address2 = $db->real_escape_string($address2);
		$description = $db->real_escape_string($description);

		$bank_name = $db->real_escape_string($bank_name);
		$account_holder = $db->real_escape_string($account_holder);
		$account_number = $db->real_escape_string($account_number);
		$iban_no = $db->real_escape_string($iban_no);
		$branch_name = $db->real_escape_string($branch_name);
		$branch_code = $db->real_escape_string($branch_code);

		//Check if user already exist
		$query = "SELECT * from users WHERE email='" . $email . "'";
		$result = $db->query($query) or die($db->error);

		$num_user = $result->num_rows;
		if ($num_user > 0) {
			return _("Email cannot be added") . ' <strong>' . $email . '</strong> ' . _("Already exists.");
			exit();
		}
		//username validation
		$query = "SELECT * from users WHERE username='" . $username . "'";
		$result = $db->query($query);

		$num_user = $result->num_rows;

		if ($num_user > 0) {
			return _("Username couldn't add") . ' <strong>' . $username . '</strong> ' . _("User already exists");
			exit();
		}
		$registration_date = date('Y-m-d');

		$password_hash = get_option('password_hash');

		if ($password_hash == "argon2") {
			$options = ['cost' => 12];
			$password_con = password_hash($password, PASSWORD_DEFAULT, $options);
		} else {
			$password_con = md5($password);
		}

		$date_of_birth = (!empty($date_of_birth)) ? date("Y-m-d H:i:s", strtotime($date_of_birth)) : '2000-01-01 00:00:00';
		$all_users = "SELECT * FROM users WHERE user_type LIKE '%subscriber%'";
		$result = $db->query($all_users);
		$count = $result->num_rows;
		$prefix = 'BIZ';
		$auto_generated_user_name = '';

		do {
		    // this line handles ALL cases (1, 10, 100, 1000...)
		    $auto_generated_user_name = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);

		    $stmt = $conn->prepare("SELECT 1 FROM users WHERE username = ? LIMIT 1");
		    $stmt->bind_param("s", $auto_generated_user_name);
		    $stmt->execute();
		    $stmt->store_result();

		    $exists = $stmt->num_rows > 0;

		    if ($exists) {
		        $count++; 
		    }

		} while ($exists);


		// die(strlen($count) . " $auto_generated_user_name");
		// $auto_generated_username = 'BIZ' . count();
		//Running Query to add user.
		$query = "INSERT into users(first_name, last_name, gender, date_of_birth, address1, address2, city, state, country, zip_code, mobile, phone, username, email, password, profile_image, description, status, activation_key, date_register, user_type, referral_id, bank_name, account_holder, account_number, iban_no, branch_name, branch_code) VALUES('" . $first_name . "', '" . $last_name . "', '" . $gender . "', '" . $date_of_birth . "', '" . $address1 . "', '" . $address2 . "', '" . $city . "', '" . $state . "', '" . $country . "', '" . $zip_code . "', '" . $mobile . "', '" . $phone . "', '" . $auto_generated_user_name . "', '" . $email . "', '" . $password_con . "', '" . $profile_image . "', '" . $description . "', '" . $status . "', '', '" . date('Y-m-d') . "', '" . $user_type . "','" . $referral_id . "', '$bank_name', '$account_holder', '$account_number', '$iban_no', '$branch_name', '$branch_code')";
		$result = $db->query($query) or die($db->error);
		$user_id = $db->insert_id;

		//Email to user
		$site_url = get_option('site_url');

		$email_message = _("Your account has been successfully created by the administrator.") . "<br />";
		$email_message .= _("Please use the following credentials to access your account:");
		$email_message .= "<br><br><strong>" . _("Email address") . ":</strong> " . $email;
		$email_message .= "<br><strong>" . _("Password") . ":</strong> " . $password;
		$email_message .= "<br><br><a href='" . $site_url . "'>" . _("Log in to your account") . "</a><br>";

		$message = $email_message;
		$mailto = $email;
		$subject = _("Account Notification: Your new login credentials");

		send_email($mailto, $subject, $message);

		//Notify other users of same level on new registration.
		if (get_option('notify_user_group') == '1'):
			//message object.
			$subject = _("New user registration.");
			$message = "<h2>" . _("New user on your user group.") . "</h2>";
			$message .= "<p><strong>" . _("Name") . ": </strong>" . $first_name . " " . $last_name . "</p>";
			$message .= "<p><strong>" . _("Email") . ": </strong>" . $email . "</p>";
			$message .= "<p><strong>" . _("Username") . ": </strong>" . $username . "</p>";

			$message_obj = new Messages;
			$message_obj->level_message($user_type, $subject, $message);
		endif;

		// WordPress Sync: Admin Created User
		try {
			$wpService = new WordPressService();
			$wpService->syncUser($user_id, $email, $password, $auto_generated_user_name, $first_name, $last_name, $description, $status);
		} catch (Exception $e) {
			// Silent fail
		}

		return array(
			'message' => _("User added details are sent on email") . ' ' . $email,
			'user_id' => $user_id
		);
	}//add user function ends here.

	function wc_last_logins()
	{
		global $db;

		$query = "SELECT * FROM `user_meta` ORDER BY `last_login_time` DESC LIMIT 5";
		$result = $db->query($query) or die($db->error);

		$output = '<table class="table table-hover mb-0"><thead>';
		$output .= '<tr><th>' . _("Login Time") . '</th>';
		$output .= '<th>' . _("IP") . '</th>';
		$output .= '<th>' . _("Name") . '</th>';
		$output .= '<th>' . _("Username") . '</th>';
		$output .= '<th>' . _("User Type") . '</th></tr>';
		$output .= '</thead><tbody>';

		while ($row = $result->fetch_array()) {
			$login_ip = $row["last_login_ip"];
			$login_time = $row["last_login_time"];

			$user_id = $row["user_id"];

			$first_name = self::get_user_info($user_id, "first_name");
			$last_name = self::get_user_info($user_id, "last_name");
			$username = self::get_user_info($user_id, "username");
			$user_type = self::get_user_info($user_id, "user_type");

			$output .= '<tr><td>' . time_elapsed_string($login_time) . '</td>';
			$output .= '<td>' . $login_ip . '</td>';
			$output .= '<td>' . $first_name . ' ' . $last_name . '</td>';
			$output .= '<td>' . wc_get_user_display_name($username, $first_name, $last_name) . '</td>';
			$output .= '<td>' . $user_type . '</td></tr>';
		}

		$output .= '</tbody></table>';

		echo $output;
	}
}//class ends here.