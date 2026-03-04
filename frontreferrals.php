<?php
require_once("lib/system_load.php");

authenticate_user('all');

$user_id = (int)$_SESSION['user_id'];

$page_title = _("My Referrals");

require_once("lib/includes/header.php");

$query = "
SELECT 
u.username,
COALESCE(ui.amount,0) as amount,
ui.issue_date,
ip.plan_name,
COALESCE(ui.amount * 0.01,0) AS referral_commission
FROM users u
LEFT JOIN user_investments ui ON ui.user_id = u.user_id
LEFT JOIN investment_plans ip ON ip.plan_id = ui.plan_id
WHERE u.referral_id = $user_id
ORDER BY ui.issue_date DESC
";

$result = $db->query($query);
?>

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
<th>Your Commission (1%)</th>
<th>Date</th>
</tr>
</thead>

<tbody>

<?php if($result->num_rows == 0): ?>

<tr>
<td colspan="5" class="text-center">No referrals found</td>
</tr>

<?php else: ?>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td><?php echo $row['username']; ?></td>

<td><?php echo $row['plan_name'] ?: "-"; ?></td>

<td><?php echo number_format((float)$row['amount'],2); ?></td>
<td><?php echo number_format((float)$row['referral_commission'],2); ?></td>

<td><?php echo $row['issue_date'] ?: "-"; ?></td>

</tr>

<?php endwhile; ?>

<?php endif; ?>

</tbody>

</table>

</div>
</div>
</div>

<?php require_once("lib/includes/footer.php"); ?>