<?php
require_once("lib/system_load.php");
authenticate_user('admin');

if(isset($_POST['edit_plan'])){
    $investment_obj->set_plan($_POST['edit_plan']);
}

if(isset($_POST['update_plan'])){
    extract($_POST);
    $message = $investment_obj->update_plan($edit_plan,$plan_name,$total_cycles,$commission,$cycle_days);
    $investment_obj->set_plan($edit_plan);
    $_POST['edit_plan'] = $edit_plan;
}

if(isset($_POST['add_plan'])){
    extract($_POST);
    $message = $investment_obj->add_plan($plan_name,$total_cycles,$commission,$cycle_days);
}

$page_title = "Manage Investment Plan";
require_once("lib/includes/header.php");
?>

<form method="post">
    <label id="plan_name"> Plan Name *</label>
    <input type="text" name="plan_name" id="plan_name" class="form-control"
    value="<?php echo $investment_obj->plan_name; ?>" placeholder="Plan Name"><br>

    <label id="total_cycles"> Total Cycles *</label>
    <input type="number" id="total_cycles" name="total_cycles" class="form-control"
    value="<?php echo $investment_obj->total_cycles; ?>" placeholder="Total Cycles"><br>

    <label id="commission"> Commission *</label>
    <input type="number" id="commission" step="0.01" name="commission" class="form-control"
    value="<?php echo $investment_obj->commission; ?>" placeholder="Commission"><br>

    <label id="cycle_days"> Each Cycle Days *</label>
    <input type="number" id="cycle_days" name="cycle_days" class="form-control"
    value="<?php echo $investment_obj->cycle_days ?: 35; ?>" placeholder="Cycle Days"><br>

    <?php if(isset($_POST['edit_plan'])): ?>
    <input type="hidden" name="edit_plan" value="<?=$_POST['edit_plan'];?>">
    <input type="hidden" name="update_plan" value="1">
    <input type="submit" class="btn btn-primary" value="Update Plan">
    <?php else: ?>
    <input type="hidden" name="add_plan" value="1">
    <input type="submit" class="btn btn-primary" value="Add Plan">\
    <a href="investment_plans.php" class="btn btn-secondary">View Plans</a>
    <?php endif; ?>

</form>

<?php require_once("lib/includes/footer.php"); ?>