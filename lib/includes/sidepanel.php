<?php
    if(!defined("SITE_NAME")) {
        return;
    }
?>
<?php if(partial_access("admin")): ?>
<div class="default-sidebar">
    <!-- Begin Side Navbar -->
    <nav class="side-navbar box-scroll sidebar-scroll">
        <!-- Begin Main Navigation -->
        <ul class="list-unstyled">
            <li><a href="dashboard.php"><i class="la la-columns"></i><span><?php _e("Dashboard"); ?></span></a></li>
            <li><a href="#dropdown-users" aria-expanded="false" data-bs-toggle="collapse"><i class="la la-users"></i><span><?php _e("Users"); ?></span></a>
                <ul id="dropdown-users" class="collapse list-unstyled pt-0">
                    <li><a href="users.php"><?php _e("Manage Users"); ?></a></li>
                    <li><a href="manage_users.php"><?php _e("Add User"); ?></a></li>
                </ul>
            </li>
            <li><a href="#dropdown-userlevel" aria-expanded="false" data-bs-toggle="collapse"><i class="la la-file-text"></i><span><?php _e("User Levels"); ?></span></a>
                <ul id="dropdown-userlevel" class="collapse list-unstyled pt-0">
                    <li><a href="user_levels.php"><?php _e("User Levels"); ?></a></li>
                    <li><a href="manage_user_level.php"><?php _e("Add User Level"); ?></a></li>
                </ul>
            </li>
            <li><a href="messages.php"><i class="la la-comments"></i><span><?php _e("Messages"); ?> <span class="nb-new badge-rounded info badge-rounded-small"><?php $message_obj->unread_count(); ?></span></span></a></li>
            <li><a href="notes.php"><i class="la la-sticky-note"></i><span><?php _e("My Notes"); ?></span></a></li>
            <li>
                <a href="#dropdown-announcements" aria-expanded="false" data-bs-toggle="collapse"><i class="la la-bullhorn"></i><span><?php _e("Announcements"); ?></span></a>
                <ul id="dropdown-announcements" class="collapse list-unstyled pt-0">
                    <li><a href="announcements.php"><?php _e("Announcements"); ?></a></li>
                    <li><a href="manage_announcement.php"><?php _e("Add New"); ?></a></li>
                </ul>
            </li>
            <li>
                <a href="#dropdown-packages" aria-expanded="false" data-bs-toggle="collapse">
                <i class="la la-file-text"></i>
                <span><?php _e("Packages"); ?></span>
                </a>

                <ul id="dropdown-packages" class="collapse list-unstyled pt-0">
                    <li><a href="investment_plans.php"><?php _e("Packages"); ?></a></li>
                    <li><a href="manage_investment_plan.php"><?php _e("Add Package"); ?></a></li>
                </ul>
            </li>           
            <li>
                <a href="#dropdown-investments" aria-expanded="false" data-bs-toggle="collapse">
                <i class="la la-money"></i>
                <span><?php _e("Investments"); ?></span>
                </a>
                <ul id="dropdown-investments" class="collapse list-unstyled pt-0">
                    <li><a href="investments.php"><?php _e("Investments"); ?></a></li>
                    <li><a href="manage_investment.php"><?php _e("Add Investment"); ?></a></li>
                </ul>
            </li>             
            <li>
                <a href="#dropdown-transactions" aria-expanded="false" data-bs-toggle="collapse">
                <i class="la la-money"></i>
                <span><?php _e("Transactions"); ?></span>
                </a>
                <ul id="dropdown-transactions" class="collapse list-unstyled pt-0">
                    <li><a href="transactions.php"><?php _e("Transactions"); ?></a></li>
                    <li><a href="manage_transactions.php"><?php _e("Add Transaction"); ?></a></li>
                </ul>
            </li>
            <li>
                <a href="withdrawl.php"><i class="la la-user"></i><span><?php _e("Withdrawl Requests"); ?></span></a>
            </li>
            <li>
                <a href="power_generation.php"><i class="la la-bolt"></i><span><?php _e("Power Generation"); ?></span></a>
            </li>
            <li>
                <a href="#dropdown-videos" aria-expanded="false" data-bs-toggle="collapse">
                <i class="la la-video"></i>
                <span><?php _e("Videos"); ?></span>
                </a>
                <ul id="dropdown-videos" class="collapse list-unstyled pt-0">
                    <li><a href="videos.php"><?php _e("Videos"); ?></a></li>
                    <li><a href="manage_video.php"><?php _e("Add Video"); ?></a></li>
                </ul>
            </li>                       
        </ul>
        <!-- <hr> -->
<!--         <span class="heading"><?php _e("Other Pages"); ?></span>
        <ul class="list-unstyled">
            <li><a href="website_labels.php"><i class="la la-share-alt"></i><span><?php _e("Update Language"); ?></span></a></li>
            <li><a href="all.php"><i class="la la-at"></i><span><?php _e("For All Logged In User"); ?></span></a></li>
            <li><a href="subscriber.php"><i class="la la-user"></i><span><?php _e("For Subscriber User Level"); ?></span></a></li>
            <li><a href="admin.php"><i class="la la-user"></i><span><?php _e("For Admin User Level"); ?></span></a></li>
        </ul> -->
        <!-- End Main Navigation -->
    </nav>
    <!-- End Side Navbar -->
</div>
<!-- End Left Sidebar -->
<?php elseif(partial_access("all")): ?>
    <?php $level_page = $new_level->get_userlevel_info($_SESSION['user_type'], 'level_page'); ?>
   
<div class="default-sidebar">
    <!-- Begin Side Navbar -->
    <nav class="side-navbar box-scroll sidebar-scroll">
        <!-- Begin Main Navigation -->
        <ul class="list-unstyled">
            <li><a href="#dropdown-dashboard" aria-expanded="false" data-bs-toggle="collapse"><i class="la la-columns"></i><span><?php _e("Dashboard"); ?></span></a>
                <ul id="dropdown-dashboard" class="collapse list-unstyled pt-0">
                    <li><a href="<?=$level_page;?>"><?php _e("Dashboard"); ?></a></li>
                    <li><a href="dashboard.php?logout=1"><?php _e("Logout"); ?></a></li>
                </ul>
            </li>
            <li><a href="#dropdown-messages" aria-expanded="false" data-bs-toggle="collapse"><i class="la la-comments"></i><span><?php _e("Messages"); ?></span></a>
                <ul id="dropdown-messages" class="collapse list-unstyled pt-0">
                    <li><a href="messages.php"><?php _e("Messages"); ?> <span class="nb-new badge-rounded info badge-rounded-small"><?php $message_obj->unread_count(); ?></span></a></li>
                    <li><a href="messages.php?type=sent"><?php _e("Sent Items"); ?></a></li>
                </ul>
            </li>
            <li><a href="notes.php"><i class="la la-sticky-note"></i><span><?php _e("My Notes"); ?></span></a></li>
            <li><a href="frontinvestments.php"><i class="la la-money"></i><span><?php _e("My Investments"); ?></span></a></li>
            <li><a href="frontreferrals.php"><i class="la la-list"></i><span><?php _e("My Referrals"); ?></span></a></li>
            <li><a href="frontrewards.php"><i class="la la-gift"></i><span><?php _e("My Rewards"); ?></span></a></li>
            <li><a href="frontInvestmentBonus.php"><i class="las la-briefcase"></i><span><?php _e("Investment Bonus"); ?></span></a></li>
            <li>
                <a href="front-transactions.php">
                <i class="la la-money"></i>
                <span><?php _e("Transactions log"); ?></span>
                </a>
            </li>
            <li>
                <a href="wallet.php">
                <i class="las la-wallet"></i>
                <span><?php _e("Biz Wallet"); ?></span>
                </a>
            </li>             
        </ul>
<!--         <hr>
        <span class="heading"><?php _e("Other Pages"); ?></span>
        <ul class="list-unstyled">
            <li><a href="all.php"><i class="la la-at"></i><span><?php _e("For All Logged In Users"); ?></span></a></li>
            <li><a href="subscriber.php"><i class="la la-user"></i><span><?php _e("For Subscriber User Level"); ?></span></a></li>
            <li><a href="admin.php"><i class="la la-user"></i><span><?php _e("For Admin User Level"); ?></span></a></li>
        </ul> -->
        <!-- End Main Navigation -->
    </nav>
    <!-- End Side Navbar -->
</div>
<!-- End Left Sidebar -->
<?php endif; ?>