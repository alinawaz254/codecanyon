<?php

require_once "lib/system_load.php";

authenticate_user('all');

$user_id = (int) $_SESSION['user_id'];

$page_title = _("My Referrals");

require_once "lib/includes/header.php";

$today = date("Y-m-d");
     if(isset($_GET['user_investment_detail_id'])){
            $search_record = $db->query("SELECT * FROM user_investment_details WHERE id =" . $_GET['user_investment_detail_id']);
            $db->query("UPDATE user_investment_details SET is_claimed = 1,claimed_date = NOW() WHERE id =".$_GET['user_investment_detail_id']);
            
            $get_record  = $search_record->fetch_assoc();
            $search_user = $db->query("SELECT * FROM users WHERE user_id = $user_id AND user_type LIKE '%subscriber%'");
            $user        = $search_user->fetch_assoc();
            $amount      = $get_record['comission'];
            $username    = $user['username'];
            $message     = $db->real_escape_string("$username has collected his referral commission on $today");

            $db->query("INSERT INTO transactions (user_id,transaction_type,amount,description) VALUES ('$user_id',3,'$amount','$message')");

            $search_admin = $db->query("SELECT * FROM users WHERE user_type LIKE '%admin%'");

            while($row = $search_admin->fetch_assoc()){

                $admin_id = isset($row['user_id']) ?? 0;

                $db->query("INSERT INTO notifications
                (sender_id, sender_type, receiver_id, receiver_type, message)
                VALUES ('$user_id','subscriber','$admin_id','admin','$message')")
                or die($db->error);
            }

            header("Location: frontreferrals.php");
            exit;     
    }
// $today = "2026-06-19"; // test date

$query = "
    SELECT 
        u.username,
        u.user_id AS referred_user_id,
        ui.investment_id,
        ui.amount,
        ui.issue_date,
        ip.plan_name,
        ip.total_cycles,
        ip.cycle_days
    FROM users u
    LEFT JOIN user_investments ui 
        ON ui.user_id = u.user_id
    LEFT JOIN investment_plans ip 
        ON ip.plan_id = ui.plan_id
    WHERE u.referral_id = '$user_id'
    ORDER BY ui.issue_date DESC
";

$result = $db->query($query);
?>

<style>
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

    .cycles-title {
        margin: 10px 0 15px 0;
        font-weight: 600;
    }

    .investment-table thead {
        background: #f5f5f5;
    }

    .investment-table th {
        font-weight: 600;
    }

    .referral-footer {
        justify-content: flex-end;
        border-top: 0px;
        padding: 7px 9px 8px 8px !important;
    }
    
    .modal-title {
        color: white;
    }
    .investment-table{
    width:100%;
    }

    /* Mobile Modal Fix */
    @media (max-width:768px){

    .investment-table{
    min-width:600px;
    }
    .investment-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    }
    .modal-dialog{
        margin:10px;
        max-width:95%;
    }

    .modal-content{
        max-height:90vh;
        display:flex;
        flex-direction:column;
    }

    .modal-body{
        overflow-y:auto;
        overflow-x:auto;
    }

    }    
</style>

<div class="mywidget wc_data">
    <div class="widget has-shadow">
        <div class="widget-header bordered">
            <h4>My Referrals</h4>
        </div>

        <div class="widget-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Referred User</th>
                        <th>Package</th>
                        <th>Investment Amount</th>
                        <th>Date Issued</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $modals = "";

                    if ($result->num_rows == 0) {
                        echo '<tr><td colspan="5" class="text-center">No referrals found</td></tr>';
                    }

                    while ($row = $result->fetch_assoc()):
                        if (!$row['investment_id']) {
                            echo "<tr>
                                <td>{$row['username']}</td>
                                <td>-</td>
                                <td>0.00</td>
                                <td>-</td>
                                <td><span class='badge text-bg-secondary'>No Investment</span></td>
                            </tr>";
                            continue;
                        }

                        $amount = $row['amount'];

                        /* Investment expiry */
                        $expiry_query = $db->query("
                            SELECT MAX(comission_expiry_date) as last_date
                            FROM user_investment_details
                            WHERE investment_id = '" . $row['investment_id'] . "'
                        ");

                        $expiry_row = $expiry_query->fetch_assoc();

                        $is_expired = ($today > $expiry_row['last_date']);
                        ?>

                        <tr <?php if ($is_expired) echo 'style="opacity:0.5;background:#f5f5f5;"'; ?>>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['plan_name']; ?></td>
                            <td><?php echo number_format($amount, 2); ?></td>
                            <td><?php echo $row['issue_date']; ?></td>
                            <td>
                                <button class="btn btn-primary btn-sm btn-golden"
                                    data-toggle="modal"
                                    data-target="#referralModal_<?php echo $row['investment_id']; ?>_<?php echo $row['referred_user_id']; ?>">
                                    View
                                </button>

                                <?php if ($is_expired): ?>
                                    <span class="badge text-bg-secondary ml-2">Inactive</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <?php
                        ob_start();
                        ?>

                        <div class="modal fade"
                            id="referralModal_<?php echo $row['investment_id']; ?>_<?php echo $row['referred_user_id']; ?>"
                            tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content investment-modal">
                                    <div class="modal-header investment-header">
                                        <h5 class="modal-title">
                                            <?php echo $row['username']; ?> Investment Details
                                        </h5>
                                        <button type="button"
                                            class="btn-close-investment"
                                            data-dismiss="modal">
                                            ×
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="investment-info">
                                            <div>
                                                <span class="info-label">Referred User</span>
                                                <span class="info-value"><?php echo $row['username']; ?></span>
                                            </div>

                                            <div>
                                                <span class="info-label">Package</span>
                                                <span class="info-value"><?php echo $row['plan_name']; ?></span>
                                            </div>

                                            <div>
                                                <span class="info-label">Amount</span>
                                                <span class="info-value"><?php echo number_format($amount, 2); ?></span>
                                            </div>

                                            <div>
                                                <span class="info-label">Date Issued</span>
                                                <span class="info-value"><?php echo $row['issue_date']; ?></span>
                                            </div>
                                        </div>

                                        <hr>

                                        <h6>Your Referral Commission: <span class="text-success">1%</span></h6>

                                        <table class="table investment-table">
                                            <thead>
                                                <tr>
                                                    <th class="info-value">Cycle</th>
                                                    <th class="info-value">Commission Date</th>
                                                    <th class="info-value">Commission (1%)</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php
                                                $details_query = "
                                                    SELECT cycle, comission_expiry_date,
                                                    id,
                                                    is_claimed,
                                                    claimed_date
                                                    FROM user_investment_details
                                                    WHERE investment_id = '" . $row['investment_id'] . "'
                                                    ORDER BY cycle ASC
                                                ";

                                                $details_result = $db->query($details_query);

                                                while ($detail = $details_result->fetch_assoc()) {
                                                    $cycle = $detail['cycle'];
                                                    $cycle_date = $detail['comission_expiry_date'];

                                                    $commission = ($amount * 1) / 100;

                                                    $is_detail_expired = (strtotime($today) > strtotime($cycle_date));

                                                    $date1 = new DateTime(date('Y-m-d'));
                                                    $date2 = new DateTime($detail['comission_expiry_date']);

                                                    // Calculate the difference
                                                    $interval = $date1->diff($date2);
                                                    ?>

                                                    <tr <?php if ($is_detail_expired) echo 'style="opacity:0.5"'; ?>>
                                                        <td class="info-value"><?php echo $cycle; ?></td>
                                                        <td class="info-value"><?php echo $cycle_date; ?></td>
                                                        <td class="info-value"><?php echo number_format($commission, 2); ?></td>
                                                        <td class="info-value">
                                                            <?php if ($is_detail_expired  && $detail['is_claimed'] == 0): ?>
                                                                <form action="<?php echo $_SERVER['PHP_SELF']?>" name="claim_investment_form">
                                                                    <input type="hidden" name="user_investment_detail_id" value="<?php echo $detail['id']; ?>">
                                                                    <input class="text-white" type="submit" name="claim_ivestment" value="Claim Now" style="background: green;border-radius: 5%;">
                                                                </form>
                                                            <?php
                                                             elseif(($is_detail_expired && isset($detail['is_claimed']) && $detail['is_claimed'] == 1)): ?>
                                                                <span class="badge text-bg-success">Paid on <?php echo $detail['claimed_date'];?></span>
                                                            <?php else: ?>
                                                                <span class="badge text-bg-danger">Unpaid <?php echo '('.$interval->days .' days left to claim)'; ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>

                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="modal-footer referral-footer">
                                        <button class="btn btn-golden btn-md" data-dismiss="modal">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                        $modals .= ob_get_clean();
                    endwhile;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
echo $modals;

require_once "lib/includes/footer.php";
?>

<script>
$(document).ready(function(){
    $('.modal').on('show.bs.modal', function(){
        console.log('Modal opened');
    });
});
</script>