<?php

require_once "lib/system_load.php";

$db->query("
UPDATE notifications
SET is_read = 1
WHERE receiver_id=".$_SESSION['user_id']."
");

header("Location: ".$_SERVER['HTTP_REFERER']);
exit;