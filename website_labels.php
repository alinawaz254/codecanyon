 <?php
	require_once("lib/system_load.php");
	//Including this file we load system.
	
	//user Authentication.
	authenticate_user('admin');
	
	//Page display settings.
	$page_title = _("Website Labels"); //You can edit this to change your page title.
	require_once("lib/includes/header.php"); //including header file.
?>
<!-- Begin Row -->
<div class="row flex-row">
	<!-- Begin Widget -->
	<div class="col-xl-12 col-md-12 col-sm-12">
		<div class="widget widget-12 has-shadow">
			<div class="widget-body">
				<p><?php _e("Manage website labels or translation by translating login_script.pot into your own language going to root /locale directory. Then setup new language through General Settings"); ?></p>
			</div>
		</div>
	</div>
	<!-- End Widget -->
</div>
<?php
	require_once("lib/includes/footer.php");