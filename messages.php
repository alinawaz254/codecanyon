<?php
	require_once("lib/system_load.php");
	//This loads system.

	//user Authentication.
	authenticate_user('all');
	$datatables = 1;

	if(isset($_POST['reply_form']) && $_POST['reply_form'] == '1') {
		extract($_POST);
		if($reply_detail != '') {
			$message = $message_obj->send_reply($reply_to, $subject_id, $reply_detail);	
		} else { 
			$message = _("Message is empty!");
		}
	}
	
	if(isset($_GET['action']) && $_GET['action'] == 'clear_all') {
		$message = $message_obj->clear_all_messages();
	}

	if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['subject_id'])) {
		$message = $message_obj->delete_message($_GET['subject_id']);
	}
	
	$page_title = _("My Messages"); //You can edit this to change your page title.
	$sub_title 	= _("Manage Your Messages");;
	require_once("lib/includes/header.php"); //including header file.
?>

<div class="row">
    <div class="col-xl-2">
        <!-- Sorting -->
        <div class="widget has-shadow">
            <div class="widget-body">
                <div class="">
					<button class="btn btn-primary btn-md btn-golden" data-toggle="modal" data-target="#new_message" style="width:100%"><?php _e("New Message"); ?></button>
					<!-- Modal -->
					<script type="text/javascript">
						jQuery(function($) {
							$('form[data-async]').on('submit', function(event) {
							var $form = $(this);
							var $target = $($form.attr('data-target'));

							tinyMCE.triggerSave();

							$.ajax({
							type: $form.attr('method'),
							url: 'lib/includes/messageprocess.php',
							data: $form.serialize(),

							success: function(data, status) {
								$('#success_message').html('<div class="alert alert-success">'+data+'</div>');
							}
								});
							event.preventDefault();
								});
						});
					</script>				
					<div class="modal fade" id="new_message" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="myModalLabel"><?php _e("Send Message"); ?></h4>
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						</div>
						<div class="modal-body">
								<div id="success_message"></div>
							<form class="form-horizontal" data-async data-target="#rating-modal" method="POST" enctype="multipart/form-data" role="form">
								<div class="form-group">
									<label class="control-label"><?php _e("Message To"); ?> <small><?php _e("Email or Username"); ?></small></label>
									<input type="text" class="form-control" id="message_to" name="message_to" required="required" value="" />
								</div>
								
								<div class="form-group">
									<label class="control-label"><?php _e("Subject"); ?></label>
									<input type="text" class="form-control" name="subject" required="required" value="" />
								</div>
								
								<div class="form-group">
									<label class="control-label"><?php _e("Message"); ?></label>
									<textarea name="message" class="tinyst form-control"></textarea>
								</div>
								<input type="hidden" name="from" value="'.<?php echo $_SESSION['user_id']; ?>.'" />
								<input type="hidden" name="form_type" value="new_message" />
							
								<input type="submit" id="submit" value="<?php _e("Send Message"); ?>" class="btn btn-primary" />
							</form>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?php _e("Close"); ?></button>
						</div>
						</div><!-- /.modal-content -->
					</div><!-- /.modal-dialog -->
					</div><!-- /.modal -->
					<hr />
					<a href="messages.php" 
					class="btn btn-md btn-golden <?php echo isset($_GET['type']) ? 'btn-default' : 'btn-primary'; ?>" 
					style="width:100%">
						<?php _e("Inbox"); ?>
					</a>
					<div style="height:7px;"></div>
					<a href="messages.php?action=clear_all" class="btn btn-md btn-golden btn-primary" style="width:100%" onclick="return confirm('<?php _e("Are you sure you want to clear all messages?"); ?>')">
						<?php _e("Clear All"); ?>
					</a>
					<div style="height:7px;"></div>
					<a href="messages.php?type=sent" class="btn btn-<?php if(isset($_GET['type'])){echo'primary';}else{echo'default';}?>" style="width:100%">
						<?php _e("Sent"); ?>
					</a>
				</div><!-- table responsive /-->
			</div><!-- Widget body /-->
		</div><!-- Widget /-->
	</div><!-- column /-->

	<div class="col-xl-10">
        <!-- Sorting -->
        <div class="widget has-shadow">
            <div class="widget-body">
                <div class="table-responsive">
				<?php if(isset($_GET['thread_id']) && $_GET['thread_id'] != '') : ?>
					<div class="thread-wrapper">
						<?php $message_obj->list_thread($_GET['thread_id']); ?>
					</div>
					<?php else: ?>

					<table id="sorting-messages" class="table mb-0">
						<thead>
							<tr>
								<th><span class="hide"><?php _e("Pic"); ?></span></th>
								<th><span class="hide"><?php _e("Message From"); ?></span></th>
								<th><span class="hide"><?php _e("Message"); ?></span></th>
								<th><?php _e("Action"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php 
							if(isset($_GET['type']) && $_GET['type'] == 'sent') { 
								$message_obj->list_sent();
							} else { 
								$message_obj->list_inbox();
							}
							?>
						</tbody>
					</table>
					<?php endif; ?>
				</div><!-- table responsive /-->
			</div><!-- Widget body /-->
		</div><!-- Widget /-->
	</div><!-- column /-->
</div><!-- Row /-->
<?php
	require_once("lib/includes/footer.php");