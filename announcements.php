<?php
	require_once("lib/system_load.php");
	//This loads system.
	
	//user Authentication.
	authenticate_user('admin');
	
	//Delete note.
	if(isset($_POST['delete_announcement']) && $_POST['delete_announcement'] != '') { 
		$message = $announcement_obj->delete_announcement($_POST['delete_announcement']); 
	}//delete ends here.
		
	$page_title = _("Announcements"); //You can edit this to change your page title.
	require_once("lib/includes/header.php"); //including header file.
?>
<div class="text-right">
    <p>
    	<a href="manage_announcement.php" class="btn btn-primary btn-default"><?php _e("Add New"); ?></a>
    </p>
</div>	

<div class="row">
	<?php $announcement_obj->list_announcements(); ?>
</div>

<?php
	require_once("lib/includes/footer.php");