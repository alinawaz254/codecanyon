<?php
	require_once("lib/system_load.php");
	//This loads system.
	
	//user Authentication.
	authenticate_user('admin');
	
	$user_id = $transaction_type = $amount = $description = '';
	$datepicker = 1;

	//Add transaction processing
	if(isset($_POST['add_transaction']) && $_POST['add_transaction'] == '1') {
		$transaction_obj->create($_POST);
	}
	
	$page_title = _("Add New Transaction");
	require_once("lib/includes/header.php");
?>
<div class="row flex-row">
	<div class="col-12">
		<!-- Display message if exists -->
		<?php if(isset($message) && $message != ''): ?>
		<div class="alert alert-info alert-dismissible fade show" role="alert">
			<?php echo $message; ?>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<?php endif; ?>
		
		<!-- Form -->
		<div class="widget has-shadow">
			<div class="widget-header bordered no-actions d-flex align-items-center">
				<h4><?php _e("Create New Transaction"); ?></h4>
			</div>
			<div class="widget-body">
				<div class="container">
				<form action="<?php $_SERVER['PHP_SELF']?>" id="add_transaction" method="post">

				<div class="row">

				<div class="col-md-6 mb-4">
				<label>Select User *</label>
				<select name="user_id" class="form-control select2" required>
				<option value="">-- Select User --</option>

				<?php
				$result = $db->query("SELECT user_id,username FROM users WHERE user_type LIKE'%subscriber' ORDER BY username ASC");

				while($user = $result->fetch_assoc()){
				?>
				<option value="<?php echo $user['user_id']; ?>">
				<?php echo $user['username']; ?>
				</option>
				<?php } ?>

				</select>
				</div>


				<div class="col-md-6 mb-4">
				<label>Transaction Type *</label>

				<select name="transaction_type" class="form-control" required>

				<option value="">-- Select Type --</option>
				<option value="1">Withdrawal</option>
				<option value="2">Funded</option>
				<option value="3">Commission</option>

				</select>

				</div>

				</div>


				<div class="row">

				<div class="col-md-6 mb-4">

				<label>Amount *</label>

				<div class="input-group">
				<div class="input-group-prepend">
				<span class="input-group-text p-3">PKR</span>
				</div>

				<input type="number" name="amount" step="0.00000001"
				class="form-control" required>

				</div>

				</div>


				<div class="col-md-6 mb-4">

				<label>Description</label>

				<textarea name="description"
				class="form-control"
				rows="3"></textarea>

				</div>

				</div>


				<div class="row">

				<div class="col-md-12 text-right">

				<input type="hidden" name="add_transaction" value="1">

				<button type="submit" class="btn btn-golden">
				Create Transaction
				</button>

				<a href="transactions.php" class="btn btn-secondary">
				View All
				</a>

				</div>

				</div>

				</form>
				</div>
			</div><!-- widget body /-->
		</div><!-- widget /-->
	</div><!-- column /-->
</div><!-- Row /-->

<!-- Add this script for better user experience -->
<script>
$(document).ready(function() {
    // Initialize select2 for better dropdown
    if ($.fn.select2) {
        $('#user_id').select2({
            placeholder: "<?php _e('Search for a user...'); ?>",
            allowClear: true
        });
    }
    
    // Format amount based on transaction type
    $('#transaction_type').change(function() {
        var type = $(this).val();
        var amountField = $('#amount');
        var typeLabel = '';
        
        switch(type) {
            case '1':
                typeLabel = '<?php _e("Withdrawal"); ?>';
                break;
            case '2':
                typeLabel = '<?php _e("Funded"); ?>';
                break;
            case '3':
                typeLabel = '<?php _e("Commission"); ?>';
                break;
        }
        
        if(typeLabel) {
            $('.transaction-type-badge').remove();
            amountField.before('<span class="transaction-type-badge badge badge-info mr-2">' + typeLabel + '</span>');
        }
    });
    
    // Form validation
    $('#add_transaction').submit(function(e) {
        var amount = parseFloat($('#amount').val());
        if(amount <= 0) {
            e.preventDefault();
            alert('<?php _e("Amount must be greater than 0"); ?>');
            return false;
        }
        
        // Check decimal places (max 8)
        var decimalPlaces = (amount.toString().split('.')[1] || []).length;
        if(decimalPlaces > 8) {
            e.preventDefault();
            alert('<?php _e("Amount cannot have more than 8 decimal places"); ?>');
            return false;
        }
        
        return true;
    });
});
</script>

<?php
	require_once("lib/includes/footer.php");
?>