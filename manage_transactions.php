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
					<form action="<?php $_SERVER['PHP_SELF']?>" id="add_transaction" name="transaction" method="post" role="form">
						
						<!-- User Selection -->
						<div class="row">
							<div class="col-md-12 mb-3">
								<div class="form-group row d-flex align-items-center mb-5">
									<label class="col-lg-2 form-control-label d-flex justify-content-lg-end" for="user_id">
										<?php _e("Select User"); ?>*
									</label>
									<div class="col-lg-6">
										<select name="user_id" id="user_id" class="form-control select2" required style="width:100%">
											<option value=""><?php _e("-- Select User --"); ?></option>
											<?php
											$result = $db->query("SELECT user_id, username, email, first_name, last_name FROM users WHERE user_type LIKE'%subscriber' ORDER BY username ASC");
											if($result && $result->num_rows > 0) {
												while($user = $result->fetch_assoc()) {
													$selected = (isset($_POST['user_id']) && $_POST['user_id'] == $user['user_id']) ? 'selected="selected"' : '';
													$display_name = $user['username'] ;
													echo '<option value="' . $user['user_id'] . '" ' . $selected . '>' . htmlspecialchars($display_name) . '</option>';
												}
											}
											?>
										</select>
									</div>
								</div>
							</div>
						</div>

						<!-- Transaction Type -->
						<div class="row">
							<div class="col-md-12 mb-3">
								<div class="form-group row d-flex align-items-center mb-5">
									<label class="col-lg-2 form-control-label d-flex justify-content-lg-end" for="transaction_type">
										<?php _e("Transaction Type"); ?>*
									</label>
									<div class="col-lg-6">
										<select name="transaction_type" id="transaction_type" class="custom-select form-control" required>
											<option value=""><?php _e("-- Select Type --"); ?></option>
											<option value="1" <?php echo (isset($_POST['transaction_type']) && $_POST['transaction_type'] == '1') ? 'selected="selected"' : ''; ?>>
												<?php _e("Withdrawal"); ?>
											</option>
											<option value="2" <?php echo (isset($_POST['transaction_type']) && $_POST['transaction_type'] == '2') ? 'selected="selected"' : ''; ?>>
												<?php _e("Funded"); ?>
											</option>
											<option value="3" <?php echo (isset($_POST['transaction_type']) && $_POST['transaction_type'] == '3') ? 'selected="selected"' : ''; ?>>
												<?php _e("Commission"); ?>
											</option>
										</select>
									</div>
								</div>
							</div>
						</div>

						<!-- Amount -->
						<div class="row">
							<div class="col-md-12 mb-3">
								<div class="form-group row d-flex align-items-center mb-5">
									<label class="col-lg-2 form-control-label d-flex justify-content-lg-end" for="amount">
										<?php _e("Amount"); ?>*
									</label>
									<div class="col-lg-4">
										<div class="input-group">
											<div class="input-group-prepend">
												<span class="input-group-text">$</span>
											</div>
											<input type="number" step="0.00000001" min="0" name="amount" id="amount" 
												   class="form-control" placeholder="0.00000000" 
												   value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>" 
												   required />
										</div>
										<small class="form-text text-muted">
											<?php _e("Enter amount with up to 8 decimal places"); ?>
										</small>
									</div>
								</div>
							</div>
						</div>

						<!-- Description -->
						<div class="row">
							<div class="col-md-12 mb-3">
								<div class="form-group row d-flex align-items-center mb-5">
									<label class="col-lg-2 form-control-label d-flex justify-content-lg-end" for="description">
										<?php _e("Description"); ?>
									</label>
									<div class="col-lg-6">
										<textarea name="description" id="description" class="form-control" 
												  rows="4" placeholder="<?php _e("Enter transaction details here..."); ?>"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
									</div>
								</div>
							</div>
						</div>

						<!-- Hidden Fields -->
						<input type="hidden" name="add_transaction" value="1" />
						
						<!-- Submit Button -->
						<div class="row">
							<div class="col-md-12">
								<div class="form-group row">
									<div class="col-lg-2"></div>
									<div class="col-lg-6">
										<button type="submit" class="btn btn-primary btn-md btn-golden">
											<i class="la la-plus-circle"></i> <?php _e("Create Transaction"); ?>
										</button>
										<a  href="transactions.php" class="btn btn-secondary btn-md">
											<i class="la la-door"></i> <?php _e("Cancel"); ?>
										</a>
									</div>
								</div>
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