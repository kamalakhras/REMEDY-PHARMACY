<?php
// get_notifications.php - جلب إشعارات المستخدم الحالي
session_start();
require_once './db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['count' => 0, 'notifications' => []]);
    exit();
}

$user_id = intval($_SESSION['id']);

// عدد الإشعارات غير المقروءة
$cnt_res    = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM notifications WHERE user_id='$user_id' AND is_read=0");
$unread_cnt = mysqli_fetch_assoc($cnt_res)['cnt'];

// آخر 12 إشعار
$res   = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id='$user_id' ORDER BY created_at DESC LIMIT 12");
$notifs = [];
while ($row = mysqli_fetch_assoc($res)) {
    $notifs[] = $row;
}

echo json_encode(['count' => intval($unread_cnt), 'notifications' => $notifs]);
