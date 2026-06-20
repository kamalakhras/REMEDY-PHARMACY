<?php
session_start();
require_once './db_config.php';

// التأكد من صلاحية الأدمن
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { exit("Access Denied"); }

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // جلب اسم الصورة لحذفها من المجلد للحفاظ على المساحة
    $res = mysqli_query($conn, "SELECT image FROM products WHERE id = '$id'");
    $product = mysqli_fetch_assoc($res);
    
    // تنفيذ الحذف من الجدول
    if (mysqli_query($conn, "DELETE FROM products WHERE id = '$id'")) {
        if ($product['image'] && file_exists("./Images/" . $product['image'])) {
            unlink("./Images/" . $product['image']);
        }
        header("Location: admin_dashboard.php?msg=deleted");
    }
}
?>