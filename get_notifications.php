<?php

require_once "lib/system_load.php";

$query = "SELECT COUNT(*) as total
FROM notifications
WHERE receiver_id='".$_SESSION['user_id']."'
AND is_read=0";

$result = $db->query($query);
$row = $result->fetch_assoc();

echo $row['total'];