<?php

require_once("lib/system_load.php");

authenticate_user('subscriber');

$page_title = _("Wallet Windraw");

$user_id = (int)$_SESSION['user_id'];

$msg = "";

if(isset($_POST['withdraw'])){

    $amount = $_POST['amount'];

    $balance = 0;

    if($amount > $balance){

        $msg = "Insufficient balance";

    }else{

        $db->query("
            INSERT INTO withdraw_requests(user_id,amount,status,created_at)
            VALUES('$user_id','$amount','pending',NOW())
        ");

        $msg = "Withdrawal request sent";

    }
}

require_once("lib/includes/header.php");

/* GET WALLET BALANCE */
$balance = 0;

?>

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
                    <strong>PKR <?php number_format($balance,2); ?></strong>
                </div>

                <?php if($msg): ?>
                <div class="alert alert-success text-center">
                    <?php echo $msg; ?>
                </div>
                <?php endif; ?>

                <form method="post">

                    <div class="form-group">

                        <label>Withdraw Amount</label>

                        <input type="number" name="amount" class="form-control" placeholder="Enter withdraw amount"
                            required>

                    </div>

                    <div class="text-center mt-3">

                        <button name="withdraw" class="btn btn-danger">

                            <i class="la la-arrow-down"></i>
                            Request Withdraw

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<?php require_once("lib/includes/footer.php"); ?>