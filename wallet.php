<?php

require_once("lib/system_load.php");

authenticate_user('subscriber');

$user_id = (int)$_SESSION['user_id'];

$page_title = _("Biz Wallet");

require_once("lib/includes/header.php");

/* WALLET TRANSACTIONS */
$transactions = $db->query("
    SELECT *
    FROM transactions
    WHERE user_id = '$user_id'
    ORDER BY created_at DESC
    LIMIT 10
");

?>

<style>
.wallet-balance-card {
    text-align: center;
    padding: 30px;
}

.wallet-page-balance {
    font-size: 32px;
    font-weight: 700;
    color: #28a745;
}

.wallet-actions .btn {
    margin: 5px;
}
</style>


<div class="mywidget wc_data">

    <div class="widget has-shadow">

        <div class="widget-body">

            <!-- WALLET BALANCE -->
            <div class="wallet-balance-card">

                <h2 class="page-header-title mb-5">Biz Wallet</h2>
                <div class="row text-center">

                    <!-- TOTAL WALLET -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm p-4">
                            <h6>Available Balance</h6>
                            <strong class="text-success">
                                <?php echo $transaction_obj->display_balance($user_id); ?>
                            </strong>
                            <?php 
                            $pending = $transaction_obj->get_pending_withdrawal_amount($user_id);
                            if($pending > 0): 
                            ?>
                            <div class="mt-2">
                                <small class="text-muted">On Hold Balance: PKR <?php echo number_format($pending, 2); ?></small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- TOTAL INVESTMENT -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm p-4">
                            <h6>Total Investment Amount</h6>
                            <strong class="text-info">
                                PKR <?php echo number_format($transaction_obj->investement($user_id),2); ?>
                            </strong>
                        </div>
                    </div>

                    <!-- TOTAL PROFIT -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm p-4">
                            <h6>Total Profit Amount</h6>
                            <strong class="text-warning">
                                PKR <?php echo number_format($transaction_obj->profit($user_id),2); ?>
                            </strong>
                        </div>
                    </div>

                </div>


                <div class="wallet-actions mt-5">

                    <a href="wallet_transfer.php" class="btn btn-golden btn-lg">
                        <i class="la la-exchange"></i> Transfer
                    </a>

                    <a href="wallet_withdrawl.php" class="btn btn-golden btn-lg">
                        <i class="la la-arrow-down"></i> Withdraw
                    </a>

                </div>

            </div>
            <hr>

        </div>

    </div>

</div>

<?php require_once("lib/includes/footer.php"); ?>