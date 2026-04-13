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
            <table class="table dataTable">
                <thead>
                    <tr>
                        <th><?php _e("Amount"); ?></th>
                        <th><?php _e("Transaction Type"); ?></th>
                        <th><?php _e("Description"); ?></th>
                        <th><?php _e("On"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get only claimed investments
                    $transactions_query = "
                        SELECT 
                        t.user_id,
                        t.transaction_type,
                        t.amount,
                        t.is_approved,
                        t.description,
                        t.proof_image,
                        t.created_at
                        FROM transactions t WHERE t.user_id = '$user_id' ORDER BY t.created_at DESC
                    ";
                    
                    $transactions = $db->query($transactions_query);
                    
                    if ($transactions && $transactions->num_rows > 0) {
                        while ($row = $transactions->fetch_assoc()):
                            $type = null; 
                            $class = ''; 
                                switch ($row['transaction_type']) {
                                    case 1:
                                        $type = "Withdrawal";
                                        $class = 'badge text-bg-danger';
                                        break;                    
                                    case 2:
                                        $type = "Funded";
                                        $class = 'badge text-bg-success';
                                        break;
                                    case 3:
                                        $type = "ROI Commission";
                                        $class = 'badge text-bg-success';
                                        break;
                                    case 4:
                                        $type = "Transfer";
                                        $class = 'badge text-light bg-warning';
                                        break;
                                    case 5:
                                        $type = "Referral Commission";
                                        $class = 'badge text-light bg-info';
                                        break;
                                    case 6:
                                        $type = "Referral Commission";
                                        $class = 'badge text-light bg-dark';
                                        break;                                      
                                    case 7:
                                        $type = "Investment Released";
                                        $class = 'badge text-light bg-info';
                                        break;                                        
                                    default:
                                        $type = "Unknown";
                                        $class = 'badge text-bg-secondary';
                                }
                            ?>
                            <tr>
                                <td class="amount-value">
                                    <span class="rupees-symbol">Rs</span> <?php echo number_format($row['amount'], 2); ?>
                                </td>                                
                                <td class="type-value">
                                    <span class="<?php echo $class ?>"><?php echo $type; ?></span>
                                </td>                                
                                <td class="desc-value">
                                     <?php echo $row['description'] ; ?>
                                     <?php if($row['transaction_type'] == 1 && !empty($row['proof_image'])): ?>
                                         <br><a href="<?php echo htmlspecialchars($row['proof_image']); ?>" target="_blank" class="badge text-bg-info text-white mt-1" style="text-decoration:none; padding: 5px 10px; font-weight: normal;"><i class="la la-image"></i> View Proof</a>
                                     <?php endif; ?>
                                </td>
                             
                                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            </tr>
                            <?php
                        endwhile;
                    } else {
                        ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="la la-folder-open"></i>
                                    <h5><?php _e("No Transactions Found"); ?></h5>
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
                        SELECT 
                            COALESCE(SUM(
                                CASE 
                                    WHEN transaction_type = 3 THEN amount      
                                    WHEN transaction_type = 5 THEN amount   
                                    WHEN transaction_type = 6 THEN amount   
                                END
                            ),0) AS total
                        FROM transactions
                        WHERE user_id = '$user_id' GROUP BY user_id
                    ";
                    $total_result = $db->query($total_query);
                    $total_row = $total_result->fetch_assoc();

                    echo number_format($total_row['total'] ?? 0, 2);
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