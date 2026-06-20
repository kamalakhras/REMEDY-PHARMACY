<?php
require_once './db_config.php';

$results = [];

// Add stock column if it doesn't exist
$check = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'stock'");
if (mysqli_num_rows($check) === 0) {
    if (mysqli_query($conn, "ALTER TABLE products ADD COLUMN stock INT NOT NULL DEFAULT 0")) {
        $results[] = "✅ تم إضافة عمود 'stock' بنجاح إلى جدول products.";
    } else {
        $results[] = "❌ خطأ: " . mysqli_error($conn);
    }
} else {
    $results[] = "ℹ️ عمود 'stock' موجود بالفعل — لا حاجة لتعديل.";
}

// Set existing products to stock=50 if they are all 0 (first-time setup)
$zero_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM products WHERE stock = 0"));
if (intval($zero_check['c']) > 0) {
    mysqli_query($conn, "UPDATE products SET stock = 50 WHERE stock = 0");
    $results[] = "✅ تم تعيين المخزون الافتراضي 50 قطعة لجميع المنتجات التي كانت كميتها 0.";
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إعداد المخزون</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
<div class="container">
    <div class="card shadow p-4 mx-auto" style="max-width:600px; border-radius:20px;">
        <h4 class="fw-bold mb-4 text-primary">🔧 إعداد جدول المخزون</h4>
        <?php foreach ($results as $msg): ?>
            <div class="alert alert-<?= str_starts_with($msg, '✅') ? 'success' : (str_starts_with($msg, '❌') ? 'danger' : 'info') ?>" style="border-radius:12px;">
                <?= $msg ?>
            </div>
        <?php endforeach; ?>
        <hr>
        <a href="admin_stock.php" class="btn btn-primary w-100 mt-2" style="border-radius:12px;">
            ✅ انتقل إلى صفحة المخزون
        </a>
        <p class="text-muted small mt-3 text-center">يمكنك حذف هذا الملف (setup_stock.php) بعد الانتهاء.</p>
    </div>
</div>
</body>
</html>
