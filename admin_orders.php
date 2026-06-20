<?php
// 1. تفعيل تقارير الأخطاء (للمساعدة في البرمجة - يمكنك تعطيلها عند رفع الموقع)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once './db_config.php';
require_once './notify.php';

// 2. حماية الصفحة
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 3. معالجة تحديث الحالة (مع الحماية من SQL Injection)
if (isset($_POST['mark_shipped']) && isset($_POST['order_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    
    $update_query = "UPDATE orders SET order_status = 'Shipped' WHERE order_id = '$order_id'";
    if (mysqli_query($conn, $update_query)) {
        // ─── إشعار المستخدم بالشحن ───
        $ord_res = mysqli_query($conn, "SELECT o.user_id, o.order_group_id, u.email, u.firstname
                                        FROM orders o
                                        LEFT JOIN users u ON o.user_id = u.id
                                        WHERE o.order_id = '$order_id' LIMIT 1");
        if ($ord_row = mysqli_fetch_assoc($ord_res)) {
            notifyOrderShipped($conn,
                $ord_row['user_id'],
                $ord_row['email'],
                $ord_row['firstname'],
                $ord_row['order_group_id']
            );
        }
        header("Location: admin_orders.php?success=1");
        exit();
    } else {
        $error_msg = "خطأ في التحديث: " . mysqli_error($conn);
    }
}

// 4. جلب البيانات (استخدام LEFT JOIN لضمان عدم اختفاء الطلبات إذا حُذف منتج أو مستخدم)
$query = "SELECT o.*, u.firstname, p.name as medicine_name 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          LEFT JOIN products p ON o.product_id = p.id 
          ORDER BY o.order_date DESC";

$result = mysqli_query($conn, $query);

include './admin_navbar.php'; 
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الطلبات | Remedy Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Cairo', sans-serif; }
        .main-container { padding: 30px 15px; }
        .table-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border: none; }
        .status-badge { padding: 5px 12px; border-radius: 50px; font-size: 0.85rem; font-weight: 600; }
        .table thead th { background-color: #f8f9fa; color: #555; border-bottom: 2px solid #eee; }
        .btn-shipped { border-radius: 8px; transition: 0.3s; }
        .btn-shipped:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(13, 110, 253, 0.2); }
    </style>
</head>
<body>

<div class="container main-container">
    <?php if(isset($error_msg)): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="table-card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary mb-3 mb-md-0">
                <i class="fas fa-boxes-packing me-2"></i> إدارة طلبات الزبائن
            </h3>
            <div class="stats">
                <span class="badge bg-primary p-2 px-3">إجمالي الطلبات: <?php echo mysqli_num_rows($result); ?></span>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>التاريخ</th>
                        <th>الزبون</th>
                        <th>المنتج</th>
                        <th>الكمية</th>
                        <th>الإجمالي</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="fw-bold">#<?php echo htmlspecialchars($row['order_id']); ?></td>
                            <td><small class="text-muted"><?php echo date('Y/m/d H:i', strtotime($row['order_date'])); ?></small></td>
                            <td><?php echo htmlspecialchars($row['firstname'] ?? 'مستخدم محذوف'); ?></td>
                            <td><span class="text-dark"><?php echo htmlspecialchars($row['medicine_name'] ?? 'منتج غير متوفر'); ?></span></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td>
    <a href="print_invoice.php?group_id=<?php echo $row['order_group_id']; ?>" target="_blank" class="btn btn-primary btn-sm">
    <i class="fas fa-print"></i> طباعة الفاتورة
</a>
</td>
                            <td class="text-success fw-bold"><?php echo number_format($row['Order_total'], 2); ?>$</td>
                            <td>
                                <?php if($row['order_status'] == 'Shipped'): ?>
                                    <span class="status-badge bg-success text-white"><i class="fas fa-check-circle me-1"></i> تم الشحن</span>
                                <?php else: ?>
                                    <span class="status-badge bg-warning text-dark"><i class="fas fa-spinner fa-spin me-1"></i> قيد الانتظار</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if($row['order_status'] !== 'Shipped'): ?>
                                    <form method="POST" onsubmit="return confirm('هل أنت متأكد من تأكيد الشحن؟');" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                        <button name="mark_shipped" class="btn btn-sm btn-primary btn-shipped shadow-sm">
                                            تأكيد الشحن
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <a href="delete_order.php?id=<?php echo $row['order_id']; ?>" 
                                   class="btn btn-sm btn-outline-danger rounded-pill px-3" 
                                   onclick="return confirm('حذف الطلب نهائياً؟');">
                                    <i class="fas fa-trash"></i> حذف
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x mb-3 text-light d-block"></i>
                                <p class="text-muted">لا توجد طلبات لعرضها حالياً.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>