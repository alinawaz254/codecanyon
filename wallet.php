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

                <h2 class="page-header-title">Biz Wallet</h2>

                <div class="wallet-page-balance">
                    <?php echo $transaction_obj->balance($_SESSION["user_id"]); ?>
                </div>

                <div class="wallet-actions mt-3">

                    <a href="wallet_transfer.php" class="btn btn-golden btn-lg">
                        <i class="la la-exchange"></i> Transfer
                    </a>

                    <a href="wallet_withdraw.php" class="btn btn-golden btn-lg">
                        <i class="la la-arrow-down"></i> Withdraw
                    </a>

                </div>

            </div>
            <hr>

        </div>

    </div>

</div>

<?php require_once("lib/includes/footer.php"); ?>