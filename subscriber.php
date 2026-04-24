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

// My Commision
$total_commission = 0;

$q = $db->query("
SELECT SUM(uid.comission) as total_commission
FROM user_investment_details uid
JOIN user_investments ui 
ON ui.investment_id = uid.investment_id
WHERE ui.user_id = '$user_id'
");

if($row = $q->fetch_assoc()){
$total_commission = $row['total_commission'] ?? 0;
}
?>

<style>
.row.flex-row {
    display: flex;
    align-items: stretch;
}

/* CARDS ONE LINE SYSTEM */
.dashboard-media-card {
    display: flex;
    flex-direction: column;
}

.widget.widget-12 {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* ICON COLORS (PURPLE) */
.widget-12 .media i {
    color: #5d5386 !important;
    font-size: 2.5rem;
}

/* VIDEO BOX (OPTIMIZED) */
.video-box {
    width: 100%;
    aspect-ratio: 16 / 9;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    position: relative;
    background: #1a1a1a;
}

.dashboard-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.2);
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
</style>

<div class="row mb-4">
    <!-- 1. VIDEO -->
    <div class="col-xl-3 col-md-6 mb-3 dashboard-media-card">
        <?php $video_obj->show_dashboard_videos(1); ?>
    </div>
    
    <!-- 2. IMAGE -->
    <div class="col-xl-3 col-md-6 mb-3 dashboard-media-card">
        <?php $image_obj->show_dashboard_images(1); ?>
    </div>

    <!-- 3. MY INVESTMENTS -->
    <div class="col-xl-2 col-md-4 mb-3">
        <a href="frontinvestments.php" class="f-links">
            <div class="widget widget-12 has-shadow">
                <div class="widget-body">
                    <div class="media">
                        <div class="align-self-center mr-3">
                            <i class="la la-money"></i>
                        </div>
                        <div class="media-body align-self-center">
                            <div class="title">Investments</div>
                            <div class="number"><?php echo $investment_count; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- 4. MY REFERRALS -->
    <div class="col-xl-2 col-md-4 mb-3">
        <a href="frontreferrals.php" class="f-links">
            <div class="widget widget-12 has-shadow">
                <div class="widget-body">
                    <div class="media">
                        <div class="align-self-center mr-3">
                            <i class="la la-users"></i>
                        </div>
                        <div class="media-body align-self-center">
                            <div class="title">Referrals</div>
                            <div class="number"><?php echo $referral_count; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- 5. TOTAL INVESTMENTS -->
    <div class="col-xl-2 col-md-4 mb-3">
        <a href="frontinvestments.php" class="f-links">
            <div class="widget widget-12 has-shadow">
                <div class="widget-body">
                    <div class="media">
                        <div class="align-self-center mr-3">
                            <i class="la la-wallet"></i>
                        </div>
                        <div class="media-body align-self-center">
                            <div class="title">Total</div>
                            <div class="number">PKR <?php echo number_format($transaction_obj->investement($user_id),0); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

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
