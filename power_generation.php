<?php
require_once("lib/system_load.php");

$datatables = 1;

// user Authentication.
authenticate_user('admin');

// Release Gift handler
if (isset($_POST['release_gift']) && $_POST['release_gift'] != '') {
    $u_id = (int)$_POST['gift_user_id'];
    $l_id = (int)$_POST['gift_level'];
    
    // Check if already released
    $check = $db->query("SELECT id FROM user_reward_releases WHERE user_id = $u_id AND level = $l_id");
    if($check->num_rows == 0) {
        $db->query("INSERT INTO user_reward_releases (user_id, level) VALUES ($u_id, $l_id)");
        $message = "Gift Released Successfully for Level $l_id";
    } else {
        $message = "Gift already released for this level";
    }
}

$page_title = _("Power Generation");
require_once("lib/includes/header.php");
?>

<div class="row">
    <div class="col-xl-12">
        <div class="widget has-shadow">
            <div class="widget-header bordered no-actions d-flex align-items-center">
                <h4><?php _e("Power Generation Achievements"); ?></h4>
            </div>
            <div class="widget-body">
                <div class="table-responsive">
                    <table id="export-table" class="table mb-0">
                        <thead>
                            <tr>
                                <th><?php _e("User"); ?></th>
                                <th><?php _e("Achievement"); ?></th>
                                <th><?php _e("Referral Investment"); ?></th>
                                <th><?php _e("Status"); ?></th>
                                <th><?php _e("Action"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $unit_value = 30000;
                            $levels = [
                                1 => 3,
                                2 => 9,
                                3 => 27,
                                4 => 81,
                                5 => 243,
                                6 => 729
                            ];

                            // Fetch all subscribers
                            $users_query = $db->query("SELECT user_id, username, first_name, last_name, email FROM users WHERE user_type LIKE '%subscriber%' ORDER BY username ASC");

                            while ($u = $users_query->fetch_assoc()) {
                                $target_user_id = $u['user_id'];
                                
                                // Calculate total referral investment
                                $total_query = $db->query("
                                    SELECT SUM(ui.amount) as total_investment
                                    FROM user_investments ui
                                    JOIN users u ON u.user_id = ui.user_id
                                    WHERE u.referral_id = '$target_user_id'
                                ");
                                
                                $row = $total_query->fetch_assoc();
                                $total_investment = $row['total_investment'] ? floatval($row['total_investment']) : 0;
                                
                                if ($total_investment <= 0) continue;

                                /* UNITS ACHIEVED */
                                $units_achieved = floor($total_investment / $unit_value);
                                
                                // Calculate highest level achieved
                                $highest_level = 0;
                                $temp_remaining_units = $units_achieved;
                                foreach ($levels as $level => $required) {
                                    if ($temp_remaining_units >= $required) {
                                        $highest_level = $level;
                                        $temp_remaining_units -= $required;
                                    } else {
                                        break; 
                                    }
                                }

                                // If user has achieved at least Level 1
                                if ($highest_level >= 1) {
                                    $user_display = wc_get_user_display_name($u['username'], $u['first_name'], $u['last_name']);
                                    
                                    // Loop through each achieved level to show as individual record
                                    for ($i = 1; $i <= $highest_level; $i++) {
                                        // Check if gift is released
                                        $rel_check = $db->query("SELECT id FROM user_reward_releases WHERE user_id = $target_user_id AND level = $i");
                                        $is_released = ($rel_check->num_rows > 0);
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($user_display); ?></strong><br>
                                                <small><?php echo htmlspecialchars($u['email']); ?></small>
                                            </td>
                                            <td>Level <?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo number_format($total_investment, 2); ?></td>
                                            <td>
                                                <?php if($is_released): ?>
                                                    <span class="badge text-bg-success">Gift Released</span>
                                                <?php else: ?>
                                                    <span class="badge text-bg-warning">Pending Release</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if(!$is_released): ?>
                                                    <form method="post" style="display:inline" onsubmit="return confirm('Are you sure you want to release the gift?');">
                                                        <input type="hidden" name="gift_user_id" value="<?php echo $target_user_id; ?>">
                                                        <input type="hidden" name="gift_level" value="<?php echo $i; ?>">
                                                        <button type="submit" name="release_gift" value="1" class="btn btn-success btn-sm btn-release-gift">
                                                            Release Gift
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary btn-sm" disabled>Already Released</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.btn-release-gift {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
    color: white !important;
}
.btn-release-gift:hover {
    background-color: #218838 !important;
    border-color: #1e7e34 !important;
}
</style>

<?php
require_once("lib/includes/footer.php");
?>
