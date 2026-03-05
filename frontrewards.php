<?php
require_once("lib/system_load.php");

authenticate_user('all');

$user_id = (int)$_SESSION['user_id'];

$page_title = _("Rewards");

require_once("lib/includes/header.php");

/* CONSTANT UNIT VALUE */
$unit_value = 30000;

/* TOTAL USER INVESTMENT */
$total_query = $db->query("
SELECT SUM(amount) as total_investment
FROM user_investments
WHERE user_id = '$user_id'
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

.reward-header{
    margin-bottom:20px;
}

.reward-stats{
    font-size:14px;
    margin-bottom:15px;
}

.reward-image{
    width:100%;
    height:auto;
    margin-top:30px;
}

.badge-complete{
    color:green;
    font-weight:600;
}

.badge-ongoing{
    color:#e67e22;
    font-weight:600;
}

</style>

<div class="mywidget wc_data">

<div class="widget has-shadow">

<div class="widget-body">

<div class="reward-stats">

<b>Total Investment:</b> <?php echo number_format($total_investment,2); ?> PKR<br>
<b>Unit Value:</b> <?php echo number_format($unit_value); ?> PKR<br>
<b>Units Achieved:</b> <?php echo $units_achieved; ?>

</div>

<table class="table table-bordered table-striped">

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

foreach($levels as $level => $required){

$status = ($units_achieved >= $required) ? "Completed" : "Ongoing";

echo "<tr>

<td>0$level</td>
<td>$required</td>
<td>$units_achieved</td>

<td>";

if($status == "Completed"){
echo "<span class='badge-complete'>Completed</span>";
}else{
echo "<span class='badge-ongoing'>Ongoing</span>";
}

echo "</td>
</tr>";

}

?>

</tbody>

</table>

<!-- REWARD IMAGE -->

<img src="assets/images/rewards.png" class="reward-image">

</div>
</div>
</div>

<?php require_once("lib/includes/footer.php"); ?>