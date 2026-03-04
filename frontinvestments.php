<?php
require_once("lib/system_load.php");


authenticate_user('all');

$user_id = (int)$_SESSION['user_id'];

$page_title = _("My Investments");

require_once("lib/includes/header.php");

$query = "
SELECT ui.*, ip.plan_name, ip.total_cycles, ip.cycle_days, ip.commission
FROM user_investments ui
LEFT JOIN investment_plans ip ON ip.plan_id = ui.plan_id
WHERE ui.user_id = '$user_id'
ORDER BY ui.issue_date DESC
";

$result = $db->query($query);
?>

<style>
    .investment-modal{
border-radius:10px;
box-shadow:0 10px 30px rgba(0,0,0,0.2);
}

.investment-header{
background:#2c304d;
color:#fff;
padding:15px 20px;
display:flex;
justify-content:space-between;
align-items:center;
}

.btn-close-investment{
background:#ff4d4d;
border:none;
color:#fff;
font-size:20px;
width:35px;
height:35px;
border-radius:50%;
cursor:pointer;
}

.btn-close-investment:hover{
background:#ff0000;
}

.investment-info{
display:flex;
justify-content:space-between;
margin-bottom:15px;
}

.info-label{
display:block;
font-size:12px;
color:#777;
}

.info-value{
font-size:12px;
font-weight:600;
}

.cycles-title{
margin:10px 0 15px 0;
font-weight:600;
}

.investment-table thead{
background:#f5f5f5;
}

.investment-table th{
font-weight:600;
}

.investment-footer{
justify-content:flex-end;
border-top: 0px;
padding: 7px 9px 8px 8px !important;
}
.modal-title{
    color: white;
}
</style>

<div class="mywidget wc_data">

<div class="widget has-shadow">

<div class="widget-header bordered">
<h4>My Investments</h4>
</div>

<div class="widget-body">

<table class="table table-bordered table-striped">

<thead>
<tr>
<th>Packages</th>
<th>Amount</th>
<th>Issue Date</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php
$modals = "";

while($row = $result->fetch_assoc()):

$amount = $row['amount'];
$cycle_days = $row['cycle_days'];
$total_cycles = $row['total_cycles'];
$commission_percent = $row['commission'];
$commission = ($amount * $commission_percent) / 100;
$issue_date = strtotime($row['issue_date']);

$expiry_date = date(
    "Y-m-d",
    strtotime("+".($cycle_days * $total_cycles)." days",$issue_date)
);

$today = date("Y-m-d");
$is_expired = ($today > $expiry_date);
?>

<tr <?php if($is_expired) echo 'style="opacity:0.5"'; ?>>

<td><?php echo $row['plan_name']; ?></td>
<td><?php echo number_format($amount,2); ?></td>
<td><?php echo $row['issue_date']; ?></td>

<td>
<button class="btn btn-primary btn-sm"
data-toggle="modal"
data-target="#investmentModal_<?php echo $row['investment_id']; ?>">
View
</button>

<?php if($is_expired): ?>
<span class="badge badge-secondary ml-2">Inactive</span>
<?php endif; ?>

</td>

</tr>

<?php
/* build modal separately */
ob_start();
?>

<div class="modal fade"
id="investmentModal_<?php echo $row['investment_id']; ?>"
tabindex="-1">

<div class="modal-dialog modal-lg modal-dialog-centered">

<div class="modal-content investment-modal">

<div class="modal-header investment-header">

<h5 class="modal-title">
<?php echo $row['plan_name']; ?> Investment Details
</h5>

<button type="button"
class="btn-close-investment"
data-dismiss="modal">

&times;

</button>

</div>

<div class="modal-body">

<div class="investment-info">

<div>
<span class="info-label">Package</span>
<span class="info-value"><?php echo $row['plan_name']; ?></span>
</div>

<div>
<span class="info-label">Amount</span>
<span class="info-value"><?php echo number_format($amount,2); ?></span>
</div>

<div>
<span class="info-label">Issue Date</span>
<span class="info-value"><?php echo $row['issue_date']; ?></span>
</div>

</div>

<hr>

<table class="table investment-table">

<thead>
<tr>
<th class="info-value">Cycle</th>
<th class="info-value">Commission Date</th>
<th class="info-value">Commission (<?php echo $commission_percent; ?>%)</th>
</tr>
</thead>

<tbody>

<?php
for($i=1;$i<=$total_cycles;$i++){

$cycle_date = date(
"Y-m-d",
strtotime("+".($cycle_days*$i)." days",$issue_date)
);
?>

<tr>

<td class="info-value"><?php echo $i; ?></td>
<td class="info-value"><?php echo $cycle_date; ?></td>
<td class="info-value"><?php echo number_format($commission,2); ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<div class="modal-footer investment-footer">

<button class="btn btn-danger"
data-dismiss="modal">

Close

</button>

</div>

</div>
</div>
</div>

<?php
$modals .= ob_get_clean();

endwhile;
?>

</tbody>

</table>

</div>
</div>
</div>

<?php
/* print all modals outside table */
echo $modals;

require_once("lib/includes/footer.php");
?>

<script>
$(document).ready(function(){

$('.modal').on('show.bs.modal', function () {
console.log('Modal opened');
});

});
</script>