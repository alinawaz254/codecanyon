<?php

require_once("lib/system_load.php");

date_default_timezone_set("Asia/Karachi");

authenticate_user('subscriber');

$user_id = (int)$_SESSION['user_id'];

$page_title = _("Wallet Transfer");

$transactions_obj = new Transactions();

if(isset($_POST['send'])){

    // $email  = trim($_POST['email'] ?? '');
    $identity = trim($_POST['email'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);

    $balance = $transactions_obj->get_balance($user_id);

    if(empty($identity) || $amount <= 0){
        header("Location: wallet_transfer.php?error=invalid");
        exit;
    }

    if($amount > $balance){
        header("Location: wallet_transfer.php?error=balance");
        exit;
    }

    $q = $db->prepare("SELECT user_id FROM users WHERE email=? OR username=? LIMIT 1");
    $q->bind_param("ss",$identity,$identity);
    $q->execute();
    $result = $q->get_result();    

    if($result->num_rows == 0){
        header("Location: wallet_transfer.php?error=user");
        exit;
    }

    $row = $result->fetch_assoc();
    $receiver_id = $row['user_id'];

    if($receiver_id == $user_id){
        header("Location: wallet_transfer.php?error=self");
        exit;
    }

    $_SESSION['transfer_receiver'] = $receiver_id;
    $_SESSION['transfer_amount']   = $amount;

    // generate OTP
    $otp = rand(100000,999999);

    $expires = date("Y-m-d H:i:s", strtotime("+10 minutes")); // 5 min

    // delete old otp
    $stmt = $db->prepare("DELETE FROM wallet_otps WHERE user_id=?");
    $stmt->bind_param("i",$user_id);
    $stmt->execute();

    // insert new otp
    $stmt = $db->prepare("INSERT INTO wallet_otps(user_id,otp,expires_at) VALUES(?,?,?)");
    $stmt->bind_param("iis",$user_id,$otp,$expires);
    $stmt->execute();

    // send email
    // send_email($_SESSION['email'],"Wallet OTP","Your OTP is: ".$otp);

    $subject = "Wallet OTP";
    $message = "Your Wallet Transfer OTP is: <b>".$otp."</b><br>This OTP will expire in 10 minutes.";

    send_email($_SESSION['email'], $subject, $message);     

    header("Location: wallet_transfer_verify.php");
    exit;
}

require_once("lib/includes/header.php");
?>

<?php
if(isset($_GET['error']) && $_GET['error']=="invalid"){
	show_alert("Withdrawal request Invalid email or username or amount");
}

if(isset($_GET['error']) && $_GET['error']=="balance"){
	show_alert("Withdrawal request Invalid Insufficient wallet balance");
}

if(isset($_GET['error']) && $_GET['error']=="user"){
	show_alert("Withdrawal request Invalid Receiver not found");
}

if(isset($_GET['error']) && $_GET['error']=="self"){
	show_alert("Withdrawal request Invalid You cannot send money to yourself");
}
?>

<style>
.transfer-card {
    max-width: 500px;
    margin: auto;
}

.transfer-title {
    text-align: center;
    margin-bottom: 20px;
}
</style>


<div class="mywidget wc_data">

    <div class="widget has-shadow">

        <div class="widget-body">

            <div class="transfer-card">

                <h3 class="transfer-title">Wallet Transfer</h3>

                <!-- Wallet Balance -->
                <div class="alert alert-light bg-dark text-center">
                    Balance: <?php echo $transactions_obj->display_balance($user_id); ?>
                </div>

                <form method="post">

                    <div class="form-group mb-3">

                        <label>Login ID</label>

                        <input type="text" name="email" class="form-control" placeholder="Enter Your Login ID"
                            required>

                    </div>

                    <div class="form-group">

                        <label>Amount</label>

                        <input type="number" name="amount" class="form-control" placeholder="Enter amount" required>

                    </div>

                    <div class="text-center mt-3">

                        <button type="submit" name="send" class="btn btn-golden btn-md">

                            <i class="la la-paper-plane"></i>
                            Send OTP

                        </button>
                        <a href="wallet.php" class="btn btn-outline-dark">
                            Back
                        </a>
                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<?php require_once("lib/includes/footer.php"); ?>