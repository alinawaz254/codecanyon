<?php
	require_once("lib/system_load.php");
	//Including this file we load system.

	/*
	Logout function if called.
	*/
	if(isset($_GET['logout']) && $_GET['logout'] == 1) { 
		session_destroy();
		setcookie("loginCredentials", "", time() - 2592000);
		
		if(get_option('google_login') == '1') {
			session_start();
			$_SESSION["wc_google_logout"]	= 'confirm';	
		}
		
		HEADER('LOCATION: '.get_option('redirect_on_logout').'?logmeout=1');
		exit();
	} //Logout done.
	
	//user Authentication.
	authenticate_user('admin');
	
	$page_title = _("Welcome to Dashboard"); //You can edit this to change your page title.
	require_once('lib/includes/header.php'); //including header file.
?>

	<!-- Begin Row -->
	<div class="row flex-row">
		<!-- Begin Widget -->
		<div class="col-xl-4 col-md-6 col-sm-6">
			<div class="widget widget-12 has-shadow">
				<div class="widget-body">
					<div class="media">
						<div class="align-self-center ml-5 mr-5">
							<i class="la la-users"></i>
						</div>
						<div class="media-body align-self-center">
							<div class="title"><?php _e("Total Users"); ?></div>
							<div class="number"><?php $new_user->get_total_users('all');?> <?php _e("Users"); ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Widget -->

		<!-- Begin Widget -->
		<div class="col-xl-4 col-md-6 col-sm-6">
			<div class="widget widget-12 has-shadow">
				<div class="widget-body">
					<div class="media">
						<div class="align-self-center ml-5 mr-5">
							<i class="la la-user-plus"></i>
						</div>
						<div class="media-body align-self-center">
							<div class="title"><?php _e("Active Users"); ?></div>
							<div class="number"><?php $new_user->get_total_users('activate');?> <?php _e("Users"); ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Widget -->

		<!-- Begin Widget -->
		<div class="col-xl-4 col-md-6 col-sm-6">
			<div class="widget widget-12 has-shadow">
				<div class="widget-body">
					<div class="media">
						<div class="align-self-center ml-5 mr-5">
							<i class="la la-user-times"></i>
						</div>
						<div class="media-body align-self-center">
							<div class="title"><?php _e("Deactive Users"); ?></div>
							<div class="number"><?php $new_user->get_total_users('deactivate');?> <?php _e("Users"); ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Widget -->

		<!-- Begin Widget -->
		<div class="col-xl-4 col-md-6 col-sm-6">
			<div class="widget widget-12 has-shadow">
				<div class="widget-body">
					<div class="media">
						<div class="align-self-center ml-5 mr-5">
							<i class="la la-ban"></i>
						</div>
						<div class="media-body align-self-center">
							<div class="title"><?php _e("Ban Users"); ?></div>
							<div class="number"><?php $new_user->get_total_users('ban');?> <?php _e("Users"); ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Widget -->

		<!-- Begin Widget -->
		<div class="col-xl-4 col-md-6 col-sm-6">
			<div class="widget widget-12 has-shadow">
				<div class="widget-body">
					<div class="media">
						<div class="align-self-center ml-5 mr-5">
							<i class="la la-archive"></i>
						</div>
						<div class="media-body align-self-center">
							<div class="title"><?php _e("Suspend Users"); ?></div>
							<div class="number"><?php $new_user->get_total_users('suspend');?> <?php _e("Users"); ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Widget -->
	</div>
	<!-- End Row -->

	<!-- <div class="row flex-row"> 
		<div class="col-lg-12 mt-4 mb-4">
			<h2><?php _e("User Levels"); ?></h2>
		</div>
		<?php $new_level->get_level_info(); ?>
	</div>     -->
    <!--============================= Chat Section area Starts Here =====================================-->
    
	<div class="row flex-row mt-4">

		<div class="col-xl-6 col-md-6 col-sm-12">
			<div class="widget widget-07 has-shadow">
				<!-- Begin Widget Header -->
				<div class="widget-header bordered d-flex align-items-center">
					<h2><?php _e("Announcements"); ?>
							<small><?php _e("Recent Announcements"); ?></small></h2>
					<div class="widget-options">
						<a href="announcements.php" class="btn btn-shadow">View all</a>
					</div>
				</div>
				<!-- End Widget Header -->

				<div class="widget-body">
					<ul class="reviews list-group w-100">
						<?php $announcement_obj->announcement_widget(); ?>
					</ul>
				</div>
			</div><!-- widgte ends /-->
		</div>
		
		<div class="col-xl-6 col-md-6 col-sm-12">
			<div class="widget widget-07 has-shadow">
				<!-- Begin Widget Header -->
				<div class="widget-header bordered d-flex align-items-center">
					<h2><?php _e("Recent Logins"); ?></h2>
				</div>
				<!-- End Widget Header -->

				<div class="widget-body">
					<div class="table-responsive table-scroll padding-right-10" style="max-height:520px;">
						<?php $new_user->wc_last_logins(); ?>
					</div>	
				</div>
			</div><!-- widgte ends /-->
		</div>	
	</div>
	<!--new HTML Ends HEre-->
	
<div class="clearfix"></div>
<?php
	require_once("lib/includes/footer.php");