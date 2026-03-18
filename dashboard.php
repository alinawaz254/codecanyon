<?php
require_once("lib/system_load.php");

/* LOGOUT */
if(isset($_GET['logout']) && $_GET['logout'] == 1){ 
    session_destroy();
    setcookie("loginCredentials", "", time() - 2592000);

    if(get_option('google_login') == '1'){
        session_start();
        $_SESSION["wc_google_logout"] = 'confirm';	
    }

    header('LOCATION: '.get_option('redirect_on_logout').'?logmeout=1');
    exit();
}

/* AUTH */
authenticate_user('admin');

$page_title = "Admin Dashboard";
require_once('lib/includes/header.php');
?>

<style>
/* ===== EQUAL HEIGHT SYSTEM ===== */
.equal-row {
    display: flex;
    flex-wrap: wrap;
}

.equal-row>div {
    display: flex;
}

.equal-card {
    width: 100%;
    display: flex;
}

.equal-card .widget-body {
    display: flex;
    align-items: center;
    width: 100%;
}

/* ICON ALIGN */
.widget .media {
    align-items: center !important;
}

/* VIDEO BOX */
.video-box {
    width: 100%;
    height: 100%;
    max-height: 328px !important;    
    border-radius: 6px;
    overflow: hidden;
}

.dashboard-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-box:hover .video-overlay {
    opacity: 1;
}

/* no video */
.video-box.no-video {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.no-video-text {
    color: #999;
}
</style>

<!-- MAIN ROW -->
<div class="row equal-row">

    <!-- LEFT VIDEO -->
    <div class="col-xl-4 col-md-4 mb-3">
        <div class="w-100">
            <?php
                $video = new Video();
                $video->show_dashboard_videos(1);
            ?>
        </div>
    </div>

    <!-- RIGHT CARDS -->
    <div class="col-xl-8 col-md-8">
        <div class="row equal-row">

            <!-- Total Users -->
            <div class="col-md-6 mb-3">
                <div class="widget widget-12 has-shadow equal-card">
                    <div class="widget-body">
                        <div class="media">
                            <div class="mx-3">
                                <i class="la la-users"></i>
                            </div>
                            <div class="media-body">
                                <div class="title">Total Users</div>
                                <div class="number">
                                    <?php $new_user->get_total_users('all');?> Users
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="col-md-6 mb-3">
                <div class="widget widget-12 has-shadow equal-card">
                    <div class="widget-body">
                        <div class="media">
                            <div class="mx-3">
                                <i class="la la-user-plus"></i>
                            </div>
                            <div class="media-body">
                                <div class="title">Active Users</div>
                                <div class="number">
                                    <?php $new_user->get_total_users('activate');?> Users
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deactive Users -->
            <div class="col-md-6 mb-3">
                <div class="widget widget-12 has-shadow equal-card">
                    <div class="widget-body">
                        <div class="media">
                            <div class="mx-3">
                                <i class="la la-user-times"></i>
                            </div>
                            <div class="media-body">
                                <div class="title">Deactive Users</div>
                                <div class="number">
                                    <?php $new_user->get_total_users('deactivate');?> Users
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ban Users -->
            <div class="col-md-6 mb-3">
                <div class="widget widget-12 has-shadow equal-card">
                    <div class="widget-body">
                        <div class="media">
                            <div class="mx-3">
                                <i class="la la-ban"></i>
                            </div>
                            <div class="media-body">
                                <div class="title">Ban Users</div>
                                <div class="number">
                                    <?php $new_user->get_total_users('ban');?> Users
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- SECOND ROW -->
<div class="row mt-4">

    <!-- Announcements -->
    <div class="col-xl-6 col-md-6 col-sm-12 mb-3">
        <div class="widget widget-07 has-shadow">
            <div class="widget-header bordered d-flex align-items-center">
                <h2>Announcements <small>Recent Announcements</small></h2>
                <div class="widget-options">
                    <a href="announcements.php" class="btn btn-shadow">View all</a>
                </div>
            </div>

            <div class="widget-body">
                <ul class="reviews list-group w-100">
                    <?php $announcement_obj->announcement_widget(); ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Recent Logins -->
    <div class="col-xl-6 col-md-6 col-sm-12 mb-3">
        <div class="widget widget-07 has-shadow">
            <div class="widget-header bordered d-flex align-items-center">
                <h2>Recent Logins</h2>
            </div>

            <div class="widget-body">
                <div class="table-responsive table-scroll" style="max-height:520px;">
                    <?php $new_user->wc_last_logins(); ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script>


document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll(".video-box").forEach(box => {

        let video = box.querySelector(".video-hover");

        if (!video) return;

        video.removeAttribute("controls");

        box.addEventListener("mouseenter", () => {
            video.setAttribute("controls", "controls");
        });

        box.addEventListener("mouseleave", () => {
            video.removeAttribute("controls");
        });

    });

});
</script>

<?php require_once("lib/includes/footer.php"); ?>