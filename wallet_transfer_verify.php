<?php

require_once("lib/system_load.php");

authenticate_user('subscriber');

$msg = "";

if(isset($_POST['verify'])){

    $otp = $_POST['otp'];

    if($wallet_obj->verify_otp($_SESSION['user_id'],$otp)){

        $wallet_obj->transfer(
            $_SESSION['user_id'],
            $_SESSION['transfer_receiver'],
            $_SESSION['transfer_amount']
        );

        unset($_SESSION['transfer_receiver']);
        unset($_SESSION['transfer_amount']);

        $msg = "Transfer Successful";

    }else{

        $msg = "Invalid OTP";

    }

}

require_once("lib/includes/header.php");

?>

<style>
.verify-card {
    max-width: 450px;
    margin: auto;
}

.verify-title {
    text-align: center;
    margin-bottom: 20px;
}
</style>


<div class="mywidget wc_data">

    <div class="widget has-shadow">

        <div class="widget-body">

            <div class="verify-card">

                <h3 class="verify-title">Verify Wallet Transfer</h3>

                <?php if($msg): ?>
                <div class="alert alert-info text-center">
                    <?php echo $msg; ?>
                </div>
                <?php endif; ?>

                <form method="post">

                    <div class="form-group">

                        <label>Enter OTP</label>

                        <input type="text" name="otp" class="form-control" placeholder="Enter OTP" required>

                    </div>

                    <div class="text-center mt-3">

                        <button name="verify" class="btn btn-success">

                            <i class="la la-check"></i>
                            Verify Transfer

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<?php require_once("lib/includes/footer.php"); ?>