<?php
	require_once("lib/system_load.php");
	//This loads system.
	
	//user Authentication.
	authenticate_user('admin');
	
	//updating Notes
	if(isset($_POST['update_announcement'])) { 
		extract($_POST);
		$message = $announcement_obj->update_announcement($edit_announcement, $announcement_title, $announcement_detail, $user_type, $announcement_status);
	}//update ends here.
	
	//setting level data if updating or editing.
	if(isset($_POST['edit_announcement'])) {
		$announcement_obj->set_announcement($_POST['edit_announcement']);	
	} //level set ends here
	
	//add user processing.
	if(isset($_POST['add_announcement']) && $_POST['add_announcement'] == '1') { 
		extract($_POST);
		if($announcement_title == '') { 
			$message = _("Title Required");
		} else if($announcement_detail == '') { 
			$message = _("Detail Required");
		}  else {
		$message = $announcement_obj->add_announcement($announcement_title, $announcement_detail, $user_type, $announcement_status);
		}
	}//add user processing ends here.
	
	if(isset($_POST['edit_announcement'])){ 
		$page_title = _("Edit Announcement"); 
	} else { 
		$page_title = _("Add Announcement");
	} //page title set.
	require_once("lib/includes/header.php"); //including header file.
	
?>
<div class="row flex-row">
	<div class="col-12">
		<!-- Form -->
		<div class="widget has-shadow">
			<div class="widget-body">
				<form action="<?php $_SERVER['PHP_SELF']?>" id="add_user" name="user" method="post" enctype="multipart/form-data" role="form">

					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-3 form-control-label d-flex justify-content-lg-end" for="announcement_title"><?php _e("Announcement Title"); ?>*</label>
						<div class="col-lg-6">
							<input type="text" class="form-control" name="announcement_title" id="announcement_title" required="required" placeholder="<?php _("Announcement Title"); ?>" value="<?php echo $announcement_obj->announcement_title; ?>" />
						</div>
					</div>

					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-3 form-control-label d-flex justify-content-lg-end" for="announcement_detail"><?php _e("Announcement Detail"); ?>*</label>
						<div class="col-lg-6">
							<textarea name="announcement_detail" class="tinyst form-control" id="announcement_detail" placeholder="<?php _e("Announcement Detail"); ?>"><?php echo $announcement_obj->announcement_detail; ?></textarea>
						</div>
					</div>

					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-3 form-control-label d-flex justify-content-lg-end" for="user_type"><?php _e("User Type"); ?></label>
						<div class="col-lg-6">
							<select name="user_type" class="custom-select form-control" id="user_type">
								<option <?php if($announcement_obj->user_type == 'all'){echo 'selected="selected"';} ?> value="all">
									<?php _e("All users"); ?>
								</option>
								<option <?php if($announcement_obj->user_type == 'admin'){echo 'selected="selected"';} ?> value="admin">
									<?php _e("Admin"); ?>
								</option>
								<?php $new_level->userlevel_options($announcement_obj->user_type); ?>	                            
							</select>                 
						</div>
					</div>

					<div class="form-group row d-flex align-items-center mb-5">
						<label class="col-lg-3 form-control-label d-flex justify-content-lg-end" for="announcement_status"><?php _e("Announcement Status"); ?></label>
						<div class="col-lg-6">
							<select name="announcement_status" class="custom-select form-control" id="announcement_status">
								<option <?php if($announcement_obj->announcement_status == 'active'){echo 'selected="selected"';} ?> value="active">
									<?php _e("Activate"); ?>
								</option>
								<option <?php if($announcement_obj->announcement_status == 'deactive'){echo 'selected="selected"';} ?> value="deactive">
									<?php _e("Deactivate"); ?>
								</option>
							</select>                 
						</div>
					</div>

					<?php 
					if(isset($_POST['edit_announcement'])){ 
						echo '<input type="hidden" name="edit_announcement" value="'.$_POST['edit_announcement'].'" />';
						echo '<input type="hidden" name="update_announcement" value="1" />'; 
					} else { 
						echo '<input type="hidden" name="add_announcement" value="1" />';
					} ?>
					<div class="text-center">
						<input type="submit" value="<?php if(isset($_POST['edit_announcement'])){ _e("Update Announcement"); } else { _e("Add Announcement"); } ?>" class="btn btn-primary btn-md btn-golden" />
					</div>

				</form>
			</div><!-- Widget body /-->
		</div><!-- Widget /-->
	</div><!-- Column /-->
</div><!-- Row /-->

<?php
	require_once("lib/includes/footer.php");