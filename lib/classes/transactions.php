<?php
class Transactions {
    
    function create($request) {
        global $db;
        extract($request);

        if($user_id == '') {
            return _("User is required");
        } else if($transaction_type == '') {
            return _("Transaction type is required");
        } else if($amount == '' || !is_numeric($amount)) {
            return _("Valid amount is required");
        } else {
            // Sanitize inputs
            $user_id = intval($user_id);
            $transaction_type = intval($transaction_type);
            $amount = floatval($amount);
            $description = $db->real_escape_string($description);
            $is_approved = 0;
            if($transaction_type == 2 || $transaction_type == 1){
                $is_approved = 1;
            }

            $proof_image = '';
            if(isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0){
                $temp = $_FILES['payment_proof']['tmp_name'];
                $name = $_FILES['payment_proof']['name'];
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $new_name = time() . '_' . rand(1000, 9999) . '.' . $ext;
                $upload_dir = 'assets/upload/proofs/';
                if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                if(move_uploaded_file($temp, $upload_dir . $new_name)){
                    $proof_image = $upload_dir . $new_name;
                }
            }

            // Insert transaction into database
            $result = $db->query("INSERT INTO transactions 
                (user_id, transaction_type, amount, description, is_approved, proof_image, created_at, updated_at) 
                VALUES ('$user_id', '$transaction_type', '$amount', '$description', '$is_approved', '$proof_image', NOW(), NOW())");
        
            if($result) {
            $transaction_id = $db->insert_id;

            $admin_query = $db->query("SELECT username FROM users WHERE user_id = " . ADMIN_ID);
            $admin = $admin_query->fetch_assoc()['username'] ?? 'Admin';

            $user_query = $db->query("SELECT username, email FROM users WHERE user_id = $user_id");
            $user_data = $user_query->fetch_assoc();
            $user = $user_data['username'] ?? 'User';
            $user_email = $user_data['email'] ?? '';

            if($transaction_type == 2){
                send_notification(
                    ADMIN_ID,
                    $user_id,
                    "$admin funded $user with PKR $amount",
                    "funded",
                    $transaction_id
                );

                if(!empty($user_email)) {
                    $subject = "Funds Received - PKR " . number_format($amount, 2);
                    $message = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e1e1e1; border-radius: 10px;'>
                            <h2 style='color: #2c3e50; text-align: center;'>Account Credited</h2>
                            <p>Dear <strong>$user</strong>,</p>
                            <p>We are writing to notify you that your account has been successfully funded by the administrator.</p>
                            
                            <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                                <table style='width: 100%;'>
                                    <tr>
                                        <td style='padding: 5px 0;'><strong>Amount:</strong></td>
                                        <td style='padding: 5px 0;'>PKR " . number_format($amount, 2) . "</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 5px 0;'><strong>Transaction ID:</strong></td>
                                        <td style='padding: 5px 0;'>#$transaction_id</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 5px 0;'><strong>Date:</strong></td>
                                        <td style='padding: 5px 0;'>" . date('F j, Y, g:i a') . "</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <p>The funds are now available in your account balance. You can log in to your dashboard to view your updated balance and transaction history.</p>
                            
                            <p style='text-align: center; margin-top: 30px;'>
                                <a href='" . SITEURL . "' style='background-color: #d4af37; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Login to Dashboard</a>
                            </p>
                            
                            <hr style='border: 0; border-top: 1px solid #eee; margin: 30px 0;'>
                            <p style='font-size: 12px; color: #7f8c8d; text-align: center;'>
                                This is an automated message, please do not reply to this email.<br>
                                &copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.
                            </p>
                        </div>
                    ";
                    send_email($user_email, $subject, $message);
                }
            }

            return "Transaction added successfully.";
            } else {
                return _("Error adding transaction: ") . $db->error;
            }
        }    
    }

    function list_transactions() {
        global $db;
        $modals = ""; 
        $currency = 'Rs';

        $result = $db->query("
            SELECT 
                t.*,
                u.username,
                u.first_name,
                u.last_name,
                u.email           
            FROM transactions t
            JOIN users u ON t.user_id = u.user_id
            WHERE t.is_approved = 1
            ORDER BY t.created_at DESC
        ");    

        echo '<div class="col-12">';
        echo '<div class="widget has-shadow">';
        echo '<div class="widget-body">';
        echo '<div class="table-responsive">';
        echo '<table id="export-table" class="table table-hover mb-0">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . _("User") . '</th>';
        echo '<th>' . _("Type") . '</th>';
        echo '<th>' . _("Amount") . '</th>';
        echo '<th>' . _("Description") . '</th>';
        echo '<th>' . _("Date") . '</th>';
        echo '<th class="text-right">' . _("Actions") . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        if($result->num_rows == 0) {
            // Show empty state message
            echo '<tr>';
            echo '<td colspan="6" class="text-center">';
            echo '<div class="empty-state p-5">';
            echo '<i class="la la-exchange la-4x text-muted mb-3"></i>';
            echo '<h5 class="text-muted">' . _("No Transactions Found") . '</h5>';
            echo '<p class="text-muted mb-3">' . _("There are no transactions to display at the moment.") . '</p>';
            echo '<a href="manage_transactions.php" class="btn btn-primary btn-md btn-golden">';
            echo '<i class="la la-plus-circle"></i> ' . _("Add Your First Transaction");
            echo '</a>';
            echo '</div>';
            echo '</td>';
            echo '</tr>';
        } else {
            while($row = $result->fetch_assoc()) {
                // Set transaction type label and class
                $type_label = '';
                $type_class = '';
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
                        $type = "Bonus Commission";
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
                
                // Format amount with sign based on type
                $amount_sign = '';
                $amount_class = '';
                
                if($row['transaction_type'] == 1 || $row['transaction_type'] == 4){
                    $amount_sign = '-';
                    $amount_class = 'text-danger';
                }
                else{
                    $amount_sign = '+';
                    $amount_class = 'text-success';
                }
                
                $user_display = wc_get_user_display_name($row['username'], $row['first_name'], $row['last_name']);
                
                echo "<tr>";
                echo "<td><strong>" . htmlspecialchars($user_display) . "</strong><br><small>" . htmlspecialchars($row['email']) . "</small></td>";
                echo "<td><span class='" . $class . "'>" . $type . "</span></td>";
                echo "<td class='" . $amount_class . "'><strong>" .$currency.' '. $amount_sign . number_format($row['amount'], 2) . "</strong></td>";
                
                // Description column with truncation
                echo "<td>";
                if(!empty($row['description'])) {
                    $desc = htmlspecialchars($row['description']);
                    if(strlen($desc) > 50) {
                        echo '<span title="' . $desc . '">' . substr($desc, 0, 55) . '...</span>';
                    } else {
                        echo $desc;
                    }
                } else {
                    echo '<em class="text-muted">' . _("No description") . '</em>';
                }
                echo "</td>";
                
                echo "<td>" . date("Y-m-d H:i", strtotime($row['created_at'])) . "</td>";
                echo "<td><div class='action-btn-container'>";
                
                // View Details Button
                echo "<button class='btn btn-warning btn-sm'
                        data-toggle='modal'
                        data-target='#transactionModal_" . $row['id'] . "'>
                        <i class='la la-eye'></i> " . _("View") . "
                      </button>";
                
                // Delete Form
                echo "<form method='post' onsubmit='return confirm(\"" . _("Are you sure you want to delete this transaction?") . "\");'>";
                echo "<input type='hidden' name='delete_transaction' value='" . $row['id'] . "'>";
                echo "<button type='submit' class='btn btn-danger btn-sm'>
                        <i class='la la-trash'></i> " . _("Delete") . "
                      </button>";
                echo "</form>";
                
                echo "</div></td>";
                echo "</tr>";

                // Generate Modal for this transaction
                $modals .= $this->generate_transaction_modal($row);
            }
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Output all modals
        echo $modals;
    }
    
    function generate_transaction_modal($transaction) {
        ob_start();
        $currency = 'Rs';
        // Set transaction type label
        $type_label = '';
        switch($transaction['transaction_type']) {
            case 1: $type_label = _("Withdrawal"); break;
            case 2: $type_label = _("Funded"); break;
            case 3: $type_label = _("ROI Commission"); break;
            case 4: $type_label = _("Transfer"); break;
            case 5: $type_label = _("Referral Commission"); break;
            case 6: $type_label = _("Bonus Commission"); break;
            case 7: $type_label = _("Investment Released"); break;
            default: $type_label = _("Unknown");
        }
        
        // Format amount with sign
        // $amount_sign = ($transaction['transaction_type'] == 1) ? '-' : '+';
        if($transaction['transaction_type'] == 1 || $transaction['transaction_type'] == 4){
            $amount_sign = '-';
            $amount_class = 'text-danger';
        }else{
            $amount_sign = '+';
            $amount_class = 'text-success';
        }        
        // $amount_class = ($transaction['transaction_type'] == 1) ? 'text-danger' : 'text-success';
        ?>

<div class="modal fade" id="transactionModal_<?php echo $transaction['id']; ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="text-dark text-center"><?php echo _("Transaction Details"); ?></h5>
            </div>
            <div class="modal-body">
                <!-- Transaction Info Cards -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0 text-white"><?php echo _("User Information"); ?></h6>
                            </div>
                            <div class="card-body">
                                 <p><strong><?php echo _("User:"); ?></strong>
                                     <?php echo wc_get_user_display_name($transaction['username'], $transaction['first_name'], $transaction['last_name']); ?>
                                 </p>
                                <p><strong><?php echo _("Email:"); ?></strong>
                                    <?php echo $transaction['email'] ? htmlspecialchars($transaction['email']) : 'N\A'; ?>
                                </p>
                                <p><strong><?php echo _("User ID:"); ?></strong> #<?php echo $transaction['user_id']; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0 text-white"><?php echo _("Transaction Information"); ?></h6>
                            </div>
                            <div class="card-body">
                                <p><strong><?php echo _("Transaction ID:"); ?></strong>
                                    #<?php echo $transaction['id']; ?></p>
                                <p><strong><?php echo _("Type:"); ?></strong>
                                <?php
                                $class = null;
                                    switch ($transaction['transaction_type']) {
                                        case 1:
                                            $class = 'badge text-bg-danger';
                                            break;                    
                                        case 2:
                                            $class = 'badge text-bg-success';
                                            break;
                                        case 3:
                                            $class = 'badge text-bg-success';
                                            break;
                                        case 4:
                                            $class = 'badge text-light bg-warning';
                                            break;
                                        case 5:
                                            $class = 'badge text-light bg-info';
                                            break;
                                        case 6:
                                            $class = 'badge text-light bg-dark';
                                            break;                                        
                                        case 7:
                                            $class = 'badge text-light bg-info';
                                            break;                                          
                                        default:
                                            $class = 'badge text-bg-secondary';
                                    }
                                ?>
                                <span class="<?php echo $class; ?>">
                                <?php echo $type_label; ?>
                                </span>
                                </p>
                                <p><strong><?php echo _("Amount:"); ?></strong>
                                    <span class="<?php echo $amount_class; ?>">
                                        <?php echo $currency .' '. $amount_sign; ?><?php echo number_format($transaction['amount'], 2); ?>
                                    </span>
                                </p>
                                <p><strong><?php echo _("Created:"); ?></strong>
                                    <?php echo date("F j, Y, g:i a", strtotime($transaction['created_at'])); ?></p>
                                <p><strong><?php echo _("Last Updated:"); ?></strong>
                                    <?php echo date("F j, Y, g:i a", strtotime($transaction['updated_at'])); ?></p>
                                <?php if(!empty($transaction['proof_image'])): ?>
                                <p><strong><?php echo _("Payment Proof:"); ?></strong>
                                    <br><a href="<?php echo htmlspecialchars($transaction['proof_image']); ?>" target="_blank" class="btn btn-sm btn-info text-white mt-1"><i class="la la-image"></i> <?php echo _("View Screenshot"); ?></a>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0 text-white"><?php echo _("Description"); ?></h6>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($transaction['description'])): ?>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($transaction['description'])); ?></p>
                        <?php else: ?>
                        <p class="text-muted mb-0">
                            <em><?php echo _("No description provided for this transaction."); ?></em></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-golden" data-dismiss="modal">
                    <i class="la la-close"></i> <?php echo _("Close"); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php
        return ob_get_clean();
    }
    
    function delete_transaction($id) {
        global $db;
        
        $id = intval($id);
        
        // Optional: Add validation to check if transaction exists
        $check = $db->query("SELECT id FROM transactions WHERE id = '$id'");
        if($check->num_rows == 0) {
            return _("Transaction not found.");
        }
        
        // Delete the transaction
        $result = $db->query("DELETE FROM transactions WHERE id = '$id'");
        
        if($result) {
            return _("Transaction deleted successfully.");
        } else {
            return _("Error deleting transaction: ") . $db->error;
        }
    }
    
    // Optional: Add method to get transaction statistics
    function get_transaction_stats() {
        global $db;
        
        $stats = array(
            'total'               => 0,
            'withdrawals'         => 0,
            'funded'              => 0,
            'roi_commission'      => 0,
            'referral_commission' => 0,
            'transfers'           => 0,            
            'today_count'         => 0,
            'today_amount'        => 0,
            'bonus'               => 0,
            'released'            => 0
        );
        
        // Total transactions count
        $result = $db->query("SELECT COUNT(*) as total FROM transactions");
        if($result) {
            $stats['total'] = $result->fetch_assoc()['total'];
        }
        
        if($stats['total'] > 0) {
            // Total amount by type
            $result = $db->query("
                SELECT 
                    SUM(CASE WHEN transaction_type = 1 THEN amount ELSE 0 END) as total_withdrawals,
                    SUM(CASE WHEN transaction_type = 2 THEN amount ELSE 0 END) as total_funded,
                    SUM(CASE WHEN transaction_type = 3 THEN amount ELSE 0 END) as total_roi_commission,
                    SUM(CASE WHEN transaction_type = 4 THEN amount ELSE 0 END) as total_transfers,
                    SUM(CASE WHEN transaction_type = 5 THEN amount ELSE 0 END) as total_referral_commission,
                    SUM(CASE WHEN transaction_type = 6 THEN amount ELSE 0 END) as total_bonus,
                    SUM(CASE WHEN transaction_type = 7 THEN amount ELSE 0 END) as total_released
                FROM transactions
            ");
            if($result) {
                $amounts                      = $result->fetch_assoc();
                $stats['withdrawals']         = $amounts['total_withdrawals'] ?? 0;
                $stats['funded']              = $amounts['total_funded'] ?? 0;
                $stats['roi_commission']      = $amounts['total_roi_commission'] ?? 0;
                $stats['transfers']           = $amounts['total_transfers'] ?? 0;                
                $stats['referral_commission'] = $amounts['total_referral_commission'] ?? 0;
                $stats['bonus']               = $amounts['total_bonus'] ?? 0;        
                $stats['released']            = $amounts['total_released'] ?? 0;        
            }
            
            // Today's transactions
            $result = $db->query("
                SELECT COUNT(*) as today_count, SUM(amount) as today_amount
                FROM transactions 
                WHERE DATE(created_at) = CURDATE()
            ");
            if($result) {
                $today = $result->fetch_assoc();
                $stats['today_count'] = $today['today_count'] ?? 0;
                $stats['today_amount'] = $today['today_amount'] ?? 0;
            }
        }
        
        return $stats;
    }

    function display_balance($user_id)
    {
        global $db;

        $balance = $this->get_available_balance($user_id);
        
        // Prevent negative balance display
        if ($balance < 0) {
            $balance = 0;
        }

        // Show k only for exact thousands
        if ($balance >= 1000 && $balance % 1000 == 0 && $balance < 1000000) {
            return ($balance / 1000) . 'k';
        }

        // Show M only for exact millions
        if ($balance >= 1000000 && $balance % 1000000 == 0) {
            return ($balance / 1000000) . 'M';
        }

        return 'PKR '.$balance;
    }

    function get_balance($user_id)
    {
        global $db;

        $investment_sql = $db->query("SELECT amount FROM user_investments WHERE user_id = '$user_id'");

        $sql = "
            SELECT 
                COALESCE(SUM(
                    CASE 
                        WHEN transaction_type = 1 THEN -amount
                        WHEN transaction_type = 2 THEN amount     
                        WHEN transaction_type = 3 THEN amount    
                        WHEN transaction_type = 4 THEN -amount     
                        WHEN transaction_type = 5 THEN amount
                        WHEN transaction_type = 6 THEN amount
                        WHEN transaction_type = 7 THEN amount
                    END
                ),0) AS balance
            FROM transactions
            WHERE user_id = ?
            AND is_approved = 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $result  = $stmt->get_result();
        $row     = $result->fetch_assoc();
        $balance = (float)$row['balance'];
        return $balance;
    }    
    function investement($user_id)
    {
        global $db;

        $investment_sql = $db->query("SELECT amount FROM user_investments WHERE user_id = '$user_id' AND is_released = 0");
        $investement = 0;
        if ($investment_sql && $investment_sql->num_rows > 0) {

            while($row = $investment_sql->fetch_assoc()){
            $investement += (float)$row['amount'];
            }
        }       

        return $investement;
    }
        
    function profit($user_id)
    {
        global $db;
        $sql = "
            SELECT 
                COALESCE(SUM(
                    CASE    
                        WHEN transaction_type = 3 THEN amount      
                        WHEN transaction_type = 5 THEN amount
                        WHEN transaction_type = 6 THEN amount
                    END
                ),0) AS balance
            FROM transactions
            WHERE user_id = ?
            AND is_approved = 1
        ";

        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $result  = $stmt->get_result();
        $row     = $result->fetch_assoc();
        $balance = (float)$row['balance'];

        return $balance;
    }
    
    function transfer($sender_id, $receiver_id, $amount)
    {
        global $db;
        // Get sender username
        $s = $db->query("SELECT username FROM users WHERE user_id = $sender_id");
        $sender = $s->fetch_assoc()['username'] ?? 'User';

        // Get receiver username
        $r = $db->query("SELECT username FROM users WHERE user_id = $receiver_id");
        $receiver = $r->fetch_assoc()['username'] ?? 'User';

        $amount = floatval($amount);

        if($amount <= 0){
            return false;
        }

        // Check available balance before proceeding
        $available_balance = $this->get_available_balance($sender_id);
        if($amount > $available_balance){
            return false;
        }

        $db->begin_transaction();

        try {

            // Sender debit
            $stmt1 = $db->prepare("
                INSERT INTO transactions
                (user_id,transaction_type,amount,is_approved,description,created_at,updated_at)
                VALUES(?,4,?,1,?,NOW(),NOW())
            ");
            $desc1 = "Wallet transfer sent to $receiver";
            $stmt1->bind_param("ids",$sender_id,$amount,$desc1);
            $stmt1->execute();


            // Receiver credit
            $stmt2 = $db->prepare("
                INSERT INTO transactions
                (user_id,transaction_type,amount,is_approved,description,created_at,updated_at)
                VALUES(?,2,?,1,?,NOW(),NOW())
            ");
            $desc2 = "Wallet transfer received from $sender";            
            $stmt2->bind_param("ids",$receiver_id,$amount,$desc2);
            $stmt2->execute();

            $transaction_id = $db->insert_id;

            $db->commit();

            $message = "$sender sent PKR $amount to $receiver";

            send_notification(
                ADMIN_ID,
                $sender_id,
                $message,
                "transfer",
                $transaction_id
            );

            return true;

        } catch(Exception $e){

            $db->rollback();
            return false;
        }
    }     
    function withdrawl($user_id, $amount)
    {
        global $db;

        $amount = floatval($amount);

        if($amount <= 0){
            return false;
        }

        // Check available balance before proceeding
        $available_balance = $this->get_available_balance($user_id);
        if($amount > $available_balance){
            return false;
        }

        $result = $db->query("SELECT username FROM users WHERE user_id = $user_id");

        $username = 'User';
        if($result && $result->num_rows > 0){
            $row = $result->fetch_assoc();
            $username = $row['username'];
        }

        $db->begin_transaction();

        try {

            $stmt1 = $db->prepare("
                INSERT INTO transactions
                (user_id, transaction_type, amount, is_approved, description, created_at, updated_at)
                VALUES(?, 1, ?, 0, 'Withdrawl Request', NOW(), NOW())
            ");
            $stmt1->bind_param("id", $user_id, $amount);
            $stmt1->execute();

            $transaction_id = $db->insert_id;

            $db->commit();

            $message = "$username sent you a withdrawal request of PKR $amount";

            send_notification(
                ADMIN_ID,
                $user_id,
                $message,
                "withdrawal",
                $transaction_id
            );

            return true;

        } catch(Exception $e){

            $db->rollback();
            return false;
        }
    }  

    function get_pending_withdrawal_amount($user_id)
    {
        global $db;
        $sql = "SELECT COALESCE(SUM(amount), 0) as pending_amount FROM transactions WHERE user_id = ? AND transaction_type = 1 AND is_approved = 0";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (float)$row['pending_amount'];
    }

    function get_available_balance($user_id)
    {
        $total_balance = $this->get_balance($user_id);
        $pending_withdrawals = $this->get_pending_withdrawal_amount($user_id);
        return $total_balance - $pending_withdrawals;
    }

    function expire_requests()
    {
        global $db;
        // Expire withdrawal requests older than 36 hours (transaction_type = 1, is_approved = 0)
        // We use is_approved = 2 to mark as Expired
        $sql = "UPDATE transactions SET is_approved = 2, updated_at = NOW(), description = CONCAT(description, ' (Expired after 36h)') 
                WHERE transaction_type = 1 AND is_approved = 0 AND created_at < DATE_SUB(NOW(), INTERVAL 36 HOUR)";
        $db->query($sql);
    }
}
?>