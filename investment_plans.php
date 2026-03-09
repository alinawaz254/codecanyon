<?php
require_once("lib/system_load.php");
authenticate_user('admin');

if(isset($_POST['delete_plan']) && $_POST['delete_plan'] != ''){
    $message = $investment_obj->delete_plan($_POST['delete_plan']);
}

$page_title = _("Investment Packages");
require_once("lib/includes/header.php");
?>

<div class="text-right">
    <p>
        <a href="manage_investment_plan.php" class="btn btn-primary btn-md btn-golden">
            <?php _e("Add Package"); ?>
        </a>
    </p>
</div>

<div class="row">
    <?php $investment_obj->list_plans(); ?>
</div>

<?php require_once("lib/includes/footer.php"); ?>