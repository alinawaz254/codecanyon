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

// Data Queries
// 1. Power Generation (Gifts)
$gifts_data = [];
$gifts_query = $db->query("SELECT level, COUNT(*) as count FROM user_reward_releases GROUP BY level");
while($row = $gifts_query->fetch_assoc()) {
    $gifts_data[$row['level']] = $row['count'];
}

// 2. Investment ROI
$roi_query = $db->query("SELECT SUM(amount) as total FROM transactions WHERE transaction_type = 3 AND is_approved = 1");
$roi_total = $roi_query->fetch_assoc()['total'] ?? 0;

// 3. Commission (Bonus and Referral)
$bonus_query = $db->query("SELECT SUM(amount) as total FROM transactions WHERE transaction_type = 6 AND is_approved = 1");
$bonus_total = $bonus_query->fetch_assoc()['total'] ?? 0;
$referral_query = $db->query("SELECT SUM(amount) as total FROM transactions WHERE transaction_type = 5 AND is_approved = 1");
$referral_total = $referral_query->fetch_assoc()['total'] ?? 0;

// 4. User Stats
$active_users = $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'activate'")->fetch_assoc()['count'];
$deactive_users = $db->query("SELECT COUNT(*) as count FROM users WHERE status = 'deactivate'")->fetch_assoc()['count'];
$admin_users = $db->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'admin'")->fetch_assoc()['count'];
$subscriber_users = $db->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'subscriber'")->fetch_assoc()['count'];

// 5. Total Withdrawal
$withdrawal_query = $db->query("SELECT SUM(amount) as total FROM transactions WHERE transaction_type = 1 AND is_approved = 1");
$withdrawal_total = $withdrawal_query->fetch_assoc()['total'] ?? 0;

// 6. Total Investments
$investments_query = $db->query("SELECT SUM(amount) as total FROM user_investments");
$investments_total = $investments_query->fetch_assoc()['total'] ?? 0;


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
/* VIDEO/IMAGE BOX */
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
.video-box:hover .video-overlay {
    opacity: 1;
}
.video-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.1);
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
}
.no-video-text {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    color: #999;
    font-size: 14px;
}
/* TABS STYLE */
.dashboard-tabs .nav-link {
    color: #333;
    font-weight: 600;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 10px 20px;
    transition: all 0.3s;
}
.dashboard-tabs .nav-link.active {
    color: #ECAD3D !important;
    background: transparent !important;
    border-bottom: 3px solid #ECAD3D !important;
}
.stat-card {
    background: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    text-align: center;
    height: 100%;
    transition: transform 0.3s;
    border: 1px solid #eee;
}
.stat-card:hover { transform: translateY(-5px); }
.stat-title { font-size: 13px; color: #777; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.5px; }
.stat-value { font-size: 24px; font-weight: 700; color: #333; }
</style>

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

<!-- TABS ROW -->
<div class="row">
    <div class="col-xl-12">
        <div class="widget has-shadow">
            <div class="widget-body">
                <ul class="nav nav-tabs dashboard-tabs mb-4" id="dashboardTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="power-tab" data-bs-toggle="tab" href="#power" role="tab">Power Generation</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="investments-tab" data-bs-toggle="tab" href="#investments" role="tab">Investments Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="commission-tab" data-bs-toggle="tab" href="#commission" role="tab">Commission Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="users-tab" data-bs-toggle="tab" href="#users" role="tab">Total Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="withdrawal-tab" data-bs-toggle="tab" href="#withdrawal" role="tab">Total Withdrawal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="total-invest-tab" data-bs-toggle="tab" href="#total-invest" role="tab">Total Investments</a>
                    </li>
                </ul>

                <div class="tab-content" id="dashboardTabsContent">
                    <!-- Tab 1: Power Generation -->
                    <div class="tab-pane fade show active" id="power" role="tabpanel">
                        <div class="row">
                            <?php for($i=1; $i<=6; $i++): ?>
                            <div class="col-md-4 mb-3">
                                <div class="stat-card">
                                    <div class="stat-title">Level <?php echo $i; ?> Achievements</div>
                                    <div class="stat-value text-primary"><?php echo $gifts_data[$i] ?? 0; ?> Gifts</div>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Tab 2: Investments Details -->
                    <div class="tab-pane fade" id="investments" role="tabpanel">
                        <div class="row justify-content-center">
                            <div class="col-md-6 mb-3">
                                <div class="stat-card">
                                    <div class="stat-title">Total ROI Given to Users</div>
                                    <div class="stat-value text-success">PKR <?php echo number_format($roi_total, 2); ?></div>
                                    <small class="text-muted">Accumulated from approved ROI transactions</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Commission Details -->
                    <div class="tab-pane fade" id="commission" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="stat-card border-start border-4 border-warning">
                                    <div class="stat-title">10% Bonus Given</div>
                                    <div class="stat-value text-warning">PKR <?php echo number_format($bonus_total, 2); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="stat-card border-start border-4 border-info">
                                    <div class="stat-title">1% Referral Commission Given</div>
                                    <div class="stat-value text-info">PKR <?php echo number_format($referral_total, 2); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 4: Total Users -->
                    <div class="tab-pane fade" id="users" role="tabpanel">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-title">Active</div>
                                    <div class="stat-value"><?php echo $active_users; ?></div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-title">Deactive</div>
                                    <div class="stat-value"><?php echo $deactive_users; ?></div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-title">Admins</div>
                                    <div class="stat-value"><?php echo $admin_users; ?></div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stat-card">
                                    <div class="stat-title">Subscribers</div>
                                    <div class="stat-value"><?php echo $subscriber_users; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 5: Total Withdrawal -->
                    <div class="tab-pane fade" id="withdrawal" role="tabpanel">
                        <div class="row justify-content-center">
                            <div class="col-md-6 mb-3">
                                <div class="stat-card">
                                    <div class="stat-title">Total Withdrawal Till Now</div>
                                    <div class="stat-value text-danger">PKR <?php echo number_format($withdrawal_total, 2); ?></div>
                                    <small class="text-muted">Total approved withdrawal transactions</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 6: Total Investments -->
                    <div class="tab-pane fade" id="total-invest" role="tabpanel">
                        <div class="row justify-content-center">
                            <div class="col-md-6 mb-3">
                                <div class="stat-card">
                                    <div class="stat-title">Total Investments Till Now</div>
                                    <div class="stat-value text-dark font-weight-bold">PKR <?php echo number_format($investments_total, 2); ?></div>
                                    <small class="text-muted">Sum of all user investments</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- tab content -->
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
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

<?php require_once("lib/includes/footer.php"); ?>