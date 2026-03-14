<?php
  require_once("lib/system_load.php");
  //This loads system.

  //user Authentication.
  authenticate_user('admin');
  
  // Handle approval action
  if(isset($_POST['approve_withdrawal']) && isset($_POST['transaction_id'])) {
      $transaction_id = (int)$_POST['transaction_id'];
      
      // Update the is_approved column to true (1)
      $update_sql = "UPDATE transactions SET is_approved = 1, updated_at = NOW() 
                     WHERE id = $transaction_id AND transaction_type IN (1, 4)";
      
      if(mysqli_query($db, $update_sql)) {
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
                  <table class="table mb-0">
                    <thead>
                      <tr>
                          <th><?php _e("Username"); ?></th>
                          <th><?php _e("Amount"); ?></th>
                          <th><?php _e("Withdrawn On"); ?></th>
                          <th><?php _e("Status"); ?></th>
                          <th><?php _e("Action"); ?></th>
                      </tr>
                    </thead>

                    <tbody>
                        <?php
                        // Get withdrawal requests from transactions table
                        // transaction_type = 1 for withdrawals, 4 for transfers (both need approval)
                        
                        $sql = "SELECT t.*, u.username 
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
                                    <td><?php echo htmlspecialchars($withdrawal['username'] ?? 'N/A'); ?></td>
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
                                            <form method="post" style="display: inline;" onsubmit="return confirm('<?php _e("Are you sure you want to approve this withdrawal request?"); ?>');">
                                                <input type="hidden" name="transaction_id" value="<?php echo $withdrawal['id']; ?>">
                                                <button type="submit" name="approve_withdrawal" class="btn btn-success btn-sm">
                                                    <i class="la la-check"></i> <?php _e("Approve"); ?>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-success"><i class="la la-check-circle"></i> <?php _e("Approved"); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="5" class="text-center"><?php _e("No withdrawal or transfer requests found."); ?></td>
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