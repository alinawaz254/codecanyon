<?php
	if(!defined('ACCESSDBINS')) {
		die('Direct access not permitted');
	}

	//Database Connection file. Update with your Database information once you create database from cpanel, or mysql.
	if(if_table_exists("user_meta") == FALSE) { 
		$query = 'CREATE TABLE user_meta (
			`user_meta_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) NULL,
			`message_email` varchar(50) NULL,
			`last_login_time` datetime NULL,
			`last_login_ip` varchar(120) NULL,
			`login_attempt` bigint(20) NULL,
			`login_lock` varchar(50) NULL,
			PRIMARY KEY (`user_meta_id`)
		)';	
		$result = $db->query($query) or die($db->error);
		echo 'User Meta Table created.<br>';
	}  //Creating user notes table ends here.

	if(if_table_exists("message_meta") == FALSE) { 
		$query = 'CREATE TABLE message_meta (
			`msg_meta_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`message_id` bigint(20) NULL,
			`status` varchar(100) NULL,
			`from_id` bigint(20) NULL,
			`to_id` bigint(20) NULL,
			`subject_id` bigint(20) NULL,
			PRIMARY KEY (`msg_meta_id`)
		)';	
		$result = $db->query($query) or die($db->error);
		echo 'Message Meta Table created.<br>';
	}  //Creating user notes table ends here.

	if(if_table_exists("messages") == FALSE) { 
		$query = 'CREATE TABLE messages (
			`message_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`message_datetime` datetime NULL,
			`message_detail` varchar(1000) NULL,
			PRIMARY KEY (`message_id`)
		)';	
		$result = $db->query($query) or die($db->error);
		echo 'Messages Table created.<br>';
	}  //Creating user notes table ends here.

	if(if_table_exists("subjects") == FALSE) { 
		$query = 'CREATE TABLE subjects (
			`subject_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`subject_title` varchar(600) NULL,
			PRIMARY KEY (`subject_id`)
		)';	
		$result = $db->query($query) or die($db->error);
		echo 'Subjects Table created.<br>';
	}  //Creating user notes table ends here.

	if(if_table_exists("notes") == FALSE) { 
		$query = 'CREATE TABLE notes (
			`note_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`note_date` timestamp NULL,
			`note_title` varchar(200) NULL,
			`note_detail` varchar(600) NULL,
			`user_id` bigint(20) NULL,
			PRIMARY KEY (`note_id`)
		)';	
		$result = $db->query($query) or die($db->error);
		echo 'Notes Table created.<br>';
	}  //Creating user notes table ends here.

	if(if_table_exists("announcements") == FALSE) { 
		$query = 'CREATE TABLE announcements (
			`announcement_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`announcement_date` date NULL,
			`announcement_title` varchar(200) NULL,
			`announcement_detail` varchar(1000) NULL,
			`user_type` varchar(100) NULL,
			`announcement_status` varchar(50) NULL,
			PRIMARY KEY (`announcement_id`)
		)';	
		$result = $db->query($query) or die($db->error);
		echo 'Notes Table created.<br>';
	}  //Creating user notes table ends here.

	//if database tables does not exist already create them.
	if(if_table_exists("options") == FALSE) {
		$query = 'CREATE TABLE options (
			`option_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`option_name` varchar(500) NULL,
			`option_value` longtext NULL,
			PRIMARY KEY (`option_id`)
		)';	
		$result = $db->query($query) or die($db->error);
		echo 'Options Table created.<br>';
	} //creating options table.

	if(if_table_exists("users") == FALSE) { 
		$query = 'CREATE TABLE users (
			`user_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`first_name` varchar(100) NULL,
			`last_name` varchar(100) NULL,
			`gender` varchar(50) NULL,
			`date_of_birth` date NULL,
			`address1` varchar(200) NULL,
			`address2` varchar(200) NULL,
			`city` varchar(100) NULL,
			`state` varchar(100) NULL,
			`country` varchar(100) NULL,
			`zip_code` varchar(100) NULL,
			`mobile` varchar(200) NULL,
			`phone` varchar(200) NULL,
			`username` varchar(100) NULL,
			`email` varchar(200) NULL,
			`password` varchar(200) NULL,
			`profile_image` varchar(500) NULL,
			`description` varchar(600) NULL,
			`status` varchar(100) NULL,
			`activation_key` varchar(100) NULL,
			`date_register` date NULL,
			`user_type` varchar(100) NULL,
			PRIMARY KEY (`user_id`)
		)';	
		$result = $db->query($query) or die($db->error);
		echo 'Users Table created.<br>';
	}  //Creating users table ends here.

	//if database tables does not exist already create them.
	if(if_table_exists("user_level") == FALSE) {
		$query = 'CREATE TABLE user_level (
			`level_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`level_name` varchar(200) NULL,
			`level_description` varchar(600) NULL,
			`level_page` varchar(100) NULL,
			PRIMARY KEY (`level_id`)
		)';	
		$result = $db->query($query) or die($db->error);
		echo 'Options Table created.<br>';
	} //creating user level table ends.

	if(if_table_exists("usermeta") == FALSE) { 
		$query = 'CREATE TABLE `usermeta` (
			`meta_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) NULL,
			`meta_key` varchar(255) NULL,
			`meta_value` longtext NULL,
			PRIMARY KEY (`meta_id`),
			FOREIGN KEY (`user_id`) REFERENCES users(`user_id`) 
		)';	
		$result = $db->query($query) or die($db->error);
		echo 'User Meta Table created.<br>';
	}  //Creating user notes table ends here.


	if(if_table_exists("investment_plans") == FALSE) { 
		$query = 'CREATE TABLE investment_plans (
			`plan_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`plan_name` varchar(100) NOT NULL,
			`total_cycles` int(11) NOT NULL,
			`commission` decimal(12,2) NOT NULL,
			`cycle_days` int(11) DEFAULT 35,
			`created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`plan_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';
		
		$result = $db->query($query) or die($db->error);
		echo "Investment Plans Table created.<br>";
	}

	if(if_table_exists("user_investments") == FALSE) { 
		$query = 'CREATE TABLE user_investments (
			`investment_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) NOT NULL,
			`plan_id` bigint(20) NOT NULL,
			`amount` decimal(12,2) NOT NULL,
			`issue_date` date NOT NULL,
			`created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`investment_id`),
			FOREIGN KEY (`user_id`) REFERENCES users(`user_id`) ON DELETE CASCADE,
			FOREIGN KEY (`plan_id`) REFERENCES investment_plans(`plan_id`) ON DELETE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';
		
		$result = $db->query($query) or die($db->error);
		echo "User Investments Table created.<br>";
	}
	if(if_table_exists("user_investment_details") == FALSE) { 
		$query = 'CREATE TABLE user_investment_details (
			`id` bigint(20) NOT NULL  AUTO_INCREMENT,
			`investment_id` bigint(20) NOT NULL,
			`cycle` int(11) NOT NULL,
			`comission` decimal(12,2) NOT NULL,
			`comission_expiry_date` date NOT NULL,
			`created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';
		
		$result = $db->query($query) or die($db->error);
		echo "User Investment Details Table created.<br>";
	}
	if(if_table_exists("videos") == FALSE) { 
		$query = 'CREATE TABLE videos (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`title` varchar(255) NOT NULL,
			`description` text NULL,
			`video_file` varchar(255) NULL,
			`video_url` varchar(500) NULL,
			`created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';
		
		$result = $db->query($query) or die($db->error);
		echo "Videos Table created.<br>";
	}	