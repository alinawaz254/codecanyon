<?php
  require_once("lib/system_load.php");
  //This loads system.

  $datatables = 1;

  //user Authentication.
  authenticate_user('admin');
  
  // Ensure the column exists
  $res = $db->query("SHOW COLUMNS FROM transactions LIKE 'proof_image'");
  if($res && $res->num_rows == 0){
      $db->query("ALTER TABLE transactions ADD proof_image VARCHAR(500) NULL AFTER amount");
  }

  // Handle approval action
  if(isset($_POST['approve_withdrawal']) && isset($_POST['transaction_id'])) {
      $transaction_id = (int)$_POST['transaction_id'];
      
      $proof_url = '';
      if(isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0){
          $temp = $_FILES['payment_proof']['tmp_name'];
          $name = $_FILES['payment_proof']['name'];
          $ext = pathinfo($name, PATHINFO_EXTENSION);
          $new_name = time() . '_' . rand(1000, 9999) . '.' . $ext;
          $upload_dir = 'assets/upload/proofs/';
          if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
          if(move_uploaded_file($temp, $upload_dir . $new_name)){
              $proof_url = $upload_dir . $new_name;
          }
      }

      if($proof_url != '') {
          $update_sql = "UPDATE transactions SET is_approved = 1, proof_image = '$proof_url', updated_at = NOW() 
                     WHERE id = $transaction_id AND transaction_type IN (1, 4)";
      } else {
          $update_sql = "UPDATE transactions SET is_approved = 1, updated_at = NOW() 
                     WHERE id = $transaction_id AND transaction_type IN (1, 4)";
      }
      
      if(mysqli_query($db, $update_sql)) {
          // Send email notification to user
          $info_query = "SELECT t.amount, u.username, u.email 
                        FROM transactions t 
                        JOIN users u ON t.user_id = u.user_id 
                        WHERE t.id = $transaction_id";
          $info_res = mysqli_query($db, $info_query);
          
          if($info_res && mysqli_num_rows($info_res) > 0) {
              $info = mysqli_fetch_assoc($info_res);
              $amount = $info['amount'];
              $username = $info['username'];
              $user_email = $info['email'];
              
              if(!empty($user_email)) {
                  $subject = "Withdrawal Approved - PKR " . number_format($amount, 2);
                  $message = "
                      <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e1e1e1; border-radius: 10px;'>
                          <h2 style='color: #27ae60; text-align: center;'>Withdrawal Request Approved</h2>
                          <p>Dear <strong>$username</strong>,</p>
                          <p>We are pleased to inform you that your withdrawal request has been reviewed and approved by the administrator.</p>
                          
                          <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                              <table style='width: 100%;'>
                                  <tr>
                                      <td style='padding: 5px 0;'><strong>Approved Amount:</strong></td>
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
                                  <tr>
                                      <td style='padding: 5px 0;'><strong>Status:</strong></td>
                                      <td style='padding: 5px 0; color: #27ae60;'><strong>Approved</strong></td>
                                  </tr>
                              </table>
                          </div>
                          
                          <p>The funds have been processed according to your withdrawal details.</p>
                          
                          <p style='text-align: center; margin-top: 30px;'>
                              <a href='" . SITEURL . "' style='background-color: #d4af37; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>View Dashboard</a>
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

          $success_message = _("Withdrawal request approved successfully.");
      } else {
          $error_message = _("Error approving withdrawal: ") . mysqli_error($db);
      }

      header("Location: withdrawl.php");
      exit;
  }
  
  $page_title = _("Withdrawal Requests"); //You can edit this to change your page title.
  require_once("lib/includes/header.php"); //including header file.
?>

<div class="row">
  <div class="col-xl-12">
      <!-- Sorting -->
      <div class="widget has-shadow">
          <div class="widget-body">
            
              
              <div class="table-responsive">
                  <table id="export-table" class="table mb-0">
                    <thead>
                      <tr>
                          <th><?php _e("Username"); ?></th>
                          <th><?php _e("Amount"); ?></th>
                          <th><?php _e("Withdrawn On"); ?></th>
                          <th><?php _e("Status"); ?></th>
                          <th><?php _e("Action"); ?></th>
                          <th><?php _e("Payment Proof"); ?></th>
                      </tr>
                    </thead>

                    <tbody>
                        <?php
                        // Get withdrawal requests from transactions table
                        // transaction_type = 1 for withdrawals, 4 for transfers (both need approval)
                        
                        $sql = "SELECT t.*, u.username, u.first_name, u.last_name 
                                FROM transactions t
                                LEFT JOIN users u ON t.user_id = u.user_id
                                WHERE t.transaction_type = 1
                                ORDER BY 
                                    CASE WHEN t.is_approved = 0 THEN 0 ELSE 1 END,
                                    t.created_at DESC";
                        
                        $result = mysqli_query($db, $sql);
                        
                        if(!$result) {
                            echo "<tr><td colspan='5' class='text-center text-danger'>Database error: " . mysqli_error($db) . "</td></tr>";
                        } else if(mysqli_num_rows($result) > 0) {
                            while($withdrawal = mysqli_fetch_assoc($result)) {
                                $status = ($withdrawal['is_approved'] == 1) ? 'Approved' : 'Pending';
                                $status_class = ($withdrawal['is_approved'] == 1) ? 'badge text-success' : 'badge text-warning';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(wc_get_user_display_name($withdrawal['username'] ?? 'N/A', $withdrawal['first_name'] ?? '', $withdrawal['last_name'] ?? '')); ?></td>
                                    <td><?php echo number_format($withdrawal['amount'], 2); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($withdrawal['created_at'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo $status_class; ?>"><b><?php _e($status); ?></b></span>
                                        <?php if($withdrawal['transaction_type'] == 4): ?>
                                            <small class="d-block text-muted">(Transfer)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($withdrawal['is_approved'] == 0): ?>
                                            <form id="approve_form_<?php echo $withdrawal['id']; ?>" method="post" enctype="multipart/form-data" style="display: inline;" onsubmit="return confirm('<?php _e("Are you sure you want to approve this withdrawal request?"); ?>');">
                                                <input type="hidden" name="transaction_id" value="<?php echo $withdrawal['id']; ?>">
                                                <button type="submit" name="approve_withdrawal" class="btn btn-success btn-sm">
                                                    <i class="la la-check"></i> <?php _e("Approve"); ?>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-success"><i class="la la-check-circle"></i> <?php _e("Approved"); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($withdrawal['is_approved'] == 0): ?>
                                            <input type="file" form="approve_form_<?php echo $withdrawal['id']; ?>" name="payment_proof" class="form-control form-control-sm" accept="image/*" style="width: 220px;">
                                        <?php else: ?>
                                            <?php if(!empty($withdrawal['proof_image'])): ?>
                                                <a href="<?php echo htmlspecialchars($withdrawal['proof_image']); ?>" target="_blank" class="btn btn-sm btn-info">View Proof</a>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="6" class="text-center"><?php _e("No withdrawal or transfer requests found."); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                  </table>
              </div><!-- Table responsive /-->
          </div><!-- Widget body /-->
      </div><!-- widget /-->
  </div><!-- column /-->
</div><!-- Row /-->

<?php
  require_once("lib/includes/footer.php");
?>