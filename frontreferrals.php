<?php
require_once("lib/system_load.php");

authenticate_user('all');

$user_id = (int)$_SESSION['user_id'];

$page_title = _("My Referrals");

require_once("lib/includes/header.php");

$query = "
SELECT 
    u.username,
    u.user_id as referred_user_id,
    COALESCE(ui.amount,0) as amount,
    ui.issue_date,
    ui.investment_id,
    ip.plan_name,
    ip.total_cycles,
    ip.cycle_days,
    ip.commission,
    COALESCE(ui.amount * 0.01,0) AS referral_commission
FROM users u
LEFT JOIN user_investments ui ON ui.user_id = u.user_id
LEFT JOIN investment_plans ip ON ip.plan_id = ui.plan_id
WHERE u.referral_id = $user_id
ORDER BY ui.issue_date DESC
";

$result = $db->query($query);
?>

<style>
    .investment-modal{
        border-radius:10px;
        box-shadow:0 10px 30px rgba(0,0,0,0.2);
    }

    .investment-header{
        background:#2c304d;
        color:#fff;
        padding:15px 20px;
        display:flex;
        justify-content:space-between;
        align-items:center;
    }

    .btn-close-investment{
        background:#ff4d4d;
        border:none;
        color:#fff;
        font-size:20px;
        width:35px;
        height:35px;
        border-radius:50%;
        cursor:pointer;
    }

    .btn-close-investment:hover{
        background:#ff0000;
    }

    .investment-info{
        display:flex;
        justify-content:space-between;
        margin-bottom:15px;
    }

    .info-label{
        display:block;
        font-size:12px;
        color:#777;
    }

    .info-value{
        font-size:12px;
        font-weight:600;
    }

    .cycles-title{
        margin:10px 0 15px 0;
        font-weight:600;
    }

    .investment-table thead{
        background:#f5f5f5;
    }

    .investment-table th{
        font-weight:600;
    }

    .investment-footer{
        justify-content:flex-end;
        border-top: 0px;
        padding: 7px 9px 8px 8px !important;
    }
    .modal-title{
        color: white;
    }
</style>

<div class="mywidget wc_data">

    <div class="widget has-shadow">

        <div class="widget-header bordered">
            <h4>My Referrals</h4>
        </div>

        <div class="widget-body">

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
                    
                    if($result->num_rows == 0): 
                    ?>

                        <tr>
                            <td colspan="5" class="text-center">No referrals found</td>
                        </tr>

                    <?php else: ?>

                        <?php while($row = $result->fetch_assoc()): 
                        
                        // Calculate investment details if exists
                        if($row['investment_id']):
                            $amount = $row['amount'];
                            $cycle_days = $row['cycle_days'];
                            $total_cycles = $row['total_cycles'];
                            $commission = ($amount * 1) / 100;
                            $issue_date = strtotime($row['issue_date']);
                            
                            $expiry_date = date(
                                "Y-m-d",
                                strtotime("+".($cycle_days * $total_cycles)." days",$issue_date)
                            );
                            
                            $today = date("Y-m-d");
                            $is_expired = ($today > $expiry_date);
                        endif;
                        ?>
                        
                        <tr>

                            <td><?php echo $row['username']; ?></td>

                            <td><?php echo $row['plan_name'] ?: "-"; ?></td>

                            <td><?php echo number_format((float)$row['amount'],2); ?></td>

                            <td><?php echo $row['issue_date'] ?: "-"; ?></td>
                            
                            <td>
                                <?php if($row['investment_id']): ?>
                                    <button class="btn btn-primary btn-sm"
                                        data-toggle="modal"
                                        data-target="#referralModal_<?php echo $row['investment_id']; ?>_<?php echo $row['referred_user_id']; ?>">
                                        View Details
                                    </button>
                                <?php else: ?>
                                    <span class="badge badge-secondary">No Investment</span>
                                <?php endif; ?>
                            </td>

                        </tr>

                        <?php 
                        /* Build modal for this referral's investment if exists */
                        if($row['investment_id']):
                            ob_start();
                        ?>

                        <div class="modal fade"
                            id="referralModal_<?php echo $row['investment_id']; ?>_<?php echo $row['referred_user_id']; ?>"
                            tabindex="-1">

                            <div class="modal-dialog modal-lg modal-dialog-centered">

                                <div class="modal-content investment-modal">

                                    <div class="modal-header investment-header">

                                        <h5 class="modal-title">
                                            <?php echo $row['username']; ?>'s Investment Details
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
                                                <span class="info-label">Referred User</span>
                                                <span class="info-value"><?php echo $row['username']; ?></span>
                                            </div>

                                            <div>
                                                <span class="info-label">Package</span>
                                                <span class="info-value"><?php echo $row['plan_name']; ?></span>
                                            </div>

                                            <div>
                                                <span class="info-label">Amount</span>
                                                <span class="info-value"><?php echo number_format($amount,2); ?></span>
                                            </div>

                                            <div>
                                                <span class="info-label">Date Issued</span>
                                                <span class="info-value"><?php echo $row['issue_date']; ?></span>
                                            </div>

                                        </div>

                                        <hr>

                                        <h6 class="cycles-title">Your Referral Commission: <span class="text-success">1%</span></h6>

                                        <table class="table investment-table">

                                            <thead>
                                                <tr>
                                                    <th class="info-value">Cycle</th>
                                                    <th class="info-value">Commission Date</th>
                                                    <th class="info-value">Commission (1%)</th>
                                                </tr>
                                            </thead>

                                            <tbody>

                                                <?php
                                                for($i=1;$i<=$total_cycles;$i++){

                                                    $cycle_date = date(
                                                        "Y-m-d",
                                                        strtotime("+".($cycle_days*$i)." days",$issue_date)
                                                    );
                                                ?>

                                                <tr>

                                                    <td class="info-value"><?php echo $i; ?></td>
                                                    <td class="info-value"><?php echo $cycle_date; ?></td>
                                                    <td class="info-value"><?php echo number_format($commission,2); ?></td>

                                                </tr>

                                                <?php } ?>

                                            </tbody>

                                        </table>

                                    </div>

                                    <div class="modal-footer investment-footer">

                                        <button class="btn btn-danger"
                                            data-dismiss="modal">
                                            Close
                                        </button>

                                    </div>

                                </div>
                            </div>
                        </div>

                        <?php
                            $modals .= ob_get_clean();
                        endif;
                        
                        endwhile; ?>

                    <?php endif; ?>

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
$(document).ready(function(){

    $('.modal').on('show.bs.modal', function () {
        console.log('Modal opened');
    });

});
</script>