<?php
// إعدادات الاتصال بقاعدة البيانات الخاصة بك
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pharmacy_db"; // تأكد من اسم قاعدة البيانات عندك

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>