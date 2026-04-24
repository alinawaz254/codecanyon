<?php
require_once("lib/system_load.php");

authenticate_user('subscriber');

$user_id = (int)$_SESSION['user_id'];

$page_title = "Subscriber Dashboard";

require_once("lib/includes/header.php");

/* COUNTS */

// My Investments
$investment_count = 0;
$q = $db->query("SELECT COUNT(*) as total FROM user_investments WHERE user_id='$user_id'");
if($row = $q->fetch_assoc()){
$investment_count = $row['total'];
}

// My Referrals
$referral_count = 0;
$q = $db->query("SELECT COUNT(*) as total FROM users WHERE referral_id='$user_id'");
if($row = $q->fetch_assoc()){
$referral_count = $row['total'];
}

// My Notes
$notes_count = 0;
$q = $db->query("SELECT COUNT(*) as total FROM notes WHERE user_id='$user_id'");
if($row = $q->fetch_assoc()){
$notes_count = $row['total'];
}

// Total Investments
$investments_total = $transaction_obj->investement($user_id);
?>

<style>
/* ICON COLORS (PURPLE) */
.widget-12 .media i {
    color: #5d5386 !important;
    font-size: 2.5rem;
}

/* VIDEO/IMAGE BOX (MATCHING ADMIN DASHBOARD) */
.video-box {
    width: 100%;
    aspect-ratio: 16 / 9;
    max-height: 328px;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    background: #1a1a1a;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border: 1px solid rgba(0,0,0,0.05);
}

.dashboard-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.video-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.1);
    opacity: 0;
    transition: 0.3s;
    pointer-events: none;
}

.video-box:hover .video-overlay {
    opacity: 1;
}

.video-box.no-video {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.no-video-text {
    color: #999;
}

/* CARDS EQUAL HEIGHT & FULL WIDTH */
.f-links {
    display: block;
    height: 100%;
    text-decoration: none !important;
}

.widget.widget-12 {
    height: 100%;
    margin-bottom: 0 !important;
    display: flex;
    flex-direction: column;
}

.widget-12 .widget-body {
    flex: 1;
    display: flex;
    align-items: center;
    padding: 1.5rem !important;
}

.widget-12 .media {
    width: 100%;
}

/* REDUCE OUTER MARGINS AND WIDEN CARDS */
.container-fluid {
    padding-left: 10px !important;
    padding-right: 10px !important;
}

.tight-row {
    margin-left: -5px;
    margin-right: -5px;
    display: flex;
    flex-wrap: wrap;
}

.tight-row > [class*='col-'] {
    padding-left: 5px;
    padding-right: 5px;
    display: flex; /* Ensures children stretch */
}

.tight-row > [class*='col-'] > * {
    width: 100%; /* Ensures link/widget takes full width */
}
</style>

<!-- MEDIA ROW -->
<div class="row mb-4 tight-row">
    <!-- VIDEO COLUMN (col-6) -->
    <div class="col-md-6 mb-3">
        <?php $video_obj->show_dashboard_videos(1); ?>
    </div>
    
    <!-- IMAGE COLUMN (col-6) -->
    <div class="col-md-6 mb-3">
        <?php $image_obj->show_dashboard_images(1); ?>
    </div>
</div>

<!-- STAT CARDS ROW (Equally fulfilling the row below media) -->
<div class="row mb-4 tight-row">
    <!-- 1. MY INVESTMENTS -->
    <div class="col-md-4 mb-3">
        <a href="frontinvestments.php" class="f-links">
            <div class="widget widget-12 has-shadow">
                <div class="widget-body">
                    <div class="media">
                        <div class="align-self-center mr-3">
                            <i class="la la-money"></i>
                        </div>
                        <div class="media-body align-self-center">
                            <div class="title">My Investments</div>
                            <div class="number"><?php echo $investment_count; ?> Investments</div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- 2. MY REFERRALS -->
    <div class="col-md-4 mb-3">
        <a href="frontreferrals.php" class="f-links">
            <div class="widget widget-12 has-shadow">
                <div class="widget-body">
                    <div class="media">
                        <div class="align-self-center mr-3">
                            <i class="la la-users"></i>
                        </div>
                        <div class="media-body align-self-center">
                            <div class="title">My Referrals</div>
                            <div class="number"><?php echo $referral_count; ?> Referrals</div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- 3. TOTAL INVESTMENTS -->
    <div class="col-md-4 mb-3">
        <a href="frontinvestments.php" class="f-links">
            <div class="widget widget-12 has-shadow">
                <div class="widget-body">
                    <div class="media">
                        <div class="align-self-center mr-3">
                            <i class="la la-wallet"></i>
                        </div>
                        <div class="media-body align-self-center">
                            <div class="title">Total Investments</div>
                            <div class="number">PKR <?php echo number_format($investments_total, 0); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- WELCOME ROW -->
<div class="row mt-4">
    <div class="col-xl-12">
        <div class="widget has-shadow">
            <div class="widget-header bordered d-flex align-items-center">
                <h4>Welcome</h4>
            </div>
            <div class="widget-body">
                <p>This is your subscriber dashboard.</p>
                <p>From here you can manage your investments, referrals and notes.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once("lib/includes/footer.php"); ?>
