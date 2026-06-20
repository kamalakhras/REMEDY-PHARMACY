<?php
session_start();
include 'db_config.php';

// ── إضافة منتج للسلة ──
if (isset($_GET['add'])) {
    $id = intval($_GET['add']);
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]++;
    } else {
        $_SESSION['cart'][$id] = 1;
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo array_sum($_SESSION['cart']);
        exit;
    }
    header("Location: cart.php");
    exit();
}

// ── حذف منتج من السلة ──
if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    unset($_SESSION['cart'][$id]);

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['ok' => true]);
        exit;
    }
    header("Location: cart.php");
    exit();
}

// ── تحديث الكمية (AJAX) ──
if (isset($_POST['update_qty'])) {
    header('Content-Type: application/json');
    $id  = intval($_POST['product_id']);
    $qty = intval($_POST['qty']);

    if ($qty <= 0) {
        unset($_SESSION['cart'][$id]);
        echo json_encode(['ok' => true, 'qty' => 0, 'removed' => true, 'total_items' => array_sum($_SESSION['cart'] ?? [])]);
        exit;
    }

    // فحص المخزون المتوفر
    $res   = mysqli_query($conn, "SELECT stock, name FROM products WHERE id = $id");
    $prod  = mysqli_fetch_assoc($res);
    $stock = intval($prod['stock'] ?? 0);

    if ($qty > $stock) {
        // لا نعدّل الكمية تلقائياً — نخزّنها كما طلبها المستخدم ونُعلمه بالمشكلة
        $_SESSION['cart'][$id] = $qty;
        echo json_encode([
            'ok'         => true,
            'qty'        => $qty,
            'over_stock' => true,
            'max'        => $stock,
            'msg'        => "الكمية المطلوبة ($qty) غير متوفرة. المتاح: $stock قطعة فقط.",
            'total_items'=> array_sum($_SESSION['cart'])
        ]);
        exit;
    }

    $_SESSION['cart'][$id] = $qty;
    echo json_encode([
        'ok'          => true,
        'qty'         => $qty,
        'capped'      => false,
        'total_items' => array_sum($_SESSION['cart'])
    ]);
    exit;
}
?>