<?php
//user levels Class

class Userlevel {
	public $level_name;
	public $level_description;
	public $level_page;
	
	function get_userlevel_info($levelname, $term) { 
		global $db;
		$query = "SELECT * from user_level WHERE level_name='".$levelname."'";
		$result = $db->query($query) or die($db->error);
		$row = $result->fetch_array();
		return $row[$term];
	}//get user email ends here.
	
	function set_level($level_id) { 
		global $db;
		$query = 'SELECT * from user_level WHERE level_id="'.$level_id.'"';
		$result = $db->query($query) or die($db->error);
		$row = $result->fetch_array();
		$this->level_name = $row['level_name'];
		$this->level_description = $row['level_description'];
		$this->level_page = $row['level_page'];
	}//level set ends here.
	
	function update_user_level($level_id, $level_name, $level_description, $level_page) { 
		global $db;
		
		$level_name 		= $db->real_escape_string($level_name);
		$level_description 	= $db->real_escape_string($level_description);
		
		$level_page 		= stripslashes($level_page);
		
		$query = 'UPDATE user_level SET
				  level_name = "'.$level_name.'",
				  level_description = "'.$level_description.'",
				  level_page = "'.$level_page.'"
				   WHERE level_id="'.$level_id.'"';
		$result = $db->query($query) or die($db->error);
		return _("User level updated");
	}//update user level ends here.
	
	function add_user_level($level_name, $level_description, $level_page) { 
		global $db;
		
		//checking if level already exist.
		$query = "SELECT * from user_level WHERE level_name='".$level_name."'";
		$result = $db->query($query) or die($db->error);
		$num_rows = $result->num_rows;
		
		if($num_rows > 0) { 
			$message = _("User level cannot be added");
		} else { 
			$level_page = stripslashes($level_page);
			
			$level_name 		= $db->real_escape_string($level_name);
			$level_description 	= $db->real_escape_string($level_description);
			
			$query = "INSERT into user_level VALUES(NULL, '".$level_name."', '".$level_description."', '".$level_page."')";
			$result = $db->query($query) or die($db->error);
			$message = _("Level added successfuly");
		}
		return $message;
	}//add_user_level ends here.
	
	function list_levels($user_type) {
		global $db;
		
		if($user_type == 'admin') { 
			$query = 'SELECT * from user_level ORDER by level_name ASC';
			$result = $db->query($query) or die($db->error);
			$content = '';
			$count = 0;
			while($row = $result->fetch_array()) { 
				extract($row);
				$count++;
				if($count % 2 == 0) { 
					$class = 'even';
				} else { 
					$class = 'odd';
				}
				$content .= '<tr class="'.$class.'">';
				$content .= '<td>';
				$content .= $level_id;
				$content .= '</td><td>';
				$content .= $level_name;
				$content .= '</td><td>';
				$content .= $level_description;
				$content .= '</td><td>';
				$content .= $level_page;
				$content .= '</td><td>';
				$content .= '<button class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal_'.$level_id.'">
  							'._("Message").'
							</button>';
				$content .= '<!-- Modal -->
<script type="text/javascript">
$(function(){
$("#message_form_'.$level_id.'").on("submit", function(e){
  e.preventDefault();
  tinyMCE.triggerSave();
  $.post("lib/includes/messageprocess.php", 
	 $("#message_form_'.$level_id.'").serialize(), 
	 function(data, status, xhr){
	   $("#success_message_'.$level_id.'").html("<div class=\'alert alert-success\'>"+data+"</div>");
	 });
});
});
</script>				
<div class="modal fade" id="modal_'.$level_id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="message_form_'.$level_id.'" method="post" name="send_message">
	<div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="myModalLabel">'._("Send Message").'</h4>
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      </div>
	  
      <div class="modal-body">
      		<div id="success_message_'.$level_id.'"></div>
	   		<div class="form-group">
				<label class="control-label">'._("To").'</label>
				<input type="text" class="form-control" name="message_to" value="All '.$level_name.'" readonly="readonly" />
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
	  <input type="hidden" name="level_name" value="'.$level_name.'" />
	  <input type="hidden" name="level_form" value="1" />
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">'._("Close").'</button>
		<input type="submit" value="'._("Send Message").'" class="btn btn-primary" />
      </div>
    </div><!-- /.modal-content -->
   </form>
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->';			
				$content .= '</td><td class="td-actions">';
				$content .= '<form method="post" name="edit" class="inlineblockform" action="manage_user_level.php">';
				$content .= '<input type="hidden" name="edit_level" value="'.$level_id.'">';
				$content .= '<button class="btn btn-default btn-sm"><i class="la la-edit edit"></i></button>';
				$content .= '</form>';
				$content .= '<form method="post" name="delete" class="inlineblockform" onsubmit="return confirm_delete();" action="">';
				$content .= '<button class="btn btn-default btn-sm"><i class="la la-trash delete"></i></button>';
				$content .= '<input type="hidden" name="delete_level" value="'.$level_id.'">';
				$content .= '</form>';
				$content .= '</td>';
				$content .= '</tr>';
				unset($class);
			}//loop ends here.
			
		} else { 
			$content = _("You cannot view list of levels.");
		}	
		echo $content;
	}//list_levels ends here.
	
	function delete_level($user_type, $level_id) {
		global $db;
		
		if($user_type == 'admin') {
			$query = 'DELETE from user_level WHERE level_id="'.$level_id.'"';
			$result = $db->query($query) or die($db->error);
			$message = _("User level deleted");	
		} else { 
			$message = _("User level cannot delete");
		}	
		return $message;
	}//delete level ends here.
	
	function userlevel_options($user_type) {
		global $db;
		$query = 'SELECT * from user_level ORDER by level_name ASC';
		$result = $db->query($query) or die($db->error);
		$options = '';
		if($user_type != '') { 
			while($row = $result->fetch_array()) { 
				if($user_type == $row['level_name']) {
				$options .= '<option selected="selected" value="'.$row['level_name'].'">'.ucfirst($row['level_name']).'</option>';
				} else { 
				$options .= '<option value="'.$row['level_name'].'">'.ucfirst($row['level_name']).'</option>';
				}
			}
		} else { 
			while($row = $result->fetch_array()) { 
				$options .= '<option value="'.$row['level_name'].'">'.ucfirst($row['level_name']).'</option>';
			}
		}
		echo $options;	
	}//return user level options for select
	
	function get_level_info() { 
		global $db;

		if($_SESSION['user_type'] == 'admin') { 
				$query = "SELECT * from users WHERE user_type='admin'";
				$result = $db->query($query) or die($db->error);
				$num_rows = $result->num_rows;
				
				$table = '<div class="col-xl-3 col-md-6 col-sm-6">
					<div class="widget widget-12 has-shadow">
						<div class="widget-body">
							<div class="media">
								<div class="align-self-center ml-5 mr-5">
									<i class="la la-archive"></i>
								</div>
								<div class="media-body align-self-center">
									<div class="title">'._("Admin").'</div>
									<div class="number">'.$num_rows.' '._("Users").'</div>
								</div>
							</div>
						</div>
						<div class="dash-lower">
							<span>'._("Redirects to").'</span>
							<strong>dashboard.php</strong>
						</div>
					</div>
				</div>';

				
			$query = "SELECT * from user_level ORDER by level_name ASC";
			$result = $db->query($query) or die($db->error);
			
			while($row = $result->fetch_array()) { 
				$query_users = "SELECT * from users WHERE user_type='".$row['level_name']."'";
				$result_users = $db->query($query_users) or die($db->error);
				$num_rows = $result_users->num_rows;
				
				$table .= '<div class="col-xl-3 col-md-6 col-sm-6">
					<div class="widget widget-12 has-shadow">
						<div class="widget-body">
							<div class="media">
								<div class="align-self-center ml-5 mr-5">
									<i class="la la-archive"></i>
								</div>
								<div class="media-body align-self-center">
									<div class="title">'.ucfirst($row['level_name']).'</div>
									<div class="number">'.$num_rows.' '._("Users").'</div>
								</div>
							</div>
						</div>
						<div class="dash-lower">
							<span>'._("Redirects to").'</span>
							<strong>'.$row['level_page'].'</strong>
						</div>
					</div>
				</div>';
			}
			echo $table;
		} else { 
			echo _e("Cannot view this list");
		}
	}//get user level info ends here.
}//class ends here.