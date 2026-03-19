<?php
require_once "lib/system_load.php";

authenticate_user('all');

$user_id = (int) $_SESSION['user_id'];

$page_title = _("My Referrals");

require_once "lib/includes/header.php";

/* ================= ALERTS ================= */

if(isset($_SESSION['success_message'])){

    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
        '. $_SESSION['success_message'] . '
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        </button>
    </div>';
    unset($_SESSION['success_message']);    
}

if(isset($_SESSION['error_message'])){

    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            ' . $_SESSION['error_message'] . '
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            </button>
          </div>';
    unset($_SESSION['error_message']);
}

$today = date("Y-m-d");
// $today = '	2026-07-15';

// Handle referral commission claim
if(isset($_GET['referral_detail_id'])){
    $detail_id = (int)$_GET['referral_detail_id'];
    
    // Check if already claimed
    $check_claimed = $db->query("SELECT is_claimed, comission, investment_id FROM user_investment_details WHERE id = $detail_id");
    $check_result = $check_claimed->fetch_assoc();
    
    if($check_result && $check_result['is_claimed'] == 0) {
        // Get the commission record
        $search_record = $db->query("SELECT * FROM user_investment_details WHERE id = $detail_id");
        
        // Update as claimed
        $db->query("UPDATE user_investment_details SET is_claimed = 1, claimed_date = NOW() WHERE id = $detail_id");
        
        $get_record = $search_record->fetch_assoc();
        
        // Get user details
        $search_user = $db->query("SELECT * FROM users WHERE user_id = $user_id");
        $user = $search_user->fetch_assoc();
        
        $amount = $get_record['comission'];
        $username = $user['username'];
        
        // Get investment details to find which referred user generated this commission
        $investment_details = $db->query("
            SELECT ui.user_id as referred_user_id, u.username as referred_username
            FROM user_investment_details uid
            JOIN user_investments ui ON uid.investment_id = ui.investment_id
            JOIN users u ON ui.user_id = u.user_id
            WHERE uid.id = $detail_id
        ");
        $investment_info = $investment_details->fetch_assoc();
        
        $referred_username = $investment_info['referred_username'] ?? 'Unknown User';
        $message = $db->real_escape_string("$username has collected referral commission of $amount from $referred_username on $today");

        // Insert into transactions (using type 3 for commission as per your schema)
        $db->query("
            INSERT INTO transactions 
            (user_id, transaction_type, amount, description, is_approved, created_at) 
            VALUES ($user_id, 5, $amount, '$message', 1, NOW())
        ");

        // Notify admins
        $transaction_id = $db->insert_id;

        send_notification(
            ADMIN_ID,
            $user_id,
            "$username claimed referral commission of PKR $amount from $referred_username",
            "referral_claim",
            $transaction_id
        );        

        $_SESSION['success_message'] = "Referral commission claimed successfully!";
    } else {
        $_SESSION['error_message'] = "This commission has already been claimed or doesn't exist.";
    }

    header("Location: frontreferrals.php");
    exit();     
}

// Query to get referrals
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
    flex-wrap: wrap;
    gap: 10px;
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

.investment-table {
    width: 100%;
}

.btn-claim {
    background: #28a745;
    color: white;
    border: none;
    padding: 5px 15px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
}

.btn-claim:hover {
    background: #218838;
}

/* Mobile Modal Fix */
@media (max-width: 768px) {
    .investment-table {
        min-width: 600px;
    }

    .investment-info {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

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
            <h4>My Referrals</h4>
        </div>

        <div class="widget-body table-responsive">
            <table class="table dataTable">
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
                                <td><span class='badge bg-secondary'>No Investment</span></td>
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

                    <tr <?php if ($is_expired) echo 'style="opacity:0.6;background:#f5f5f5;"'; ?>>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['plan_name']; ?></td>
                        <td><?php echo number_format($amount, 2); ?></td>
                        <td><?php echo $row['issue_date']; ?></td>
                        <td>
                            <?php if ($is_expired): ?>
                            <button class="btn btn-sm btn-secondary ml-2">In-Active</button>
                            <?php else : ?>
                            <?php endif; ?>                            
                            <button class="btn btn-primary btn-sm btn-golden" data-toggle="modal"
                                data-target="#referralModal_<?php echo $row['investment_id']; ?>_<?php echo $row['referred_user_id']; ?>">
                                View Commission
                            </button>

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
                                        <?php echo $row['username']; ?> - Investment Details
                                    </h5>
                                    <button type="button" class="btn-close-investment" data-dismiss="modal">
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

                                    <h6>Your Referral Commission: <span class="text-success">1% (Fixed)</span></h6>

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
                                                    claimed_date,
                                                    comission
                                                    FROM user_investment_details
                                                    WHERE  user_id = $user_id AND investment_id = '" . $row['investment_id'] . "'
                                                    ORDER BY cycle ASC
                                                ";

                                                $details_result = $db->query($details_query);

                                                while ($detail = $details_result->fetch_assoc()) {
                                                    $cycle = $detail['cycle'];
                                                    $cycle_date = $detail['comission_expiry_date'];
                                                    
                                                    // Commission is already stored in the database
                                                    $commission = $detail['comission'];

                                                    $is_detail_expired = (strtotime($today) > strtotime($cycle_date));

                                                    $date1 = new DateTime(date('Y-m-d'));
                                                    $date2 = new DateTime($detail['comission_expiry_date']);
                                                    $interval = $date1->diff($date2);
                                                    
                                                    // Check if commission is ready to claim (expired and not claimed)
                                                    $can_claim = ($is_detail_expired && $detail['is_claimed'] == 0);
                                                    ?>

                                            <tr
                                                <?php if ($is_detail_expired && $detail['is_claimed'] == 1) echo 'style="opacity:0.7"'; ?>>
                                                <td class="info-value"><?php echo $cycle; ?></td>
                                                <td class="info-value"><?php echo $cycle_date; ?></td>
                                                <td class="info-value">Rs <?php echo number_format($commission, 2); ?>
                                                </td>
                                                <td class="info-value">
                                                    <?php if ($can_claim): ?>
                                                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET"
                                                        style="display: inline;"
                                                        onsubmit="return confirm('Are you sure you want to claim this referral commission of <?php echo number_format($commission, 2); ?>?');">
                                                        <input type="hidden" name="referral_detail_id"
                                                            value="<?php echo $detail['id']; ?>">
                                                        <button type="submit" class="btn-claim">
                                                            Claim Now
                                                        </button>
                                                    </form>
                                                    <?php elseif($detail['is_claimed'] == 1): ?>
                                                    <span class="badge bg-success">Paid on
                                                        <?php echo date('Y-m-d', strtotime($detail['claimed_date'])); ?></span>
                                                    <?php else: ?>
                                                    <span class="badge bg-danger">Pending
                                                        (<?php echo $interval->days; ?> days left)</span>
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
$(document).ready(function() {
    $('.modal').on('show.bs.modal', function() {
        console.log('Modal opened: ' + $(this).attr('id'));
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>