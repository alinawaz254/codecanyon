<?php
	/*This file have all functions to handle options.
	1) Set Option
	2) Get Option
	3) Install Admin
	4) Authentication
	*/

	function wc_get_table_field_value($table_name, $term_name, $term_value, $return_value) {
		global $db;
		
		$query = "SELECT * FROM `".$table_name."` WHERE `".$term_name."` ='".$term_value."'";
		$result = $db->query($query) or die($db->error);
		
		$row = $result->fetch_array();
		
		return $row[$return_value];
	}
	
	function set_option($option_name, $option_value) {
		global $db;
		
		$query 		= "SELECT * from options WHERE option_name='".$option_name."'";
		$result 	= $db->query($query) or die($db->error);
		$num_rows 	= $result->num_rows;

		if($num_rows > 0) { 
			$query = "DELETE from options WHERE option_name='".$option_name."'";
			$result = $db->query($query) or die($db->error);
		}//This will delete record
		$query = "INSERT into options VALUES(NULL, '".$option_name."', '".$option_value."')";
		$result = $db->query($query) or die($db->error);
		//this function do not return anything!
	}//set option function ends here.
	
	function get_option($option_name) { 
		global $db;
		$query = "SELECT * from options WHERE option_name='".$option_name."'";
		$result = $db->query($query) or die($db->error);
		$row = $result->fetch_array();
		$count = $result->num_rows;
		
		if($count > 0) {
			$option_value = stripslashes($row['option_value']);//this will remove database slashes from values
		} else {
			$option_value = FALSE;
		}
		return $option_value; //This function returns option value.
	}//get option value function ends here.

	function return_base_url() {
		if(isset($_SERVER['HTTPS'])){
			$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
		}
		else{
			$protocol = 'http';
		}
		return $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	}

	function install_admin($first_name, $last_name, $username, $email, $password) {
		global $db;

		$password_hash = get_option('password_hash');

		if($password_hash == "argon2") {
			$options 	= ['cost' => 12];
			$password 	= password_hash($password, PASSWORD_DEFAULT, $options);
		} else {
			$password = md5($password);
		}

		//check if admin already exist.
		$query = "SELECT * from users WHERE user_type='admin'";
		$result = $db->query($query) or die($db->error);
		$num_rows = $result->num_rows;

		if($num_rows>0) { 
			echo _e("Admin already exists");
		} else { 
			//adding admin
			$query = "INSERT into users (user_id, first_name, last_name, username, email, password, status, date_register, user_type)
					VALUES(NULL, '".$first_name."', '".$last_name."', '".$username."', '".$email."', '".$password."', 'activate', '".date('Y-m-d')."', 'admin')";
			$result = $db->query($query) or die($db->error);
		}

		//adding deafult user level subscriber.
		$query = "SELECT * from user_level WHERE level_name='subscriber'";
		$result = $db->query($query) or die($db->error);
		$num_rows = $result->num_rows;

		if($num_rows > 0) { 
			//do nothing already subscriber level
		} else { 
			$query = "INSERT into user_level VALUES(NULL, 'subscriber', 'Default user level given access to subscriber.php', 'subscriber.php')";
			$result = $db->query($query) or die($db->error);
		}

	}//Function checkes if admin does not exist this will create admin.
	
	function redirect_user($user_type) { 
		global $db;

		$site_url = get_option("site_url");

		if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') { 
				HEADER('LOCATION: '.$site_url.'dashboard.php');
			} else {
				$query = "SELECT * from user_level WHERE level_name='".$_SESSION['user_type']."'";
					$result = $db->query($query) or die($db->error);
					$num_rows = $result->num_rows;
					if($num_rows > 0) { 
						$row = $result->fetch_array();
						$page = $row['level_page'];
						HEADER('LOCATION:'.$page);
					} else { 
						//If you are not admin and not given access user. You will be redirected to index.php
						HEADER('LOCATION: '.$site_url.'index.php');
					}
			}
	}	
	function authenticate_user($access_level) { 
		global $db;
		
		$site_url = get_option("site_url");
		
		if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != '') {
			//check user level
			if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin') { 
				//admin can access all pages.
			} else if($access_level == 'all') { 
				//all user types can access here but only when signed in.
			} else { 
				if(isset($_SESSION['user_type']) && $_SESSION['user_type'] == $access_level) { 
					//You can access this page now.
				} else { 
					$query = "SELECT * from user_level WHERE level_name='".$_SESSION['user_type']."'";
					$result = $db->query($query) or die($db->error);
					$num_rows = $result->num_rows;
					
					if($num_rows > 0) { 
						$row = $result->fetch_array();
						$page = $row['level_page'];
						HEADER('LOCATION:'.$page);
					} else { 
						//If you are not admin and not given access user. You will be redirected to index.php
						HEADER('LOCATION: '.$site_url.'index.php');
					}
					
				} //if user level is accessable.
			}
		} else { 
			HEADER('LOCATION: '.$site_url.'index.php');
		}//this is loged in user.
	}//authenticate user ends here.
	
	function partial_access($access_type) { 
		if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != '') {
			if($access_type == 'admin' && $_SESSION['user_type'] == 'admin') { 
				return TRUE;
			} else if($access_type == 'all') { 
				return TRUE;
			} else if($access_type == $_SESSION['user_type']) { 
				return TRUE;
			} else { 
				return FALSE;
			}
		} else { 
			return FALSE;
		}
	}//partial access function ends here.
	
	function randomPassword() {
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$pass = array(); 
		$alphaLength = strlen($alphabet) - 1; 
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode( $pass );
	}
	
	function send_email($mailto, $subject, $message) { 
		if ( get_option('smtp_activation') == '1' ) {
			send_mail_smtp( $mailto, $subject, $message );
		} else {
			//getting set email addresses from database.
			$from_email = get_option('email_from');
			$reply_to = get_option('email_to');
	
            $mailheaders = "From: BizProMax";
            $mailheaders .="Reply-To:".$reply_to;
			$from = $from_email;
			$filename = '';
			$fileatt_type = '';
			
			$headers = "FROM: ".$from;
			$semi_rand = md5(time());
			$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
			
			$headers .= "\nMIME-Version: 1.0\n" .
			"Content-Type: multipart/mixed;\n" .
			" boundary=\"{$mime_boundary}\"";
	
			$message .= "This is a multi-part message in MIME format.\n\n" .
			"--{$mime_boundary}\n" .
			"Content-Type:text/html; charset=\"iso-8859-1\"\n" .
			"Content-Transfer-Encoding: 7bit\n\n" .
			$message . "\n\n";
			$message .= "--{$mime_boundary}\n" .
			"Content-Type: {$fileatt_type};\n" .
			" name=\"{$filename}\"\n" .
			"Content-Transfer-Encoding: base64\n\n" .
			mail($mailto, $subject, $message, $headers);
		}
	}

	function send_mail_smtp( $mailto, $subject, $message ) {
		global $mail;

		$error = '';
		$mail->isSMTP();
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = "tls";
		
		//$mail->SMTPDebug = 2;
		
		if ( empty( get_option( 'smtp_host' ) ) ) {
			$error = _( 'Missing SMTP Host' );
		}
		$mail->Host = get_option( 'smtp_host' );

		if ( empty( get_option( 'smtp_port' ) ) ) {
			$error = _( 'Missing SMTP Port' );
		}
		$mail->Port = get_option( 'smtp_port' );

		if ( empty( get_option( 'smtp_username' ) ) ) {
			$error = _( 'Missing SMTP Username' );
		}
		$mail->Username = get_option( 'smtp_username' );

		if ( empty( get_option( 'smtp_password' ) ) ) {
			$error = _( 'Missing SMTP Password' );
		}
		$mail->Password = get_option( 'smtp_password' );

		if ( empty( get_option('email_from') ) ) {
			$error = _( 'Missing email from' );
		}
		$mail->setFrom(get_option('email_from'), '');

		if ( ! empty( get_option('email_to') ) ) {
			$mail->addReplyTo(get_option('email_to'), '');
		}

		if ( empty( $mailto ) ) {
			$error = _( 'Mailto is missing' );
		}
		$mail->addAddress( $mailto, '' );
		
		if ( empty( $subject ) ) {
			$error = _( 'Mailto is missing' );
		}
		$mail->Subject = $subject;
		
		$mail->isHTML(true);
		$mail->Body = $message;

		if ( empty( $error ) ) {
			if (!$mail->send()) {
				set_option( 'last_smtp_response', 'Mailer Error: ' . $mail->ErrorInfo );
			} else {
				set_option( 'last_smtp_response', 'Message Sent!' );
			}
		} else {
			set_option( 'last_smtp_response', $error );
		}
	}
	
	function get_client_ip() {
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}
	
if(!function_exists("time_elapsed_string")):
    function time_elapsed_string($datetime, $full = false) {
        // Fix for null datetime
        if ($datetime === null) {
            return _('just now');
        }
        
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        // Fix for dynamic property 'w' - use array instead
        $weeks = floor($diff->d / 7);
        $days = $diff->d - ($weeks * 7);

        $string = array(
            'y' => _('year'),
            'm' => _('month'),
            'w' => _('week'),
            'd' => _('day'),
            'h' => _('hour'),
            'i' => _('minute'),
            's' => _('second'),
        );
        
        foreach ($string as $k => &$v) {
            // Handle weeks separately
            if ($k === 'w') {
                if ($weeks) {
                    $v = $weeks . ' ' . $v . ($weeks > 1 ? 's' : '');
                } else {
                    unset($string[$k]);
                }
            } 
            // Handle days separately (using our calculated days)
            elseif ($k === 'd') {
                if ($days) {
                    $v = $days . ' ' . $v . ($days > 1 ? 's' : '');
                } else {
                    unset($string[$k]);
                }
            }
            // Handle all other time units normally
            else {
                if ($diff->$k) {
                    $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                } else {
                    unset($string[$k]);
                }
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }
        
        return $string ? implode(', ', $string) . _(' ago') : _('just now');
    }
endif;

if(!function_exists("_e")):
    function _e($text) {
        echo _($text);
    }
endif;


	/**
	 * Returns Flags to change.
	 *
	 * Default array accepted.
	 */
	if(!function_exists('wc_language_output')):
		function wc_language_output() {
			$languages_array = array(
					array(
						"language_english", "english.png"
					),
					array(
						"language_spanish", "spanish.png"
					),
					array(	
						"language_dutch", "dutch.png"
					),
					array(	
						"language_french", "french.png"
					),
					array(	
						"language_german", "german.png"
					),
					array(	
						"language_italian", "italian.png"
					),
					array(	
						"language_thai", "thai.png"
					)
			);
			
			$list_output = "";
			
			foreach($languages_array as $language) {
				$selected_language = get_option($language[0]);
				
				if($selected_language == 1) {
					$language_code 	= $language[0];
					$image_name		= $language[1];
					
					$list_output .= "<li><a href=?language=".$language_code."><img src='assets/images/flags/".$image_name."'></a></li>";
				}
			}
			
			if(!empty($list_output)):
				$output = '<div class="languages-flags"><ul>';
				$output .= $list_output;
				$output .= '</ul></div>';
			
				echo $output;
			endif;
		}
	endif;

	function wc_upload_image_return_url($image_submit, $directory) {
		if(empty($image_submit) && $image_submit['error'] != 0) {
			return _("Nothing uploaded");
		}
		if(empty($directory)) {
			$directory = $directory;
		} else {
			$directory = $directory."/";
		}
		$directory_name = $directory;
		
		$directory = ROOT_DIR."/assets/upload/".$directory;

		if (!file_exists($directory)) {
			mkdir($directory, 0755, true);
		}
		
		$fileType = $image_submit["type"];
		$fileSize = $image_submit["size"];

		if($fileSize/1024 > "307200") {
			//Its good idea to restrict large files to be uploaded.
			$message 	=  "Filesize is not correct it should equal to 300 MB or less than 300 MB.";
			$error 		= 1;
			return array(
				"message"	=> $message,
				"error"		=> $error
			);
			exit();
		} //FileSize Checking

		if(
			$fileType != "image/png" &&
			$fileType != "image/gif" &&
			$fileType != "image/jpg" &&
			$fileType != "image/jpeg" &&
			$fileType != "image/svg+xml" &&
			$fileType != "application/vnd.openxmlformats-officedocument.wordprocessingml.document" &&
			$fileType != "application/zip" &&
			$fileType != "application/x-zip-compressed" && 
			$fileType != "application/pdf"
		) {
			//Client's input
			//doc, docx, xlsx, xls, ppt, pptx, pdf, ai, xd, psd, ttf, otf, mp3, mp4, mov
			//300 MB
			$message 	= "Sorry this file type is not supported we accept only JPG, JPEG, PNG, GIF, SVG formats. Found ".$fileType;
			$error 		= 1;
			return array(
				"message"	=> $message,
				"error"		=> $error
			);
			exit();
		 } //file type checking ends here.
		
		$filename 	= date("Y_m_d_H_i_s").clean($image_submit["name"]);
		$upFile 	= $directory.$filename;
		
		if(is_uploaded_file($image_submit["tmp_name"])) {
			if(!move_uploaded_file($image_submit["tmp_name"], $upFile)) {
				$message = "Problem could not move file to destination.";
				$error 		= 1;
				return array(
					"message"	=> $message,
					"error"		=> $error
				);
				exit;
			} else {
				$return_file = "assets/upload/".$directory_name.$filename;
			}
		} else {
			$message = "Problem: Possible file upload attack. Filename: ".$image_submit['name'];
			$error 		= 1;
			$return = array(
				"message"	=> $message,
				"error"		=> $error
			);
			exit;
		}
		return $return_file;
	}
	
	function clean($string) {
		$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
	 	return $string;
	}

	function generate_salt() {
		$characters 		= '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength 	= strlen($characters);
		$randomString 		= '';

		for ($i = 0; $i < 32; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		
		set_option("system_salt", $randomString);
	}
	function clean_input($data) {
    	$data = htmlspecialchars($data);
    	$data = stripslashes($data);
    	$data = trim($data);
    	return $data;
	}

	function return_info_messages($message) {
		$output_message = '';
		if(!empty($message)) { 
			$output_message = $message;
		} else if(isset($_GET['message']) && $_GET['message'] != '') { 
			$output_message = $_GET['message'];
		}

		if(!empty($output_message)) {
			echo '<div class="mywidget">
					<div class="row">
						<div class="col-sm-12">
							<div class="alert alert-warning alert-dismissible fade show" role="alert">';
			echo $output_message;
			echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>';
			echo '</div></div></div></div>';
		}
	}

	function return_announcements() {
		global $ann_obj;

		//announcement box starts here.
		if(isset($_POST['active_notification'])) { 
			$_SESSION['active_notification'] = $_POST['active_notification'];
		}
		if(isset($_SESSION['active_notification']) && $_SESSION['active_notification'] == 'No'):
		//when notification is not active.
		else:
			if(isset($_SESSION['user_type'])) {
				$ann_obj = new Announcements;
				$ann_obj->get_latest_announcement();
			}
		endif;//announcement box ends here.
	}

	function countries_dropdown($selected) {

		$countries_arr = 
		array(
		""	=> _("Select Country"),
		"AF" => _("Afghanistan"),
		"AL" => _("Albania"),
		"DZ" => _("Algeria"),
		"AS" => _("American Samoa"),
		"AD" => _("Andorra"),
		"AO" => _("Angola"),
		"AI" => _("Anguilla"),
		"AQ" => _("Antarctica"),
		"AG" => _("Antigua and Barbuda"),
		"AR" => _("Argentina"),
		"AM" => _("Armenia"),
		"AW" => _("Aruba"),
		"AU" => _("Australia"),
		"AT" => _("Austria"),
		"AZ" => _("Azerbaijan"),
		"BS" => _("Bahamas"),
		"BH" => _("Bahrain"),
		"BD" => _("Bangladesh"),
		"BB" => _("Barbados"),
		"BY" => _("Belarus"),
		"BE" => _("Belgium"),
		"BZ" => _("Belize"),
		"BJ" => _("Benin"),
		"BM" => _("Bermuda"),
		"BT" => _("Bhutan"),
		"BO" => _("Bolivia"),
		"BA" => _("Bosnia and Herzegovina"),
		"BW" => _("Botswana"),
		"BV" => _("Bouvet Island"),
		"BR" => _("Brazil"),
		"IO" => _("British Indian Ocean Territory"),
		"BN" => _("Brunei Darussalam"),
		"BG" => _("Bulgaria"),
		"BF" => _("Burkina Faso"),
		"BI" => _("Burundi"),
		"KH" => _("Cambodia"),
		"CM" => _("Cameroon"),
		"CA" => _("Canada"),
		"CV" => _("Cape Verde"),
		"KY" => _("Cayman Islands"),
		"CF" => _("Central African Republic"),
		"TD" => _("Chad"),
		"CL" => _("Chile"),
		"CN" => _("China"),
		"CX" => _("Christmas Island"),
		"CC" => _("Cocos (Keeling) Islands"),
		"CO" => _("Colombia"),
		"KM" => _("Comoros"),
		"CG" => _("Congo"),
		"CD" => _("Congo, the Democratic Republic of the"),
		"CK" => _("Cook Islands"),
		"CR" => _("Costa Rica"),
		"CI" => _("Cote D'Ivoire"),
		"HR" => _("Croatia"),
		"CU" => _("Cuba"),
		"CY" => _("Cyprus"),
		"CZ" => _("Czech Republic"),
		"DK" => _("Denmark"),
		"DJ" => _("Djibouti"),
		"DM" => _("Dominica"),
		"DO" => _("Dominican Republic"),
		"EC" => _("Ecuador"),
		"EG" => _("Egypt"),
		"SV" => _("El Salvador"),
		"GQ" => _("Equatorial Guinea"),
		"ER" => _("Eritrea"),
		"EE" => _("Estonia"),
		"ET" => _("Ethiopia"),
		"FK" => _("Falkland Islands (Malvinas)"),
		"FO" => _("Faroe Islands"),
		"FJ" => _("Fiji"),
		"FI" => _("Finland"),
		"FR" => _("France"),
		"GF" => _("French Guiana"),
		"PF" => _("French Polynesia"),
		"TF" => _("French Southern Territories"),
		"GA" => _("Gabon"),
		"GM" => _("Gambia"),
		"GE" => _("Georgia"),
		"DE" => _("Germany"),
		"GH" => _("Ghana"),
		"GI" => _("Gibraltar"),
		"GR" => _("Greece"),
		"GL" => _("Greenland"),
		"GD" => _("Grenada"),
		"GP" => _("Guadeloupe"),
		"GU" => _("Guam"),
		"GT" => _("Guatemala"),
		"GN" => _("Guinea"),
		"GW" => _("Guinea-Bissau"),
		"GY" => _("Guyana"),
		"HT" => _("Haiti"),
		"HM" => _("Heard Island and Mcdonald Islands"),
		"VA" => _("Holy See (Vatican City State)"),
		"HN" => _("Honduras"),
		"HK" => _("Hong Kong"),
		"HU" => _("Hungary"),
		"IS" => _("Iceland"),
		"IN" => _("India"),
		"ID" => _("Indonesia"),
		"IR" => _("Iran, Islamic Republic"),
		"IQ" => _("Iraq"),
		"IE" => _("Ireland"),
		"IL" => _("Israel"),
		"IT" => _("Italy"),
		"JM" => _("Jamaica"),
		"JP" => _("Japan"),
		"JO" => _("Jordan"),
		"KZ" => _("Kazakhstan"),
		"KE" => _("Kenya"),
		"KI" => _("Kiribati"),
		"KP" => _("Korea, Democratic People's Republic"),
		"KR" => _("Korea, Republic of"),
		"KW" => _("Kuwait"),
		"KG" => _("Kyrgyzstan"),
		"LA" => _("Lao People's Democratic Republic"),
		"LV" => _("Latvia"),
		"LB" => _("Lebanon"),
		"LS" => _("Lesotho"),
		"LR" => _("Liberia"),
		"LY" => _("Libyan Arab Jamahiriya"),
		"LI" => _("Liechtenstein"),
		"LT" => _("Lithuania"),
		"LU" => _("Luxembourg"),
		"MO" => _("Macao"),
		"MK" => _("Macedonia, the Former Yugoslav Republic of"),
		"MG" => _("Madagascar"),
		"MW" => _("Malawi"),
		"MY" => _("Malaysia"),
		"MV" => _("Maldives"),
		"ML" => _("Mali"),
		"MT" => _("Malta"),
		"MH" => _("Marshall Islands"),
		"MQ" => _("Martinique"),
		"MR" => _("Mauritania"),
		"MU" => _("Mauritius"),
		"YT" => _("Mayotte"),
		"MX" => _("Mexico"),
		"FM" => _("Micronesia, Federated States of"),
		"MD" => _("Moldova, Republic of"),
		"MC" => _("Monaco"),
		"MN" => _("Mongolia"),
		"MS" => _("Montserrat"),
		"MA" => _("Morocco"),
		"MZ" => _("Mozambique"),
		"MM" => _("Myanmar"),
		"NA" => _("Namibia"),
		"NR" => _("Nauru"),
		"NP" => _("Nepal"),
		"NL" => _("Netherlands"),
		"AN" => _("Netherlands Antilles"),
		"NC" => _("New Caledonia"),
		"NZ" => _("New Zealand"),
		"NI" => _("Nicaragua"),
		"NE" => _("Niger"),
		"NG" => _("Nigeria"),
		"NU" => _("Niue"),
		"NF" => _("Norfolk Island"),
		"MP" => _("Northern Mariana Islands"),
		"NO" => _("Norway"),
		"OM" => _("Oman"),
		"PK" => _("Pakistan"),
		"PW" => _("Palau"),
		"PS" => _("Palestine"),
		"PA" => _("Panama"),
		"PG" => _("Papua New Guinea"),
		"PY" => _("Paraguay"),
		"PE" => _("Peru"),
		"PH" => _("Philippines"),
		"PN" => _("Pitcairn"),
		"PL" => _("Poland"),
		"PT" => _("Portugal"),
		"PR" => _("Puerto Rico"),
		"QA" => _("Qatar"),
		"RE" => _("Reunion"),
		"RO" => _("Romania"),
		"RU" => _("Russian Federation"),
		"RW" => _("Rwanda"),
		"SH" => _("Saint Helena"),
		"KN" => _("Saint Kitts and Nevis"),
		"LC" => _("Saint Lucia"),
		"PM" => _("Saint Pierre and Miquelon"),
		"VC" => _("Saint Vincent and the Grenadines"),
		"WS" => _("Samoa"),
		"SM" => _("San Marino"),
		"ST" => _("Sao Tome and Principe"),
		"SA" => _("Saudi Arabia"),
		"SN" => _("Senegal"),
		"CS" => _("Serbia and Montenegro"),
		"SC" => _("Seychelles"),
		"SL" => _("Sierra Leone"),
		"SG" => _("Singapore"),
		"SK" => _("Slovakia"),
		"SI" => _("Slovenia"),
		"SB" => _("Solomon Islands"),
		"SO" => _("Somalia"),
		"ZA" => _("South Africa"),
		"GS" => _("South Georgia and the South Sandwich Islands"),
		"ES" => _("Spain"),
		"LK" => _("Sri Lanka"),
		"SD" => _("Sudan"),
		"SR" => _("Suriname"),
		"SJ" => _("Svalbard and Jan Mayen"),
		"SZ" => _("Swaziland"),
		"SE" => _("Sweden"),
		"CH" => _("Switzerland"),
		"SY" => _("Syrian Arab Republic"),
		"TW" => _("Taiwan, Province of China"),
		"TJ" => _("Tajikistan"),
		"TZ" => _("Tanzania, United Republic of"),
		"TH" => _("Thailand"),
		"TL" => _("Timor-Leste"),
		"TG" => _("Togo"),
		"TK" => _("Tokelau"),
		"TO" => _("Tonga"),
		"TT" => _("Trinidad and Tobago"),
		"TN" => _("Tunisia"),
		"TR" => _("Turkey"),
		"TM" => _("Turkmenistan"),
		"TC" => _("Turks and Caicos Islands"),
		"TV" => _("Tuvalu"),
		"UG" => _("Uganda"),
		"UA" => _("Ukraine"),
		"AE" => _("United Arab Emirates"),
		"GB" => _("United Kingdom"),
		"US" => _("United States"),
		"UM" => _("United States Minor Outlying Islands"),
		"UY" => _("Uruguay"),
		"UZ" => _("Uzbekistan"),
		"VU" => _("Vanuatu"),
		"VE" => _("Venezuela"),
		"VN" => _("Viet Nam"),
		"VG" => _("Virgin Islands, British"),
		"VI" => _("Virgin Islands, U.s."),
		"WF" => _("Wallis and Futuna"),
		"EH" => _("Western Sahara"),
		"YE" => _("Yemen"),
		"ZM" => _("Zambia"),
		"ZW" => _("Zimbabwe")
		);
		
		$output = "";

		foreach($countries_arr as $code => $country) {
			$slectfi = ($selected == $code) ? "selected='selected'" : "";
			$output .= "<option {$slectfi} value='{$code}'>{$country}</option>";
		}
		echo $output;
	}

	function return_extra_field_options( $delete_option, $_predefined ) {
		$additional_field_label = $additional_field_type = $additional_registration_form = $additional_update_form = $additional_edit_profile = $additional_field_identifier = '';

		$setting_body = '<div class="form-group row wcrb_repater_field">';
		$setting_body .= '<div class="col-lg-2">';

		if ( isset( $_predefined ) && is_array( $_predefined ) ) {
			$additional_field_label 	  = ( isset( $_predefined['additional_field_label'] ) && ! empty( $_predefined['additional_field_label'] ) ) ? $_predefined['additional_field_label'] : '';
			$additional_field_type 		  = ( isset( $_predefined['additional_field_type'] ) && ! empty( $_predefined['additional_field_type'] ) ) ? $_predefined['additional_field_type'] : '';
			$additional_registration_form = ( isset( $_predefined['additional_registration_form'] ) && ! empty( $_predefined['additional_registration_form'] ) ) ? $_predefined['additional_registration_form'] : '';
			$additional_update_form 	  = ( isset( $_predefined['additional_update_form'] ) && ! empty( $_predefined['additional_update_form'] ) ) ? $_predefined['additional_update_form'] : '';
			$additional_edit_profile 	  = ( isset( $_predefined['additional_edit_profile'] ) && ! empty( $_predefined['additional_edit_profile'] ) ) ? $_predefined['additional_edit_profile'] : '';
			$additional_field_identifier  = ( isset( $_predefined['additional_field_identifier'] ) && ! empty( $_predefined['additional_field_identifier'] ) ) ? $_predefined['additional_field_identifier'] : '';
		}

		$setting_body .= ( $delete_option == 'delete' ) ? '<a class="delme delmeextrafield" href="#" title="Remove row">
																<i class="la la-trash"></i>
															</a>' : '';
		$setting_body .= '<label for="additional_field_label">'. _( 'Field label' ) . '
								<input id="additional_field_label" name="additional_field_label[]" type="text" placeholder="" value="'. $additional_field_label .'" class="form-control">
							</label>
						</div>
						<div class="col-lg-2">
							<label for="additional_field_type">'. _( 'Field type' ) . '
								<select id="additional_field_type" name="additional_field_type[]" class="custom-select form-control">
									<option'. ( ( $additional_field_type == 'input'  ) ? ' selected' : '' ) .' value="input">'. _( 'Input Field' ) . '</option>
									<option'. ( ( $additional_field_type == 'textarea'  ) ? ' selected' : '' ) .' value="textarea">'. _( 'Text Area' ) . '</option>
								</select>
							</label>
						</div>
						<div class="col-lg-2">
							<label for="additional_registration_form">'. _( 'In registration form' ) . '
								<select id="additional_registration_form" name="additional_registration_form[]" class="custom-select form-control">
									<option'. ( ( $additional_registration_form == 'show' ) ? ' selected' : '' ) .' value="show">'. _( 'Show' ) . '</option>
									<option'. ( ( $additional_registration_form == 'hide' ) ? ' selected' : '' ) .' value="hide">'. _( 'Hide' ) . '</option>
								</select>
							</label>
						</div>
						<div class="col-lg-2">
							<label for="additional_update_form">'. _( 'Add & update user by admin' ) . '
								<select id="additional_update_form" name="additional_update_form[]" class="custom-select form-control">
									<option'. ( ( $additional_update_form == 'show' ) ? ' selected' : '' ) .' value="show">'. _( 'Show' ) . '</option>
									<option'. ( ( $additional_update_form == 'hide' ) ? ' selected' : '' ) .' value="hide">'. _( 'Hide' ) . '</option>
								</select>
							</label>
						</div>
						<div class="col-lg-2">
							<label for="additional_edit_profile">'. _( 'Update profile by user' ) . '
								<select id="additional_edit_profile" name="additional_edit_profile[]" class="custom-select form-control">
									<option'. ( ( $additional_edit_profile == 'show' ) ? ' selected' : '' ) .' value="show">'. _( 'Show' ) . '</option>
									<option'. ( ( $additional_edit_profile == 'hide' ) ? ' selected' : '' ) .' value="hide">'. _( 'Hide' ) . '</option>
								</select>
							</label>
						</div><input type="hidden" name="additional_field_identifier[]" value="'. $additional_field_identifier .'" />';
		$setting_body .= '</div><!-- Row ends /-->';

		return $setting_body;
	}

	function return_additionalfields_array( $form_type ) {
		$user_additional_fields = get_option( '_extra_user_fields' );
		$user_additional_fields = ( ! empty( $user_additional_fields ) ) ? unserialize( $user_additional_fields ) : '';

		if ( is_array( $user_additional_fields ) && ! empty( $user_additional_fields ) ) {
			$counter = 0;
			$return_array = array();
			foreach( $user_additional_fields as $_additional_field ) {
				if ( $form_type == 'registration' && $_additional_field['additional_registration_form'] == 'show' ) {
					$return_array[] = $_additional_field['additional_field_identifier'];
					$counter++;
				} else if ( $form_type == 'update' && $_additional_field['additional_update_form'] == 'show' ) {
					$return_array[] = $_additional_field['additional_field_identifier'];
					$counter++;
				} else if ( $form_type == 'edit' && $_additional_field['additional_edit_profile'] == 'show' ) {
					$return_array[] = $_additional_field['additional_field_identifier'];
					$counter++;
				}
				$counter++;
			}//foreach ends here.
			return $return_array;
		}//if ends here.
	}//function ends here.

	function show_alert($message){
		echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">'
			.$message.
			'<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
			</div>';
	}	
	function return_additional_field_options( $user_id, $form_type ) {
		global $new_user;
		$user_additional_fields = get_option( '_extra_user_fields' );
		$user_additional_fields = ( ! empty( $user_additional_fields ) ) ? unserialize( $user_additional_fields ) : '';

		$output = '';
		if ( is_array( $user_additional_fields ) && ! empty( $user_additional_fields ) ) {
			foreach( $user_additional_fields as $_additional_field ) {
				$value = ( ! empty( $user_id ) ) ? $new_user->get_usermeta( $user_id, $_additional_field['additional_field_identifier'] ) : '';

				$_produce = '';
				$_class = 'form-group row';
				$_label_class = $inputwrap = $inputwrapend = '';
				if ( $form_type == 'registration' && $_additional_field['additional_registration_form'] == 'show' ) {
					$_produce = 'YES';
					$_class = 'group material-input';
					$_label_class = '';
					$inputwrap    = '';
					$inputwrapend = '';
				} else if ( $form_type == 'update' && $_additional_field['additional_update_form'] == 'show' ) {
					$_produce = 'YES';
					$_class = 'form-group row d-flex align-items-center mb-5';
					$_label_class = 'col-lg-4 form-control-label d-flex justify-content-lg-end';
					$inputwrap    = '<div class="col-lg-5">';
					$inputwrapend = '</div>';
				} else if ( $form_type == 'edit' && $_additional_field['additional_edit_profile'] == 'show' ) {
					$_produce = 'YES';
					$_class = 'form-group row d-flex align-items-center mb-5';
					$_label_class = 'col-lg-4 form-control-label d-flex justify-content-lg-end';
					$inputwrap    = '<div class="col-lg-5">';
					$inputwrapend = '</div>';
				}

				if ( $_produce == 'YES' && ! empty( $_additional_field['additional_field_label'] ) ) {
					$label = $_additional_field['additional_field_label'];
					$fieldID = $_additional_field['additional_field_identifier'];
					$fieldType = $_additional_field['additional_field_type'];

					$output .= '<div class="'. $_class .'"><label class="'. $_label_class .'" for="'. $fieldID .'">'. $label . '</label>';
					$output .= $inputwrap;
					if ( $fieldType == 'input' ) {
						$output .= '<input type="text" name="'. $fieldID .'" id="'. $fieldID .'" value="'. $value .'" class="form-control">';
					} else if ( $fieldType == 'textarea' ) {
						$output .= '<textarea name="'. $fieldID .'" id="'. $fieldID .'" class="form-control">'. $value .'</textarea>';
					}
					$output .= $inputwrapend;
					$output .= '</div>';
				}

			}//foreach ends here.
		}
		return $output;
	}//function ends here.

	function wc_get_user_display_name($username, $first_name, $last_name) {
		if (!empty($first_name) || !empty($last_name)) {
			return $username . ' -' . trim($first_name . ' ' . $last_name) . '';
		}
		return $username;
	}

	function is_logged_in() {
		return (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']));
	}
