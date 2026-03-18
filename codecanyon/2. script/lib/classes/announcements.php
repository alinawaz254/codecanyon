<?php
//Announcements Class

class Announcements {
	public $announcement_title;
	public $announcement_detail;
	public $user_type;
	public $announcement_status;
	
	function set_announcement($announcement_id) { 
		global $db;
		$query 		= 'SELECT * from announcements WHERE announcement_id="'.$announcement_id.'"';
		$result 	= $db->query($query) or die($db->error);
		$row 		= $result->fetch_array();
		
		$this->announcement_title 	= $row['announcement_title'];
		$this->announcement_detail 	= $row['announcement_detail'];
		$this->user_type 			= $row['user_type'];
		$this->announcement_status 	= $row['announcement_status'];
	}//level set ends here.
	
	function get_latest_announcement() {
		global $db;
		$query = "SELECT * from announcements WHERE announcement_status='active' ORDER by announcement_id DESC";
		$result = $db->query($query) or die($db->error);
		
		$content = '';

		while($row = $result->fetch_array()) { 
			
			if($row['user_type'] == 'all' || $row['user_type'] == $_SESSION['user_type']) { 
					$announcement_title = (empty($row['announcement_title'])) ? '': '<div class="title"><h2>'.$row['announcement_title'].'</h2></div>';
					//echo $content;
					$content .= '<div class="row "><div class="col-xl-12 col-md-12 col-sm-12">
						<div class="widget widget-12 has-shadow alert alert-secondary-bordered alert-lg square fade show">
							<div class="widget-body">
								<div class="media">
									<div class="media-body align-self-center">
										'.$announcement_title.'
										<p>'.$row['announcement_detail'].'</p>
									</div>
								</div><form action="" method="post">
								<input type="hidden" name="active_notification" value="No" />
								<input type="submit" class="close" value="x" />
							</form>
							</div>
						</div>
					</div></div>';		

				echo $content;
				//exit();
			}
		}
	}
	function update_announcement($announcement_id, $announcement_title, $announcement_detail, $user_type, $announcement_status) { 
		global $db;
		
		$announcement_title 	= $db->real_escape_string($announcement_title);
		$announcement_detail 	= $db->real_escape_string($announcement_detail);
		
		$query = 'UPDATE announcements SET
				  announcement_title = "'.$announcement_title.'",
				  announcement_detail = "'.$announcement_detail.'",
				  user_type = "'.$user_type.'",
				  announcement_status = "'.$announcement_status.'"
				WHERE announcement_id="'.$announcement_id.'"';
		$result = $db->query($query) or die($db->error);
		return _("Announcement Updated");
	}//update user level ends here.
	
	function list_announcements() {
		global $db;

		$query = 'SELECT * from announcements ORDER by announcement_id DESC';
		$result = $db->query($query) or die($db->error);
		
		$content = '';
		while($row = $result->fetch_array()) { 
		 	extract($row);

			$content .= '<div class="col-xl-12">';
			$content .= '<div class="widget has-shadow">';
			$content .= '<div class="widget-header bordered no-actions d-flex align-items-center">';
			$content .= '<h4>'.$announcement_title.'</h4>';
			$content .= '</div>';
			$content .= '<div class="widget-body">';
			$content .= '<div class="basic-scroll pr-2 pl-2">';
			$content .= '<p><strong>Date: </strong>'.$announcement_date.'<strong> User type: </strong>'.$user_type.' <strong> Status: </strong>'.$announcement_status.'</p>';
			$content .= $announcement_detail;
			$content .= '</div>';

			$content .= '<div class="widget-options"><ul class="pager wizard text-right">
							<li class="previous d-inline-block disabled">';
			$content .= '<form method="post" name="edit" action="manage_announcement.php">';
			$content .= '<input type="hidden" name="edit_announcement" value="'.$announcement_id.'">';
			$content .= '<input type="submit" class="btn btn-default btn-sm pull-left" value="'._("Edit").'">';
			$content .= '</form>';
			$content .= '</li>
						<li class="next d-inline-block">';
			$content .= '<form method="post" name="delete" onsubmit="return confirm_delete();" action="">';
			$content .= '<input type="hidden" name="delete_announcement" value="'.$announcement_id.'">';
			$content .= '<input type="submit" class="btn btn-default btn-sm pull-right" value="'._("Delete").'">';
			$content .= '</form>';							
			$content .= '</li>
						</ul></div><div class="clearfix"></div></div>';	

			$content .= '</div>';
			$content .= '</div>';
		
		 }//while loop ends here.
		 echo $content;
	}//list_notes ends here.
	
	function announcement_widget() {
		global $db;
		
		$query 		= 'SELECT * from announcements ORDER by announcement_id DESC LIMIT 3';
		$result 	= $db->query($query) or die($db->error);
		
		$content = '<hr>';
		while($row = $result->fetch_array()) { 
		 	extract($row);
  			$content .= '<li><div class="dash-comment-entry clearfix"><div class="dash-comment">';
			$content .= '<h3>'.$announcement_title.'</h3><p>'.$announcement_detail.'</p>';
			$content .= '</div></div></li>';
											
		 }//while loop ends here.
		 echo $content;
	}//list_notes ends here.
	
	function add_announcement($announcement_title, $announcement_detail, $user_type, $announcement_status) { 
		global $db;
		
		$announcement_title 	= $db->real_escape_string($announcement_title);
		$announcement_detail 	= $db->real_escape_string($announcement_detail);
		
		$query = 'INSERT into announcements VALUES(NULL, "'.date("Y-m-d").'", "'.$announcement_title.'", "'.$announcement_detail.'", "'.$user_type.'", "'.$announcement_status.'")';
		$result = $db->query($query) or die($db->error);
		return _("Announcement Added");
	}//add notes ends here.

	function delete_announcement($announcement_id) {
		global $db;
	
		$query = 'DELETE from announcements WHERE announcement_id="'.$announcement_id.'"';
		$result = $db->query($query) or die($db->error);
		$message = _("Announcement Deleted");	
		return $message;
	}//delete level ends here.
}//class ends here.