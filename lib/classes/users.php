<?php
//users Class
class Users {
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

	function update_user_row( $user_id, $term, $value ) { 
		global $db;

		if ( empty( $user_id ) || empty( $term ) || empty( $value ) ) {
			return;
		}

		//We have to update existing record. 
		$query = 'UPDATE `users` SET
				'.$term.' = "'.$value.'"
		WHERE user_id="'.$user_id.'"';

		$result = $db->query($query) or die($db->error);
	}//set user meta information.

	function get_usermeta( $recordID, $term ) { 
		global $db;

        if(empty($recordID) && empty($term)) {
            return "";
        }
        $query 	= "SELECT * from `usermeta` WHERE `user_id`='{$recordID}' AND `meta_key`='{$term}'";
		$result = $db->query( $query ) or die( $db->error );
		$row 	= $result->fetch_array();

		if(!empty($row)) {
			return $row["meta_value"];
		} else {
			return "";
		}
	}//get user email ends here.

	function set_usermeta($recordID, $term, $value) { 
		global $db;

        if(empty($recordID) && empty($term)) {
            return "";
        }

        $query 	= "SELECT * from `usermeta` WHERE `user_id`='".$recordID."' AND `meta_key`='".$term."'";
		$result = $db->query( $query ) or die( $db->error );
		$rows 	= $result->num_rows;
		
		if($rows > 0) {
			//We have to update existing record. 
			$query = "UPDATE `usermeta` SET
   	    			`meta_value` = '".$value."'
			WHERE `user_id`='".$recordID."' AND `meta_key`='".$term."'";
		} else { 
			//we have to add new record.
			$query = "INSERT into `usermeta`(`meta_id`, `user_id`, `meta_key`, `meta_value`) 
						VALUES(NULL, '".$recordID."', '".$term."', '".$value."')";
		}
		$result = $db->query($query) or die($db->error);
	}//set user meta information.
	
	function set_user_meta($user_id, $term, $value) { 
		global $db;
		$query 	= "SELECT * from user_meta WHERE user_id='".$user_id."'";
		$result = $db->query($query) or die($db->error);
		$rows 	= $result->num_rows;
		
		if($rows > 0) {
			//We have to update existing record. 
			$query = 'UPDATE user_meta SET
   	    			'.$term.' = "'.$value.'"
			WHERE user_id="'.$user_id.'"';
		} else { 
			//we have to add new record.
			$query = 'INSERT into user_meta(user_meta_id, user_id, '.$term.') VALUES(NULL, "'.$user_id.'", "'.$value.'")';
		}
		$result = $db->query($query) or die($db->error);
	}//set user meta information.
	
	function get_user_meta($user_id, $term) { 
		global $db;
		$query 	= "SELECT * from user_meta WHERE user_id='".$user_id."'";
		$result = $db->query($query) or die($db->error);
		$row 	= $result->fetch_array();
		return isset($row[$term]) ? $row[$term] : "";
	}//get user email ends here.
	
	function get_user_info($user_id, $term) { 
		global $db;
		$query = "SELECT * from users WHERE user_id='".$user_id."'";
		$result = $db->query($query) or die($db->error);
		$row = $result->fetch_array();
		return isset($row[$term]) ? $row[$term] : "";
	}//get user email ends here.
	
	function register_user( $first_name, $last_name, $user_type, $username, $email, $password ){
		global $db;
		//Check if user already exist
		$query = "SELECT * from users WHERE email='".$email."'";
		$result = $db->query($query);
		
		$num_user = $result->num_rows;
		
		if($num_user > 0) { 
			return _("Email cannot be added ").' <strong>'.$email.'</strong> '._("already exists.");
			exit();
		}
		//username validation
		$query = "SELECT * from users WHERE username='".$username."'";
		$result = $db->query($query);
		
		$num_user = $result->num_rows;
		
		if($num_user > 0) { 
			return _("Username cannot be added").' <strong>'.$username.'</strong> '._("Already exists.");
			exit();
		}
		$registration_date = date('Y-m-d');
		
		$password_hash = get_option('password_hash');
		
		if($password_hash == "argon2") {
			$options 	= ['cost' => 12];
			$password 	= password_hash($password, PASSWORD_DEFAULT, $options);
		} else {
			$password = md5($password);
		}
		
		$activation_key = substr(md5(uniqid(rand(), true)), 16, 16);
		
		if(get_option('register_verification') != '1') { 
			$status = "deactivate";
		} else { 
			$status = "activate";
		}

		$user_type = get_option('register_user_level');
		//adding user into database
		$query = "INSERT INTO users(first_name,last_name,username,email,password,activation_key,date_register,user_type,status) VALUES ('$first_name', '$last_name', '$username', '$email', '$password', '$activation_key', '$registration_date', '$user_type', '$status')";
		$result = $db->query($query) or die($db->error);
		$user_id = $db->insert_id;
		//Email to user
		$site_url = get_option('site_url');
				
		$email_message = _("Thank you for registration.")."<br />";
		$email_message .= _("Your username is").": <strong> ".$username.'</strong><br>';
		$email_message .= _("Kindly click the link below to confirm your account and start using our services.")."<br />";
		$email_message .= "<a href='".$site_url."login.php?confirmation_code=".$activation_key."&user_id=".$user_id."'>"._("Confirm Email Address")."</a>";
		$email_message .= "<br><br>"._("Thank you again. Please contact us if you need any assistance.");			
		
		$message 	= $email_message;
		$mailto 	= $email;
		$subject 	= _("Confirm your email id.");
		
		send_email($mailto, $subject, $message);
		//Notify other users of same level on new registration.
		if(get_option('notify_user_group') == '1'):
			//message object.
			$subject = _("New user registration");
			$message = "<h2>"._("New user in your user group.")."</h2>";
			$message .= "<p><strong>"._("Name").": </strong>".$first_name." ".$last_name."</p>";
			$message .= "<p><strong>"._("Email").": </strong>".$email."</p>";
			$message .= "<p><strong>"._("Username").": </strong>".$username."</p>";

			if(!isset($_SESSION["user_id"])) {
				$_SESSION["user_id_sender"] = $user_id;
			}

			$message_obj = new Messages;
			$message_obj->level_message($user_type, $subject, $message);
		endif;
		return $user_id;
	}//register_user ends here.
	
	function google_facebook_login_register($first_name, $last_name, $gender, $email, $user_type, $profile_image) {
		global $db; //starting database object.
		 
		$query = "SELECT * from users WHERE email='".$email."' OR username='".$email."'";
		$result = $db->query($query) or die($db->error);
		$num_rows = $result->num_rows;
		 
		$registration_date = date('Y-m-d');
		$pass = randomPassword();
			
		$password_hash = get_option('password_hash');
			
		if($password_hash == "argon2") {
			$options 	= ['cost' => 12];
			$password 	= password_hash($pass, PASSWORD_DEFAULT, $options);
		} else {
			$password = md5($pass);
		}	

		$status = 'activate';
  		 
		 if($user_type == 'admin') { 
			$user_type = get_option('register_user_level');
		 }
			
		 if($num_rows == 0) {
		 	$query = "INSERT INTO users(first_name,last_name,gender,username,email,profile_image,password,date_register,user_type,status) VALUES('$first_name', '$last_name', '$gender', '$email', '$email', '$profile_image', '$password', '$registration_date', '$user_type', '$status')";
			$result = $db->query($query) or die($mysqli->error);
			$user_id = $db->insert_id;
			
			$email_msg = '<h1>'._("Thank you for registration").'.</h1>';
			$email_msg .= '<p>'._("You can use facebook login or following login information to access our system.").'</p>';			
			$email_msg .= '<p>'._("Email").':'.$email.'<br>';
			$email_msg .= _("Password").':'.$pass.'</p>';
			$email_msg .= get_option('site_url');
			
			$subject = _("Thank you for registration").' | '.get_option('site_name');
			
			send_email($email, $subject, $email_msg);
			//Notification to user group on new registration.
			if(get_option('notify_user_group') == '1'):
				//message object.
				$subject = _("New user registration.");
				$message = "<h2>"._("New user in your user group.")."</h2>";
				$message .= "<p><strong>"._("Name").": </strong>".$first_name." ".$last_name."</p>";
				$message .= "<p><strong>"._("Email").": </strong>".$email."</p>";
				$message .= "<p><strong>"._("Username").": </strong>".$username."</p>";

				$message_obj = new Messages;
				$message_obj->level_message($user_type, $subject, $message);
			endif;
			$query = "SELECT * from users WHERE email='".$email."' OR username='".$email."'";
			$result = $db->query($query) or die($db->error);
			$num_rows = 1;
		 }//if user do not exist register user.
		 
		 if($num_rows > 0) { 
		 	$row = $result->fetch_array();
			
				if($row['status'] == 'deactivate') { 
					$message = _("Your account is not activated yet please confirm your Email address to activate your account!");
				} else if($row['status'] == 'activate'){
					extract($row);
					$this->user_id 		= $user_id; 
					$this->first_name 	= $first_name;
					$this->last_name 	= $last_name;
					$this->username 	= $username;
					$this->email 		= $email;
					$this->status 		= $status;
					$this->user_type 	= $user_type;
					if($profile_image != '') { 
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
		
	function login_user($email, $password) {
		global $db;
		
		if(empty($email) || empty($password)) {
			return _("Email and password both are required fields.");
		}

		$password_submission = $password;
		
		$query 	= "SELECT * FROM `users` WHERE `email`='{$email}' OR `username`='{$email}'";

	 	$select_query   = "SELECT * FROM `users` WHERE `email`=? OR `username`=?";
        $stmt           = $db->prepare($select_query);

        $stmt->bind_param('ss', $email, $email);
        $stmt->execute();

        $result = $stmt->get_result();
		 
		 if($result->num_rows > 0) { 
			$row = $result->fetch_assoc();

			$lock_account = $this->get_user_meta($row['user_id'], 'login_lock');
			 
			$lock_account = (empty($lock_account)) ? 0 : $lock_account;

			if($lock_account != 'No') { 
				if($lock_account + get_option('wrong_attempts_time') * 60 > time()) { 
					return _("You are locked cause of maximum login attempts.");
					exit();
				} else { 
					$this->set_user_meta($row['user_id'], 'login_attempt', '0');
					$this->set_user_meta($row['user_id'], 'login_lock', 'No'); //setting last login time.
				}
			}
			 
			$password_hash = get_option('password_hash');

			if($password_hash == "argon2") {
				// This is the hash of the password in example above.
				$hash = $row['password'];
				
				if(password_verify($password_submission, $hash)) {
					$status = "valid";
				} else {
					$status = "invalid";
				}	
			} else {
				$password_submission = md5($password_submission);
				
				if($row['password'] == $password_submission) {
					$status = "valid";
				} else {
					$status = "invalid";
				}
			} 
			 
			if($status == "valid") {
				if($row['status'] == 'deactivate') { 
					$message = _("Your account is not activated yet please confirm your Email address to activate your account!");
				} else if($row['status'] == 'activate'){
					extract($row);
					$this->user_id 		= $user_id; 
					$this->first_name 	= $first_name;
					$this->last_name	= $last_name;
					$this->username 	= $username;
					$this->email 		= $email;
					$this->status 		= $status;
					$this->user_type 	= $user_type;
					
					if($profile_image != '') { 
						$this->profile_image = $profile_image;
					} else { 
						$this->profile_image = 'assets/images/thumb.png';
					}
					
					$message = 1;
				} else { 
					$message = _("You cannot login your account is ban or suspend. Contact site admin.");
				}
			} else { 
				$message 		= _("Password does not match.");
				$login_attempt 	= $this->get_user_meta($row['user_id'], 'login_attempt');
				$login_attempt = ( empty( $login_attempt ) ) ? 1 : $login_attempt+1;
				
				$this->set_user_meta($row['user_id'], 'login_attempt', $login_attempt);
				
				if($login_attempt >= get_option('maximum_login_attempts')) { 
					$this->set_user_meta($row['user_id'], 'login_lock', time());	
				}
			}
			
		 } else { 
		 	$message = _("Couldn't find email.");
		 }
		 return $message;
	}//login func ends here.
	
	function delete_user($user_type, $user_id) {
		global $db;

		if($user_type == 'admin') {
			$query 		= 'DELETE from users WHERE user_id="'.$user_id.'"';
			$result 	= $db->query($query) or die($db->error);
			$message 	= _("User deleted successfuly");	
		} else { 
			$message 	= _("Cannot delete user");
		}	
		return $message;
	}//delete level ends here.

function list_users($user_type) {
		global $db;
	
		if($user_type == 'admin') { 
			$query 		= 'SELECT * from users ORDER by first_name ASC';
			$result 	= $db->query($query) or die($db->error);
			$content 	= '';
			$count 		= 0;
			
			while($row = $result->fetch_array()) { 
				extract($row);
				$referral_id = intval($row['referral_id']);
 				$referal = $db->query("SELECT username FROM users WHERE user_id = " . $referral_id . " LIMIT 1");				
 				$ref_row = $referal ? $referal->fetch_assoc() : null;

				$count++;
				if($count%2 == 0) { 
					$class = 'even';
				} else { 
					$class = 'odd';
				}
				$content .= '<tr class="'.$class.'">';
				$content .= '<td>';
				$content .= $first_name.' '.$last_name;
				$content .= '</td><td>';
				if($city != '') { 
				$content .= $city.', ';
				}
				if($state != '') { 
				$content .= $state.', ';
				}
				$content .= $country;
				$content .= '</td><td>';
				$content .= $username;
				$content .= '</td><td>';
				$content .= $email;
				$content .= '</td><td>';				
				$content .= isset($ref_row) && !empty($ref_row) ? $ref_row['username'] : 'N\A';
				$content .= '</td><td>';
				$content .= ucfirst($status);
				$content .= '</td><td>';
				$content .= ucfirst($user_type);
				$content .= '</td><td>';
				$content .= '<button class="btn btn-default btn-sm pull-left" style="margin-right:5px;" data-toggle="modal" data-target="#modal_'.$user_id.'">'._("Message").'</button>';
				$content .= '<!-- Modal -->
<script type="text/javascript">
$(function(){
$("#message_form_'.$user_id.'").on("submit", function(e){
  e.preventDefault();
  tinyMCE.triggerSave();
  $.post("lib/includes/messageprocess.php", 
	 $("#message_form_'.$user_id.'").serialize(), 
	 function(data, status, xhr){
	   $("#success_message_'.$user_id.'").html("<div class=\'alert alert-success\'>"+data+"</div>");
	 });
});
});
</script>				
<div class="modal fade" id="modal_'.$user_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="message_form_'.$user_id.'" method="post" name="send_message">
	<div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">'._("Send Message").'</h4>
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      </div>
	  
      <div class="modal-body">
      		<div id="success_message_'.$user_id.'"></div>
	   		<div class="form-group">
				<label class="control-label">'._("To").'</label>
				<input type="text" class="form-control" name="message_to" value="'._("Email").':('.$email.') '._("Username").': ('.$username.')" readonly="readonly" />
			</div>
			
			<div class="form-group">
				<label class="control-label">'._("Subject").'</label>
				<input type="text" class="form-control" name="subject" value="" />
			</div>
			
			<div class="form-group">
				<label class="control-label">'._("Message").'</label>
				<textarea class="tinyst form-control" name="message"></textarea>
			</div>
      </div>
	  <input type="hidden" name="from" value="'.$_SESSION['user_id'].'" />
	  <input type="hidden" name="user_id" value="'.$user_id.'" />
	  <input type="hidden" name="single_form" value="1" />
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">'._("Close").'</button>
		<input type="submit" value="'._("Send Message").'" class="btn btn-primary" />
      </div>
    </div><!-- /.modal-content -->
   </form>
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->';			
				$content .= '<form method="post" name="edit" action="manage_users.php">';
				$content .= '<input type="hidden" name="edit_user" value="'.$user_id.'">';
				$content .= '<input type="submit" style="margin-right:5px;" class="btn btn-default btn-sm pull-left" value="'._("Edit").'">';
				$content .= '</form>';
				$content .= '<form method="post" name="delete" onsubmit="return confirm_delete();" action="">';
				$content .= '<input type="hidden" name="delete_user" value="'.$user_id.'">';
				$content .= '<input type="submit" class="btn btn-default btn-sm pull-left" value="'._("Delete").'">';
				$content .= '</form>';
				$content .= '</td>';
				$content .= '</tr>';
				unset($class);
			}//loop ends here.
		} else { 
			$content = _("You cannot view list of users.");
		}	
		echo $content;
	}//list_levels ends here.

	function get_total_users($condition) { 
		global $db;

		if($_SESSION['user_type'] == 'admin') {
			if($condition == 'all') { 
				$query = "SELECT * from users";
			} else { 
				$query = "SELECT * from users WHERE status='".$condition."'";
			}
			$result 	= $db->query($query) or die($db->error);
			$num_rows 	= $result->num_rows;
			echo $num_rows;
		} else { 
			echo _("You are not allowed to view this list.");
		}
	}//prints total registered users.

function edit_profile($user_id, $first_name, $last_name, $gender, $date_of_birth, $address1, $address2, $city, $state, $country, $zip_code, $mobile, $phone, $username, $email, $password, $profile_image, $description) {
		global $db;
		
		$current_email		= $this->get_user_info($_SESSION['user_id'], 'email');
		$current_username 	= $this->get_user_info($_SESSION['user_id'], 'username');
		
		$address1 			= $db->real_escape_string($address1);
		$address2 			= $db->real_escape_string($address2);
		$description 		= $db->real_escape_string($description);
	
		if($email != $current_email) {
		$query = "SELECT * from users WHERE email='".$email."'";
			$result = $db->query($query);
			
			$num_user = $result->num_rows;
			
			if($num_user > 0) { 
				return _("Email cannot be added").' <strong>'.$email.'</strong> '._("Already exists.");
				exit();
			}
		}
		
		if($current_username != $username) {
			//username validation
			$query = "SELECT * from users WHERE username='".$username."'";
			$result = $db->query($query);
			
			$num_user = $result->num_rows;
			
			if($num_user > 0) { 
				return _("Username couldn't be added").' <strong>'.$username.'</strong> '._("Already exists");
				exit();
			}
		}
		$date_of_birth = (!empty($date_of_birth)) ? date('Y-m-d', strtotime(str_replace('-', '/', $date_of_birth))) : "000-00-00";
		if($password == '') {
			$query = 'UPDATE users SET
   	    			first_name = "'.$first_name.'",
					last_name = "'.$last_name.'",
					gender = "'.$gender.'",
					date_of_birth = "'.$date_of_birth.'",
					address1 = "'.$address1.'",
					address2 = "'.$address2.'",
					city = "'.$city.'",
					state = "'.$state.'",
					country = "'.$country.'",
					zip_code = "'.$zip_code.'",
					mobile = "'.$mobile.'",
					phone = "'.$phone.'",
					username = "'.$username.'",
					email = "'.$email.'",
					profile_image = "'.$profile_image.'",
					description = "'.$description.'"
			WHERE user_id="'.$user_id.'"';
			} else { 
			
			$password_hash = get_option('password_hash');

			if($password_hash == "argon2") {
				$options 	= ['cost' => 12];
				$password 	= password_hash($password, PASSWORD_DEFAULT, $options);
			} else {
				$password = md5($password);
			}
			
			$query = 'UPDATE users SET
   	    			first_name = "'.$first_name.'",
					last_name = "'.$last_name.'",
					gender = "'.$gender.'",
					date_of_birth = "'.$date_of_birth.'",
					address1 = "'.$address1.'",
					address2 = "'.$address2.'",
					city = "'.$city.'",
					state = "'.$state.'",
					country = "'.$country.'",
					zip_code = "'.$zip_code.'",
					mobile = "'.$mobile.'",
					phone = "'.$phone.'",
					username = "'.$username.'",
					email = "'.$email.'",
					password = "'.$password.'",
					profile_image = "'.$profile_image.'",
					description = "'.$description.'"
			WHERE user_id="'.$user_id.'"';
			}
			$result = $db->query($query) or die($db->error);
			return _("User updated successfuly.");
	}//update user ends here.

	function set_user($user_id, $user_type, $login_user) {
		 global $db;
		
		 if($user_type == 'admin') { 
			$query = 'SELECT * from users WHERE user_id="'.$user_id.'"'; 
		 } else if($user_id == $login_user) { 
		 	$query = 'SELECT * from users WHERE user_id="'.$user_id.'"';
		 } else { 
		 	echo _("You are trying to do something which you are not allowed to do.");
		 }
		$result = $db->query($query) or die($db->error);
		$row = $result->fetch_array();
		//results ends here.
		$this->user_id       = $row['user_id'];
		$this->first_name    = $row['first_name'];
		$this->last_name     = $row['last_name'];
		$this->gender        = $row['gender'];
		$this->date_of_birth = $row['date_of_birth'];
		$this->address1      = $row['address1'];
		$this->address2      = $row['address2'];
		$this->city          = $row['city'];
		$this->state         = $row['state'];
		$this->country       = $row['country'];
		$this->zip_code      = $row['zip_code'];
		$this->mobile        = $row['mobile'];
		$this->phone         = $row['phone'];
		$this->username      = $row['username'];
		$this->email         = $row['email'];
		$this->profile_image = $row['profile_image'];
		$this->description   = $row['description'];
		$this->status        = $row['status'];
		$this->user_type     = $row['user_type'];
		$this->referral_id   = $row['referral_id'];
	}//level set ends here.

	function update_user($user_id, $user_type_ses, $first_name, $last_name, $gender, $date_of_birth, $address1, $address2, $city, $state, $country, $zip_code, $mobile, $phone, $username, $email, $password, $profile_image, $description, $status, $user_type,$referral_id) {
		global $db;

		$current_email 		= $this->get_user_info($user_id, 'email');
		$current_username 	= $this->get_user_info($user_id, 'username');
		
		$address1 			= $db->real_escape_string($address1);
		$address2 			= $db->real_escape_string($address2);
		$description 		= $db->real_escape_string($description);
		
		if($email != $current_email) {
		$query = "SELECT * from users WHERE email='".$email."'";
			$result = $db->query($query);
			
			$num_user = $result->num_rows;
			
			if($num_user > 0) { 
				return _("This email cannot be used").' <strong>'.$email.'</strong> '._("already exists in our system.");
				exit();
			}
		}
			
		if($current_username != $username) {
			//username validation
			$query = "SELECT * from users WHERE username='".$username."'";
			$result = $db->query($query);

			$num_user = $result->num_rows;

			if($num_user > 0) { 
				return _("Username couldn't be added").' <strong>'.$username.'</strong> '._("username already exists.");
				exit();
			}
		}

		$date_of_birth = (!empty($date_of_birth)) ? date('Y-m-d', strtotime(str_replace('-', '/', $date_of_birth))) : "000-00-00";
		//updating user info.
		if($user_type_ses == 'admin') { 
			if($password == '') {

			$query = 'UPDATE users SET
   	    			first_name = "'.$first_name.'",
					last_name = "'.$last_name.'",
					gender = "'.$gender.'",
					date_of_birth = "'.$date_of_birth.'",
					address1 = "'.$address1.'",
					address2 = "'.$address2.'",
					city = "'.$city.'",
					state = "'.$state.'",
					country = "'.$country.'",
					zip_code = "'.$zip_code.'",
					mobile = "'.$mobile.'",
					phone = "'.$phone.'",
					username = "'.$username.'",
					email = "'.$email.'",
					profile_image = "'.$profile_image.'",
					description = "'.$description.'",
					status = "'.$status.'",
					user_type = "'.$user_type.'",
					referral_id = "'.$referral_id ?? null.'"
			WHERE user_id="'.$user_id.'"';
			} else { 
			
			$password_hash = get_option('password_hash');

			if($password_hash == "argon2") {
				$options 	= ['cost' => 12];
				$password 	= password_hash($password, PASSWORD_DEFAULT, $options);
			} else {
				$password = md5($password);
			}
				
			$query = 'UPDATE users SET
   	    			first_name = "'.$first_name.'",
					last_name = "'.$last_name.'",
					gender = "'.$gender.'",
					date_of_birth = "'.$date_of_birth.'",
					address1 = "'.$address1.'",
					address2 = "'.$address2.'",
					city = "'.$city.'",
					state = "'.$state.'",
					country = "'.$country.'",
					zip_code = "'.$zip_code.'",
					mobile = "'.$mobile.'",
					phone = "'.$phone.'",
					username = "'.$username.'",
					email = "'.$email.'",
					password = "'.$password.'",
					profile_image = "'.$profile_image.'",
					description = "'.$description.'",
					status = "'.$status.'",
					user_type = "'.$user_type.'",
					referral_id = "'.$referral_id ?? null.'"
			WHERE user_id="'.$user_id.'"';
			}
			$result = $db->query($query) or die($db->error);
			return _("User updated successful.");
		} else { 
			return _("You are trying to do something you are not allowed for.");
		}
	}//update user ends here.
	
	function reset_pass_user($user_id,$confirmation_code,$new_pass){
		global $db;
		
		$query 		= "SELECT * from users WHERE user_id='".$user_id."'";
		$result 	= $db->query($query) or die($db->error);
		$row 		= $result->fetch_array();
		
		$password_hash = get_option('password_hash');

		if($password_hash == "argon2") {
			$options 	= ['cost' => 12];
			$new_pass 	= password_hash($new_pass, PASSWORD_DEFAULT, $options);
		} else {
			$new_pass = md5($new_pass);
		}
		
		if($confirmation_code == $row['activation_key']){
				$query 		= 'UPDATE users SET password="'.$new_pass.'",activation_key="" WHERE user_id="'.$user_id.'"';
				$row 		= $db->query($query) or die($db->error);
				$message 	= _("Your Password has been reset please use new password to login.");
			} else { 
				$message = _("Your activation key is expired and password cannot be reset.");
			}
			return $message;
		}//reset password function ends here.	

function match_confirm_code($confirmation_code,$user_id){
		global $db;
	
		//Getting Confirmation Code from database.
		$query 		= "SELECT * from users WHERE user_id='".$user_id."'";
		$result 	= $db->query($query) or die($db->error);
		$row 		= $result->fetch_array();
		
		if($row['activation_key'] == $confirmation_code){
			if($row['status'] == 'suspend'||$row['status'] == 'ban'){
				$message= _("Your account has been suspended. Please contact the administrator for help.");
			} else {
				$status = 'activate';
				$query = 'UPDATE users SET status="'.$status.'",activation_key="" WHERE user_id="'.$user_id.'"';
				$row = $db->query($query) or die($db->error);
				$message = _("Congratulations! You are activated successfully now you can use email and password to login and use our services.");
			} 
		} else {
			  $message = _("Your account cannot be activated.");
		}
		return $message;
}//function  close

	function forgot_user($email){
		global $db;
		 $query = "SELECT * from users WHERE email='".$email."' OR username='".$email."'";
		 $result = $db->query($query) or die($db->error);
		 $num_rows = $result->num_rows;

			 if($num_rows > 0) { 
				$row = $result->fetch_array();
				$user_id =$row['user_id'];
				$email = $row['email'];
			 } else {
				return _("Email is not in our system.");
				exit();
			}
		$activation_key = substr(md5(uniqid(rand(), true)), 16, 16);
		$query = 'UPDATE users SET activation_key="'.$activation_key.'" WHERE user_id="'.$user_id.'"';
		$result = $db->query($query) or die($db->error);

		$site_url = get_option('site_url');
		$email_message = _("Reset your password.")."<br />";
		$email_message .= _("Kindly click the link below to reset your password.")."<br />";
		$email_message .= "<a href='".$site_url."forgot.php?confirmation_code=".$activation_key."&user_id=".$user_id."'>"._("Confirm Email Address")."</a>";
		$email_message .= "<br><br>"._("Thank you again. Please contact us if you need any assistance.");			
		$message = $email_message;
		$mailto = $email;
		$subject = _("Password reset instructions");

		send_email($mailto, $subject, $message);

		return _("Password recovery email sent please check mail for details.");
	}//forgot password function endsh ere.

	function add_user($first_name, $last_name, $gender, $date_of_birth, $address1, $address2, $city, $state, $country, $zip_code, $mobile, $phone, $username, $email, $password, $profile_image, $description, $status, $user_type,$referral_id) { 
			global $db;
		
			$address1 		= $db->real_escape_string($address1);
			$address2 		= $db->real_escape_string($address2);
			$description 	= $db->real_escape_string($description);
		
			//Check if user already exist
			$query = "SELECT * from users WHERE email='".$email."'";
			$result = $db->query($query) or die($db->error);
			
			$num_user = $result->num_rows;
			if($num_user > 0) { 
				return _("Email cannot be added").' <strong>'.$email.'</strong> '._("Already exists.");
				exit();
			}
			//username validation
			$query = "SELECT * from users WHERE username='".$username."'";
			$result = $db->query($query);
			
			$num_user = $result->num_rows;
			
			if($num_user > 0) { 
				return _("Username couldn't add").' <strong>'.$username.'</strong> '._("User already exists");
				exit();
			}
			$registration_date = date('Y-m-d');
			
			$password_hash = get_option('password_hash');

			if($password_hash == "argon2") {
				$options 		= ['cost' => 12];
				$password_con 	= password_hash($password, PASSWORD_DEFAULT, $options);
			} else {
				$password_con = md5($password);
			}
						
			$date_of_birth = ( ! empty( $date_of_birth ) ) ? date( "Y-m-d H:i:s", strtotime( $date_of_birth ) ) : '2000-01-01 00:00:00';
			$all_users                = "SELECT * FROM users WHERE user_type LIKE '%subscriber%'";
			$result                   = $db->query($all_users);
			$count                    = $result->num_rows;
			$auto_generated_user_name = 'BIZ';

			if(strlen($count) == 1){
				$auto_generated_user_name .= '000' . ($count + 1);
			}elseif (strlen($count) == 2) {
				$auto_generated_user_name .= '00' . ($count + 1);
			}elseif (strlen($count) == 3) {
				$auto_generated_user_name .= '0' . ($count + 1);
			}elseif (strlen($count) == 4) {
				$auto_generated_user_name .= ($count + 1);
			}
			
			// $auto_generated_username = 'BIZ' . count();
			//Running Query to add user.
			$query = "INSERT into users VALUES(NULL, '".$first_name."', '".$last_name."', '".$gender."', '".$date_of_birth."', '".$address1."', '".$address2."', '".$city."', '".$state."', '".$country."', '".$zip_code."', '".$mobile."', '".$phone."', '".$auto_generated_user_name."', '".$email."', '".$password_con."', '".$profile_image."', '".$description."', '".$status."', '', '".date('Y-m-d')."', '".$user_type."','".$referral_id."')";
			$result = $db->query($query) or die($db->error);
			$user_id = $db->insert_id;
			
			//Email to user
			$site_url = get_option('site_url');
					
			$email_message = _("Your account have been registered.")."<br />";
			$email_message .= _("Please use the following details to sign in on our website.");
			$email_message .= "<br><a href='".$site_url."'>"._("Confirm Email Address.")."</a><br>";
			$email_message .= _("Email OR Username")." <strong>".$email."</strong><br>";
			$email_message .= _("Password").": <strong>".$password."</strong>";			
			
			$message = $email_message;
			$mailto = $email;
			$subject = _("Registration Details");
			
			send_email($mailto, $subject, $message);

			//Notify other users of same level on new registration.
			if(get_option('notify_user_group') == '1'):
				//message object.
				$subject = _("New user registration.");
				$message = "<h2>"._("New user on your user group.")."</h2>";
				$message .= "<p><strong>"._("Name").": </strong>".$first_name." ".$last_name."</p>";
				$message .= "<p><strong>"._("Email").": </strong>".$email."</p>";
				$message .= "<p><strong>"._("Username").": </strong>".$username."</p>";

				$message_obj = new Messages;
				$message_obj->level_message($user_type, $subject, $message);
			endif;
		return array(
			'message' => _("User added details are sent on email").' '.$email,
			'user_id' => $user_id
		);
	}//add user function ends here.
	
	function wc_last_logins() {
		global $db;
		
		$query 	= "SELECT * FROM `user_meta` ORDER BY `last_login_time` DESC LIMIT 5";
		$result = $db->query($query) or die($db->error);
		
		$output = '<table class="table table-hover mb-0"><thead>';
		$output .= '<tr><th>'._("Login Time").'</th>';
		$output .= '<th>'._("IP").'</th>';
		$output .= '<th>'._("Name").'</th>';
		$output .= '<th>'._("Username").'</th>';
		$output .= '<th>'._("User Type").'</th></tr>';
		$output .= '</thead><tbody>';
		
		while($row = $result->fetch_array()) { 
			$login_ip = $row["last_login_ip"];
			$login_time = $row["last_login_time"];
			
			$user_id = $row["user_id"];
			
			$first_name = self::get_user_info($user_id, "first_name");
			$last_name 	= self::get_user_info($user_id, "last_name");
			$username 	= self::get_user_info($user_id, "username");
			$user_type 	= self::get_user_info($user_id, "user_type");
			
			$output .= '<tr><td>'.time_elapsed_string($login_time).'</td>';
			$output .= '<td>'.$login_ip.'</td>';
			$output .= '<td>'.$first_name.' '.$last_name.'</td>';
			$output .= '<td>'.$username.'</td>';
			$output .= '<td>'.$user_type.'</td></tr>';
		}
		
		$output .= '</tbody></table>';
		
		echo $output;
	}
}//class ends here.