<?php
require_once("lib/system_load.php");
authenticate_user('admin');

if(isset($_POST['delete_investment'])){
    $message = $investment_obj->delete_investment($_POST['delete_investment']);
}

$page_title = _("User Investments");
require_once("lib/includes/header.php");
?>

<div class="text-right">
    <a href="manage_investment.php" class="btn btn-primary">
        <?php _e("Add Investment"); ?>
    </a>
</div>

<div class="row">
    <?php $investment_obj->list_investments(); ?>
</div>

<?php require_once("lib/includes/footer.php"); ?>