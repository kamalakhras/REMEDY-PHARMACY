<?php
session_start();
require_once './db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { exit("Access Denied"); }

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    if (mysqli_query($conn, "DELETE FROM orders WHERE order_id = '$id'")) {
        header("Location: admin_orders.php?msg=order_deleted");
    } else {
        echo "خطأ في الحذف: " . mysqli_error($conn);
    }
}
?>
