<?php
	require_once("lib/system_load.php");
	//Including this file we load system.
	
	//This loads system.
	authenticate_user('all');

	// Decide which user to show
	$target_user_id = $_SESSION['user_id'];
	if($_SESSION['user_type'] == 'admin' && isset($_GET['user_id']) && !empty($_GET['user_id'])) {
		$target_user_id = intval($_GET['user_id']);
	}

	// Fetch target user data
	$view_user = new Users();
	// We need to set the user data into the object. 
	// The current Users class seems to use set_user method.
	$view_user->set_user($target_user_id, $_SESSION['user_type'], $_SESSION['user_id']);

	$page_title = _("User Profile"); //You can edit this to change your page title.
	require_once('lib/includes/header.php');

	// Get Referrer Info
	$referrer_name = 'N/A';
	if($view_user->referral_id > 0) {
		$ref_data = $db->query("SELECT first_name, last_name, username FROM users WHERE user_id = " . intval($view_user->referral_id))->fetch_assoc();
		if($ref_data) {
			$referrer_name = $ref_data['first_name'] . ' ' . $ref_data['last_name'] . ' (' . $ref_data['username'] . ')';
		}
	}

	$profile_img = (!empty($view_user->profile_image)) ? $view_user->profile_image : 'assets/images/thumb.png';
?>

<style>
	.profile-container {
		background: #fff;
		border-radius: 15px;
		box-shadow: 0 10px 30px rgba(0,0,0,0.05);
		overflow: hidden;
		margin-bottom: 30px;
	}
	.profile-header {
		background: linear-gradient(135deg, #ECAD3D 0%, #f1bc1c 100%);
		padding: 40px 30px;
		color: #fff;
		display: flex;
		align-items: center;
		gap: 30px;
	}
	.profile-avatar {
		width: 150px;
		height: 150px;
		border-radius: 50%;
		border: 5px solid rgba(255,255,255,0.3);
		object-fit: cover;
		box-shadow: 0 5px 15px rgba(0,0,0,0.1);
	}
	.profile-info h2 {
		margin: 0;
		font-weight: 800;
		font-size: 28px;
		letter-spacing: 0.5px;
	}
	.profile-info p {
		margin: 5px 0 0;
		opacity: 0.9;
		font-size: 16px;
	}
	.profile-badge {
		display: inline-block;
		padding: 4px 12px;
		background: rgba(255,255,255,0.2);
		border-radius: 20px;
		font-size: 12px;
		font-weight: 700;
		text-transform: uppercase;
		margin-top: 10px;
	}
	.nav-tabs-custom {
		border-bottom: 2px solid #f4f4f4;
		padding: 0 30px;
	}
	.nav-tabs-custom .nav-link {
		border: none;
		color: #888;
		font-weight: 700;
		padding: 20px 0;
		margin-right: 30px;
		position: relative;
		background: transparent;
	}
	.nav-tabs-custom .nav-link.active {
		color: #ECAD3D;
		background: transparent;
	}
	.nav-tabs-custom .nav-link.active::after {
		content: '';
		position: absolute;
		bottom: -2px;
		left: 0;
		width: 100%;
		height: 2px;
		background: #ECAD3D;
	}
	.profile-body {
		padding: 30px;
	}
	.info-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
		gap: 25px;
	}
	.info-item {
		padding: 15px;
		background: #f9f9f9;
		border-radius: 10px;
		border-left: 4px solid #ECAD3D;
	}
	.info-label {
		display: block;
		font-size: 11px;
		text-transform: uppercase;
		color: #999;
		font-weight: 700;
		margin-bottom: 5px;
		letter-spacing: 1px;
	}
	.info-value {
		display: block;
		font-size: 15px;
		color: #333;
		font-weight: 600;
	}
	.document-card {
		background: #fff;
		border: 1px solid #eee;
		border-radius: 10px;
		padding: 15px;
		text-align: center;
		transition: transform 0.3s;
	}
	.document-card:hover {
		transform: translateY(-5px);
		box-shadow: 0 5px 15px rgba(0,0,0,0.05);
	}
	.document-thumb {
		width: 100%;
		height: 180px;
		object-fit: contain;
		margin-bottom: 15px;
		border-radius: 5px;
		cursor: pointer;
	}
	.section-title {
		font-size: 18px;
		font-weight: 800;
		color: #333;
		margin-bottom: 20px;
		display: flex;
		align-items: center;
		gap: 10px;
	}
	.section-title i {
		color: #ECAD3D;
	}
	.action-buttons {
		margin-top: 30px;
		display: flex;
		gap: 15px;
	}
	
	@media (max-width: 768px) {
		.profile-header {
			flex-direction: column;
			text-align: center;
			padding: 30px 20px;
		}
		.nav-tabs-custom {
			padding: 0 15px;
			display: flex;
			overflow-x: auto;
		}
		.nav-tabs-custom .nav-link {
			margin-right: 20px;
			white-space: nowrap;
		}
	}
</style>

<div class="profile-container">
	<div class="profile-header">
		<img src="<?=$profile_img?>" class="profile-avatar" alt="Profile Image">
		<div class="profile-info">
			<h2><?php echo $view_user->first_name . ' ' . $view_user->last_name; ?></h2>
			<p><i class="la la-envelope"></i> <?php echo $view_user->email; ?></p>
			<div class="profile-badge"><?php echo ucfirst($view_user->user_type); ?></div>
			<div class="profile-badge bg-white text-dark ml-2"><?php echo ucfirst($view_user->status); ?></div>
		</div>
	</div>

	<ul class="nav nav-tabs nav-tabs-custom" id="profileTabs" role="tablist">
		<li class="nav-item">
			<button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab"><?php _e("Overview"); ?></button>
		</li>
		<li class="nav-item">
			<button class="nav-link" id="account-tab" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab"><?php _e("Account Details"); ?></button>
		</li>
		<li class="nav-item">
			<button class="nav-link" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab"><?php _e("Bank & Identification"); ?></button>
		</li>
	</ul>

	<div class="tab-content" id="profileTabsContent">
		<!-- Overview Tab -->
		<div class="tab-pane fade show active" id="overview" role="tabpanel">
			<div class="profile-body">
				<div class="section-title"><i class="la la-user"></i> <?php _e("Personal Information"); ?></div>
				<div class="info-grid">
					<div class="info-item">
						<span class="info-label"><?php _e("Username"); ?></span>
						<span class="info-value"><?php echo $view_user->username; ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("Gender"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->gender)) ? $view_user->gender : 'N/A'; ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("Date of Birth"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->date_of_birth)) ? date("d M, Y", strtotime($view_user->date_of_birth)) : 'N/A'; ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("Mobile"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->mobile)) ? $view_user->mobile : 'N/A'; ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("Phone"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->phone)) ? $view_user->phone : 'N/A'; ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("Referred By"); ?></span>
						<span class="info-value"><?php echo $referrer_name; ?></span>
					</div>
				</div>

				<div class="section-title mt-5"><i class="la la-map-marker"></i> <?php _e("Location Information"); ?></div>
				<div class="info-grid">
					<div class="info-item">
						<span class="info-label"><?php _e("Address 1"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->address1)) ? $view_user->address1 : 'N/A'; ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("Address 2"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->address2)) ? $view_user->address2 : 'N/A'; ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("City"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->city)) ? $view_user->city : 'N/A'; ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("State"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->state)) ? $view_user->state : 'N/A'; ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("Zip Code"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->zip_code)) ? $view_user->zip_code : 'N/A'; ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("Country"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->country)) ? $view_user->country : 'N/A'; ?></span>
					</div>
				</div>
			</div>
		</div>

		<!-- Account Details Tab -->
		<div class="tab-pane fade" id="account" role="tabpanel">
			<div class="profile-body">
				<div class="section-title"><i class="la la-cog"></i> <?php _e("Account Settings"); ?></div>
				<div class="info-grid">
					<div class="info-item">
						<span class="info-label"><?php _e("Account Status"); ?></span>
						<span class="info-value text-success"><?php echo ucfirst($view_user->status); ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("User Level"); ?></span>
						<span class="info-value"><?php echo ucfirst($view_user->user_type); ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("Registration Date"); ?></span>
						<span class="info-value">
							<?php 
								$reg_date = $view_user->get_user_info($target_user_id, 'date_register');
								echo (!empty($reg_date)) ? date("d M, Y", strtotime($reg_date)) : 'N/A'; 
							?>
						</span>
					</div>
				</div>

				<?php 
					$description = $view_user->get_user_info($target_user_id, 'description');
					if(!empty($description)): 
				?>
				<div class="section-title mt-5"><i class="la la-info-circle"></i> <?php _e("Description / Notes"); ?></div>
				<div class="p-4 bg-light rounded" style="border-left: 4px solid #ECAD3D;">
					<?php echo nl2br(htmlspecialchars($description)); ?>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Bank & Identification Tab -->
		<div class="tab-pane fade" id="bank" role="tabpanel">
			<div class="profile-body">
				<div class="section-title"><i class="la la-university"></i> <?php _e("Bank Information"); ?></div>
				<div class="info-grid">
					<div class="info-item">
						<span class="info-label"><?php _e("Bank Name"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->bank_name)) ? $view_user->bank_name : 'N/A'; ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("Account Holder"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->account_holder)) ? $view_user->account_holder : 'N/A'; ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("Account Number"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->account_number)) ? $view_user->account_number : 'N/A'; ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("IBAN No"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->iban_no)) ? $view_user->iban_no : 'N/A'; ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("Branch Name"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->branch_name)) ? $view_user->branch_name : 'N/A'; ?></span>
					</div>
					<div class="info-item">
						<span class="info-label"><?php _e("Branch Code"); ?></span>
						<span class="info-value"><?php echo (!empty($view_user->branch_code)) ? $view_user->branch_code : 'N/A'; ?></span>
					</div>
				</div>

				<div class="section-title mt-5"><i class="la la-id-card"></i> <?php _e("Identification Documents"); ?></div>
				<div class="row">
					<?php
						$nic_front = $view_user->get_user_info($target_user_id, 'nic_front');
						$nic_back = $view_user->get_user_info($target_user_id, 'nic_back');
					?>
					<div class="col-md-6 mb-3">
						<div class="document-card">
							<span class="info-label mb-2"><?php _e("NIC Front Side"); ?></span>
							<img src="<?php echo (!empty($nic_front)) ? $nic_front : 'assets/images/thumb.png'; ?>" class="document-thumb" onclick="viewImage('<?php echo (!empty($nic_front)) ? $nic_front : 'assets/images/thumb.png'; ?>')">
						</div>
					</div>
					<div class="col-md-6 mb-3">
						<div class="document-card">
							<span class="info-label mb-2"><?php _e("NIC Back Side"); ?></span>
							<img src="<?php echo (!empty($nic_back)) ? $nic_back : 'assets/images/thumb.png'; ?>" class="document-thumb" onclick="viewImage('<?php echo (!empty($nic_back)) ? $nic_back : 'assets/images/thumb.png'; ?>')">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="profile-body pt-0">
		<div class="action-buttons">
			<?php if($_SESSION['user_id'] == $target_user_id): ?>
				<a href="edit_profile.php" class="btn btn-golden-admin"><i class="la la-edit"></i> <?php _e("Edit My Profile"); ?></a>
			<?php elseif($_SESSION['user_type'] == 'admin'): ?>
				<form action="manage_users.php" method="post" class="d-inline">
					<input type="hidden" name="edit_user" value="<?php echo $target_user_id; ?>">
					<button type="submit" class="btn btn-golden-admin"><i class="la la-edit"></i> <?php _e("Edit User"); ?></button>
				</form>
				<a href="users.php" class="btn btn-secondary"><?php _e("Back to List"); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Image View Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: transparent; border: none;">
            <div class="modal-body p-0 text-center">
                <button type="button" class="btn-close-investment" data-bs-dismiss="modal" style="position: absolute; right: -15px; top: -15px; z-index: 1051;">&times;</button>
                <img id="fullImage" src="" style="max-width: 100%; max-height: 90vh; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            </div>
        </div>
    </div>
</div>

<script>
function viewImage(url) {
	document.getElementById('fullImage').src = url;
	var myModal = new bootstrap.Modal(document.getElementById('imageModal'));
	myModal.show();
}
</script>

<?php
	require_once("lib/includes/footer.php");
?>
