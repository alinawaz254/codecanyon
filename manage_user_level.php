<?php
	require_once("lib/system_load.php");
	//This loads system.

	//user Authentication.
	authenticate_user('admin');


	if(isset($_POST['update_level'])) { 
		extract($_POST);
		$message = $new_level->update_user_level($edit_level,$level_name, $level_description, $level_page);
	}//update ends here.

	//setting level data if updating or editing.
	if(isset($_POST['edit_level'])) {
		$new_level->set_level($_POST['edit_level']);	
	} //level set ends here

	if(isset($_POST['add_level'])) {
		$add_level = $_POST['add_level'];
		if($add_level == '1') { 
			extract($_POST);
			$message = $new_level->add_user_level($level_name, $level_description, $level_page);
		}
	}//isset add level

	$page_title = (isset($_POST['edit_level'])) ? _("Edit User Level") : _("Add User Level");

	require_once("lib/includes/header.php"); //including header file.
?>
<div class="row flex-row">
	<div class="col-12">
		<!-- Form -->
		<div class="widget has-shadow">
			<div class="widget-body">

			<form action="<?php $_SERVER['PHP_SELF']?>" id="add_level" name="level" method="post">
				<div class="form-group row d-flex align-items-center mb-5">
					<label class="col-lg-3 form-control-label d-flex justify-content-lg-end" for="level_name"><?php _e("Level Name"); ?>*</label>
					<div class="col-lg-6">
						<input type="text" class="form-control" name="level_name" id="level_name" placeholder="<?php _e("Level Name"); ?>" value="<?php echo $new_level->level_name; ?>" required="required" />
					</div>
				</div>
                      
				<div class="form-group row d-flex align-items-center mb-5">
					<label class="col-lg-3 form-control-label d-flex justify-content-lg-end" for="leve_description"><?php _e("Level Description"); ?></label>
					<div class="col-lg-6">
						<textarea class="form-control" placeholder="<?php _e("Level Description"); ?>" id="leve_description" name="level_description"><?php echo $new_level->level_description; ?></textarea>
					</div>
				</div>
                      
				<div class="form-group row d-flex align-items-center mb-5">
					<label class="col-lg-3 form-control-label d-flex justify-content-lg-end" for="page_name"><?php _e("Page Name"); ?>* <br /><small><?php _e("This would be default page where user of stated level redirected after login."); ?></small></label>
					<div class="col-lg-6">
						<input type="text" name="level_page" class="form-control" id="page_name" value="<?php echo $new_level->level_page; ?>" placeholder="<?php _e("User Level Page"); ?>" required="required" /><br /><small><?php _e("e.g manager.php this would be default page for user types of entered name. You can use ../manager.php if your file is one directory back of this script."); ?></small>
					</div>
				</div>

				<div class="form-group row d-flex align-items-center mb-5">
					<label class="col-lg-3 form-control-label d-flex justify-content-lg-end"><?php _e("How to use user levels."); ?></label>
					<div class="col-lg-6">
				<small><?php _e("To make a page password secure and accessable by entered level name users let's say you created user level manager and setup default page manager.php. Now you want to secure manager_2.php with password but manager user level users can access and admins we need to put."); ?> 
<pre class="code">
&lt;?php
	include('system_load.php'); //<?php _e("Please make sure this file loads properly."); ?>
	
    <?php _e("//This loads system."); ?>
    
    authenticate_user('manager');
?&gt;		
</pre>
<?php _e("PHP code in start of manager.php and manager_2.php Both files will need login and user level manager."); ?></small>
					</div>
				</div>
				<?php 
				if(isset($_POST['edit_level'])){ 
					echo '<input type="hidden" name="edit_level" value="'.$_POST['edit_level'].'" />';
					echo '<input type="hidden" name="update_level" value="1" />'; 
				} else { 
					echo '<input type="hidden" name="add_level" value="1" />';
				} ?>
				<div class="text-center">
					<input type="submit" class="btn btn-primary btn-md btn-golden" value="<?php if(isset($_POST['edit_level'])){ _e("Update Level"); } else { _e("Add Level"); } ?>" />
				</div>
			</form>
			</div><!-- widget body /-->
		</div><!-- Widget /-->
	</div><!-- Column /-->
</div><!-- row /-->
                  
<?php
	require_once("lib/includes/footer.php");