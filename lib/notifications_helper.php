<?php

function send_notification($receiver_id, $sender_id, $message, $event_key=null, $reference_id=null){
    global $db;

    // Duplicate check
    if($event_key && $reference_id){
        $check = $db->query("
            SELECT id FROM notifications 
            WHERE event_key='$event_key' 
            AND reference_id='$reference_id'
            LIMIT 1
        ");

        if($check->num_rows > 0){
            return false;
        }
    }

    $stmt = $db->prepare("
        INSERT INTO notifications
        (sender_id, receiver_id, message, is_read, event_key, reference_id, created_at)
        VALUES (?, ?, ?, 0, ?, ?, NOW())
    ");

    $stmt->bind_param("iissi", $sender_id, $receiver_id, $message, $event_key, $reference_id);
    $stmt->execute();

    return true;
}