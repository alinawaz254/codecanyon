<?php
require_once "lib/system_load.php";

authenticate_user('all');

$user_id = (int) $_SESSION['user_id'];
$page_title = "Investment Bonus";

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

/* ================= CLAIM BONUS ================= */

if(isset($_GET['bonus_id'])){

    $bonus_id = intval($_GET['bonus_id']);

    $check = $db->query("
        SELECT * FROM user_investment_bonus 
        WHERE bonus_id = $bonus_id 
        AND user_id = $user_id
    ");

    $row = $check->fetch_assoc();

    if($row && $row['is_claimed'] == 0){

        $amount = $row['bonus_amount'];

        // Mark claimed
        $db->query("
            UPDATE user_investment_bonus 
            SET is_claimed = 1, claimed_at = NOW()
            WHERE bonus_id = $bonus_id
        ");

        // Transaction entry
        $db->query("
            INSERT INTO transactions 
            (user_id, transaction_type, amount, description, is_approved, created_at)
            VALUES ($user_id, 6, $amount, '10% Investment Bonus Claimed', 1, NOW())
        ");

        $_SESSION['success_message'] = "Bonus claimed successfully!";

    } else {
        $_SESSION['error_message'] = "Already claimed or invalid.";
    }

    header("Location: frontInvestmentBonus.php");
    exit();
}

/* ================= FETCH DATA ================= */

$query = "
SELECT 
    uib.bonus_id,
    uib.bonus_amount,
    uib.is_claimed,
    uib.claimed_at,
    ui.amount,
    ui.issue_date,
    ip.plan_name
FROM user_investment_bonus uib
JOIN user_investments ui ON ui.investment_id = uib.investment_id
JOIN investment_plans ip ON ip.plan_id = ui.plan_id
WHERE uib.user_id = '$user_id'
ORDER BY uib.bonus_id DESC
";

$result = $db->query($query);
?>

<div class="mywidget wc_data">
    <div class="widget has-shadow">
        <div class="widget-header bordered">
            <h4>Investment Bonus (10%)</h4>
        </div>

        <div class="widget-body table-responsive">

            <table class="table dataTable">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Investment</th>
                        <th>Bonus (10%)</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if($result->num_rows == 0): ?>
                    <tr>
                        <td colspan="5" class="text-center">
                            No bonus available
                        </td>
                    </tr>
                    <?php endif; ?>

                    <?php while($row = $result->fetch_assoc()): ?>

                    <tr>
                        <td><?= $row['plan_name'] ?></td>
                        <td><?= number_format($row['amount'],2) ?></td>
                        <td class="text-success">
                            <?= number_format($row['bonus_amount'],2) ?>
                        </td>
                        <td><?= $row['issue_date'] ?></td>

                        <td>
                            <?php if($row['is_claimed'] == 0): ?>

                            <a href="?bonus_id=<?= $row['bonus_id'] ?>" class="btn btn-success btn-sm"
                                onclick="return confirm('Claim 10% bonus?')">
                                Claim Now
                            </a>

                            <?php else: ?>

                            <span class="badge bg-success">
                                Claimed on <?= date('Y-m-d', strtotime($row['claimed_at'])) ?>
                            </span>

                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php endwhile; ?>

                </tbody>
            </table>

        </div>
    </div>
</div>

<?php require_once "lib/includes/footer.php"; ?>