<?php
	if(get_option('version') < '4.0') { 
		set_option('version', '4.0');
		
		//This will work when Version is less than 4
	}