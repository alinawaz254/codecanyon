<?php
require_once("lib/system_load.php");
authenticate_user('admin');

// Initialize transactions object
$transactions_obj = new Transactions();

// Handle delete transaction
if(isset($_POST['delete_transaction'])){
    $message = $transactions_obj->delete_transaction($_POST['delete_transaction']);
}

$page_title = _("User Transactions");
require_once("lib/includes/header.php");
?>

<div class="row">
    <div class="col-12">
        <!-- Display message if exists - Only show once -->
        <?php if(isset($message) && $message != '' && !isset($_SESSION['message_shown'])): 
            $_SESSION['message_shown'] = true;
        ?>
        <?php 
        // Clear the session variable after showing
        unset($_SESSION['message_shown']);
        endif; 
        ?>

        <!-- Stats Cards - Only show if there are transactions -->
        <?php 
        $stats = $transactions_obj->get_transaction_stats();
        if($stats['total'] > 0): 
        ?>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="widget has-shadow">
                    <div class="widget-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex flex-row align-items-center">
                                <div class="avatar avatar-icon bg-primary mr-2">
                                    <!-- <i class="la la-exchange"></i> -->
                                </div>
                                <div>
                                    <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                                    <span class="text-muted"><?php _e("Total Transactions"); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="widget has-shadow">
                    <div class="widget-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex flex-row align-items-center">
                                <div class="avatar avatar-icon bg-success mr-2">
                                    <!-- <i class="la la-plus-circle"></i> -->
                                </div>
                                <div>
                                    <h3 class="mb-0 text-success">
                                        +PKR <?php echo number_format($stats['funded'] + $stats['commission'], 2); ?></h3>
                                    <span class="text-muted"><?php _e("Total Credits"); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="widget has-shadow">
                    <div class="widget-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex flex-row align-items-center">
                                <div class="avatar avatar-icon bg-danger mr-2">
                                    <!-- <i class="la la-minus-circle"></i> -->
                                </div>
                                <div>
                                    <h3 class="mb-0 text-danger">
                                        -PKR <?php echo number_format($stats['withdrawals'], 2); ?></h3>
                                    <span class="text-muted"><?php _e("Total Withdrawals"); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="widget has-shadow">
                    <div class="widget-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex flex-row align-items-center">
                                <div class="avatar avatar-icon bg-warning mr-2"></div>
                                <div>
                                    <h3 class="mb-0 text-warning">
                                        -PKR <?php echo number_format($stats['transfers'], 2); ?>
                                    </h3>
                                    <span class="text-muted"><?php _e("Total Transfers"); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Add Transaction Button -->
        <div class="text-right mb-4">
            <a href="manage_transactions.php" class="btn btn-primary btn-md btn-golden">
                <i class="la la-plus-circle"></i> <?php _e("Add New Transaction"); ?>
            </a>
        </div>

        <!-- Transactions List -->
        <div class="row">
            <?php $transactions_obj->list_transactions(); ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize select2 if available
    if ($.fn.select2) {
        $('#filter_user').select2({
            placeholder: "<?php _e('Search user...'); ?>",
            allowClear: true
        });
    }
});
</script>

<?php 
// Clear any session messages
unset($_SESSION['message_shown']);
require_once("lib/includes/footer.php"); 
?>