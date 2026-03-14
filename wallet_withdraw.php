<?php

require_once("lib/system_load.php");

authenticate_user('subscriber');

$page_title = _("Wallet Withdraw");

$user_id = (int)$_SESSION['user_id'];
$transactions_obj = new Transactions();

if(isset($_POST['send'])){

    $email  = trim($_SESSION['email'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);

    $balance = $transactions_obj->get_balance($user_id);

    if(empty($email) || $amount <= 0){
        header("Location: withdrawl.php?error=invalid");
        exit;
    }

    if($amount > $balance){
        header("Location: withdrawl.php?error=balance");
        exit;
    }

    $_SESSION['withdrawl_amount']   = $amount;

    // generate OTP
    $otp = rand(100000,999999);

    $expires = date("Y-m-d H:i:s", time() + 300); // 5 min

    // delete old otp
    $stmt = $db->prepare("DELETE FROM wallet_otps WHERE user_id=?");
    $stmt->bind_param("i",$user_id);
    $stmt->execute();

    // insert new otp
    $stmt = $db->prepare("INSERT INTO wallet_otps(user_id,otp,expires_at) VALUES(?,?,?)");
    $stmt->bind_param("iis",$user_id,$otp,$expires);
    $stmt->execute();

    // send email
    mail($_SESSION['email'],"Withdrawl OTP","Your OTP is: ".$otp);

    header("Location: withdrawl_verify.php");
    exit;
}

require_once("lib/includes/header.php");
?>

<?php if(isset($_GET['success'])): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">Withdrawal request sent successfully
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
</button></div>
<?php endif; ?>

<?php if(isset($_GET['error']) && $_GET['error']=="balance"): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">Insufficient balance
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
</button></div>
<?php endif; ?>

<?php if(isset($_GET['error']) && $_GET['error']=="invalid"): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">Invalid amount
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
</button></div>    
<?php endif; ?>

<style>
.withdraw-card {
    max-width: 450px;
    margin: auto;
}

.withdraw-title {
    text-align: center;
    margin-bottom: 20px;
}
</style>


<div class="mywidget wc_data">

    <div class="widget has-shadow">

        <div class="widget-body">

            <div class="withdraw-card">

                <h3 class="withdraw-title">Wallet Withdrawl</h3>

                <div class="alert alert-light bg-dark text-center">
                    Available Balance:
                    <strong><?php echo $transaction_obj->balance($_SESSION["user_id"]); ?></strong>
                </div>

                <form method="post">
<!-- 
                    <div class="form-group">

                        <label>Your Email</label>

                        <input type="email" name="email" class="form-control" placeholder="Enter receiver email"
                            required>

                    </div> -->

                    <div class="form-group">

                        <label>Withdrawl Amount</label>

                        <input type="number" name="amount" class="form-control" placeholder="Enter amount" required>

                    </div>

                    <div class="text-center mt-3">

                        <button type="submit" name="send" class="btn btn-golden btn-md">

                            <i class="la la-paper-plane"></i>
                            Send OTP

                        </button>
                        <a href="withdrawl.php" class="btn btn-md btn-secondary">
                            Back
                        </a>
                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<?php require_once("lib/includes/footer.php"); ?>