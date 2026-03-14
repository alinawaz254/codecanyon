<?php

require_once("lib/system_load.php");

authenticate_user('subscriber');

$user_id = (int)$_SESSION['user_id'];

$page_title = _("Wallet Transfer");

$msg = "";

if(isset($_POST['send'])){

    $email  = trim($_POST['email'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $user_id = $_SESSION['user_id'];

    // Get wallet balance
    $balance = $wallet_obj->get_balance($user_id);

    if(empty($email) || $amount <= 0){
        $msg = "Invalid email or amount";
    }
    elseif($amount > $balance){
        $msg = "Insufficient wallet balance";
    }
    else{

        // Find receiver
        $q = $db->prepare("SELECT user_id FROM users WHERE email=? LIMIT 1");
        $q->bind_param("s", $email);
        $q->execute();

        $result = $q->get_result();

        if($result->num_rows == 0){
            $msg = "User not found";
        }
        else{

            $r = $result->fetch_assoc();
            $receiver_id = $r['user_id'];

            // Prevent sending to self
            if($receiver_id == $user_id){
                $msg = "You cannot send money to yourself";
            }
            else{

                $_SESSION['transfer_receiver'] = $receiver_id;
                $_SESSION['transfer_amount']   = $amount;

                // Generate OTP
                $otp = $wallet_obj->send_otp($user_id);

                if($otp){

                    mail($_SESSION['email'], "Wallet OTP", "Your OTP is: ".$otp);

                    header("Location: wallet_transfer_verify.php");
                    exit;
                }else{
                    $msg = "Failed to generate OTP";
                }
            }
        }
    }
}

require_once("lib/includes/header.php");
?>

<style>
.transfer-card {
    max-width: 500px;
    margin: auto;
}

.transfer-title {
    text-align: center;
    margin-bottom: 20px;
}
</style>


<div class="mywidget wc_data">

    <div class="widget has-shadow">

        <div class="widget-body">

            <div class="transfer-card">

                <h3 class="transfer-title">Wallet Transfer</h3>

                <?php if($msg): ?>
                <div class="alert alert-danger">
                    <?php echo $msg; ?>
                </div>
                <?php endif; ?>

                <form method="post">

                    <div class="form-group">

                        <label>Receiver Email</label>

                        <input type="email" name="email" class="form-control" placeholder="Enter receiver email"
                            required>

                    </div>

                    <div class="form-group">

                        <label>Amount</label>

                        <input type="number" name="amount" class="form-control" placeholder="Enter amount" required>

                    </div>

                    <div class="text-center mt-3">

                        <button type="submit" name="send" class="btn btn-golden btn-md">

                            <i class="la la-paper-plane"></i>
                            Send OTP

                        </button>
                        <a href="wallet.php" class="btn btn-md btn-secondary">
                            Back
                        </a> 
                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<?php require_once("lib/includes/footer.php"); ?>