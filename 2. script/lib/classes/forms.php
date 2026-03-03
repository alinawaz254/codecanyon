<?php
//Forms Class Generator

class FORMS {
	/**
	* Declare Private Variables for Make
	*/

	
	
	/**
	* Construct Function
	* Default features run on load of class
	*/
	function __construct() {
		//Default Function
	}
	
	
	/**
	* Form Generator
	* This function helps generate Forms.
	*
	* Forms default have HTML of Bootstrap. 
	* Accepts Array and returns HTML for Form.
	*
	* @Since 1.0.0
	*/
	function wc_form_generator($form_values) {
		
		if(is_array($form_values)) :
			
			//We are good to go
			$content = "";
		
			//Let's grab form values and create Form field first
			$form_value = $form_values;	
			
			//Let's stop Ajax Submnission if User selected this not to go DB
			$async = '';
			if(isset($form_value['auto_submit_form']) && $form_value['auto_submit_form'] == TRUE) {
				$async = " data-async";
			}
		
			$content .= '<form'.$async.' enctype="multipart/form-data" role="form" method="post"';
			$content .= empty($form_value['form-id']) ? '' : ' id="'.$form_value['form-id'].'"';
			$content .= empty($form_value['form-name']) ? '' : ' name="'.$form_value['form-name'].'"';
			$content .= empty($form_value['form-classes']) ? ' class="wc_form"' : ' class="wc_form '.$form_value['form-classes'].'"';
			$content .= (empty($form_value['form-action']) || ($form_value['form-action'] == 'self')) ? ' action="'.$_SERVER['PHP_SELF'].'"' : ' action="'.$form_value['form-action'].'"';
			$content .= ">";
			
				
			/**
			* Let's generate Form Fields
			* Different types of form fields handled different way.
			*/
			foreach($form_value['form-fields'] as $form_field) {
				
				if(isset($form_field['field-columns']) && $form_field['field-columns'] == 2) {
					//Define Row counter 0 on first run
					!isset($row_counter) ? $row_counter = 0 : $row_counter;
					
					if($row_counter == 0) {
						$row_started = TRUE;
						$content .= '<div class="form-row">';	
					}
					
					$wrapper_classes = "form-group col-md-6";
					!empty($form_field['wrapper-classes']) ? $wrapper_classes .= ' '.$form_field['wrapper-classes']: $wrapper_classes;
					
					$content .= '<div class="'.$wrapper_classes.'"';
					$content .= !empty($form_field['wrapper-id']) ? ' id="'.$form_field['wrapper-id'].'"': '';
					$content .= '>';
					
					$field_label 	= isset($form_field['field-label']) ? $form_field['field-label'] : "";
					$field_desc 	= isset($form_field['field-desc']) ? $form_field['field-desc']: "";
					$field_name 	= isset($form_field['field-name']) ? $form_field['field-name'] : '';
					
					
					//Let's Generate Label
					$content .= !empty($form_field['field-label']) ? $this->wc_generate_label($field_label, $field_desc, $field_name): '';
					
					//Let's generate Field now.
					$field_type 		= isset($form_field['field-type']) ? $form_field['field-type'] : '';
					$field_placeholder 	= isset($form_field['field-placeholder']) ? $form_field['field-placeholder'] : '';
					$field_required 	= isset($form_field['field-required']) ? $form_field['field-required'] : FALSE;
					
					$field_value = isset($form_field['field-value']) ? $form_field['field-value'] : "";
					
					//Get Existing Value from DataBase if Update Key is Present 
					if(isset($form_value['update']) 
						&& isset($form_value['update_key']) 
						&& !empty($form_value['update_key'])) {
						
						$table   		= $form_value['database-table'];
						$field 	 		= $field_name;
						$row_key 		= $form_value['update_key'];
						$primary_column = $form_value['primary_column'];
						
						//$field_value = "Work";
						
						if(isset($form_field["field-value"]) && $form_field["field-value"] == FALSE) {
							//Do nothing user said dont make value
						} else {
							if(empty($field_value) && $field_value == FALSE) {
								$field_value 	= $this->wc_get_existing_value($table, $field, $row_key, $primary_column);		
							}
						}
					}
					
					$select_array = isset($form_field['select-array']) ? $form_field['select-array'] : "";
					
					$content .= $this->wc_generate_field($field_name, $field_type, $field_placeholder, $field_required, $field_value, $select_array);
					
					if(isset($form_field['db_duplicate']) && $form_field['db_duplicate'] == FALSE) {
						$content .= $this->wc_generate_hidden_field("db_duplicate[]", $field_name);
					}
					
					if(isset($form_field['duplicate_join'])) {
						if($form_field['duplicate_join'] == "and") {
							$content .= $this->wc_generate_hidden_field("duplicate_join_and[]", $form_field['field-name']);	
						} elseif($form_field["duplicate_join"] == "or") {
							$content .= $this->wc_generate_hidden_field("duplicate_join_or[]", $form_field['field-name']);
						}
					}
					
					$content .= "</div><!-- form-group /-->";
					
					$row_counter++;
					if($row_counter == 2) {
						$content .= '</div><!-- Form Row -->';	
						$row_started = FALSE; // To close row in single column if next row is not double
						$row_counter = 0; // Redefine row columns counter.
					}
					
				} else {
					if(isset($row_started) && $row_started == TRUE) {
						//Close Row if exists
						$content .= '</div><!-- Form Row -->';	
						$row_started = FALSE;
						$row_counter = 0;	
					}
					
					$wrapper_classes = "form-group";
					!empty($form_field['wrapper-classes']) ? $wrapper_classes .= ' '.$form_field['wrapper-classes']: $wrapper_classes;
					
					$content .= '<div class="'.$wrapper_classes.'"';
					$content .= !empty($form_field['wrapper-id']) ? ' id="'.$form_field['wrapper-id'].'"': '';
					$content .= '>';
					
					$field_label 	= isset($form_field['field-label']) ? $form_field['field-label'] : "";
					$field_desc 	= isset($form_field['field-desc']) ? $form_field['field-desc']: "";
					$field_name 	= isset($form_field['field-name']) ? $form_field['field-name'] : '';
					
					
					//Let's Generate Label
					$content .= !empty($form_field['field-label']) ? $this->wc_generate_label($field_label, $field_desc, $field_name): '';
					
					
					//Let's generate Field now.
					$field_type 		= isset($form_field['field-type']) ? $form_field['field-type'] : '';
					$field_placeholder 	= isset($form_field['field-placeholder']) ? $form_field['field-placeholder'] : '';
					$field_required 	= isset($form_field['field-required']) ? $form_field['field-required'] : FALSE;
					
					
					$field_value = isset($form_field['field-value']) ? $form_field['field-value'] : "";
					
					//Get Existing Value from DataBase if Update Key is Present 
					if(isset($form_value['update']) 
						&& isset($form_value['update_key']) 
						&& !empty($form_value['update_key'])) {
						
						$table   		= $form_value['database-table'];
						$field 	 		= $field_name;
						$row_key 		= $form_value['update_key'];
						$primary_column = $form_value['primary_column'];
						
						//$field_value = "Work";
						
						if(isset($form_field["field-value"]) && $form_field["field-value"] == FALSE) {
							//Do nothing user said dont make value
						} else {
							if(empty($field_value) && $field_value == FALSE) {
								$field_value 	= $this->wc_get_existing_value($table, $field, $row_key, $primary_column);		
							}
						}
					}
					
					$select_array = isset($form_field['select-array']) ? $form_field['select-array'] : "";

					$content .= $this->wc_generate_field($field_name, $field_type, $field_placeholder, $field_required, $field_value, $select_array);
					
					if(isset($form_field['db_duplicate']) && $form_field['db_duplicate'] == FALSE) {
						$content .= $this->wc_generate_hidden_field("db_duplicate[]", $field_name);
					}
					
					if(isset($form_field['duplicate_join'])) {
						if($form_field['duplicate_join'] == "and") {
							$content .= $this->wc_generate_hidden_field("duplicate_join_and[]", $form_field['field-name']);	
						} elseif($form_field["duplicate_join"] == "or") {
							$content .= $this->wc_generate_hidden_field("duplicate_join_or[]", $form_field['field-name']);
						}
					}
					
					$content .= "</div><!-- form-group /-->";
				}
				
			} //Foreach ends
			
			//Make Sure to close the Form row if loop is even end or have single column or field. 
			if(isset($row_started) && $row_started == TRUE) {
				//Close Row if exists
				$content .= '</div><!-- Form Row -->';	
				$row_started = FALSE;
				$row_counter = 0;
			}
			
			
			/**
			* Let's see if table wants to insert or Update
			* Set INSERt or Update to YES 
			* Set Update KEY if UPDATE is YES and INSERT is false*
			*/
			if(isset($form_value['insert']) && $form_value['insert'] == TRUE) {
				$content .= $this->wc_generate_hidden_field("table_insert", "YES");
			} elseif(isset($form_value['update']) && $form_value['update'] == TRUE) {
				if(!isset($form_value['update_key'])) {
					return _("Update key is required with update field TRUE");
				} else {
					$content .= $this->wc_generate_hidden_field("table_update", "YES");
					$content .= $this->wc_generate_hidden_field("table_update_key", $form_value['update_key']);
					$content .= $this->wc_generate_hidden_field("primary_column", $form_value['primary_column']);
				}
			}
		
			// Let's send Field for Table Name
			$content .= !empty($form_value['database-table']) ? $this->wc_generate_hidden_field("dbtable", $form_value['database-table']): '';
			
			/*if(isset($form_value['auto_submit_form']) && $form_value['auto_submit_form'] == TRUE) {
				$content .= $this->wc_generate_hidden_field("wc_forms_presence", "1");	
			}*/
			$content .= $this->wc_generate_hidden_field("wc_forms_presence", "1");
			
			//Let's Generate Button
			$content .= $this->wc_generate_submit_button(
									isset($form_value['submit-label']) ? $form_value['submit-label']: '', 
									isset($form_value['submit-classes']) ? $form_value['submit-classes']: ''
						);
			
			//Let's finalize ending form field
			$content .= "</form>";	
		else:
			return _("Sorry forms fields have nothing. Or its not array.");
		endif;
		
		return $content;
	} //Function Form Generator Ends.
	
	
	/**
	* Field Generator
	* This function helps generate input fields and textarea.
	*
	* Forms default have HTML of Bootstrap. 
	* Accepts Array and returns HTML for Form.
	*
	* @Since 1.0.0
	*/
	function wc_generate_field($field_name, $field_type, $field_placeholder, $field_required, $field_value, $select_array) {
		if(empty($field_name)) {
			return _("Field name is required");
		}
		
		if($field_type != "email" 		&& 
		   $field_type != "password" 	&& 
		   $field_type != "text" 		&& 
		   $field_type != "phone" 		&& 
		   $field_type != "select"		&&
		   $field_type != "upload"		&&
		   $field_type != "textarea"	&&
		   $field_type != "hidden"
		  ) {
			return _("This type of field is not supported yet.");
		}
		
		if($field_type == "select"): 
			//Generate Select Type
			$content = '<select class="form-control"';
			$content .= !empty($field_name) ? ' id="'.$field_name.'"': "";
			$content .= !empty($field_name) ? ' name="'.$field_name.'"': "";
			$content .= !empty($field_required) ? ' required': "";
			$content .= '>';
			
			if(empty($select_array)) {
				$content .= '<option value="">'._("No data in select options array").'</option>';
			} else {
				foreach($select_array as $option_field) {
					$option_value = isset($option_field["option_value"]) ? $option_field["option_value"]: "";
					$option_label = isset($option_field["option_label"]) ? $option_field["option_label"]: "";
					
					if(isset($option_field["selected"]) && $option_field["selected"] == TRUE) {
						$selected = "selected";
					} else {
						$selected = "";
					}
					
					$content .= '<option '.$selected.' value="'.$option_value.'">'.$option_label.'</option>';	
				}
			}
		
			$content .= '</select>';
		elseif($field_type == "textarea"):
			$content = '<textarea class="textarea form-control"';
			$content .= !empty($field_name) ? ' id="'.$field_name.'"': "";
			$content .= !empty($field_name) ? ' name="'.$field_name.'"': "";
			$content .= !empty($field_placeholder) ? ' placeholder="'.$field_placeholder.'"': "";
			$content .= !empty($field_required) ? ' required': "";
			$content .= '>';
			$content .= !empty($field_value) ? $field_value : "";
			$content .= '</textarea>';
		elseif($field_type == "upload"):
			$content = '<input class="form-control-file" type="file"';
			$content .= !empty($field_name) ? ' id="'.$field_name.'"': "";
			$content .= !empty($field_name) ? ' name="'.$field_name.'"': "";
			$content .= !empty($field_placeholder) ? ' placeholder="'.$field_placeholder.'"': "";
			$content .= !empty($field_required) ? ' required': "";
			$content .= !empty($field_value) ? ' value="'.$field_value.'"': "";
			$content .= '/>';
		else: 
			//All other types
			$content = '<input class="form-control"';
			$content .= !empty($field_type) ? ' type="'.$field_type.'"': " type='text'";
			$content .= !empty($field_name) ? ' id="'.$field_name.'"': "";
			$content .= !empty($field_name) ? ' name="'.$field_name.'"': "";
			$content .= !empty($field_placeholder) ? ' placeholder="'.$field_placeholder.'"': "";
			$content .= !empty($field_required) ? ' required': "";
			$content .= !empty($field_value) ? ' value="'.$field_value.'"': "";
			$content .= '/>';
		endif;
		
		return $content;
	}// Function ends.
	
	/**
	* Generate label
	*
	* Accetps for (like name of field), description, and title
	* @since 1.0.0
	*/
	function wc_generate_label($title, $description, $for) {
		if(empty($title)) {
			return _("Title is required for label");
		}
		
		$content = '<label';
		$content .= !empty($for) ? ' for="'.$for.'"': '';
		$content .= '>';
		$content .= $title;
		$content .= !empty($description) ? ' <small>'.$description.'</small>': '';
		$content .= '</label>';
		
		return $content;
	}
	

	/**
	* Generate hidden field
	*
	* Accepts Field name, and Value as hidden fields sends default value.
	* @since 1.0.0
	*/
	function wc_generate_hidden_field($name, $value) {
		if(empty($name) || empty($value)) {
			return _("Name and value for hidden fields cannot be empty.");
		}
		
		$content = '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
		
		return $content;
	}
	
	
	function wc_generate_submit_button($label, $classes) {
		
		$content = '<input type="submit"';
		$content .= empty($classes) ? ' class="btn btn-primary"': ' class="'.$classes.'"';
		$content .= empty($label) ? 'value="'. _("Submit") .'"': 'value="'.$label.'"';
		$content .= '>';
		
		return $content;
	}//Generate Submit btn ends

  
	/**
	* Returns the value from Database.
	* Takes Table, PRimary key, column name and return value.
	*/
	function wc_get_existing_value($table, $get_field, $row_key, $primary_column) {
		global $db;
		
		if(empty($table) || empty($get_field) && empty($row_key) && empty($primary_column)) {
			return _("Sorry cannot retrive the data some field sent was empty.");
		}
		
		$query = "SELECT * FROM `".$table."` WHERE `".$primary_column."`='".$row_key."'";
		$result = $db->query($query) or die($db->error);
		
		$row = $result->fetch_array();
		
		
		return $row[$get_field];
	}
	
	/**
	* Generate Form SELECT options array
	* require table name, label column, value column, default value
	*/
	function wc_generate_options_array($table_name, $value_column, $label_column, $select_title, $selected_value, $condition_column, $condition_value) {
		global $db;
		
		if(empty($table_name) && empty($value_column) && empty($label_column)) {
			return;
		}
		
		$query_type = 'simple';
		
		if(!isset($condition_column) && !isset($condition_value)) {
			$query_type = 'simple';
		} else {
			if(!empty($condition_column) && !empty($condition_value)) {
				$query_type = 'conditional';
			}
		}
		
		if($query_type == 'simple') {
			$query = "SELECT * FROM `".$table_name."`";
		} else {
			
			if(is_array($condition_column) && is_array($condition_value)) {
				
				$counter = 0;
				
				$condition = '';
				
				foreach($condition_column as $conditional_column) {
					if($counter > 0) {
						$condition .= ' AND ';
					}
					$condition .= '`'.$conditional_column.'` = "'.$condition_value[$counter].'"';	
					
					$counter++;
				} 
				$query = "SELECT * FROM `".$table_name."` WHERE ".$condition;
			} else {
				$query = "SELECT * FROM `".$table_name."` WHERE `".$condition_column."` = '".$condition_value."'";	
			}
		}
		
		$result = $db->query($query) or die($db->error);
		
		if(!isset($selected_value)) {
			$selected_value = "";
		}
		
		$content = array();
		
		$content[] = array(
						"option_value"	=> "",
						"option_label"	=> $select_title,
					);
		
		while($row = $result->fetch_array()) {
			
			if(is_array($label_column)) {
				$column_label = "";
				foreach($label_column as $column) {
					$column_label .= $row[$column]." ";
				}
			} else {
				$column_label = $row[$label_column];
			}
			
			$content_op["option_value"] 	= $row[$value_column];
			$content_op["option_label"] 	= $column_label;
			$content_op["selected"] 		= ($row[$value_column] == $selected_value) ? TRUE : FALSE;
			
			$content[] = $content_op;
		}
		
		return $content;
	}

}//class ends here.