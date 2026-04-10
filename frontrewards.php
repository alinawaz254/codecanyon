<?php

require_once("lib/system_load.php");

authenticate_user('all');

$user_id = (int) $_SESSION['user_id'];

$page_title = _("My Rewards");

require_once("lib/includes/header.php");

/* CONSTANT UNIT VALUE */
$unit_value = 30000;

/* TOTAL USER INVESTMENT */
// $total_query = $db->query("
//     SELECT SUM(amount) as total_investment
//     FROM user_investments
//     WHERE user_id = '$user_id'
// ");
/* TOTAL Refel USER INVESTMENT */
$total_query = $db->query("
    SELECT SUM(ui.amount) as total_investment
    FROM user_investments ui
    JOIN users u ON u.user_id = ui.user_id
    WHERE u.referral_id = '$user_id'
");

$row = $total_query->fetch_assoc();
$total_investment = $row['total_investment'] ? $row['total_investment'] : 0;

/* UNITS ACHIEVED */
$units_achieved = floor($total_investment / $unit_value);

/* LEVEL STRUCTURE */
$levels = [
    1 => 3,
    2 => 9,
    3 => 27,
    4 => 81,
    5 => 243,
    6 => 729
];
?>

<style>
    .reward-header {
        margin-bottom: 20px;
    }

    .reward-stats {
        font-size: 14px;
        margin-bottom: 15px;
    }

    .reward-image {
        width: 100%;
        height: auto;
        margin-top: 30px;
    }

    .badge-complete {
        color: green;
        font-weight: 600;
    }

    .badge-ongoing {
        color: #e67e22;
        font-weight: 600;
    }

    .units-achieved-info {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
</style>

<div class="mywidget wc_data">
    <div class="widget has-shadow table-responsive">
        <div class="widget-body">
            <div class="reward-stats">
                <b>Total Investment:</b> <?php echo number_format($total_investment, 2); ?> PKR<br>
                <b>Unit Value:</b> <?php echo number_format($unit_value); ?> PKR<br>
                <b>Units Achieved:</b> <?php echo $units_achieved; ?>
            </div>

            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>Level</th>
                        <th>Units Required</th>
                        <th>Units Achieved</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $remaining_units = $units_achieved;

                    foreach ($levels as $level => $required) {
                        // Calculate units used for this level
                        $units_used = 0;

                        if ($remaining_units >= $required) {
                            // Level is fully completed
                            $units_used = $required;
                            $remaining_units -= $required;
                        } elseif ($remaining_units > 0) {
                            // Level is partially completed
                            $units_used = $remaining_units;
                            $remaining_units = 0;
                        }
                        // If remaining_units is 0, units_used remains 0
                    
                        ?>
                        <tr>
                            <td>0<?php echo $level; ?></td>
                            <td><?php echo $required; ?></td>
                            <td><?php echo $units_used; ?></td>
                            <td>
                                <?php if ($units_used >= $required): ?>
                                    <?php
                                    $gift_check = $db->query("SELECT id FROM user_reward_releases WHERE user_id = $user_id AND level = $level");
                                    $gift_received = ($gift_check->num_rows > 0);
                                    ?>
                                    <span class='badge-complete'>Completed
                                        <?php echo $gift_received ? "(Received Gift)" : ""; ?></span>
                                <?php elseif ($units_used > 0): ?>
                                    <span class='badge-ongoing'>In Progress
                                        (<?php echo $units_used; ?>/<?php echo $required; ?>)</span>
                                <?php else: ?>
                                    <span class='badge-ongoing'>Ongoing</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>

            <?php if ($remaining_units > 0): ?>
                <div class="units-achieved-info">
                    <strong>Extra Units:</strong> <?php echo $remaining_units; ?> unit(s) available for next levels
                </div>
            <?php endif; ?>

            <!-- REWARD IMAGE -->
            <img src="assets/images/rewards.png" class="reward-image">
        </div>
    </div>
</div>

<?php
require_once("lib/includes/footer.php");
?>