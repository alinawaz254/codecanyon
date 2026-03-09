<?php
//Notes Class

class Notes {
	public $note_title;
	public $note_detail;
	
	function set_note($note_id) { 
		global $db;
		$query = 'SELECT * from notes WHERE note_id="'.$note_id.'" AND user_id="'.$_SESSION['user_id'].'"';
		$result = $db->query($query) or die($db->error);
		$row = $result->fetch_array();
		$this->note_title = $row['note_title'];
		$this->note_detail = $row['note_detail'];
	}//level set ends here.
	
	function check_note_access($note_id, $user_id) {
		global $db;
		
		$query 	= "SELECT * FROM `notes` WHERE note_id='".$note_id."' AND user_id='".$user_id."'";
		$result = $db->query($query) or die($db->error);
		
		return $result->num_rows;
	}
	
	function update_note($note_id, $note_title, $note_detail) { 
		global $db;
		
		$note_title 	= $db->real_escape_string($note_title);
		$note_detail 	= $db->real_escape_string($note_detail);
		
		$query = "UPDATE notes SET
				  note_title = '".$note_title."',
				  note_detail = '".$note_detail."'
				   WHERE note_id='".$note_id."' AND user_id='".$_SESSION['user_id']."'";
		$result = $db->query($query) or die($db->error);
		return _("Note Updated");
	}//update user level ends here.
	
	
	function wc_add_note_form() {
		global $forms_obj;
		
		//Default Array for Form generation
		$form_values = array(
							'form-id'			=> 'note_form',
							'form-name'			=> 'wc-form',
							'form-classes' 		=> 'wc-note-form',
							'form-action'		=> 'self',
							'submit-label'		=> _('Add Note'),
							'submit-classes'	=> 'btn btn-primary',
							'insert'			=> TRUE,
							'update'			=> FALSE, //If set true, make sure INSERT is set False. And Update_key is present.
							'update_key'		=> '', 
							'database-table'	=> 'notes',
							'auto_submit_form'	=> TRUE,
							'form-fields'	=> array(
									array(
										'field-label'		=> _('Note Title')." *",
										'field-name'		=> 'note_title',
										'db_duplicate'		=> TRUE,
										'field-type'		=> 'text',
										'field-desc'		=> _('Your Note Title'),
										'field-placeholder'	=> _('Enter Note Title'),
										'field-required'	=> TRUE,
										'wrapper-classes'	=> 'note-wrapper',
										'wrapper-id'		=> 'note_wrapper',
										'field-columns'		=> '1',
										'field-value'		=> '',
									),
									array(
										'field-label'		=> _('Note Detail')." *",
										'field-name'		=> 'note_detail',
										'db_duplicate'		=> TRUE,
										'field-type'		=> 'textarea',
										'field-desc'		=> _('Your Note Detail'),
										'field-required'	=> FALSE,
										'wrapper-classes'	=> 'note-wrapper advance_editor',
										'wrapper-id'		=> 'note_wrapper',
										'field-columns'		=> '1',
										'field-value'		=> '',
									),
									array(
										'field-name'		=> 'note_date',
										'db_duplicate'		=> TRUE,
										'field-type'		=> 'hidden',
										'field-required'	=> TRUE,
										'field-value'		=> date("Y-m-d H:i:s"),
									)
							)
		);
		
		return $forms_obj->wc_form_generator($form_values);
	}
	
	function wc_update_note_form($note_id) {
		global $forms_obj;
		
		if(self::check_note_access($note_id, $_SESSION["user_id"]) == 0) {
			HEADER("LOCATION: notes.php?message="._("You are doing something you don't have access to do."));
			exit;
		} 
		
		//Default Array for Form generation
		$form_values = array(
							'form-id'			=> 'note_form',
							'form-name'			=> 'wc-form',
							'form-classes' 		=> 'wc-note-form',
							'form-action'		=> 'self',
							'submit-label'		=> _('Update Note'),
							'submit-classes'	=> 'btn btn-primary',
							'insert'			=> FALSE,
							'update'			=> TRUE, //If set true, make sure INSERT is set False. And Update_key is present.
							'update_key'		=> $note_id, 
							'database-table'	=> 'notes',
							'primary_column'	=> 'note_id',
							'auto_submit_form'	=> TRUE,
							'form-fields'	=> array(
									array(
										'field-label'		=> _('Note Title')." *",
										'field-name'		=> 'note_title',
										'db_duplicate'		=> TRUE,
										'field-type'		=> 'text',
										'field-desc'		=> _('Your Note Title'),
										'field-placeholder'	=> _('Enter Note Title'),
										'field-required'	=> TRUE,
										'wrapper-classes'	=> 'note-wrapper',
										'wrapper-id'		=> 'note_wrapper',
										'field-columns'		=> '1'
									),
									array(
										'field-label'		=> _('Note Detail')." *",
										'field-name'		=> 'note_detail',
										'db_duplicate'		=> TRUE,
										'field-type'		=> 'textarea',
										'field-desc'		=> _('Your Note Detail'),
										'field-required'	=> FALSE,
										'wrapper-classes'	=> 'note-wrapper advance_editor',
										'wrapper-id'		=> 'note_wrapper',
										'field-columns'		=> '1'
									),
									array(
										'field-name'		=> 'note_id',
										'db_duplicate'		=> TRUE,
										'field-type'		=> 'hidden',
										'field-required'	=> TRUE,
										'field-value'		=> time(),
									)
							)
		);
		
		return $forms_obj->wc_form_generator($form_values);
	} //End Function Update Make Form
	
	
	function list_notes() {
		global $db;
		
		$query = 'SELECT * from notes WHERE user_id="'.$_SESSION['user_id'].'" ORDER by note_id DESC';
		$result = $db->query($query) or die($db->error);
		
		$content = '';
		while($row = $result->fetch_array()) { 
		 	extract($row);
			
			$content .= '<div class="col-xl-4">
				<!-- Auto Hide Example -->
				<div class="widget has-shadow">
					<div class="widget-header bordered d-flex align-items-center">
						<h4>'.$note_title.'</h4>';
			$content .= '</div>
					<div class="widget-body">
						<div class="basic-scroll pr-2 pl-2" style="height:300px;">';
			$content .= '<p><strong>'._("Date").': </strong>'.$note_date.'</p>';						
			$content .= '<p>'.$note_detail.'</p> 
						</div>';
			$content .= '<div class="widget-options">';
			$content .= '<ul class="pager wizard text-right">
							<li class="previous d-inline-block disabled">
								<a href="?update_data=1&note_id='.$note_id.'" class="btn btn-primary btn-md btn-golden">'._("Edit Note").'</a>
							</li>
							<li class="next d-inline-block">';
			$content .= '<form method="post" name="delete" onsubmit="return confirm_delete();" action="">';
			$content .= '<input type="hidden" name="delete_note" value="'.$note_id.'">';
			$content .= '<input type="submit" class="btn btn-secondary ripple" value="'._("Delete").'">';
			$content .= '</form>';
			$content .= '</li>
						</ul>';
			$content .= '</div><div class="clearfix"></div>';
			$content .= '</div>
				</div>
				<!-- End Auto Hide Example -->
			</div>';
		 }//while loop ends here.
		 echo $content;
	}//list_notes ends here.
	
	function add_note($note_title, $note_detail) { 
		global $db;
		
		$note_title 	= $db->real_escape_string($note_title);
		$note_detail 	= $db->real_escape_string($note_detail);
		
		$query 			= 'INSERT into notes VALUES(NULL, "'.date("Y-m-d").'", "'.$note_title.'", "'.$note_detail.'", "'.$_SESSION['user_id'].'")';
		$result 		= $db->query($query) or die($db->error);
		return _("Note Added");
	}//add notes ends here.

	function delete_note($note_id) {
		global $db;

		$query = 'DELETE from notes WHERE user_id="'.$_SESSION['user_id'].'" AND note_id="'.$note_id.'"';
		$result = $db->query($query) or die($db->error);
		$message = _("Note Deleted");	
		return $message;
	}//delete level ends here.

	function notes_widget() {
		global $db;
		
		$query = 'SELECT * from notes WHERE user_id="'.$_SESSION['user_id'].'" ORDER by note_id DESC LIMIT 3';
		$result = $db->query($query) or die($db->error);
		$content = '';
		while($row = $result->fetch_array()) { 
			extract($row);

			$content .= '<li><a href="notes.php"><div class="message-body">';
			$content .= '<div class="message-body-heading">'.strip_tags($note_title).'</div>';
			$note_detail = (strlen($note_detail) > 103) ? substr($note_detail,0,60).'...' : $note_detail;;
			$note_detail = strip_tags($note_detail);
			$content .= "<p class='mb-0'>".$note_detail."</p>";
			$content .= '</div></a></li>';
		}//loop ends here.	
	echo $content;
	}//list_notes ends here.
	
	function notes_count() { 
		global $db;
		$query = "SELECT * from notes WHERE user_id='".$_SESSION['user_id']."'";
		$result = $db->query($query) or die($db->error);
		echo $result->num_rows;
	}//unread count ends here.

}//class ends here.