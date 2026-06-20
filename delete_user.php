<?php
session_start();
require_once './db_config.php';

// حماية الصفحة
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // منع حذف الأدمن الحالي لنفسه
    if ($id == $_SESSION['id']) {
        echo "<script>alert('لا يمكنك حذف حسابك الخاص!'); window.location='admin_users.php';</script>";
        exit();
    }

    $sql = "DELETE FROM users WHERE id = '$id'";
    if (mysqli_query($conn, $sql)) {
        header("Location: admin_users.php?deleted=1");
    } else {
        echo "خطأ في الحذف: " . mysqli_error($conn);
    }
} else {
    header("Location: admin_users.php");
}
?>
