<?php
require_once("lib/system_load.php");

// User Authentication
authenticate_user('all');

$user_id = (int)$_SESSION['user_id'];
$page_title = _("My Transactions");

require_once("lib/includes/header.php");
?>

<style>
    /* Clean table styling matching original theme */
    .mywidget.wc_data {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .widget-header {
        border-bottom: 1px solid #e9ecef;
        padding: 15px 20px;
    }
    
    .widget-header h4 {
        color: #495057;
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        border-bottom: 2px solid #dee2e6;
        border-top: none;
        color: #6c757d;
        font-size: 13px;
        font-weight: 600;
        padding: 12px 15px;
        text-transform: uppercase;
        background-color: #f8f9fa;
    }
    
    .table tbody td {
        padding: 15px;
        vertical-align: middle;
        color: #495057;
        font-size: 14px;
        border-top: 1px solid #e9ecef;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #fcfcfc;
    }
    
    .amount-value {
        font-weight: 500;
        color: #28a745;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }
    
    .empty-state i {
        font-size: 48px;
        color: #adb5bd;
        margin-bottom: 15px;
    }
    
    .empty-state h5 {
        color: #495057;
        font-size: 16px;
        margin-bottom: 10px;
    }
    
    .empty-state p {
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 20px;
    }
    
    .btn-golden {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529;
        font-size: 13px;
        padding: 6px 15px;
        border-radius: 4px;
    }
    
    .btn-golden:hover {
        background-color: #e0a800;
        border-color: #e0a800;
        color: #212529;
    }
    
    .investment-details {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }
    
    /* Rupees symbol styling */
    .rupees-symbol {
        font-weight: 500;
        margin-right: 2px;
    }
</style>

<div class="mywidget wc_data">
    <div class="widget has-shadow">
        <div class="widget-header bordered">
            <h4><?php _e("My Claimed Transactions"); ?></h4>
        </div>

        <div class="widget-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><?php _e("Plan Name"); ?></th>
                        <th><?php _e("Invested Amount"); ?></th>
                        <th><?php _e("Commission Earned"); ?></th>
                        <th><?php _e("Invested On"); ?></th>
                        <th><?php _e("Claimed Date"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get only claimed investments
                    $transactions_query = "
                        SELECT 
                            ui.investment_id,
                            ui.amount as invested_amount,
                            ui.issue_date as invested_on,
                            ip.plan_name,
                            uid.cycle,
                            uid.comission as commission_amount,
                            uid.claimed_date
                        FROM user_investments ui
                        JOIN investment_plans ip ON ip.plan_id = ui.plan_id
                        JOIN user_investment_details uid ON uid.investment_id = ui.investment_id
                        WHERE ui.user_id = '$user_id' 
                        AND uid.is_claimed = 1
                        ORDER BY uid.claimed_date DESC, ui.investment_id DESC
                    ";
                    
                    $transactions = $db->query($transactions_query);
                    
                    if ($transactions && $transactions->num_rows > 0) {
                        while ($row = $transactions->fetch_assoc()):
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo $row['plan_name']; ?></strong>
                                </td>
                                <td class="amount-value">
                                    <span class="rupees-symbol">Rs</span> <?php echo number_format($row['invested_amount'], 2); ?>
                                </td>
                                <td class="amount-value">
                                    <span class="rupees-symbol">Rs</span> <?php echo number_format($row['commission_amount'], 2); ?>
                                </td>
                                <td><?php echo date('d M Y', strtotime($row['invested_on'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['claimed_date'])); ?></td>
                            </tr>
                            <?php
                        endwhile;
                    } else {
                        ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="la la-folder-open"></i>
                                    <h5><?php _e("No Claimed Transactions Found"); ?></h5>
                                    <p class="text-muted"><?php _e("You haven't claimed any commissions yet."); ?></p>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            
            <?php if ($transactions && $transactions->num_rows > 0): ?>
            <div class="p-3 text-right">
                <strong><?php _e("Total Commission Earned:"); ?> </strong>
                <span class="amount-value">
                    <span class="rupees-symbol">Rs</span> 
                    <?php
                    // Calculate total commission
                    $total_query = "
                        SELECT SUM(uid.comission) as total_commission
                        FROM user_investment_details uid
                        JOIN user_investments ui ON ui.investment_id = uid.investment_id
                        WHERE ui.user_id = '$user_id' AND uid.is_claimed = 1
                    ";
                    $total_result = $db->query($total_query);
                    $total_row = $total_result->fetch_assoc();
                    echo number_format($total_row['total_commission'] ?? 0, 2);
                    ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once("lib/includes/footer.php");
?>