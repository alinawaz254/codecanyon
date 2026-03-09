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

$page_title = "Manage Investment Package";
require_once("lib/includes/header.php");
?>

<div class="row">
    <div class="col-xl-8 col-lg-8 col-md-10 col-12">

        <form method="post" class="mb-5">

            <div class="row">

                <div class="col-12 mb-3">
                    <label for="plan_name">Plan Name *</label>
                    <input type="text" name="plan_name" id="plan_name" class="form-control"
                        value="<?php echo $investment_obj->plan_name; ?>" placeholder="Plan Name">
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="total_cycles">Total Cycles *</label>
                    <input type="number" id="total_cycles" name="total_cycles" class="form-control"
                        value="<?php echo $investment_obj->total_cycles; ?>" placeholder="Total Cycles">
                </div>

                <div class="col-md-6 col-12 mb-3">
                    <label for="commission">Commission *</label>
                    <input type="number" id="commission" step="0.01" name="commission" class="form-control"
                        value="<?php echo $investment_obj->commission; ?>" placeholder="Commission">
                </div>

            </div>

            <?php if(isset($_POST['edit_plan'])): ?>
            <input type="hidden" name="edit_plan" value="<?=$_POST['edit_plan'];?>">
            <input type="hidden" name="update_plan" value="1">
            <input type="submit" class="btn btn-primary btn-md btn-golden" value="Update">
            <a href="investment_plans.php" class="btn btn-md btn-outline-dark">View All</a>
            <?php else: ?>
            <input type="hidden" name="add_plan" value="1">
            <input type="submit" class="btn btn-primary btn-md btn-golden" value="Add Package">
            <a href="investment_plans.php" class="btn btn-outline-dark">View All</a>
            <?php endif; ?>

        </form>

    </div>
</div>

<?php require_once("lib/includes/footer.php"); ?>