<?php
//Forms Class Generator

class FORM_SUBMISSIONS extends FORMS {
	private $message;
	private $submission_type;
	public $return_array;
	
	
	/**
	* Construct Function
	* Default features run on load of class
	*/
	function __construct($post_value) {
		global $db;
		
		foreach ($post_value as $name => $val) { 
			if($name != "db_duplicate" && $name != "duplicate_join_and" && $name != "duplicate_join_or") {
				$post_value[$name] = $db->real_escape_string($val);	
			} 
		}
		
		//Default Function
		$this->wc_process_form($post_value);
		
		$this->return_array = $this->wc_return_response();
	}
	
	
	/**
	* Generate Return Response
	*
	*/
	function wc_return_response() {
		
		$return_array['message'] 	= $this->message;
		$return_array['submission'] = $this->submission_type;
		
		return $return_array;
	}
	
	/*
	* Process Form to Database
	*
	*/
	public function wc_process_form($posted_values) {
		
		if(isset($posted_values['table_insert']) && $posted_values['table_insert'] == "YES") {
			//Run Posted Values Function here.
			$this->wc_process_insert_form($posted_values);
			$this->submission_type = "INSERT";
		} elseif(isset($posted_values['table_update']) && $posted_values['table_update'] == "YES" && !empty($posted_values['table_update_key'])) {
			//Run Update Function
			$this->wc_process_update_form($posted_values);
			$this->submission_type = "UPDATE";
		} else {
			$this->message = _("Either you have not declared the Update Field and its key or you have not stated INSERT TRUE check array.");
			$this->submission_type = "";
		}

	}//Function wc_process_form Ends.
	
	
	
	/**
	* Let's process how to INSERT Data to DB.
	*/
	function wc_process_insert_form($posted_values) {
		global $db;
		
		if(!isset($posted_values['dbtable'])) {
			//Table name is not defined
			$this->message 	= _("Table name is not defined and its required");
			return;
		}
		
		if(!isset($_SESSION['user_id'])) {
			$this->message = _("Who are you to insert something in table.");
			return;
		}
		
		if($this->wc_verify_db_table($posted_values['dbtable']) == FALSE) {
			$this->message = _("Table does not match in db.");
			return;
		}
		
		//Let's now create array of table columns
		$columns_array = $this->wc_get_table_columns($posted_values['dbtable']);
		
		//Let's now make a new Array with matching fields in $_POST and DB column array. 
		$table_matching_array = array_intersect_key($posted_values, $columns_array);
		
		if(empty($table_matching_array)) {
			$this->message = _("No Match in column names and fields names of form sent. Please check array of form generation and make sure they match the column names in database.");
		}
		
		if(isset($posted_values['db_duplicate'])) {
			$posted_db_duplicate = $posted_values['db_duplicate'];
			$posted_db_and_value = isset($posted_values['duplicate_join_and']) ? $posted_values['duplicate_join_and'] : array();
			$posted_db_or_value  = isset($posted_values['duplicate_join_or']) ? $posted_values['duplicate_join_or'] : array();
			
			//Let's Generate query for And if And is not empty.
			$and_query_part = "";
			$counter 		= 0;
			
			foreach($posted_db_and_value as $key) {
				$column = $key;
				$value  = $posted_values[$key];
				
				if($counter == 0) {
					$and_query_part .= "(";
				} else {
					$and_query_part .= " AND ";
				}
				
				$and_query_part .= "`".$column."` = '".$value."'";
				
				$counter++;
			}
			$and_query_part .= ($counter > 0) ? ")": "";
			
			
			//Let's Generate query for OR if And is not empty.
			$or_query_part = "";
			$counter 		= 0;
			
			foreach($posted_db_or_value as $key) {
				$column = $key;
				$value  = $posted_values[$key];
				
				if($counter == 0) {
					$or_query_part .= "(";
				} else {
					$or_query_part .= " OR ";
				}
				
				$or_query_part .= "`".$column."` = '".$value."'";
				
				$counter++;
			}
			$or_query_part .= ($counter > 0) ? ")": "";
				
			$error = '';
			
			//Returns ARray for update query
			$not_in = '';
			
			foreach($posted_db_duplicate as $key) {
				$column = $key;
				$value  = $posted_values[$key]; 
				
				$received = $this->wc_check_duplicate_db_value($column, $value, $posted_values['dbtable'], $and_query_part, $or_query_part, $not_in);
				//Produce Error for duplicate data found
				
				if($received == "YES") {
					$error .= " {".$column.": ".$value."}";
				}
			}// End foreach
			
			if(!empty($error)):
				$this->message = _("Duplicate data found. ").$error;
				return;
			endif;
		}
		
		//Let's generate our Query now.
		$return_message 	= $this->wc_create_insert_query($table_matching_array, $posted_values['dbtable']);
		
		$this->message 		= $return_message;
		
	}
	
	
	/**
	* Let's process how to UPDATE Data to DB.
	*/
	function wc_process_update_form($posted_values) {
		global $db;
		
		if(!isset($posted_values['dbtable'])) {
			//Table name is not defined
			$this->message 	= _("Table name is not defined and its required");
			return;
		}
		
		if(!isset($_SESSION['user_id'])) {
			$this->message = _("Who are you to insert something in table.");
			return;
		}
		
		//Verify if submited table name match table name in db
		if($this->wc_verify_db_table($posted_values['dbtable']) == FALSE) {
			$this->message = _("Table does not match in db.");
			return;
		}
		
		//Let's now create array of table columns
		$columns_array = $this->wc_get_table_columns($posted_values['dbtable']);
		
		
		//Let's now make a new Array with matching fields in $_POST and DB column array. 
		$table_matching_array = array_intersect_key($posted_values, $columns_array);
		
		if(empty($table_matching_array)) {
			$this->message = _("No Match in column names and fields names of form sent. Please check array of form generation and make sure they match the column names in database.");
			return;
		}
		
		if(isset($posted_values['db_duplicate'])) {
			$posted_db_duplicate = $posted_values['db_duplicate'];
			$posted_db_and_value = isset($posted_values['duplicate_join_and']) ? $posted_values['duplicate_join_and'] : array();
			$posted_db_or_value = isset($posted_values['duplicate_join_or']) ? $posted_values['duplicate_join_or'] : array();
			
			//Let's Generate query for And if And is not empty.
			$and_query_part = "";
			$counter 		= 0;
			
			foreach($posted_db_and_value as $key) {
				$column = $key;
				$value  = $posted_values[$key];
				
				if($counter == 0) {
					$and_query_part .= "(";
				} else {
					$and_query_part .= " AND ";
				}
				
				$and_query_part .= "`".$column."` = '".$value."'";
				
				$counter++;
			}
			$and_query_part .= ($counter > 0) ? ")": "";
			
			
			//Let's Generate query for OR if And is not empty.
			$or_query_part = "";
			$counter 		= 0;
			
			foreach($posted_db_or_value as $key) {
				$column = $key;
				$value  = $posted_values[$key];
				
				if($counter == 0) {
					$or_query_part .= "(";
				} else {
					$or_query_part .= " OR ";
				}
				
				$or_query_part .= "`".$column."` = '".$value."'";
				
				$counter++;
			}
			$or_query_part .= ($counter > 0) ? ")": "";
			
			
			
			$error = '';
			
			$not_in = array(
						"primary_column"	=> $posted_values["primary_column"],
						"primary_id"		=> $posted_values["table_update_key"]
			);
			
			foreach($posted_db_duplicate as $key) {
				$column = $key;
				$value  = $posted_values[$key]; 
				
				$received = $this->wc_check_duplicate_db_value($column, $value, $posted_values['dbtable'], $and_query_part, $or_query_part, $not_in);
				//Produce Error for duplicate data found
				
				if($received == "YES") {
					$error .= " {".$column.": ".$value."}";
				}
			}// End foreach
			
			if(!empty($error)):
				$this->message = _("Duplicate data found. ").$error;
				return;
			endif;
		}
		
		$primary_column		= $posted_values['primary_column'];
		$primary_value 		= $posted_values['table_update_key'];
		
		//Let's generate our Query now.
		$return_message 	= $this->wc_create_update_query($table_matching_array, $posted_values['dbtable'], $primary_column, $primary_value);
		$this->message 		= $return_message;
	}
	
	
	/****
		* This will check if DB already have value
		* Accepts Array.
	    */
	function wc_check_duplicate_db_value($column, $value, $table, $posted_db_and_value, $posted_db_or_value, $not_in) {
		global $db;
		
		if(empty($column) && empty($value) && empty($table)) {
			return _("Provided data is empty cannot check duplicate");
		}
		
		if(!empty($posted_db_and_value) && !empty($posted_db_or_value)) : 
			$query = "SELECT * from `".$table."` WHERE ".$posted_db_and_value." AND ".$posted_db_or_value;
		elseif(!empty($posted_db_and_value)) : 
			$query = "SELECT * from `".$table."` WHERE ".$posted_db_and_value;
		elseif(!empty($posted_db_or_value)):
			$query = "SELECT * from `".$table."` WHERE ".$posted_db_or_value;
		else:
			$query = "SELECT * from `".$table."` WHERE `".$column."`='".$value."'";
		endif;
		
		$result = $db->query($query) or die($db->error);
		
		$num_user = $result->num_rows;
		
		$retrun   = "";
		
		if(is_array($not_in)) {
			$row = $result->fetch_array();
			
			if($row[$not_in["primary_column"]] == $not_in["primary_id"]) {
				$retrun = "NO";
			}
		}
			
		if($num_user > 0 && $retrun != "NO") {
			return "YES";
		} else {
			return "NO";
		}
	}   
	
	/***
		*	Let's create a query from array to insert. 
		*	Once Query is generated let's insert in DB.
	    */
	function wc_create_insert_query($array_for_query, $table_name) {
		global $db;
		
		if(empty($array_for_query) && $table_name) {
			return _("Array to generate query is empty.");
		}	
		
		$columns 	= '';
		$values		= '';
		$counter	= 0;
		
		foreach($array_for_query as $key => $value) {
			if($counter > 0) {
				$columns .= ', ';
				$values .= ', ';
			}
			
			$columns 	.= "`".$key."`";
			$values 	.= '"'.$value.'"';
			
			$counter++;
		}
		
		$query 		= "INSERT INTO `".$table_name."`(".$columns.") VALUES(".$values.")" ;
		$result 	= $db->query($query) or die($db->error);
		
		return _("You have successfuly added data into database.");
	}
	
	
	/***
		*	Let's create a query from array to insert. 
		*	Once Query is generated let's insert in DB.
	    */
	function wc_create_update_query($array_for_query, $table_name, $primary_column, $primary_value) {
		global $db;
		
		
		if(empty($array_for_query) && $table_name) {
			return _("Array to generate query is empty.");
		}	
		
		$rows 	= '';
		$counter	= 0;
		
		foreach($array_for_query as $key => $value) {
			//first_name = "'.$first_name.'",
			if($counter > 0) {
				$rows .= ', ';
			}
			
			$rows 	.= "`".$key."` = '".$value."'";
			
			$counter++;
		}
		
		$query = "UPDATE ".$table_name." SET ".$rows."WHERE `".$primary_column."` = '".$primary_value."'";
		$result = $db->query($query) or die($db->error);
		
		return _("Updated Record!");
	}
	
	
	/***
		*	Let's get array of Column names. 
		*	From our available table name.
		*	We already verified table name exists.
	    */
	function wc_get_table_columns($table_name) {
		global $db;
		
		if(!empty($table_name)) {
			$query  		= "SHOW COLUMNS FROM ".$table_name;
			$result 		= $db->query($query) or die($db->error);

			$columns_array 	= array();

			while($row 		= $result->fetch_array()) {
				// && $row['Key'] != "PRI"   IGNORE PRIMARY KEY IGNORANCE
				if($row['Extra'] != "auto_increment") {
					$columns_array[$row['Field']] = '1';	
				}
			}	
			
			return $columns_array;
		} else {
			$this->message = _("No table name exists to check.");
		}
	}
	
	/**
	* Function Helps to verify if Table exists
	*/
	function wc_verify_db_table($table_name) {
		global $db;
		
		if(!empty($table_name)) {
			
			$query  = "SELECT 1 FROM".$table_name;
			$result = $db->query($query) or die($db->error);
			
			//Returns TRUE if Exists. 
			//Returns FALSE if not.
			return $result;
			
		} else {
			$this->message = _("No table name exists to check.");
		}
	}
	

}//class ends here.