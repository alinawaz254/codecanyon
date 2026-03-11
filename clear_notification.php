<?php

require_once "lib/system_load.php";

$id = (int)$_GET['id'];

$db->query("
UPDATE notifications
SET is_read = 1
WHERE id = $id
");

header("Location: ".$_SERVER['HTTP_REFERER']);
exit;