<?php
require_once("lib/system_load.php");

authenticate_user('all');

$user_id = (int)$_SESSION['user_id'];

$page_title = _("My Investments");

require_once("lib/includes/header.php");

// First query to get all investments for the user
$investment_query = "
SELECT 
    ui.investment_id,
    ui.user_id,
    ui.plan_id,
    ui.amount,
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
// $today = "2026-06-19"; // test date

?>

<style>
    /* Mobile Modal Fix */
    @media (max-width:768px){

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
            <h4>My Investments</h4>
        </div>

        <div class="widget-body table-responsive">
            <table class="table table-bordered table-striped">
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
                            cycle,
                            comission,
                            comission_expiry_date,
                            created_at
                        FROM user_investment_details 
                        WHERE investment_id = '" . $investment['investment_id'] . "'
                        ORDER BY cycle ASC
                        ";
                        
                        $details_result = $db->query($details_query);
                        $has_details = ($details_result && $details_result->num_rows > 0);
                    ?>

                        <tr <?php if ($is_expired) echo 'style="opacity:0.5;background-color:#f5f5f5;"'; ?>>
                            <td><?php echo $investment['plan_name']; ?></td>
                            <td><?php echo number_format($amount, 2); ?></td>
                            <td><?php echo $investment['issue_date']; ?></td>

                            <td>
                                <button class="btn btn-golden btn-sm"
                                    data-toggle="modal"
                                    data-target="#investmentModal_<?php echo $investment['investment_id']; ?>">
                                    View
                                </button>

                                <?php if ($is_expired): ?>
                                    <span class="badge badge-secondary ml-2">Inactive</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <?php
                        ob_start();
                        ?>

                        <div class="modal fade"
                            id="investmentModal_<?php echo $investment['investment_id']; ?>"
                            tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content investment-modal">
                                    <div class="modal-header investment-header">
                                        <h5 class="modal-title">
                                            <?php echo $investment['plan_name']; ?> Investment Details
                                        </h5>

                                        <button type="button"
                                            class="btn-close-investment"
                                            data-dismiss="modal">
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
                                                    <th class="info-value">Commission (<?php echo $commission_percent; ?>%)</th>
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
                                                            <td class="info-value"><?php echo $detail['comission_expiry_date']; ?></td>
                                                            <td class="info-value"><?php echo number_format($detail['comission'], 2); ?></td>
                                                            <td class="info-value">
                                                                <?php if ($is_detail_expired): ?>
                                                                    <span class="badge badge-success"><a href="#" style="color: green;">Claim Now</a> </span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-danger">Unpaid <?php echo '('.$interval->days .' days left to claim)'; ?></span>
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
                                                                    <span class="badge badge-warning">Pending</span>
                                                                <?php elseif ($today > $cycle_date): ?>
                                                                    <span class="badge badge-secondary">Expired</span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-info">Future</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>

                                                    <?php } ?>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="modal-footer investment-footer">
                                        <button class="btn btn-golden btn-md"
                                            data-dismiss="modal">
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