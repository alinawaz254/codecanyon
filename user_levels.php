<?php
	require_once("lib/system_load.php");
	//This loads system.
	
	//user Authentication.
	authenticate_user('admin');
	
  $datatables = 1;

	//Delete user level.
	if(isset($_POST['delete_level']) && $_POST['delete_level'] != '') { 
		$message = $new_level->delete_level($_SESSION['user_type'], $_POST['delete_level']);
	}//delete level ends here.
	
	$page_title = _("Manage User Levels"); //You can edit this to change your page title.
	require_once("lib/includes/header.php"); //including header file.

?>
<div class="text-right">
    <p>
    	<a 
      href="manage_user_level.php" 
      class="btn btn-primary btn-md btn-golden"><?php _e("Add New"); ?></a>
    </p>
</div>

<div class="row">
    <div class="col-xl-12">
        <!-- Sorting -->
        <div class="widget has-shadow">
            <div class="widget-body">
                <div class="table-responsive">
                    <table id="sorting-table" class="table mb-0">
                        <thead>
                          <tr>
                              <th><?php _e("ID"); ?></th>
                              <th><?php _e("Level Name"); ?></th>
                              <th><?php _e("Level Description"); ?></th>
                              <th><?php _e("Level Page Name"); ?></th>
                              <th><?php _e("Message"); ?></th>
                              <th class="sorting_disabled"><?php _e("Actions"); ?></th>
                          </tr>
                        </thead>
                        <tbody>
                        	<tr>
                            	<td>0</td>
                                <td>admin</td>
                                <td><?php _e("Default Admins Level"); ?></td>
                                <td>dashboard.php</td>
                                <td><button class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal_admin">
  							<?php _e("Message"); ?>
							</button></td>
<script type="text/javascript">
$(function(){
  $("#message_form_admin").on("submit", function(e){
    e.preventDefault();
    tinyMCE.triggerSave();
    $.post("lib/includes/messageprocess.php", 
    $("#message_form_admin").serialize(), 
    function(data, status, xhr){
      $("#success_message_admin").html("<div class='alert alert-success'>"+data+"</div>");
    });
  });
});
</script>				
  <div class="modal fade" id="modal_admin" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="message_form_admin" method="post" name="send_message">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel"><?php _e("Send Message"); ?></h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>  
          </div>

          <div class="modal-body">
            <div id="success_message_admin"></div>
            <div class="form-group">
              <label class="control-label"><?php _e("Message To"); ?></label>
              <input type="text" class="form-control" name="message_to" value="<?php _e("All Admin Users"); ?>" readonly />
            </div>

            <div class="form-group">
              <label class="control-label"><?php _e("Subject"); ?></label>
              <input type="text" class="form-control" name="subject" value="" />
            </div>

            <div class="form-group">
              <label class="control-label"><?php _e("Message"); ?></label>
              <textarea class="form-control tinyst" name="message"></textarea>
            </div>
          </div>
          <input type="hidden" name="level_name" value="admin" />
          <input type="hidden" name="level_form" value="1" />
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e("Close"); ?></button>
            <input type="submit" value="<?php _e("Send Message"); ?>" class="btn btn-primary" />
          </div>
        </div><!-- /.modal-content -->
      </form>
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
                                <td>&nbsp;</td>
                            </tr>
                            <?php $new_level->list_levels($_SESSION['user_type']); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- End Sorting -->
    </div>
</div><!-- Row /-->
<?php
	require_once("lib/includes/footer.php");