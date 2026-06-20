<?php
session_start();
require_once './db_config.php';

if (!isset($_GET['group_id'])) { die("خطأ في رقم الطلب"); }

$group_id = mysqli_real_escape_string($conn, $_GET['group_id']);

// جلب بيانات المنتجات + بيانات المستخدم
$query = "SELECT o.*, u.firstname, u.lastname, p.name as product_name, p.price as unit_price 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          JOIN products p ON o.product_id = p.id 
          WHERE o.order_group_id = '$group_id'";

$result = mysqli_query($conn, $query);
$items = [];
while($row = mysqli_fetch_assoc($result)) { $items[] = $row; }

if (empty($items)) { die("الطلب غير موجود"); }
$user = $items[0]; // بيانات المستخدم هي نفسها لكل المنتجات في هذا الطلب
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة رقم <?php echo $group_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; padding: 40px; }
        .invoice-header { border-bottom: 2px solid #0d6efd; margin-bottom: 20px; padding-bottom: 10px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="container border p-5 bg-white">
        <div class="invoice-header d-flex justify-content-between align-items-center">
            <h1 class="text-primary fw-bold">Remedy Pharmacy</h1>
            <div class="text-start">
                <h5>رقم الفاتورة: <strong><?php echo $group_id; ?></strong></h5>
                <p class="mb-0">التاريخ: <?php echo date('Y-m-d', strtotime($user['order_date'])); ?></p>
            </div>
        </div>

        <div class="row mb-4 mt-4">
            <div class="col-6">
                <h5 class="fw-bold">معلومات العميل:</h5>
                <p class="mb-1">الاسم: <?php echo $user['firstname'] . " " . $user['lastname']; ?></p>
                <p class="mb-1">الهاتف: <?php echo $user['phone']; ?></p>
                <p class="mb-1">العنوان: <?php echo $user['address']; ?></p>
            </div>
        </div>

        <table class="table table-striped text-center border">
            <thead class="table-primary">
                <tr>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    <th>المجموع</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grand_total = 0;
                foreach ($items as $item): 
                    $grand_total += $item['Order_total'];
                ?>
                <tr>
                    <td><?php echo $item['product_name']; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo $item['unit_price']; ?>$</td>
                    <td><?php echo $item['Order_total']; ?>$</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-light">
                    <th colspan="3" class="text-end">الإجمالي الكلي:</th>
                    <th class="text-danger fs-4"><?php echo $grand_total; ?>$</th>
                </tr>
            </tfoot>
        </table>

        <div class="text-center mt-5 no-print">
            <button onclick="window.print()" class="btn btn-success px-4">طباعة الفاتورة <i class="fas fa-print"></i></button>
            <a href="admin_messages.php" class="btn btn-secondary px-4">العودة للوحة التحكم</a>
        </div>
    </div>

</body>
</html>