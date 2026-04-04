<?php

if (!defined('ROOT_DIR')) {
	exit();
}?>

<style>
	@media (max-width: 768px) {
    .main-footer{
        display: none;
    }
}
</style>
<footer class="main-footer sticky footer-type-1">
	<div class="row">
		<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 d-flex align-items-center justify-content-xl-start justify-content-lg-start justify-content-md-start justify-content-center">
			<?php wc_language_output(); ?>
		</div>
		<div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 d-flex align-items-center justify-content-xl-end justify-content-lg-end justify-content-md-end justify-content-center">
			<p>&copy; <?php _e("Copyright"); ?> <?php echo date('Y'); ?> 
				<strong><?php echo get_option('site_name'); ?></strong> <?php _e("All Rights Reserved"); ?> <?php _e("Powered By"); ?>: <a href="https://marvelstack.co/">Marvel Stack</a></p>
		</div>
	</div>
</footer>
<!-- Go to Top Link, just add rel="go-top" to any link to add this functionality -->
<a href="#" id="top" class="go-top" rel="go-top">
	<i class="la la-arrow-up"></i>
</a>