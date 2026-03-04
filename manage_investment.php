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

<style>
    .select2-container .select2-selection--single{
    height:38px;
    padding:4px 10px;
    border:1px solid #ced4da;
}

.select2-container--default .select2-selection--single .select2-selection__rendered{
    line-height:28px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow{
    height:36px;
}
</style>

<form method="post">
<label for="user_id"> Select User *</label>
<select name="user_id" id="investment-users"  class="form-control "style="width:100%" required>
<option></option>
<?php
$result = $db->query("SELECT user_id,username FROM users WHERE user_type LIKE '%subscriber%'");
while($u = $result->fetch_assoc()){
$selected = ($investment_obj->user_id == $u['user_id']) ? "selected" : "";
echo "<option value='{$u['user_id']}' $selected>{$u['username']}</option>";
}
?>
</select><br>
<label for="multiple-select-plans"> Select Plan *</label>
<select name="plan_id[]" id="multiple-select-plans" multiple class="form-control" required>
<option value="">Select Plan</option>
<?php
$result = $db->query("SELECT plan_id,plan_name FROM investment_plans");
while($p = $result->fetch_assoc()){
$selected = ($investment_obj->plan_id == $p['plan_id']) ? "selected" : "";
echo "<option value='{$p['plan_id']}' $selected>{$p['plan_name']}</option>";
}
?>
</select><br>

<label for="amount"> Set Amount *</label>
<input 
type="number" 
step="0.01" 
id="amount"
name="amount"
value="<?php echo $investment_obj->amount; ?>" 
class="form-control" 
placeholder="Enter Investment Amount" 
required><br>

<label for="issue_date">Set Starting Date *</label>
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