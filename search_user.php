<?php
require_once("lib/system_load.php");

$datatables = 1;

//user Authentication.
authenticate_user('admin');

$page_title = _("Search User");
require_once("lib/includes/header.php");

$search_query = isset($_POST['search']) ? trim($_POST['search']) : '';
$user_data = null;
$error = '';
$referrals = null;

if(!empty($search_query)) {
    $q = $db->real_escape_string($search_query);
    $query = "SELECT * FROM users WHERE user_id='$q' OR username='$q' OR email='$q' OR phone='$q' LIMIT 1";
    $res = $db->query($query);
    if($res && $res->num_rows > 0) {
        $user_data = $res->fetch_assoc();
        
        $inv_q = $db->query("SELECT COALESCE(SUM(amount),0) AS total_inv FROM user_investments WHERE user_id='".$user_data['user_id']."'");
        $inv_row = $inv_q->fetch_assoc();
        $user_data['total_investment'] = $inv_row['total_inv'];
        
        $ref_query = "
            SELECT u.user_id, u.first_name, u.last_name, u.phone, u.email, u.address1, u.address2,
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

<div class="row mb-4">
    <div class="col-xl-12">
        <div class="widget has-shadow">
            <div class="widget-body">
                <form method="POST" action="">
                    <div class="row align-items-center">
                        <div class="col-md-10 mt-2">
                            <input type="text" name="search" class="form-control" placeholder="Search by ID, Username, Email, Phone..." value="<?php echo htmlspecialchars($search_query); ?>" required>
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
                                <td><strong>NAME:</strong> <?php echo htmlspecialchars($user_data['first_name'].' '.$user_data['last_name']); ?></td>
                                <td><strong>EMAIL:</strong> <?php echo htmlspecialchars($user_data['email']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>PHONE:</strong> <?php echo htmlspecialchars($user_data['phone']); ?></td>
                                <td><strong>ADDRESS:</strong> <?php echo htmlspecialchars(trim($user_data['address1'].' '.$user_data['address2'])); ?></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>INVESTMENT:</strong> PKR <?php echo number_format($user_data['total_investment'], 2); ?></td>
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
            <div class="widget-header bordered no-actions d-flex align-items-center">
                <h2>User Referrals Details</h2>
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
                                <th>T. Investment</th>
                                <th>Team Investment</th>
                                <th>Ref Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($referrals && $referrals->num_rows > 0): ?>
                                <?php while($ref = $referrals->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $ref['user_id']; ?> - <?php echo htmlspecialchars($ref['first_name'].' '.$ref['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($ref['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($ref['email']); ?></td>
                                        <td><?php echo htmlspecialchars(trim($ref['address1'] . ' ' . $ref['address2'])); ?></td>
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
