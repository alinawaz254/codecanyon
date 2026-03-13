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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <!-- <link rel="stylesheet" href="assets/vendors/css/base/bootstrap.min.css"> -->
        <link rel="stylesheet" href="assets/vendors/css/base/script_styles.css">
		<link rel="stylesheet" href="assets/vendors/css/base/main.min.css">
        <link rel="stylesheet" href="assets/css/animate/animate.min.css">
        <!-- j query -->
        <script src="assets/vendors/js/base/jquery.min.js"></script>
        <!-- bootstrap js -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <!-- choices css -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/choices.js/1.1.6/styles/css/choices.min.css" integrity="sha512-/PTsSsk4pRsdHtqWjRuAL/TkYUFfOpdB5QDb6OltImgFcmd/2ZkEhW/PTQSayBKQtzjQODP9+IAeKd7S2yTXtA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <!-- choices js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/choices.js/1.1.6/choices.min.js" integrity="sha512-7PQ3MLNFhvDn/IQy12+1+jKcc1A/Yx4KuL62Bn6+ztkiitRVW1T/7ikAh675pOs3I+8hyXuRknDpTteeptw4Bw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <!--select2  -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css">
        <!-- select2 js -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        
        
        <?php if(isset($datatables) && $datatables == "1"): ?>
        <link rel="stylesheet" href="assets/css/datatables/datatables.min.css">
        <?php endif; ?>

        <?php if(isset($croppic) && $croppic == 1) : ?>
        <link rel="stylesheet" href="assets/css/croppic.css">
        <?php endif; ?>

		
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
    <style>
        .modal-title {
            color: white;
        }
        .investment-modal {
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 25px;
        }
        .investment-header {
            background: #2c304d;
            color: #fff;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .investment-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .info-label {
            display: block;
            font-size: 12px;
            color: #777;
        }

        .info-value {
            font-size: 13px;
            font-weight: 600;
        }

        .investment-table thead {
            background: #f5f5f5;
        }

        .investment-table th {
            font-weight: 600;
        }

        .investment-footer {
            justify-content: flex-end;
            border-top: 0px;
            padding: 7px 9px 8px 8px !important;
        }
        .alert .close {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: 0;
            padding-right: 1.25rem;
            color: inherit;
            opacity: 1;
            background: linear-gradient(to right, #a07411 0%, #ce9f2b 50%, #f7d56c 100%) !important;
            color: #ffffff;
            border: 1px solid #bfa14a;
            font-weight: 600;
            box-shadow: 0 4px 8px rgb(209 156 0 / 40%);
            transition: background 0.3s ease;
        }
        .dt-buttons .btn{
            background:#d4af37;
            border:none;
            color:#fff;
            padding:6px 14px;
            border-radius:20px;
            margin-right:5px;
        }
        nav.navbar .user-size.dropdown-menu a.logout {
            background: #f1bc1c !important;
            width: 70px;
            height: 70px;
            color: #fff;
            border-radius: 50%;
            text-align: center !important;
            padding: 0;
            line-height: 55px;
            position: relative;
            bottom: -20px;
            font-size: 1.8rem;
            margin: 10px auto 0;
        }        

        .dt-buttons .btn:hover{
            background:#c19a2b;
        }             
        .side-navbar{
        background: #000;
        }
        .default-sidebar{
            background: #000;
        }
        .default-sidebar .side-navbar li:hover,
        .default-sidebar .side-navbar li:focus a{
            color: #f1bc1cd5 !important;
        }

        .default-sidebar .side-navbar li:hover a i,
        .default-sidebar .side-navbar li:focus a i{
            color: #f1bc1c !important;
        }
        .default-sidebar>.side-navbar a[aria-expanded="true"] i{
            color: #f1bc1c !important;
        }

        .default-sidebar>.side-navbar ul ul{
            background-color : #1a1818 !important;
        }
        .default-sidebar>.side-navbar ul ul a:hover{
            background-color : #464444 !important;
            color: #fabb00 !important;
        }
        .default-sidebar>.side-navbar a[aria-expanded="true"] {
            background: #0e0e0e;
            margin: 0 10px 0 10px;
            border-radius: 4px 4px 0 0;
        }             
        .page-header-title, .alert-secondary-bordered ,th,h2,h3,.btn-shadow, .btn-shadow a,.title{
            color:#000 !important;
        }
        .alert-secondary-bordered{
            border: 1px solid #eee;
            box-shadow: 7px 0 0 0 #000 inset;
        }
        .dash-lower{
            background-color : #000 !important;
        }
        .btn-golden {
            background: linear-gradient(to right, #a07411 0%, #ce9f2b 50%, #f7d56c 100%) !important;
            color: #ffffff;
            border: 1px solid #bfa14a;
            font-weight: 600;
            box-shadow: 0 4px 8px rgb(209 156 0 / 40%);
            transition: background 0.3s ease;
        }

        .btn-golden:hover {
            background: linear-gradient(to right, #916b13 0%, #b48817 50%, #ddba51 100%) !important;
            color: #ffffff;
            border: 1px solid #f1bc1c; 
        }
        .btn-golden-admin {
            background: linear-gradient(to right, #a07411 0%, #ce9f2b 50%, #f7d56c 100%) !important;
            color: #ffffff;
            border: 1px solid #bfa14a;
            font-weight: 600;
            box-shadow: 0 4px 8px rgb(209 156 0 / 40%);
            transition: background 0.3s ease;
        }

        .btn-golden-admin:hover {
            background: linear-gradient(to right, #916b13 0%, #b48817 50%, #ddba51 100%) !important;
            color: #ffffff;
            border: 1px solid #f1bc1c; 
        }

        .btn-close-investment {
            background: linear-gradient(to right, #a07411 0%, #ce9f2b 50%, #f7d56c 100%) !important;
            border: none;
            color: #fff;
            font-size: 20px;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
        }

        .btn-close-investment:hover {
            background: linear-gradient(to right, #a07411 0%, #ce9f2b 50%, #f7d56c 100%) !important;
        }  
        .content-inner{
            padding-bottom:120px;
        }
        .auth-left-panel{
            background:#000;
            height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .auth-left-inner{
            max-width:260px;
            color:#fff;
        }

        .auth-logo{
            width:120px;
            margin-bottom:30px;
        }

        .auth-title{
            color: #ffff;
            font-size:32px;
            font-weight:700;
            margin-bottom:15px;
        }

        .auth-desc{
            font-size:14px;
            opacity:.85;
            margin-bottom:25px;
        }

        .authentication-form-2{
            max-width:420px;
            padding:40px;
        }
        .f-links:hover{
            color: #c29510;
        }
        .notification-body{
        display:flex;
        align-items:flex-start;
        gap:10px;
        padding:12px 15px;
        border-bottom:1px solid #f1f1f1;
        position:relative;
        }

        .notification-icon{
        font-size:18px;
        margin-top:3px;
        }

        .notification-text{
        flex:1;
        font-size:14px;
        color:#444;
        line-height:1.4;
        }

        .notification-time{
        font-size:11px;
        color:#999;
        margin-top:3px;
        }

        .notification-action{
        position:absolute;
        right:10px;
        top:10px;
        }

        .notification-action a{
        color:#aaa;
        font-size:14px;
        }

        .notification-action a:hover{
        color:#ff4d4d;
        }
        .badge-pulse{
        position:absolute;
        top:5px;
        right:5px;
        width:10px;
        height:10px;
        border-radius:50%;
        background:#ff4d4d;
        animation:pulse 1.5s infinite;
        }

        @keyframes pulse{

        0%{
        transform:scale(0.8);
        opacity:0.8;
        }

        50%{
        transform:scale(1.6);
        opacity:0.3;
        }

        100%{
        transform:scale(0.8);
        opacity:0.8;
        }

        }
        .badge-pulse.hidden{
        display:none;
        }
        .notification-scroll{
        max-height:350px;
        overflow-y:auto;
        }
        .wallet-nav{
            display:inline-flex;
            align-items:center;
            gap:5px;
            height:40px;
        }

        .wallet-nav i{
            font-size:18px;
        }

        .wallet-balance {
            color: #28a745;
            font-weight: 600;
            font-size: 13px;
            line-height: 1;
            white-space: nowrap;
            position: absolute !important;
            top: 27px !important;
            right: 46px !important;
            border: 3px solid #fff !important;
        }
        .wallet-balance:hover{
            color: #12e744;
        }
        @media (max-width: 768px) {
            .wallet-balance {
                font-size: 8px;
                max-width: 90px;
                position: absolute !important;
                top: 45px !important;
                right: 28px !important;
            }
        }                            
    </style>
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