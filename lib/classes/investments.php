<?php
class Investments {

public $plan_name;
public $total_cycles;
public $commission;
public $cycle_days;

public $user_id;
public $plan_id;
public $amount;
public $issue_date;


/* ================= PLAN ================= */

function add_plan($name,$cycles,$commission,$cycle_days){
    global $db;
    $name = $db->real_escape_string($name);

    $db->query("INSERT INTO investment_plans 
    (plan_name,total_cycles,commission,cycle_days) 
    VALUES ('$name','$cycles','$commission','35')");
    
    return "Package Added Successfully";
}

function set_plan($plan_id){
    global $db;
    $result = $db->query("SELECT * FROM investment_plans WHERE plan_id='$plan_id'");
    $row = $result->fetch_assoc();

    $this->plan_name     = $row['plan_name'];
    $this->total_cycles  = $row['total_cycles'];
    $this->commission    = $row['commission'];
    $this->cycle_days    = $row['cycle_days'];
}

function update_plan($plan_id,$name,$cycles,$commission,$cycle_days){
    global $db;
    $name = $db->real_escape_string($name);

    $db->query("UPDATE investment_plans SET
        plan_name='$name',
        total_cycles='$cycles',
        commission='$commission',
        cycle_days='35'
        WHERE plan_id='$plan_id'");
    
    return "Package Updated Successfully";
}

function delete_plan($id){
    global $db;
    $db->query("DELETE FROM investment_plans WHERE plan_id='$id'");
    return "Package Deleted";
}

function list_plans(){
    global $db;

    $result = $db->query("SELECT * FROM investment_plans ORDER BY plan_id DESC");

    echo '<table id="plans-table" class="table table-bordered">';

    echo '<thead>
            <tr>
                <th>Name</th>
                <th>Cycles</th>
                <th>Commission</th>
                <th>Action</th>
            </tr>
          </thead>';

    echo '<tbody>';

    while($row = $result->fetch_assoc()){
        echo "<tr>
        <td>{$row['plan_name']}</td>
        <td>{$row['total_cycles']}</td>
        <td>{$row['commission']}%</td>
        <td>

        <form method='post' action='manage_investment_plan.php' style='display:inline'>
        <input type='hidden' name='edit_plan' value='{$row['plan_id']}'>
        <input type='submit' class='btn btn-primary btn-sm' value='Edit'>
        </form>

        <form method='post' style='display:inline'>
        <input type='hidden' name='delete_plan' value='{$row['plan_id']}'>
        <input type='submit' class='btn btn-danger btn-sm' value='Delete'>
        </form>

        </td>
        </tr>";
    }

    echo '</tbody>';
    echo '</table>';
}


/* ================= INVESTMENTS ================= */

function add_investment($user_id,$plan_ids,$amount,$issue_date){
    global $db;


    foreach ($plan_ids as $key => $plan_id) {    

        $search      = $db->query("SELECT * FROM investment_plans WHERE plan_id = $plan_id");
        $plan        = mysqli_fetch_assoc($search);
        $comission   = ($amount * $plan['commission']) / 100;
        $ref_query   = $db->query("SELECT referral_id FROM users WHERE user_id = '$user_id'");
        $ref_data    = $ref_query->fetch_assoc();
        $referrer_id = $ref_data['referral_id'] ?? 0;


        $investment         = $db->query("INSERT INTO user_investments 
        (user_id,plan_id,amount,issue_date) 
        VALUES ('$user_id','$plan_id','$amount','$issue_date')");

        $investment_id = $db->insert_id;

        // bonus logic
        if($referrer_id > 0){

            $bonus_amount = ($amount * 10) / 100;

            $db->query("
                INSERT INTO user_investment_bonus 
                (investment_id, user_id, bonus_amount) 
                VALUES ('$investment_id', '$referrer_id', '$bonus_amount')
            ");

            // username
            $u = $db->query("SELECT username FROM users WHERE user_id = $referrer_id");
            $referrer = $u->fetch_assoc()['username'] ?? 'User';

            if($key == 0){
               // notification
                send_notification(
                    ADMIN_ID,
                    $referrer_id,
                    "10% Referral bonus generated for $referrer",
                    "bonus",
                    $investment_id
                );
            }else{
                continue;
            }
        }       
        
        for ($i=1; $i <= $plan['total_cycles']; $i++) {     

            $comission_expiry_date = date(
            "Y-m-d",
            strtotime($issue_date . " +" . ($i * intval($plan['cycle_days'])) . " days")
            );            

            $db->query("INSERT INTO user_investment_details 
            (investment_id,user_id,cycle,comission,comission_expiry_date) 
            VALUES ('$investment_id','$user_id','$i','$comission','$comission_expiry_date')");

            if($referrer_id > 0){

                $referral_commission = ($amount * 1) / 100;

                $db->query("
                    INSERT INTO user_investment_details 
                    (investment_id,user_id,cycle,comission,comission_expiry_date) 
                    VALUES ('$investment_id','$referrer_id','$i','$referral_commission','$comission_expiry_date')
                ");
            }
        }
            
    }

    
    return "Investment Added Successfully";
}

function set_investment($investment_id){
    global $db;

    $result = $db->query("SELECT * FROM user_investments WHERE investment_id='$investment_id'");
    $row = $result->fetch_assoc();

    $this->user_id    = $row['user_id'];
    $this->plan_id    = $row['plan_id'];
    $this->amount     = $row['amount'];
    $this->issue_date = $row['issue_date'];
}


function update_investment($investment_id,$user_id,$plan_id,$amount,$issue_date){
    global $db;

    if (is_array($plan_id)) {
        $plan_id = reset($plan_id);
    }

    $investment_id = intval($investment_id);
    $user_id = intval($user_id);
    $plan_id = intval($plan_id);
    $amount = floatval($amount);
    $ref_query   = $db->query("SELECT referral_id FROM users WHERE user_id = '$user_id'");
    $ref_data    = $ref_query->fetch_assoc();
    $referrer_id = $ref_data['referral_id'] ?? 0;

    $db->query("UPDATE user_investments SET
        user_id='$user_id',
        plan_id='$plan_id',
        amount='$amount',
        issue_date='$issue_date'
        WHERE investment_id='$investment_id'");

    $db->query("DELETE FROM user_investment_details 
                WHERE investment_id='$investment_id'");

    $plan_query = $db->query("SELECT * FROM investment_plans 
                              WHERE plan_id='$plan_id'");
    $plan = $plan_query->fetch_assoc();

    $commission_amount = ($amount * $plan['commission']) / 100;

    for ($i=1; $i <= $plan['total_cycles']; $i++) {

        $expiry_date = date(
            "Y-m-d",
            strtotime($issue_date . " +" . ($i * intval($plan['cycle_days'])) . " days")
        );

        $db->query("INSERT INTO user_investment_details
        (investment_id,user_id,cycle,comission,comission_expiry_date)
        VALUES
        ('$investment_id','$user_id','$i','$commission_amount','$expiry_date')");

        if($referrer_id > 0){

            $referral_commission = ($amount * 1) / 100;

            $db->query("
                INSERT INTO user_investment_details 
                (investment_id,user_id,cycle,comission,comission_expiry_date) 
                VALUES ('$investment_id','$referrer_id','$i','$referral_commission','$expiry_date')
            ");
        }
    }

    return "Investment Updated Successfully";
}

function delete_investment($id){
    global $db;

    $id = intval($id);

    $db->query("DELETE FROM user_investment_details WHERE investment_id='$id'");
    $db->query("DELETE FROM user_investments WHERE investment_id='$id'");

    return "Investment Deleted";    
}

function list_investments(){
    global $db;
    $modals = ""; 

    $result = $db->query("
        SELECT 
        ui.investment_id,
        ui.user_id,
        ui.plan_id,
        ui.amount,
        ui.issue_date,
        ui.created_at,
        u.username,
        u.user_id,
        p.plan_name,
        p.total_cycles,
        p.cycle_days,
        p.commission
        FROM user_investments ui
        JOIN users u ON ui.user_id = u.user_id
        JOIN investment_plans p ON ui.plan_id = p.plan_id
        ORDER BY ui.investment_id DESC
    ");    

    echo '<table class="table table-bordered">';
    echo '<thead>
            <tr>
                <th>User</th>
                <th>Plan</th>
                <th>Amount</th>
                <th>Issue Date</th>
                <th>Action</th>
            </tr>
          </thead>';

    echo '<tbody>';

    while($row = $result->fetch_assoc()){
        echo "<tr>
        <td>{$row['username']}</td>
        <td>{$row['plan_name']}</td>
        <td>{$row['amount']}</td>
        <td>{$row['issue_date']}</td>
        <td>

        <button class='btn btn-warning btn-sm'
        data-toggle='modal'
        data-target='#adminInvestmentModal_{$row['investment_id']}'>
        View
        </button>

        <form method='post' action='manage_investment.php' style='display:inline'>
        <input type='hidden' name='edit_investment' value='{$row['investment_id']}'>
        <input type='submit' class='btn btn-info btn-sm' value='Edit'>
        </form>

        <form method='post' style='display:inline'>
        <input type='hidden' name='delete_investment' value='{$row['investment_id']}'>
        <input type='submit' class='btn btn-danger btn-sm' value='Delete'>
        </form>

        </td>
        </tr>";


    $details = $db->query("
        SELECT cycle,comission,comission_expiry_date,is_claimed,claimed_date
        FROM user_investment_details
        WHERE user_id = '{$row['user_id']}' AND investment_id='{$row['investment_id']}'
        ORDER BY cycle ASC
    ");

    ob_start();
    ?>

    <div class="modal fade" id="adminInvestmentModal_<?php echo $row['investment_id']; ?>">
        <div class="modal-dialog modal-lg">
            <div class="modal-content investment-modal">
                <div class="modal-header investment-header">
                    <h5 class="modal-title"><?php echo $row['plan_name']; ?> Investment Details</h5>

                    <button type="button"
                        class="btn-close-investment"
                        data-dismiss="modal">
                        &times;
                    </button>    
                </div>
                <div class="modal-body">
                    <div class="investment-info">
                        <div>
                            <label class="info-label">User</label><br>
                            <b class="info-value"><?php echo $row['username']; ?></b>
                        </div>
                        <div>
                            <label class="info-label">Amount</label><br>
                            <b class="info-value"><?php echo number_format($row['amount'],2); ?></b>
                        </div>
                        <div>
                            <label class="info-label">Date Issued On</label><br>
                            <b class="info-value"><?php echo $row['issue_date']; ?></b>
                        </div>
                    </div>
                    <hr>
                    <table class="table dataTable investment-table">
                        <thead class="">
                            <tr>
                                <th>Cycle</th>
                                <th>Commission Date</th>
                                <th>Commission</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($d = $details->fetch_assoc()){ 
                            $today    = date("Y-m-d");
                            $expired  = strtotime($today) > strtotime($d['comission_expiry_date']);
                            $date1    = new DateTime($today);
                            $date2    = new DateTime($d['comission_expiry_date']);
                            $interval = $date1->diff($date2);
                        ?>

                        <tr>
                            <td><?php echo $d['cycle']; ?></td>
                            <td><?php echo $d['comission_expiry_date']; ?></td>
                            <td><?php echo number_format($d['comission'],2); ?></td>
                            <td>

                            <?php if($d['is_claimed'] == 1){ ?>

                            <span class="badge text-bg-success">
                            Released on <?php echo $d['claimed_date']; ?>
                            </span>

                            <?php } else { ?>

                            <span class="badge text-bg-danger">
                            Unpaid (<?php echo $interval->days ?> days left)
                            </span>

                            <?php } ?>

                            </td>
                        </tr>

                        <?php } ?>
                        </tbody>
                    </table>

                </div>
                <div class="modal-footer investment-footer">
                    <button class="btn btn-golden btn-md mb-5"
                        data-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php

    $modals .= ob_get_clean();

    }

    echo "</table>";

    echo $modals;

}

}
?>