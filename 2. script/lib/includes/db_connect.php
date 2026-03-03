<?php
	//Database Connection file. Update with your Database information once you create database from cpanel, or mysql.
	define ("DB_HOST", "localhost"); //Databse Host.
	define ("DB_NAME", "codecanyon"); //database Name.
	define ("DB_USER", "root"); //Databse User.
	define ("DB_PASS", ""); //database password.

	$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

	if($db->connect_errno > 0){
		die('Unable to connect to database [' . $db->connect_error . ']');
	}

	function if_table_exists($tablename) {
		global $db; 

		if(empty($tablename)) {
			return FALSE;
		}
		if($result = $db->query("SHOW TABLES LIKE '{$tablename}'")) {
			if($result->num_rows == 0) {
				return FALSE;
			} else {
				return TRUE;
			}
		}
	}