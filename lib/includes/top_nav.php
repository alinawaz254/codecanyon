<?php
    if(!defined("SITE_NAME")) {
        return;
    }
?>
<!-- Begin Header -->
<header class="header">
    <nav class="navbar fixed-top">
        <!-- Begin Topbar -->
        <div class="navbar-holder d-flex align-items-center align-middle justify-content-between">
            <!-- Begin Logo -->
            <div class="navbar-header">
                <a href="dashboard.php" class="navbar-brand">
                    <div class="brand-image brand-big">
                        <img src="<?=SITELOGO;?>" alt="<?=SITE_NAME;?>" class="logo-big">
                    </div>
                    <div class="brand-image brand-small">
                        <img src="<?=SITELOGO;?>" alt="<?=SITE_NAME;?>" class="logo-small">
                    </div>
                </a>
                <!-- Toggle Button -->
                <a id="toggle-btn" href="#" class="menu-btn active">
                    <span></span>
                    <span></span>
                    <span></span>
                </a>
                <!-- End Toggle -->
            </div>
            <!-- End Logo -->
            <!-- Begin Navbar Menu -->
            <ul class="nav-menu list-unstyled d-flex flex-md-row align-items-md-center pull-right">
                <!-- Begin Wallet -->
                <?php if(partial_access('subscriber')): ?>

                <li class="nav-item">
                    <a href="wallet.php" class="nav-link">

                        <i class="las la-wallet"></i>

                        <span class="wallet-balance">
                            <?php echo $transaction_obj->display_balance($_SESSION["user_id"]); ?>
                        </span>

                    </a>
                </li>

                <?php endif; ?>
                <!-- End Wallet -->

                <!-- Begin Notes -->
                <li class="nav-item dropdown">
                    <a id="notifications" rel="nofollow" data-target="#" href="#" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false" class="nav-link">
                        <i class="la la-sticky-note"></i>
                        <span class="badge-pulse"></span>
                    </a>
                    <ul aria-labelledby="notifications" class="dropdown-menu notification">
                        <li>
                            <div class="notifications-header">
                                <div class="title"><?php _e("Notes"); ?> (<?php $notes_obj->notes_count(); ?>)</div>
                                <div class="notifications-overlay"></div>
                                <img src="assets/img/notifications/01.jpg" alt="..." class="img-fluid">
                            </div>
                        </li>
                        <?php $notes_obj->notes_widget(); ?>
                        <li>
                            <a rel="nofollow" href="notes.php"
                                class="dropdown-item all-notifications text-center"><?php _e("View All Notes"); ?></a>
                        </li>
                    </ul>
                </li>
                <!-- End Notes -->

                <!-- Begin Messages -->
                <li class="nav-item dropdown">
                    <a id="messages" rel="nofollow" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false" class="nav-link">
                        <i class="la la-comments"></i>
                        <span class="badge-pulse"></span>
                    </a>
                    <ul aria-labelledby="notifications" class="dropdown-menu notification">
                        <li>
                            <div class="notifications-header">
                                <div class="title"><?php _e("Messages"); ?> (<?php $message_obj->unread_count(); ?>)
                                </div>
                                <div class="notifications-overlay"></div>
                                <img src="assets/img/notifications/01.jpg" alt="Messages" class="img-fluid">
                            </div>
                        </li>
                        <?php $message_obj->message_widget(); ?>
                        <li>
                            <a rel="nofollow" href="messages.php" class="dropdown-item all-notifications text-center">
                                <?php _e("View All Messages"); ?>
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- End Messages -->
                <?php if(partial_access("admin")): ?>
                <!-- Begin Notification -->
                <li class="nav-item dropdown">

                    <a href="#" data-toggle="dropdown" class="nav-link">

                        <i class="la la-bell"></i>

                        <span id="notification-count" class="badge badge-danger">
                            <?php $notification_obj->unread_count(); ?>
                        </span>

                        <span id="notification-pulse" class="badge-pulse"></span>

                    </a>

                    <ul class="dropdown-menu notification">

                        <li class="notifications-header">

                            <div class="title">
                                Notifications (<span id="notification-count-title">
                                    <?php $notification_obj->unread_count(); ?>
                                </span>)
                            </div>

                            <a href="clear_all_notifications.php" class="clear-all">
                                Clear All
                            </a>

                        </li>

                        <div class="notification-scroll">
                            <?php $notification_obj->notification_widget(); ?>
                        </div>

                        <li class="text-center">
                            <a href="notifications.php" class="dropdown-item all-notifications">
                                View All Notifications
                            </a>
                        </li>

                    </ul>

                </li>
                <!-- End Notification -->
                <?php endif; ?>

                <!-- User -->
                <li class="nav-item dropdown">
                    <a id="user" rel="nofollow" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false" class="nav-link">
                        <img src="<?=$profile_img;?>" alt="..." class="avatar rounded-circle"></a>
                    <ul aria-labelledby="user" class="user-size dropdown-menu">
                        <li class="welcome">
                            <a class="dropdown-item">
                                <?php echo $new_user->get_user_info($_SESSION['user_id'], 'first_name'); ?>
                                <?php echo $new_user->get_user_info($_SESSION['user_id'], 'last_name'); ?>
                            </a>
                            <a href="edit_profile.php?user_id=<?php echo $_SESSION['user_id']; ?>"
                                class="edit-profil"><i class="la la-gear"></i></a>
                            <img src="<?=$profile_img;?>" alt="..." class="rounded-circle">
                        </li>
                        <?php if(partial_access('all')) : //following nav for all other loged in users. ?>
                        <li>
                            <a class="dropdown-item" href="messages.php">
                                <?php _e("Messages"); ?> <span
                                    class="nb-new badge-rounded info badge-rounded-small"><?php $message_obj->unread_count(); ?></span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="notes.php">
                                <?php _e("My Notes"); ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="separator"></li>
                        <?php if(partial_access("admin")): ?>
                        <li>
                            <a class="dropdown-item" href="announcements.php">
                                <?php _e("Announcements"); ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="general_settings.php">
                                <?php _e("General Settings"); ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li>
                            <a class="dropdown-item"
                                href="edit_profile.php?user_id=<?php echo $_SESSION['user_id']; ?>">
                                <?php _e("Edit Profile"); ?>
                            </a>
                        </li>
                        <li>
                            <a rel="nofollow" href="dashboard.php?logout=1" class="dropdown-item logout text-center"><i
                                    class="ti-power-off"></i></a>
                        </li>
                    </ul>
                </li>
                <!-- End User -->
            </ul>
            <!-- End Navbar Menu -->
        </div>
        <!-- End Topbar -->
    </nav>
</header>
<!-- End Header -->