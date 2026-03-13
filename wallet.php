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

                <h2>Biz Wallet</h2>

                <div class="wallet-page-balance">
                    PKR <?php echo 'balance'; ?>
                </div>

                <div class="wallet-actions mt-3">

                    <a href="wallet_transfer.php" class="btn btn-primary">
                        <i class="la la-exchange"></i> Transfer
                    </a>

                    <a href="wallet_withdraw.php" class="btn btn-danger">
                        <i class="la la-arrow-down"></i> Withdraw
                    </a>

                </div>

            </div>

            <hr>

            <!-- TRANSACTIONS TABLE -->

            <h4>Recent Transactions</h4>

            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if($transactions->num_rows == 0): ?>

                    <tr>
                        <td colspan="4" class="text-center">
                            No wallet transactions found
                        </td>
                    </tr>

                    <?php else: ?>

                    <?php while($row = $transactions->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php
                            switch($row['transaction_type']){
                                case 1: echo "<span class='text-danger'>Withdrawal</span>"; break;
                                case 2: echo "<span class='text-success'>Funded</span>"; break;
                                case 3: echo "<span class='text-primary'>Commission</span>"; break;
                                default: echo "Unknown";
                            }
                            ?>
                        </td>

                        <td>
                            <?php
                            $sign = ($row['transaction_type'] == 1) ? '-' : '+';
                            $class = ($row['transaction_type'] == 1) ? 'text-danger' : 'text-success';
                            ?>

                            <strong class="<?php echo $class; ?>">
                                PKR <?php echo $sign.number_format($row['amount'],2); ?>
                            </strong>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['description']); ?>
                        </td>

                        <td>
                            <?php echo date("Y-m-d H:i", strtotime($row['created_at'])); ?>
                        </td>

                    </tr>

                    <?php endwhile; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php require_once("lib/includes/footer.php"); ?>