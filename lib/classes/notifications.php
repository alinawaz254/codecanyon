<?php

class Notifications {

    function unread_count() {

        global $db;

        $query = "SELECT COUNT(*) as total
        FROM notifications
        WHERE receiver_id='".$_SESSION['user_id']."'
        AND is_read=0";

        $result = $db->query($query);
        $row = $result->fetch_assoc();

        echo $row['total'];
    }

function notification_widget() {

    global $db;

    $query = "SELECT *
    FROM notifications
    WHERE receiver_id='".$_SESSION['user_id']."'
    AND is_read=0
    ORDER BY id DESC
    LIMIT 20";

    $result = $db->query($query);

    while($row = $result->fetch_assoc()) {

        echo '

        <li>
            <div class="notification-body">

                <div class="notification-icon">
                    <i class="la la-bell text-warning"></i>
                </div>

                <div class="notification-text">
                    '.$row['message'].'
                    <div class="notification-time">
                        '.time_elapsed_string($row['created_at']).'
                    </div>
                </div>

                <div class="notification-action">
                    <a href="clear_notification.php?id='.$row['id'].'">
                        <i class="la la-times"></i>
                    </a>
                </div>

            </div>
        </li>

        ';
    }
}

}