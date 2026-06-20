<?php
session_start();
require_once './db_config.php';

// 1. حماية الصفحة: التأكد من أن المستخدم هو "admin"
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. جلب الإحصائيات الحقيقية من قاعدة البيانات
// إجمالي المنتجات
$total_p_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
$count_products = mysqli_fetch_assoc($total_p_query)['total'];

// إجمالي الطلبات (استخدام اسم الجدول الجديد orders)
$total_o_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders");
$count_orders = mysqli_fetch_assoc($total_o_query)['total'];

// إجمالي الرسائل
$total_m_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM contact_messages");
$count_messages = mysqli_fetch_assoc($total_m_query)['total'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم | Remedy Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --main-blue: #007bff; --light-bg: #f4f7f6; }
        body { background-color: var(--light-bg); font-family: 'Cairo', sans-serif; }
        
        /* Sidebar Styles */
        .sidebar { width: 260px; height: 100vh; position: fixed; background: #fff; box-shadow: 2px 0 15px rgba(0,0,0,0.05); right: 0; top: 0; z-index: 1000; }
        .main-content { margin-right: 260px; padding: 40px; }
        
        /* Cards Styles */
        .stat-card { border: none; border-radius: 20px; transition: 0.3s; background: #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.02); }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }
        .icon-circle { width: 55px; height: 55px; display: flex; align-items: center; justify-content: center; border-radius: 15px; }
        
        /* Table Styles */
        .table-container { background: #fff; border-radius: 25px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
        .product-img { width: 45px; height: 45px; object-fit: contain; border-radius: 8px; background: #f9f9f9; border: 1px solid #eee; }
        
        .btn-action { width: 35px; height: 35px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; transition: 0.2s; }
        
        @media (max-width: 992px) {
            .sidebar { display: none; }
            .main-content { margin-right: 0; }
        }
    </style>
</head>
<body>

<?php include './admin_navbar.php'; ?>

<div class="sidebar d-flex flex-column p-4 shadow-sm">
    <div class="text-center mb-4">
        <h4 class="text-primary fw-bold">REMEDY</h4>
        <span class="badge bg-light text-primary border">لوحة الإدارة</span>
    </div>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto p-0">
        <li class="nav-item mb-3">
            <a href="admin_dashboard.php" class="nav-link active rounded-4 py-2"><i class="fas fa-home ms-2"></i> الإحصائيات</a>
        </li>
        <li class="nav-item mb-3">
            <a href="admin_orders.php" class="nav-link text-dark py-2"><i class="fas fa-shopping-cart ms-2"></i> الطلبات</a>
        </li>
        <li class="nav-item mb-3">
            <a href="admin_messages.php" class="nav-link text-dark py-2"><i class="fas fa-envelope ms-2"></i> الرسائل</a>
        </li>
        <li class="nav-item mb-3">
            <a href="admin_users.php" class="nav-link text-dark py-2"><i class="fas fa-users ms-2"></i> المستخدمين</a>
        </li>
    </ul>
    <hr>
    <a href="logout.php" class="btn btn-outline-danger rounded-4 w-100"><i class="fas fa-sign-out-alt ms-2"></i> خروج</a>
</div>

<div class="main-content">
    <header class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1">لوحة التحكم</h2>
            <p class="text-muted">أهلاً بك مجدداً في نظام Remedy Pharmacy</p>
        </div>
        <a href="admin_add_product.php" class="btn btn-primary rounded-4 px-4 py-2 shadow-sm">
            <i class="fas fa-plus me-2"></i> إضافة منتج جديد
        </a>
    </header>

    <div class="row g-4 mb-5 text-end">
        <div class="col-md-4">
            <div class="card stat-card p-4">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-primary bg-opacity-10 me-3">
                        <i class="fas fa-pills text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">المنتجات</h6>
                        <h3 class="fw-bold mb-0"><?php echo $count_products; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-4">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-success bg-opacity-10 me-3">
                        <i class="fas fa-truck text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">إجمالي الطلبات</h6>
                        <h3 class="fw-bold mb-0"><?php echo $count_orders; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-4">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-warning bg-opacity-10 me-3">
                        <i class="fas fa-comment-dots text-warning fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">رسائل العملاء</h6>
                        <h3 class="fw-bold mb-0"><?php echo $count_messages; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">المخزون الحالي</h5>
            <span class="text-muted small">يعرض آخر 10 منتجات</span>
        </div>
        <div class="table-responsive text-end">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>الصورة</th>
                        <th>اسم المنتج</th>
                        <th>القسم</th>
                        <th>السعر</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
    <?php
    
    $res = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC LIMIT 10");
    
    if(mysqli_num_rows($res) > 0):
        while($row = mysqli_fetch_assoc($res)):
    ?>
    <tr>
        <td><img src="Images/<?php echo $row['image']; ?>" class="product-img"></td>
        <td><span class="fw-bold"><?php echo $row['name']; ?></span></td>
        <td><span class="badge bg-info bg-opacity-10 text-info"><?php echo $row['category']; ?></span></td>
        <td class="fw-bold"><?php echo $row['price']; ?>$</td>
        <td><span class="badge bg-success rounded-pill">متوفر</span></td>
        <td>
            <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light rounded-circle me-1">
                <i class="fas fa-edit text-primary"></i>
            </a>
            <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light rounded-circle" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
                <i class="fas fa-trash text-danger"></i>
            </a>
        </td>
    </tr>
    <?php 
        endwhile; 
    else:
    ?>
    <tr><td colspan="6" class="text-center py-4 text-muted">لا توجد منتجات حالياً.</td></tr>
    <?php endif; ?>
</tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>