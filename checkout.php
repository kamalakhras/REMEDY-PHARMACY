<?php
session_start();
include 'db_config.php';
require_once './notify.php';

if (!isset($_SESSION['id']) || empty($_SESSION['cart'])) {
    header("Location: login.php");
    exit();
}

$user_id        = intval($_SESSION['id']);
$order_group_id = "ORD-" . time();

// جلب بيانات المستخدم
$user_info  = mysqli_query($conn, "SELECT phone, address, email, firstname FROM users WHERE id='$user_id'");
$u          = mysqli_fetch_assoc($user_info);
$phone      = $u['phone'];
$address    = $u['address'];
$user_email = $u['email'];
$user_name  = $u['firstname'];

// ── فحص المخزون قبل تأكيد الطلب ──
$stock_errors = [];
foreach ($_SESSION['cart'] as $p_id => $qty) {
    $p_id = intval($p_id);
    $qty  = intval($qty);
    $res  = mysqli_query($conn, "SELECT name, stock FROM products WHERE id = '$p_id'");
    $prod = mysqli_fetch_assoc($res);

    if (!$prod) continue; // منتج محذوف

    if ($prod['stock'] < $qty) {
        $available = intval($prod['stock']);
        if ($available === 0) {
            $stock_errors[] = "المنتج \"" . htmlspecialchars($prod['name']) . "\" غير متوفر في المخزون حالياً.";
        } else {
            $stock_errors[] = "المنتج \"" . htmlspecialchars($prod['name']) . "\": طلبت $qty قطعة، المتوفر فقط $available قطعة.";
        }
    }
}

// إذا في مشكلة مخزون، أوقف الطلب وأعد المستخدم للسلة
if (!empty($stock_errors)) {
    $_SESSION['stock_errors'] = $stock_errors;
    header("Location: cart.php?stock_error=1");
    exit();
}

// ── إنشاء الطلبات وتخفيض المخزون ──
foreach ($_SESSION['cart'] as $p_id => $qty) {
    $p_id = intval($p_id);
    $qty  = intval($qty);

    $res   = mysqli_query($conn, "SELECT price FROM products WHERE id='$p_id'");
    $p     = mysqli_fetch_assoc($res);
    $total = $p['price'] * $qty;

    // إدراج الطلب
    $sql = "INSERT INTO orders (order_group_id, user_id, phone, address, product_id, quantity, Order_total, order_status)
            VALUES ('$order_group_id', '$user_id', '$phone', '$address', '$p_id', '$qty', '$total', 'Pending')";
    mysqli_query($conn, $sql);

    // تخفيض المخزون
    mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $p_id AND stock >= $qty");
}

unset($_SESSION['cart']);
unset($_SESSION['stock_errors']);

// إشعار تأكيد الطلب
notifyOrderPlaced($conn, $user_id, $user_email, $user_name, $order_group_id);

echo "<script>alert('تم تسجيل طلبك بنجاح! رقم الطلب: $order_group_id\\nسيصلك بريد تأكيد قريباً.'); window.location='index.php';</script>";
?>