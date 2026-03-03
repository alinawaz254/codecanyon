<?php
    if(!defined("SITE_NAME")) {
        return;
    }
?>
<!DOCTYPE HTML>
<html itemscope itemtype="http://schema.org/WebPage"  lang="en-US">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>
			<?php echo $page_title; ?>
		</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <!-- Google Fonts -->
        <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js"></script>
        <script>
          WebFont.load({
            google: {"families":["Montserrat:400,500,600,700","Noto+Sans:400,700"]},
            active: function() {
                sessionStorage.fonts = true;
            }
          });
        </script>
		<!--add_bootstrap start here.-->
		<?php 
			$skin = get_option('skin');
			if(isset($skin) && $skin == 'default') {
		} ?>
		<!--addd bootstap ends here.-->
        <!-- Favicon -->
        <link rel="apple-touch-icon" sizes="180x180" href="assets/img/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon-16x16.png">
        <!-- Stylesheet -->
        <link rel="stylesheet" href="assets/vendors/css/base/bootstrap.min.css">
        <link rel="stylesheet" href="assets/vendors/css/base/script_styles.css">
		<link rel="stylesheet" href="assets/vendors/css/base/main.min.css">
        <link rel="stylesheet" href="assets/css/animate/animate.min.css">
        <?php if(isset($datatables) && $datatables == "1"): ?>
        <link rel="stylesheet" href="assets/css/datatables/datatables.min.css">
        <?php endif; ?>

        <?php if(isset($croppic) && $croppic == 1) : ?>
        <link rel="stylesheet" href="assets/css/croppic.css">
        <?php endif; ?>

		<script src="assets/vendors/js/base/jquery.min.js"></script>

		<?php
			//Loading Google Login if Active
			if(get_option('google_login') == '1') { 
				if(isset($_SESSION["wc_google_logout"]) && $_SESSION["wc_google_logout"] == 'confirm') {
					$call_function = 'signout_now';
				} else {
					$call_function = 'renderButton';
				}
		?>
			<meta name="google-signin-client_id" content="<?php echo get_option('google_client_id'); ?>">
			<script src="https://apis.google.com/js/client:platform.js?onload=<?php echo $call_function; ?>" async defer></script>
			<script src="assets/js/googlelogin.js"></script>
		<?php
			}
		?>
    </head>
    <body id="page-top" class="bg-white">
        <!-- Begin Preloader -->
        <div id="preloader">
            <div class="canvas">
                <img src="<?=SITELOGO;?>" alt="logo" class="loader-logo">
                <div class="spinner"></div>   
            </div>
        </div>
        <!-- End Preloader -->

		<!--sidebar Nav ere-->
		<?php 
			if(partial_access("all")):
				echo '<div class="page">';
				require_once('top_nav.php');
			endif;
		?>
		<?php
			//This file includes top bar navigation things.
			if(partial_access('all')): //If user is loged in this bar would show up.
                echo '<!-- Begin Page Content -->
					  <div class="page-content d-flex align-items-stretch">';
				require_once('sidepanel.php');
                echo '<div class="content-inner">
                        <div class="container-fluid">';
                
                return_announcements();

                echo '<!-- Begin Page Header-->
                        <div class="row">
                            <div class="page-header">
                                <div class="d-flex align-items-center">
                                    <h2 class="page-header-title">'.$page_title.'</h2>
                                    <div>
                                    <div class="page-header-tools">
                                        <!-- Link if needed /-->
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Page Header -->';
                $message = (isset($message)) ? $message : "";
                return_info_messages($message);
			endif;