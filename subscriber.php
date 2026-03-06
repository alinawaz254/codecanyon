<?php
require_once("lib/system_load.php");

authenticate_user('subscriber');

$user_id = (int)$_SESSION['user_id'];

$page_title = "Subscriber Dashboard";

require_once("lib/includes/header.php");

/* COUNTS */

// My Investments
$investment_count = 0;
$q = $db->query("SELECT COUNT(*) as total FROM user_investments WHERE user_id='$user_id'");
if($row = $q->fetch_assoc()){
$investment_count = $row['total'];
}

// My Referrals
$referral_count = 0;
$q = $db->query("SELECT COUNT(*) as total FROM users WHERE referral_id='$user_id'");
if($row = $q->fetch_assoc()){
$referral_count = $row['total'];
}

// My Notes
$notes_count = 0;
$q = $db->query("SELECT COUNT(*) as total FROM notes WHERE user_id='$user_id'");
if($row = $q->fetch_assoc()){
$notes_count = $row['total'];
}

// My Commision
$total_commission = 0;

$q = $db->query("
SELECT SUM(uid.comission) as total_commission
FROM user_investment_details uid
JOIN user_investments ui 
ON ui.investment_id = uid.investment_id
WHERE ui.user_id = '$user_id'
");

if($row = $q->fetch_assoc()){
$total_commission = $row['total_commission'] ?? 0;
}
?>

<div class="row flex-row">

<!-- My Investments -->

<div class="col-xl-4 col-md-6 col-sm-6">
<a href="frontinvestments.php" class="f-links" style="text-decoration:none">
<div class="widget widget-12 has-shadow">
<div class="widget-body">
<div class="media">

<div class="align-self-center ml-5 mr-5">
<i class="la la-money"></i>
</div>

<div class="media-body align-self-center">
<div class="title">My Investments</div>
<div class="number"><?php echo $investment_count; ?> Investments</div>
</div>

</div>
</div>
</div>
</a>
</div>

<!-- My Referrals -->

<div class="col-xl-4 col-md-6 col-sm-6">
<a href="frontreferrals.php" class="f-links" style="text-decoration:none">
<div class="widget widget-12 has-shadow">
<div class="widget-body">
<div class="media">

<div class="align-self-center ml-5 mr-5">
<i class="la la-users"></i>
</div>

<div class="media-body align-self-center">
<div class="title">My Referrals</div>
<div class="number"><?php echo $referral_count; ?> Referrals</div>
</div>

</div>
</div>
</div>
</a>
</div>

<!-- My Notes -->

<div class="col-xl-4 col-md-6 col-sm-6">
<a href="notes.php" style="text-decoration:none" class="f-links">
<div class="widget widget-12 has-shadow">
<div class="widget-body">
<div class="media">

<div class="align-self-center ml-5 mr-5">
<i class="la la-sticky-note"></i>
</div>

<div class="media-body align-self-center">
<div class="title">My Notes</div>
<div class="number"><?php echo $notes_count; ?> Notes</div>
</div>

</div>
</div>
</div>
</a>
</div>

<div class="col-xl-4 col-md-6 col-sm-6">

<div class="widget widget-12 has-shadow">
<div class="widget-body">
<div class="media">

<div class="align-self-center ml-5 mr-5">
<i class="la la-dollar"></i>
</div>

<div class="media-body align-self-center">
<div class="title">Total Commission</div>
<div class="number">$<?php echo number_format($total_commission,2); ?></div>
</div>

</div>
</div>
</div>

</div>
</div>

<div class="row flex-row mt-4">

<div class="col-xl-12">
<div class="widget has-shadow">

<div class="widget-header bordered d-flex align-items-center">
<h4>Welcome</h4>
</div>

<div class="widget-body">
<p>This is your subscriber dashboard.</p>
<p>From here you can manage your investments, referrals and notes.</p>
</div>

</div>
</div>

</div>

<?php require_once("lib/includes/footer.php"); ?>
