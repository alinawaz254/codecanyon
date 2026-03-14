<?php

require_once("lib/system_load.php");

authenticate_user('subscriber');

$page_title = _("Wallet Withdraw");

$user_id = (int)$_SESSION['user_id'];

if(isset($_POST['withdraw'])){

    $amount = floatval($_POST['amount']);

    $balance = $transaction_obj->get_balance($user_id);
    // $balance = floatval(str_replace(['PKR','k','M',' '],'',$balance));

    if($amount <= 0){
        header("Location: wallet_withdraw.php?error=invalid");
        exit;
    }

    if($amount > $balance){
        header("Location: wallet_withdraw.php?error=balance");
        exit;
    }

    $stmt = $db->prepare("
        INSERT INTO transactions
        (user_id,transaction_type,amount,description,is_approved,created_at,updated_at)
        VALUES(?,1,?,'Withdrawal Request',0,NOW(),NOW())
    ");

    $stmt->bind_param("id",$user_id,$amount);
    $stmt->execute();

    header("Location: wallet_withdraw.php?success=1");
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

                <h3 class="withdraw-title">Wallet Withdraw</h3>

                <div class="alert alert-info text-center">
                    Available Balance:
                    <strong><?php echo $transaction_obj->balance($_SESSION["user_id"]); ?></strong>
                </div>

                <form method="post">

                    <div class="form-group">

                        <label>Withdraw Amount</label>

                        <input type="number" name="amount" class="form-control" placeholder="Enter withdraw amount"
                            min="1" step="0.01" required>

                    </div>

                    <div class="text-center mt-3">

                        <button name="withdraw" class="btn btn-golden btn-md">

                            <i class="la la-arrow-down"></i>
                            Request Withdraw

                        </button>
                        <a href="wallet.php" class="btn btn-md btn-secondary">
                            Back
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<?php require_once("lib/includes/footer.php"); ?>