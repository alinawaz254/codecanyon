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

/* left + right columns */
.col-xl-4,
.col-xl-8 {
    display: flex;
    flex-direction: column;
}

/* right side grid */
.col-xl-8 .row {
    display: flex;
    flex-wrap: wrap;
    height: 100%;
}

/* each card column */
.col-md-6 {
    display: flex;
}

/* cards equal height */
.widget.widget-12 {
    flex: 1;
}

/* VIDEO BOX (IMPORTANT) */
.video-box {
    flex: 1; /* 🔥 magic line */
    height: 308px !important;
    border-radius: 5px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    position: relative;
}

/* video full fit */
.dashboard-video {
    width: 100%;
    height: auto;
    object-fit: cover;
}

/* overlay */
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

<div class="row flex-row h-100">

    <!-- MEDIA ROW -->
    <div class="row equal-row mb-4">
        <!-- VIDEO COLUMN -->
        <div class="col-xl-6 col-md-6 mb-3">
            <div class="w-100">
                <?php
                    $video_obj->show_dashboard_videos(1);
                ?>
            </div>
        </div>
        <!-- IMAGE COLUMN -->
        <div class="col-xl-6 col-md-6 mb-3">
            <div class="w-100">
                <?php
                    $image_obj->show_dashboard_images(1);
                ?>
            </div>
        </div>
    </div>
    <div class="col-xl-8 col-md-8">

        <div class="row flex-row h-100">

            <!-- My Investments -->
            <div class="col-md-4 mb-3">
                <a href="frontinvestments.php" class="f-links">
                    <div class="widget widget-12 has-shadow mb-3">
                        <div class="widget-body">
                            <div class="media">
                                <div class="align-self-center mx-3">
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

            <!-- My Referrals -->
            <div class="col-md-4">
                <a href="frontreferrals.php" class="f-links">
                    <div class="widget widget-12 has-shadow mb-3">
                        <div class="widget-body">
                            <div class="media">
                                <div class="align-self-center mx-3">
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


            <!-- Total Commission -->
            <div class="col-md-4">
                <div class="widget widget-12 has-shadow mb-3">
                    <div class="widget-body">
                        <div class="media">
                            <div class="align-self-center mx-3">
                                <i class="las la-money-bill"></i>
                            </div>
                            <div class="media-body align-self-center">
                                <div class="title">Total Investments</div>
                                <div class="number">PKR <?php echo number_format($transaction_obj->investement($user_id),2); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<div class="row flex-row mt-4">

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

<script>

window.addEventListener("load", function(){

    let noteCard = document.querySelector(".col-md-6 .widget");
    let videoBox = document.querySelector(".video-box");

    if(noteCard && videoBox){
        videoBox.style.height = noteCard.offsetHeight + "px";
    }

});
</script>

<?php require_once("lib/includes/footer.php"); ?>

