<?php
session_start();
require_once './db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { exit("Access Denied"); }

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // جلب مسار الصورة لحذفها من السيرفر
    $res = mysqli_query($conn, "SELECT rx_path FROM midecal_consultation WHERE id = '$id'");
    $row = mysqli_fetch_assoc($res);
    
    if (mysqli_query($conn, "DELETE FROM midecal_consultation WHERE id = '$id'")) {
        if (!empty($row['rx_path']) && file_exists("./Images/Consultations/" . $row['rx_path'])) {
            unlink("./Images/Consultations/" . $row['rx_path']);
        }
        header("Location: admin_messages.php?msg=consultation_deleted");
    } else {
        echo "خطأ في الحذف: " . mysqli_error($conn);
    }
}
?>
