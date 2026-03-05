<?php
	require_once("lib/system_load.php");
	//This loads system.

  $datatables = 1;

	//user Authentication.
	authenticate_user('admin');
	
	//Delete user.
	if(isset($_POST['delete_user']) && $_POST['delete_user'] != '') { 
		$message = $new_user->delete_user($_SESSION['user_type'], $_POST['delete_user']); 
	}//delete ends here.
		
	$page_title = _("Manage Users"); //You can edit this to change your page title.
	 require_once("lib/includes/header.php"); //including header file.
?>
<div class="text-right">
    <p>
      <a href="manage_users.php" 
      class="btn btn-primary btn-default"><?php _e("Add New"); ?></a> 
      <a href="#" 
      class="btn btn-primary btn-default" 
      data-toggle="modal" data-target="#modal_all_user">
      <?php _e("Message to All Users"); ?></a><div class="clearfix"></div>
    </p>
    
    <script type="text/javascript">
    $(function(){
      $("#message_all_user").on("submit", function(e){
        e.preventDefault();
        tinyMCE.triggerSave();
        $.post("lib/includes/messageprocess.php", 
        $("#message_all_user").serialize(), 
        function(data, status, xhr){
          $("#success_message_admin").html("<div class='alert alert-success'>"+data+"</div>");
        });
      });
    });
    </script>
</div>
    <!-- Modal -->
    <div class="modal fade" id="modal_all_user" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form id="message_all_user" method="post" name="send_message">
    <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="myModalLabel"><?php _e("Send Message"); ?></h4>
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        </div>
      
        <div class="modal-body">
            <div id="success_message_admin"></div>
          <div class="form-group">
          <label class="control-label"><?php _e("Message To"); ?></label>
          <input type="text" class="form-control" name="message_to" value="<?php _e("Message All Users"); ?>" readonly />
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
        <input type="hidden" name="all_users" value="1" />
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e("Close"); ?></button>
      <input type="submit" value="<?php _e("Send Message"); ?>" class="btn btn-primary" />
        </div>
      </div><!-- /.modal-content -->
    </form>
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->

<div class="row">
  <div class="col-xl-12">
      <!-- Sorting -->
      <div class="widget has-shadow">
          <div class="widget-body">
              <div class="table-responsive">
                  <table id="export-table" class="table mb-0">
                    <thead>
                      <tr>
                          <th><?php _e("Full Name"); ?></th>
                          <th><?php _e("Location"); ?></th>
                          <th><?php _e("Username"); ?></th>
                          <th><?php _e("Email"); ?></th>
                          <th><?php _e("Referal Ids"); ?></th>
                          <th><?php _e("Status"); ?></th>
                          <th><?php _e("User Type"); ?></th>
                          <th><?php _e("Last Seen"); ?></th>
                          <th style="min-width:197px;"><?php _e("Action"); ?></th>
                      </tr>
                    </thead>

                    <tbody>
                        <?php echo $new_user->list_users($_SESSION['user_type']); ?>
                    </tbody>
                  </table>
              </div><!-- Table responsive /-->
          </div><!-- Widget body /-->
      </div><!-- widget /-->
  </div><!-- column /-->
</div><!-- Row /-->

<?php
	require_once("lib/includes/footer.php");