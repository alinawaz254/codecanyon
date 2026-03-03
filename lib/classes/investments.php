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
    VALUES ('$name','$cycles','$commission','$cycle_days')");
    
    return "Plan Added Successfully";
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
        cycle_days='$cycle_days'
        WHERE plan_id='$plan_id'");
    
    return "Plan Updated Successfully";
}

function delete_plan($id){
    global $db;
    $db->query("DELETE FROM investment_plans WHERE plan_id='$id'");
    return "Plan Deleted";
}

function list_plans(){
    global $db;
    $result = $db->query("SELECT * FROM investment_plans ORDER BY plan_id DESC");

    echo '<table class="table">';
    echo '<tr><th>Name</th><th>Cycles</th><th>Commission</th><th>Action</th></tr>';

    while($row = $result->fetch_assoc()){
        echo "<tr>
        <td>{$row['plan_name']}</td>
        <td>{$row['total_cycles']}</td>
        <td>{$row['commission']}</td>
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

    echo '</table>';
}


/* ================= INVESTMENTS ================= */

function add_investment($user_id,$plan_id,$amount,$issue_date){
    global $db;

    $db->query("INSERT INTO user_investments 
    (user_id,plan_id,amount,issue_date) 
    VALUES ('$user_id','$plan_id','$amount','$issue_date')");

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

    $db->query("UPDATE user_investments SET
        user_id='$user_id',
        plan_id='$plan_id',
        amount='$amount',
        issue_date='$issue_date'
        WHERE investment_id='$investment_id'");

    return "Investment Updated Successfully";
}

function delete_investment($id){
    global $db;
    $db->query("DELETE FROM user_investments WHERE investment_id='$id'");
    return "Investment Deleted";
}

function list_investments(){
    global $db;

    $result = $db->query("
        SELECT ui.*, u.username, p.plan_name 
        FROM user_investments ui
        JOIN users u ON ui.user_id = u.user_id
        JOIN investment_plans p ON ui.plan_id = p.plan_id
        ORDER BY ui.investment_id DESC
    ");

    echo '<table class="table">';
    echo '<tr><th>User</th><th>Plan</th><th>Amount</th><th>Issue Date</th><th>Action</th></tr>';

    while($row = $result->fetch_assoc()){
        echo "<tr>
        <td>{$row['username']}</td>
        <td>{$row['plan_name']}</td>
        <td>{$row['amount']}</td>
        <td>{$row['issue_date']}</td>
        <td>

        <form method='post' action='manage_investment.php' style='display:inline'>
        <input type='hidden' name='edit_investment' value='{$row['investment_id']}'>
        <input type='submit' class='btn btn-primary btn-sm' value='Edit'>
        </form>

        <form method='post' style='display:inline'>
        <input type='hidden' name='delete_investment' value='{$row['investment_id']}'>
        <input type='submit' class='btn btn-danger btn-sm' value='Delete'>
        </form>

        </td>
        </tr>";
    }

    echo '</table>';
}

}
?>