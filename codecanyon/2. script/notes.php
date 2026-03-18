<?php
	require_once("lib/system_load.php");
	//This loads system.

	//user Authentication.
	authenticate_user('all');
	
	//Delete note.
	if(isset($_POST['delete_note']) && $_POST['delete_note'] != '') { 
		$message = $notes_obj->delete_note($_POST['delete_note']); 
	}//delete ends here.
		
	$page_title = _("My Notes");; //You can edit this to change your page title.
	require_once("lib/includes/header.php"); //including header file.
?>
	<div class="text-right">
		<p>
			<?php if(!isset($_GET["update_data"])): ?>
			<a class="btn btn-primary btn-default" tabindex="-1" data-toggle="modal" data-target=".manage-notes">
				<?php 
					_e("Add Note");
				?>
			</a>
			<?php else: ?>
				<a href="notes.php" class="btn btn-info"><?php _e("Go Back"); ?></a>	
			<?php endif; ?>
		</p>
	</div>

	<!-- Form Modal Make /-->
	<div class="modal fade manage-notes" tabindex="-1" id="target_edit_add_form" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">

				<div class="modal-header">
					<h5 class="modal-title">
						<?php
							$form_title = _("Add Note");

							isset($_GET['update_data']) ? $form_title = _("Update Note"): $form_title;

							echo  $form_title;
						?>
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div><!-- Modal Header ends /-->

				<div class="modal-body">
					<div class="form-message"></div>
					<?php 
						$generate_form = !isset($_GET['update_data']) ? 
										 $notes_obj->wc_add_note_form(): 
										 $notes_obj->wc_update_note_form($_GET["note_id"]); 

						echo $generate_form;
					?>
				</div><!-- Modal Body /-->

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php _e("Close"); ?></button>
				</div><!-- Modal Footer /-->

			</div>
		</div>
	</div>
	<!-- Form Modal Make ends /-->

	<div class="mywidget wc_data" id="wc_data">
		<div class="row">
			<?php $notes_obj->list_notes(); ?>
		</div><!-- Row /-->
	</div>

<?php
	require_once("lib/includes/footer.php");