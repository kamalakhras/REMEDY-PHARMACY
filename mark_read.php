<?php
// mark_read.php - تحديد الإشعارات كمقروءة
session_start();
require_once './db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['ok' => false]);
    exit();
}

$user_id = intval($_SESSION['id']);

if (isset($_POST['notif_id']) && intval($_POST['notif_id']) > 0) {
    // تحديد إشعار محدد
    $id = intval($_POST['notif_id']);
    mysqli_query($conn, "UPDATE notifications SET is_read=1 WHERE id='$id' AND user_id='$user_id'");
} else {
    // تحديد كل الإشعارات
    mysqli_query($conn, "UPDATE notifications SET is_read=1 WHERE user_id='$user_id'");
}

echo json_encode(['ok' => true]);
