<?php
require_once("lib/system_load.php");
authenticate_user('admin');

if(isset($_POST['edit_investment'])){
    $investment_obj->set_investment($_POST['edit_investment']);
}

if(isset($_POST['update_investment'])){
    extract($_POST);
    $message = $investment_obj->update_investment($edit_investment,$user_id,$plan_id,$amount,$issue_date);
    $investment_obj->set_investment($edit_investment);
    $_POST['edit_investment'] = $edit_investment;
}

if(isset($_POST['add_investment'])){
    extract($_POST);
    $message = $investment_obj->add_investment($user_id,$plan_id,$amount,$issue_date);

    // optional redirect
    // header("Location: investments.php?added=1");
    // exit;
}

$page_title = "Manage Investment";
require_once("lib/includes/header.php");
?>

<form method="post">
<label id="user_id"> Select User *</label>
<select name="user_id" id="user_id" class="form-control" required>
<option value="">Select User</option>
<?php
$result = $db->query("SELECT user_id,username FROM users");
while($u = $result->fetch_assoc()){
$selected = ($investment_obj->user_id == $u['user_id']) ? "selected" : "";
echo "<option value='{$u['user_id']}' $selected>{$u['username']}</option>";
}
?>
</select><br>
<label id="plan_id"> Select Plan *</label>
<select name="plan_id" id="plan_id" class="form-control" required>
<option value="">Select Plan</option>
<?php
$result = $db->query("SELECT plan_id,plan_name FROM investment_plans");
while($p = $result->fetch_assoc()){
$selected = ($investment_obj->plan_id == $p['plan_id']) ? "selected" : "";
echo "<option value='{$p['plan_id']}' $selected>{$p['plan_name']}</option>";
}
?>
</select><br>

<label id="amount"> Set Amount *</label>
<input 
type="number" 
step="0.01" 
id="amount"
name="amount"
value="<?php echo $investment_obj->amount; ?>" 
class="form-control" 
placeholder="Enter Investment Amount" 
required><br>

<label id="issue_date">Set Starting Date *</label>
<input 
type="date" 
id="issue_date"
name="issue_date"
value="<?php echo $investment_obj->issue_date; ?>" 
class="form-control" 
required><br>

<?php if(isset($_POST['edit_investment'])): ?>
<input type="hidden" name="edit_investment" value="<?=$_POST['edit_investment'];?>">
<input type="hidden" name="update_investment" value="1">
<input type="submit" class="btn btn-primary" value="Update Investment">
<?php else: ?>
<input type="hidden" name="add_investment" value="1">
<input type="submit" class="btn btn-primary" value="Add Investment">
<a href="investments.php" class="btn btn-secondary">View Investments</a>
<?php endif; ?>

</form>

<?php require_once("lib/includes/footer.php"); ?>