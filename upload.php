<?php
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['rx_image'])) {
    $target_dir = "uploads/";
    $file_name = time() . "_" . basename($_FILES["rx_image"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["rx_image"]["tmp_name"], $target_file)) {
        // تسجيل الروشتة في قاعدة البيانات
        $sql = "INSERT INTO prescriptions (image_path, status) VALUES ('$target_file', 'pending')";
        mysqli_query($conn, $sql);
        echo "<script>alert('تم رفع الروشتة بنجاح!'); window.location.href='index.php';</script>";
    } else {
        echo "حدث خطأ أثناء الرفع.";
    }
}
?>