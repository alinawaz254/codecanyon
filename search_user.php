<?php
require_once("lib/system_load.php");

$datatables = 1;

//user Authentication.
authenticate_user('admin');

$page_title = _("Search User");
require_once("lib/includes/header.php");

$search_query = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : '';
$user_data = null;
$error = '';
$referrals = null;

if(!empty($search_query)) {
    $q = $db->real_escape_string($search_query);
    $query = "SELECT * FROM users WHERE user_id='$q' OR username='$q' OR email='$q' OR phone='$q' OR mobile='$q' LIMIT 1";
    $res = $db->query($query);
    if($res && $res->num_rows > 0) {
        $user_data = $res->fetch_assoc();
        
        $inv_q = $db->query("SELECT COALESCE(SUM(amount),0) AS total_inv FROM user_investments WHERE user_id='".$user_data['user_id']."'");
        $inv_row = $inv_q->fetch_assoc();
        $user_data['total_investment'] = $inv_row['total_inv'];
        
        $ref_query = "
            SELECT u.user_id, u.username, u.first_name, u.last_name, u.phone, u.mobile, u.email, u.address1, u.address2,
            (SELECT COALESCE(SUM(amount),0) FROM user_investments WHERE user_id = u.user_id) as total_investment,
            (SELECT COALESCE(SUM(ui.amount),0) FROM user_investments ui JOIN users sub_u ON ui.user_id = sub_u.user_id WHERE sub_u.referral_id = u.user_id) as team_investment,
            (SELECT COUNT(*) FROM users sub_u WHERE sub_u.referral_id = u.user_id) as ref_count
            FROM users u
            WHERE u.referral_id = '".$user_data['user_id']."'
        ";
        $referrals = $db->query($ref_query);
    } else {
        $error = "User not found.";
    }
}
?>

<style>
    .search-link {
        color: #333 !important;
        font-weight: bold !important;
        text-decoration: none !important;
        transition: color 0.3s;
    }
    .search-link:hover {
        color: #ff9800 !important;
        text-decoration: none !important;
    }
</style>

<div class="row mb-4">
    <div class="col-xl-12">
        <div class="widget has-shadow">
            <div class="widget-body">
                <form method="GET" action="">
                    <div class="row align-items-center">
                        <div class="col-md-10 mt-2">
                            <input type="text" name="search" class="form-control" placeholder="Search by ID, Username, Email, Phone..." value="<?php echo htmlspecialchars((string) $search_query); ?>" required>
                        </div>
                        <div class="col-md-2 mt-2">
                            <button type="submit" class="btn btn-primary btn-block btn-golden w-100">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if(!empty($error)): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if($user_data): ?>
<div class="row mb-4">
    <div class="col-xl-12">
        <div class="widget has-shadow">
            <div class="widget-body">
                <div class="row">
                    <div class="col-md-9">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>NAME:</strong> <?php echo htmlspecialchars((string) ($user_data['first_name'] . ' ' . $user_data['last_name'])); ?></td>
                                <td><strong>EMAIL:</strong> <?php echo htmlspecialchars((string) $user_data['email']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>PHONE:</strong> <?php echo htmlspecialchars((string) (!empty($user_data['phone']) ? $user_data['phone'] : $user_data['mobile'])); ?></td>
                                <td><strong>ADDRESS:</strong> <?php echo htmlspecialchars((string) trim($user_data['address1'] . ' ' . $user_data['address2'])); ?></td>
                            </tr>
                            <tr>
                                <td><strong>INVESTMENT:</strong> PKR <?php echo number_format($user_data['total_investment'], 2); ?></td>
                                <td><strong>ACCOUNT NUMBER:</strong> <?php echo htmlspecialchars((string) $user_data['account_number']); ?><?php echo !empty($user_data['bank_name']) ? ' - ' . htmlspecialchars((string) $user_data['bank_name']) : ''; ?></td>
                            </tr>

                            <tr>
                                <td colspan="2"><strong>IBAN NO:</strong> <?php echo isset($user_data['iban_no']) ?  htmlspecialchars($user_data['iban_no']) : ''; ?></td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="d-flex align-items-center mt-2">
                                        <form method="POST" action="manage_users.php" target="_blank" class="mr-2 mb-0">
                                            <input type="hidden" name="edit_user" value="<?php echo htmlspecialchars((string) $user_data['user_id']); ?>">
                                            <button type="submit" class="btn btn-primary btn-sm btn-golden" style="padding: 5px 25px;"><?php _e("Edit Profile"); ?></button>
                                        </form>
                                        <a href="view_profile.php?user_id=<?php echo $user_data['user_id']; ?>" class="btn btn-golden-admin btn-sm mx-2" style="padding: 5px 25px;">
                                            <i class="la la-user"></i> <?php _e("View Full Profile"); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-3 text-center">
                        <?php 
                        $img = !empty($user_data['profile_image']) ? $user_data['profile_image'] : 'assets/images/thumb.png'; 
                        ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" alt="Profile Image" style="max-width: 150px; border-radius: 8px; border: 1px solid #ddd; padding: 5px;" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="widget has-shadow">
            <div class="widget-header bordered no-actions d-flex align-items-center justify-content-between">
                <h2>User Referrals Details</h2>
                <button onclick="window.history.back();" class="btn btn-secondary btn-sm">Back</button>
            </div>
            <div class="widget-body">
                <div class="table-responsive">
                    <table id="export-table" class="table mb-0 table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>User (ID - Name)</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Total Investment</th>
                                <th>Team Investment</th>
                                <th>Ref Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($referrals && $referrals->num_rows > 0): ?>
                                <?php while($ref = $referrals->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <a href="search_user.php?search=<?php echo urlencode((string) $ref['username']); ?>" class="search-link">
                                                <?php echo htmlspecialchars((string) $ref['username']); ?>
                                            </a> 
                                            - <?php echo htmlspecialchars((string) ($ref['first_name'] . ' ' . $ref['last_name'])); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars((string) (!empty($ref['phone']) ? $ref['phone'] : $ref['mobile'])); ?></td>
                                        <td>
                                            <a href="search_user.php?search=<?php echo urlencode((string) $ref['email']); ?>" class="search-link">
                                                <?php echo htmlspecialchars((string) $ref['email']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars((string) trim($ref['address1'] . ' ' . $ref['address2'])); ?></td>
                                        <td>PKR <?php echo number_format($ref['total_investment'], 2); ?></td>
                                        <td>PKR <?php echo number_format($ref['team_investment'], 2); ?></td>
                                        <td><?php echo $ref['ref_count']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No referred users found in downline.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
require_once("lib/includes/footer.php");
?>
