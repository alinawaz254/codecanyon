<?php
	if(!defined('ROOT_DIR')) {
		exit();
	}

	if(!function_exists("get_language")):
		function get_language() { 
			global $db;
			$query = "SELECT * from `options` WHERE `option_name`='language'";
			$result = $db->query($query) or die($db->error);
			$row = $result->fetch_array();
			$option_value = isset($row['option_value']) && $row['option_value'] != null ?stripslashes($row['option_value']) : '';//this will remove database slashes from values
			return $option_value; //This function returns option value.
		}//get option value function ends here.
	endif;

	//If not live Translation selected. 
	//Get language from System.
	if(!isset($_SESSION["language"])):
		$language = get_language();
		 
		switch ($language) {
			case "english":
				$language_code = "language_english";
				break;
			case "spanish":
				$language_code = "language_spanish";
				break;
			case "dutch":
				$language_code = "language_dutch";
				break;
			case "french":
				$language_code = "language_french";
				break;	
			case "german":
				$language_code = "language_german";
				break;
			case "italian":
				$language_code = "language_italian";
				break;
			case "thai":
				$language_code = "language_thai";
				break;
			default:
				$language_code = "language_english";
		}
		
		$_SESSION["language"] = $language_code;
	endif;

	$language_array = array (
						"language_english"	=> 
							array (
								"codeset"	=> "UTF8",
								"language"	=> "",
								"secondary"	=> "",
								"windows"	=> ""
							),
						"language_spanish"	=> 
							array (
								"codeset"	=> "UTF8",
								"language"	=> "es_ES",
								"secondary"	=> "es",
								"windows"	=> "esp_ESP"
							),
						"language_thai"	=> 
							array (
								"codeset"	=> "UTF8",
								"language"	=> "th_TH",
								"secondary"	=> "th",
								"windows"	=> "tha_THA"
							),
						"language_dutch"	=> 
							array (
								"codeset"	=> "UTF8",
								"language"	=> "nl_NL",
								"secondary"	=> "nl",
								"windows"	=> "nld_NLD"
							),
						"language_german"	=> 
							array (
								"codeset"	=> "UTF8",
								"language"	=> "de_DE",
								"secondary"	=> "de",
								"windows"	=> "deu_DEU"
							),
						"language_french"	=> 
							array (
								"codeset"	=> "UTF8",
								"language"	=> "fr_FR",
								"secondary"	=> "fr",
								"windows"	=> "fra_FRA"
							),
						"language_italian"	=> 
							array (
								"codeset"	=> "UTF8",
								"language"	=> "it_IT",
								"secondary"	=> "it",
								"windows"	=> "ita_ITA"
							)
					);
	
	/**
	 * Setting Default Language
	 */
	if(isset($_GET["language"]) && !empty($_GET["language"])):
		$_SESSION["language"]	= $_GET["language"];
	endif;

	if ( isset( $_SESSION["language"] ) ):
		$selected_language 	= $_SESSION["language"];
		
		$codeset 			= $language_array[$selected_language]["codeset"];
		$lang	 			= $language_array[$selected_language]["language"];   
		$secondary 			= $language_array[$selected_language]["secondary"];
		$windows			= $language_array[$selected_language]["windows"];

		putenv('LANG='.$lang.'.'.$codeset);
		putenv('LANGUAGE='.$lang.'.'.$codeset);
		bind_textdomain_codeset('login_script', $codeset);

		// set locale
		bindtextdomain('login_script', ROOT_DIR.'/locale');
		/* TURN ON JUST FOR TESTIGN
		setlocale(LC_ALL, array($lang.'.'.$codeset, $lang, $secondary, $windows)) or die('Locale not installed');*/
		setlocale(LC_ALL, array($lang.'.'.$codeset, $lang, $secondary, $windows));

		textdomain('login_script');
	endif;