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

    header("Location: manage_investment.php?updated=1");
    exit;    
}

if(isset($_POST['add_investment'])){
    extract($_POST);
    $message = $investment_obj->add_investment($user_id,$plan_id,$amount,$issue_date);

    header("Location: manage_investment.php?added=1");
    exit;    
}

$page_title = "Manage Investment";
require_once("lib/includes/header.php");
?>

<!-- SUCCESS MESSAGE -->

<?php if(isset($_GET['added'])): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">Investment Added Successfully<button type="button" class="close" data-dismiss="alert" aria-label="Close">
</button></div>
<?php endif; ?>

<?php if(isset($_GET['updated'])): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">Investment Updated Successfully<button type="button" class="close" data-dismiss="alert" aria-label="Close">
</button></div>
<?php endif; ?>

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

<div class="row">
    <div class="col-xl-8 col-lg-8 col-md-10 col-12">

        <form method="post" class="mb-5">

        <div class="row">
            <div class="col-12 mb-3">
                <label for="user_id"> Select User *</label>
                <select name="user_id" id="investment-users"  class="form-control "style="width:100%" required>
                <option></option>
                <?php
                $result = $db->query("SELECT user_id,username,first_name,last_name FROM users WHERE user_type LIKE '%subscriber%'");
                while($u = $result->fetch_assoc()){
                $selected = ($investment_obj->user_id == $u['user_id']) ? "selected" : "";
                echo "<option value='{$u['user_id']}' $selected>".wc_get_user_display_name($u['username'], $u['first_name'], $u['last_name'])."</option>";
                }
                ?>
                </select>
            </div>

            <div class="col-12 mb-3">
                <label for="multiple-select-plans"> Select Plan *</label>
                <select name="plan_id[]" id="multiple-select-plans" class="form-control" required>
                <option value="">Select Plan</option>
                <?php
                $result = $db->query("SELECT plan_id,plan_name FROM investment_plans");
                while($p = $result->fetch_assoc()){
                $selected = ($investment_obj->plan_id == $p['plan_id']) ? "selected" : "";
                echo "<option value='{$p['plan_id']}' $selected>{$p['plan_name']}</option>";
                }
                ?>
                </select>
            </div>

            <div class="col-md-6 col-12 mb-3">
                <label for="amount"> Set Amount *</label>
                <input 
                type="number" 
                step="0.01" 
                id="amount"
                name="amount"
                value="<?php echo $investment_obj->amount; ?>" 
                class="form-control" 
                placeholder="Enter Investment Amount" 
                required>
            </div>

            <div class="col-md-6 col-12 mb-3">
                <label for="issue_date">Set Starting Date *</label>
                <input 
                type="date" 
                id="issue_date"
                name="issue_date"
                value="<?php echo $investment_obj->issue_date; ?>" 
                class="form-control" 
                required>
            </div>

        </div>        

        <?php if(isset($_POST['edit_investment'])): ?>
        <input type="hidden" name="edit_investment" value="<?=$_POST['edit_investment'];?>">
        <input type="hidden" name="update_investment" value="1">
        <div class="d-flex gap-2">
            <input type="submit" class="btn btn-primary btn-md btn-golden" value="Update">
            <a href="investments.php" class="btn btn-outline-dark">View All</a>
        </div>
        <?php else: ?>
        <input type="hidden" name="add_investment" value="1">
        <div class="d-flex gap-2">
            <input type="submit" class="btn btn-primary btn-md btn-golden" value="Add Investment">
            <a href="investments.php" class="btn btn-outline-dark">View All</a>
        </div>
        <?php endif; ?>

        </form>
    </div>
</div>
<?php require_once("lib/includes/footer.php"); ?>