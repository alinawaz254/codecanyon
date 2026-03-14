<?php

require_once("lib/system_load.php");

date_default_timezone_set("Asia/Karachi");

authenticate_user('subscriber');

$user_id = (int)$_SESSION['user_id'];

$page_title = _("Verify Wallet Transfer");

$msg = "";

/* -------- RESEND OTP -------- */

if(isset($_POST['resend'])){

    $otp = rand(100000,999999);
    // $expires = date("Y-m-d H:i:s", time() + 600);
    $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    $stmt = $db->prepare("DELETE FROM wallet_otps WHERE user_id=?");
    $stmt->bind_param("i",$user_id);
    $stmt->execute();

    $stmt = $db->prepare("INSERT INTO wallet_otps(user_id,otp,expires_at) VALUES(?,?,?)");
    $stmt->bind_param("iis",$user_id,$otp,$expires);
    $stmt->execute();

    // mail($_SESSION['email'],"Wallet OTP","Your OTP is: ".$otp);
    // send email
    $subject = "Wallet OTP";
    $message = "Your Wallet Transfer OTP is: <b>".$otp."</b><br>This OTP will expire in 10 minutes.";

    send_email($_SESSION['email'], $subject, $message);  

    header("Location: wallet_transfer_verify.php?success=resent");
    exit;
}


/* -------- VERIFY OTP -------- */

if(isset($_POST['verify'])){

    $otp = intval($_POST['otp']);

    if($otp == 0){
        header("Location: wallet_transfer_verify.php?error=empty");
        exit;
    }
    $query = "SELECT * FROM wallet_otps WHERE otp = ".$otp." AND user_id = ".$user_id.  " LIMIT 1";

    $result = $db->query($query);
    $row    = $result->fetch_assoc();

    if(empty($row)){
        header("Location: wallet_transfer_verify.php?error=invalid");
        exit;
    }

    if(strtotime($row['expires_at']) < time()){
        header("Location: wallet_transfer_verify.php?error=invalid");
        exit;
    }

    $transactions_obj = new Transactions();

    $balance = $transactions_obj->get_balance($user_id);

    if($_SESSION['transfer_amount'] > $balance){
        header("Location: wallet_transfer_verify.php?error=balance");
        exit;
    }

    $success = $transactions_obj->transfer(
        $user_id,
        $_SESSION['transfer_receiver'],
        $_SESSION['transfer_amount']
    );

    if(!$success){
        header("Location: wallet_transfer_verify.php?error=failed");
        exit;
    }

    /* delete OTP */

    $stmt = $db->prepare("DELETE FROM wallet_otps WHERE user_id=?");
    $stmt->bind_param("i",$user_id);
    $stmt->execute();

    unset($_SESSION['transfer_receiver']);
    unset($_SESSION['transfer_amount']);

    header("Location: wallet_transfer_verify.php?success=done");
    exit;
}

require_once("lib/includes/header.php");
?>

<?php

if(isset($_GET['success']) && $_GET['success']=="done"){
    show_alert("Transfer Successful");
}

if(isset($_GET['success']) && $_GET['success']=="resent"){
    show_alert("New OTP sent to your email");
}

if(isset($_GET['error']) && $_GET['error']=="empty"){
    show_alert("Please enter OTP");
}

if(isset($_GET['error']) && $_GET['error']=="invalid"){
    show_alert("Invalid or expired OTP");
}

if(isset($_GET['error']) && $_GET['error']=="balance"){
    show_alert("Insufficient balance");
}

if(isset($_GET['error']) && $_GET['error']=="failed"){
    show_alert("Transfer failed");
}

?>

<style>
.verify-card {
    max-width: 450px;
    margin: auto;
}

.verify-title {
    text-align: center;
    margin-bottom: 20px;
}
</style>


<div class="mywidget wc_data">

    <div class="widget has-shadow">

        <div class="widget-body">

            <div class="verify-card">

                <h3 class="verify-title">Verify Wallet Transfer</h3>

                <?php if($msg): ?>
                <div class="alert alert-info text-center">
                    <?php echo $msg; ?>
                </div>
                <?php endif; ?>

                <form method="post">

                    <div class="form-group">

                        <label>Enter OTP</label>

                        <input type="text" name="otp" class="form-control" placeholder="Enter OTP" maxlength="6">

                    </div>

                    <div class="text-center mt-3">

                        <button name="verify" class="btn btn-success mr-2">
                            <i class="la la-check"></i>
                            Verify Transfer
                        </button>

                        <button type="submit" name="resend" class="btn btn-warning mr-2">
                            <i class="la la-refresh"></i>
                            Resend OTP
                        </button>

                        <a href="wallet_transfer.php" class="btn btn-secondary">
                            <i class="la la-arrow-left"></i>
                            Back
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<?php require_once("lib/includes/footer.php"); ?>