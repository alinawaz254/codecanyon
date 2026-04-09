<?php
require_once("lib/system_load.php");

date_default_timezone_set("Asia/Karachi");

authenticate_user('all');

$user_id = (int)$_SESSION['user_id'];

$page_title = _("My Investments");

require_once("lib/includes/header.php");

/* ================= ALERTS ================= */

if(isset($_SESSION['success_message'])){

    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
        '. $_SESSION['success_message'] . '
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>';

    unset($_SESSION['success_message']);    
}

if(isset($_SESSION['error_message'])){

    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        ' . $_SESSION['error_message'] . '
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>';

    unset($_SESSION['error_message']);
}

// First query to get all investments for the user
$investment_query = "
SELECT 
    ui.investment_id,
    ui.user_id,
    ui.plan_id,
    ui.amount,
    ui.is_released,
    ui.issue_date,
    ui.created_at AS investment_created_at,
    ip.plan_name,
    ip.total_cycles,
    ip.cycle_days,
    ip.commission AS plan_commission
FROM user_investments ui
LEFT JOIN investment_plans ip 
    ON ip.plan_id = ui.plan_id
WHERE ui.user_id = '$user_id'
ORDER BY ui.issue_date DESC
";

$investments = $db->query($investment_query);
$today = date("Y-m-d");
// $today = "2026-07-28"; // test date

    if(isset($_GET['user_investment_detail_id'])){

        $search_record = $db->query("SELECT * FROM user_investment_details WHERE id =" . $_GET['user_investment_detail_id']);

        if($search_record && $search_record->num_rows > 0){

            $get_record  = $search_record->fetch_assoc();

            if($get_record['is_claimed'] == 0){

                $db->query("UPDATE user_investment_details SET is_claimed = 1,claimed_date = NOW() WHERE id =".$_GET['user_investment_detail_id']);

                $search_user = $db->query("SELECT * FROM users WHERE user_id = $user_id AND user_type LIKE '%subscriber%'");
                $user        = $search_user->fetch_assoc();

                $amount      = $get_record['comission'];
                $username    = $user['username'];
                $message     = $db->real_escape_string("$username has collected his commission on $today");

                $db->query("INSERT INTO transactions (user_id,transaction_type,amount,description,is_approved) VALUES ('$user_id',3,'$amount','$message',1)");

                $transaction_id = $db->insert_id;

                send_notification(
                    ADMIN_ID,
                    $user_id,
                    "$username claimed commission of PKR $amount",
                    "roi_claim",
                    $transaction_id
                );                

                $_SESSION['success_message'] = "Commission claimed successfully!";

            } else {
                $_SESSION['error_message'] = "Commission already claimed!";
            }

        } else {
            $_SESSION['error_message'] = "Invalid commission request!";
        }

        header("Location: frontinvestments.php");
        exit;     
    }    

    if(isset($_GET['re_invest_amount'])){
        $plan_id    = (int) $_GET['plan_id'];
        $amount     = (float) $_GET['re_invest_amount'];
        $query      = $db->query("SELECT * FROM investment_plans WHERE plan_id = " . $plan_id);
        $plan       = $query->fetch_assoc();

        if(!$plan){
            echo "Invalid plan";
        }

        $issue_date  = date('Y-m-d');
        $comission   = ($amount * $plan['commission']) / 100;
        $ref_query   = $db->query("SELECT referral_id FROM users WHERE user_id = '$user_id'");
        $ref_data    = $ref_query->fetch_assoc();
        $referrer_id = $ref_data['referral_id'] ?? 0;

        $db->query("INSERT INTO user_investments (user_id,plan_id,amount,issue_date) VALUES ('$user_id','$plan_id','$amount','$issue_date')");

        $investment_id = $db->insert_id;

        $u = $db->query("SELECT username FROM users WHERE user_id = $user_id");
        $username = $u->fetch_assoc()['username'] ?? 'User';

        send_notification(
            ADMIN_ID,
            $user_id,
            "$username re-invested PKR $amount",
            "reinvest",
            $investment_id
        );        

        for ($i=1; $i <= $plan['total_cycles']; $i++) {     

            $comission_expiry_date = date(
            "Y-m-d",
            strtotime($issue_date . " +" . ($i * intval($plan['cycle_days'])) . " days")
            );            

            $db->query("INSERT INTO user_investment_details 
            (investment_id,user_id,cycle,comission,comission_expiry_date) 
            VALUES ('$investment_id','$user_id','$i','$comission','$comission_expiry_date')");

            if($referrer_id > 0){

                $referral_commission = ($amount * 1) / 100;

                $db->query("
                    INSERT INTO user_investment_details 
                    (investment_id,user_id,cycle,comission,comission_expiry_date) 
                    VALUES ('$investment_id','$referrer_id','$i','$referral_commission','$comission_expiry_date')
                ");
            }
        }

        $_SESSION['success_message'] = "Re-investment completed successfully!";  
            // $_SESSION['error_message'] = "In Sufficient balance!";  

        header("Location: frontinvestments.php");
        exit;     

    }

    if (isset($_GET['release_investment_id'])) {

        $investment_id = (int) $_GET['release_investment_id'];

        $details_query = $db->query("
            SELECT * 
            FROM user_investments 
            WHERE investment_id = $investment_id AND is_released = 0 
            AND user_id = $user_id 
        ");

        if (!$details_query) {
            die($db->error);
        }
        
        $user_investment =   $details_query->fetch_assoc() ?? [];

        $get_user  = $db->query("SELECT username FROM users WHERE user_id = $user_id");
        $user_data = $get_user->fetch_assoc();
        $user      = $user_data['username'];
        
        if(!empty($user_investment)){
            if(isset($user_investment)){
                $amount = $user_investment['amount'];   

                if ($amount > 0) {
                    $search_investment_details = $db->query("SELECT * FROM user_investment_details WHERE investment_id = $investment_id AND user_id = $user_id");

                    $verify = true;
                    
                    while($row = $search_investment_details->fetch_assoc()){
                        if($row['is_claimed'] == 0){
                            $verify = false;
                            break;
                        }else{
                            continue;
                        }
                    }

                    if($verify){
                        $db->query("UPDATE user_investments SET is_released = 1 WHERE investment_id = $investment_id AND user_id = $user_id");
                        $message = $db->real_escape_string("$user has released his investment on $today");

                        $db->query("
                            INSERT INTO transactions 
                            (user_id, transaction_type, amount, is_approved, description) 
                            VALUES ($user_id, 7, $amount, 1, '$message')
                        ");

                        $transaction_id = $db->insert_id;

                        send_notification(
                            ADMIN_ID,
                            $user_id,
                            "$user collected his total investment of PKR $amount",
                            "roi_release",
                            $transaction_id
                        );            
                        $_SESSION['success_message'] = "Investment released successfully!";
                    }else{
                        $_SESSION['error_message'] = "Please claim all comissions first!";
                    }

                } else {
                    $_SESSION['error_message'] = "An error occured while releasing the investment!";
                }           

            }else{
                $_SESSION['error_message'] = "Cannot release this investment agian!";
            }

        }else{
            $_SESSION['error_message'] = "No investment available to release!";
        }
        
        header("Location: frontinvestments.php");
        exit;
    }
?>
<style>
/* Mobile Modal Fix */
@media (max-width:768px) {

    .modal-dialog {
        margin: 10px;
        max-width: 95%;
    }

    .modal-content {
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }

    .modal-body {
        overflow-y: auto;
        overflow-x: auto;
    }

}
</style>

<div class="mywidget wc_data">
    <div class="widget has-shadow">
        <div class="widget-header bordered">
            <h4>My Investments</h4>
        </div>

        <div class="widget-body table-responsive">
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>Packages</th>
                        <th>Amount</th>
                        <th>Date Issued</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $modals = "";

                    while ($investment = $investments->fetch_assoc()):
                        $amount             = $investment['amount'];
                        $plan_id            = $investment['plan_id'];
                        $cycle_days         = $investment['cycle_days'];
                        $total_cycles       = $investment['total_cycles'];
                        $commission_percent = $investment['plan_commission'];
                        $commission         = ($amount * $commission_percent) / 100;
                        $issue_date         = strtotime($investment['issue_date']);

                        $expiry_query = $db->query("
                        SELECT MAX(comission_expiry_date) as last_date 
                        FROM user_investment_details 
                        WHERE investment_id = '".$investment['investment_id']."'
                        ");

                        $expiry_row = $expiry_query->fetch_assoc();             
                        $is_expired = ($today > $expiry_row['last_date']);      
                        $details_query = "
                        SELECT 
                            id,
                            user_id,
                            cycle,
                            comission,
                            comission_expiry_date,
                            is_claimed,
                            claimed_date,
                            created_at
                        FROM user_investment_details 
                        WHERE user_id = $user_id AND investment_id = '" . $investment['investment_id'] . "'
                        ORDER BY cycle ASC
                        ";
                        
                        $details_result = $db->query($details_query);
                        $has_details = ($details_result && $details_result->num_rows > 0);
                    ?>

                    <tr <?php if ($is_expired) echo 'style="opacity:0.6;background-color:#f5f5f5;"'; ?>>
                        <td><?php echo $investment['plan_name']; ?></td>
                        <td><?php echo number_format($amount, 2); ?></td>
                        <td><?php echo $investment['issue_date']; ?></td>

                        <td>
                            <?php if ($is_expired): ?>
                            <button class="btn btn-golden btn-sm " data-toggle="modal"
                                data-target="#investmentModal_<?php echo $investment['investment_id']; ?>">
                                View
                            </button>
                            <span class="badge text-bg-secondary ml-2 mx-5">Inactive</span>
                            <?php if($investment['is_released'] != 1) : ?>
                                <!--                             <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="GET">
                                <input type="hidden" name="re_invest_amount" value="<?php echo $amount ?>">
                                <input type="hidden" name="plan_id" value="<?php echo $plan_id ?>">
                                <button type="submit" class="btn btn-sm btn-warning ml-2">Re-Invest</button>
                            </form> -->
                            <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="GET">
                                <input type="hidden" name="release_investment_id"
                                    value="<?php echo $investment['investment_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success ml-2">Release</button>
                            </form>
                            <?php endif; ?>

                            <?php else : ?>
                            <button class="btn btn-golden btn-sm" data-toggle="modal"
                                data-target="#investmentModal_<?php echo $investment['investment_id']; ?>">
                                View
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php
                        ob_start();
                        ?>

                    <div class="modal fade" id="investmentModal_<?php echo $investment['investment_id']; ?>"
                        tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-centered table-responsive">
                            <div class="modal-content investment-modal">
                                <div class="modal-header investment-header">
                                    <h5 class="modal-title">
                                        <?php echo $investment['plan_name']; ?> Investment Details
                                    </h5>

                                    <button type="button" class="btn-close-investment" data-dismiss="modal">
                                        &times;
                                    </button>
                                </div>

                                <div class="modal-body">
                                    <div class="investment-info">
                                        <div>
                                            <span class="info-label">Package</span>
                                            <span class="info-value"><?php echo $investment['plan_name']; ?></span>
                                        </div>

                                        <div>
                                            <span class="info-label">Amount</span>
                                            <span class="info-value"><?php echo number_format($amount, 2); ?></span>
                                        </div>

                                        <div>
                                            <span class="info-label">Date Issued</span>
                                            <span class="info-value"><?php echo $investment['issue_date']; ?></span>
                                        </div>
                                    </div>

                                    <hr>

                                    <table class="table investment-table">
                                        <thead>
                                            <tr>
                                                <th class="info-value">Cycle</th>
                                                <th class="info-value">Commission Date</th>
                                                <th class="info-value">Commission (<?php echo $commission_percent; ?>%)
                                                </th>
                                                <th class="info-value">Status</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php
                                                if ($has_details) {
                                                    // Loop through actual details from database
                                                    while ($detail = $details_result->fetch_assoc()) {
                                                        $is_detail_expired = (strtotime($today) > strtotime($detail['comission_expiry_date']));  

                                                        // Define the two dates
                                                        $date1 = new DateTime(date('Y-m-d'));
                                                        $date2 = new DateTime($detail['comission_expiry_date']);

                                                        // Calculate the difference
                                                        $interval = $date1->diff($date2);

                                                        ?>
                                            <tr <?php if ($is_detail_expired) ?>>
                                                <td class="info-value"><?php echo $detail['cycle']; ?></td>
                                                <td class="info-value"><?php echo $detail['comission_expiry_date']; ?>
                                                </td>
                                                <td class="info-value">
                                                    <?php echo number_format($detail['comission'], 2); ?></td>
                                                <td class="info-value">
                                                    <?php if ($is_detail_expired  && $detail['is_claimed'] == 0): ?>
                                                    <form action="<?php echo $_SERVER['PHP_SELF']?>"
                                                        name="claim_investment_form">
                                                        <input type="hidden" name="user_investment_detail_id"
                                                            value="<?php echo $detail['id']; ?>">
                                                        <input class="text-white" type="submit" name="claim_ivestment"
                                                            value="Claim Now"
                                                            style="background: green;border-radius: 5%;">
                                                    </form>
                                                    <?php
                                                                 elseif(($is_detail_expired && isset($detail['is_claimed']) && $detail['is_claimed'] == 1)): ?>
                                                    <span class="badge text-bg-success">Paid on
                                                        <?php echo $detail['claimed_date'];?></span>
                                                    <?php else: ?>
                                                        <?php if($is_detail_expired): ?>
                                                        <span class="badge text-bg-success">
                                                            Ready to collect
                                                        </span>
                                                        <?php else :?>
                                                        <span class="badge text-bg-danger">Unpaid
                                                            <?php echo '('.$interval->days .' days left to claim)'; ?></span>
                                                        <?php endif; ?>
                                                    <span class="badge text-bg-danger">Unpaid
                                                        <?php echo '('.$interval->days .' days left to claim)'; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php
                                                    }
                                                } else {
                                                    // If no details exist yet, show projected cycles
                                                    for ($i = 1; $i <= $total_cycles; $i++) {
                                                        $cycle_date = date(
                                                            "Y-m-d",
                                                            strtotime("+" . ($cycle_days * $i) . " days", $issue_date)
                                                        );
                                                        $is_future_cycle = ($cycle_date > $today);
                                                        ?>

                                            <tr>
                                                <td class="info-value"><?php echo $i; ?></td>
                                                <td class="info-value"><?php echo $cycle_date; ?></td>
                                                <td class="info-value"><?php echo number_format($commission, 2); ?></td>
                                                <td class="info-value">
                                                    <?php if (!$is_future_cycle && $today <= $cycle_date): ?>
                                                    <span class="badge text-bg-warning">Pending</span>
                                                    <?php elseif ($today > $cycle_date): ?>
                                                    <span class="badge text-bg-secondary">Expired</span>
                                                    <?php else: ?>
                                                    <span class="badge text-bg-info">Future</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>

                                            <?php } ?>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="modal-footer investment-footer">
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
/* print all modals outside table */
echo $modals;

require_once("lib/includes/footer.php");
?>

<script>
$(document).ready(function() {
    $('.modal').on('show.bs.modal', function() {
        console.log('Modal opened');
    });
});
</script>