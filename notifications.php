<?php

require_once "lib/system_load.php";
authenticate_user('admin');

$page_title = _("Notifications");

require_once("lib/includes/header.php");

$user_id = $_SESSION['user_id'];

$query = "
SELECT 
n.*,
u.username
FROM notifications n
LEFT JOIN users u 
ON n.sender_id = u.user_id
WHERE n.receiver_id='$user_id'
ORDER BY n.id DESC
";

$result = $db->query($query);

$total = $result->num_rows;

$unread_query = "
SELECT COUNT(*) as total
FROM notifications
WHERE receiver_id='$user_id'
AND is_read = 0
";

$unread_result = $db->query($unread_query);
$unread = $unread_result->fetch_assoc()['total'];

?>

<div class="container-fluid">

    <div class="row">

        <div class="col-xl-3 col-md-6">
            <div class="widget has-shadow">
                <div class="widget-body">
                    <h4>Total Notifications</h4>
                    <div class="number"><?= $total ?></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="widget has-shadow">
                <div class="widget-body">
                    <h4>Unread</h4>
                    <div class="number text-danger"><?= $unread ?></div>
                </div>
            </div>
        </div>

    </div>


    <div class="widget has-shadow">

        <div class="widget-header bordered no-actions d-flex align-items-center">
            <h4>Notification Logs</h4>
        </div>

        <div class="widget-body">

            <div class="table-responsive">

                <table class="table mb-0">

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>

                    </thead>
                    <tbody>

                        <?php while($row = $result->fetch_assoc()){ ?>

                        <tr>

                            <td><?= $row['id'] ?></td>

                            <td>

                                <i class="la la-user text-primary"></i>

                                <?= $row['username'] ?? 'System' ?>

                            </td>

                            <td>

                                <i class="la la-bell text-warning"></i>

                                <?= $row['message'] ?>

                            </td>

                            <td>

                                <?= date("d M Y H:i", strtotime($row['created_at'])) ?>

                            </td>

                            <td>

                                <?php if($row['is_read']==0){ ?>

                                <a href="clear_notification.php?id=<?=$row['id']?>" class="btn btn-sm btn-warning">
                                    Mark Read
                                </a>

                                <?php } else { ?>

                                <span class="badge text-success">
                                    <i class="la la-check"></i> Read
                                </span>

                                <?php } ?>

                            </td>

                        </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?php require_once("lib/includes/footer.php"); ?>