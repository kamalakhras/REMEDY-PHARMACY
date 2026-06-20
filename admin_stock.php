<?php
session_start();
require_once './db_config.php';

// Admin protection
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ── Auto-migration: add stock column if it doesn't exist ──
$col_check = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'stock'");
if (mysqli_num_rows($col_check) === 0) {
    mysqli_query($conn, "ALTER TABLE products ADD COLUMN stock INT NOT NULL DEFAULT 0");
    // Give existing products a default starting stock of 50
    mysqli_query($conn, "UPDATE products SET stock = 50 WHERE stock = 0");
}

$success = '';
$error   = '';

// Handle stock update (inline edit)
if (isset($_POST['update_stock'])) {
    $pid   = intval($_POST['product_id']);
    $stock = intval($_POST['new_stock']);
    if ($stock < 0) $stock = 0;
    $sql = "UPDATE products SET stock = $stock WHERE id = $pid";
    if (mysqli_query($conn, $sql)) {
        $success = "تم تحديث المخزون بنجاح!";
    } else {
        $error = "خطأ: " . mysqli_error($conn);
    }
}

// Handle bulk restock (low-stock items)
if (isset($_POST['restock_all'])) {
    $amt = intval($_POST['restock_amount']);
    if ($amt > 0) {
        mysqli_query($conn, "UPDATE products SET stock = stock + $amt WHERE stock < 10");
        $success = "تمت إعادة تعبئة المنتجات منخفضة المخزون بـ $amt قطعة.";
    }
}

// Fetch products with optional filter
$filter = '';
if (isset($_GET['filter'])) {
    if ($_GET['filter'] === 'low') $filter = 'WHERE stock > 0 AND stock <= 10';
    if ($_GET['filter'] === 'out') $filter = 'WHERE stock = 0';
}
$result     = mysqli_query($conn, "SELECT * FROM products $filter ORDER BY stock ASC");
$total_rows = mysqli_num_rows($result);

// Stats
$out_of_stock  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM products WHERE stock = 0"))['c'];
$low_stock     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM products WHERE stock > 0 AND stock <= 10"))['c'];
$healthy_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM products WHERE stock > 10"))['c'];
$total_items   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stock) AS c FROM products"))['c'] ?? 0;

include './admin_navbar.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ادارة المخزون | Remedy Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #f0f4ff; }

        .stock-hero {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            padding: 40px 30px 80px;
            color: #fff;
            border-bottom-left-radius: 40px;
            border-bottom-right-radius: 40px;
            margin-bottom: -60px;
        }

        .stat-card {
            background: #fff;
            border-radius: 20px;
            padding: 22px 26px;
            box-shadow: 0 8px 30px rgba(0,0,0,.07);
            border: none;
            transition: transform .2s;
        }
        .stat-card:hover { transform: translateY(-4px); }
        .stat-icon {
            width: 52px; height: 52px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; flex-shrink: 0;
        }

        .table-card { background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 8px 30px rgba(0,0,0,.07); }
        .table thead th { background: #f8f9fc; color: #666; border-bottom: 2px solid #eee; font-weight: 700; font-size: .88rem; }
        .table tbody tr:hover { background: #f8faff; }

        .badge-out { background: #fdecea; color: #c0392b; font-weight: 700; }
        .badge-low { background: #fef9e7; color: #d68910; font-weight: 700; }
        .badge-ok  { background: #e9f7ef; color: #1e8449; font-weight: 700; }
        .stock-badge { padding: 5px 14px; border-radius: 50px; font-size: .82rem; display:inline-block; }

        .stock-bar      { height: 8px; border-radius: 4px; background: #eee; overflow: hidden; }
        .stock-bar-fill { height: 100%; border-radius: 4px; transition: width .4s; }

        .qty-input {
            width: 80px; border: 2px solid #dee2e6; border-radius: 10px;
            padding: 5px 10px; text-align: center; font-weight: 700; font-family: 'Cairo', sans-serif;
        }
        .qty-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,.15); }

        .filter-btn { border-radius: 50px; padding: 6px 18px; font-size: .85rem; font-weight: 600; }
        .search-input { border-radius: 12px; border: 2px solid #eee; padding: 10px 16px; font-family: 'Cairo', sans-serif; }
        .search-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,.1); }
        .btn-save-stock { border-radius: 8px; padding: 5px 14px; font-size: .82rem; font-weight: 700; }
        .alert { border-radius: 14px; }
    </style>
</head>
<body>

<!-- HERO -->
<div class="stock-hero text-center">
    <h2 class="fw-bold mb-1"><i class="fas fa-boxes-stacked me-2"></i>ادارة المخزون</h2>
    <p class="opacity-75 mb-0">تحكم كامل في مخزون منتجات صيدلية Remedy</p>
</div>

<div class="container pb-5">

    <!-- STATS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#eef2ff;"><i class="fas fa-boxes text-primary"></i></div>
                <div>
                    <div class="text-muted small">اجمالي القطع</div>
                    <div class="fw-bold fs-4"><?= number_format($total_items) ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#e9f7ef;"><i class="fas fa-check-circle text-success"></i></div>
                <div>
                    <div class="text-muted small">مخزون كافٍ</div>
                    <div class="fw-bold fs-4 text-success"><?= $healthy_stock ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fef9e7;"><i class="fas fa-exclamation-triangle text-warning"></i></div>
                <div>
                    <div class="text-muted small">مخزون منخفض</div>
                    <div class="fw-bold fs-4 text-warning"><?= $low_stock ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:#fdecea;"><i class="fas fa-times-circle text-danger"></i></div>
                <div>
                    <div class="text-muted small">نفد المخزون</div>
                    <div class="fw-bold fs-4 text-danger"><?= $out_of_stock ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ALERTS -->
    <?php if ($success): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3 shadow-sm">
            <i class="fas fa-check-circle fa-lg"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3 shadow-sm">
            <i class="fas fa-exclamation-circle fa-lg"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    <?php if ($out_of_stock > 0): ?>
        <div class="alert alert-danger d-flex align-items-center gap-3 mb-3 shadow-sm">
            <i class="fas fa-bell fa-lg"></i>
            <div>
                <strong>تنبيه!</strong> <?= $out_of_stock ?> منتج/منتجات نفد مخزونها تماماً.
                <a href="?filter=out" class="alert-link ms-2">عرضها الآن</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- TABLE CARD -->
    <div class="table-card">

        <!-- Top Bar -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h5 class="fw-bold mb-0"><i class="fas fa-clipboard-list me-2 text-primary"></i>جدول المخزون</h5>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <input type="text" id="searchInput" class="search-input form-control" placeholder="بحث عن منتج..." onkeyup="filterTable()" style="width:200px;">
                <a href="admin_stock.php" class="btn btn-outline-secondary filter-btn <?= !isset($_GET['filter']) ? 'active' : '' ?>">الكل</a>
                <a href="?filter=low" class="btn btn-outline-warning filter-btn <?= (isset($_GET['filter']) && $_GET['filter']==='low') ? 'active' : '' ?>">
                    <i class="fas fa-exclamation-triangle me-1"></i>منخفض
                </a>
                <a href="?filter=out" class="btn btn-outline-danger filter-btn <?= (isset($_GET['filter']) && $_GET['filter']==='out') ? 'active' : '' ?>">
                    <i class="fas fa-times-circle me-1"></i>نفد
                </a>
            </div>
        </div>

        <!-- Bulk Restock Panel -->
        <form method="POST" class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3 flex-wrap" style="background:#f8f9fc;">
            <i class="fas fa-truck text-primary fs-5"></i>
            <span class="fw-bold text-muted small">اعادة تعبئة المنتجات المنخفضة (10 قطع فأقل):</span>
            <input type="number" name="restock_amount" class="qty-input" value="20" min="1" style="width:90px;">
            <button type="submit" name="restock_all" class="btn btn-primary btn-save-stock">
                <i class="fas fa-plus me-1"></i>اعادة تعبئة
            </button>
        </form>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="stockTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المنتج</th>
                        <th>القسم</th>
                        <th>السعر</th>
                        <th>الحالة</th>
                        <th>المخزون الحالي</th>
                        <th>شريط المخزون</th>
                        <th class="text-center">تعديل الكمية</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_rows > 0): ?>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($result)):
                            $stock = intval($row['stock']);
                            if ($stock === 0)     { $badge_class='badge-out'; $badge_text='نفد المخزون';  $bar_color='#e74c3c'; }
                            elseif($stock <= 10)  { $badge_class='badge-low'; $badge_text='مخزون منخفض'; $bar_color='#f39c12'; }
                            else                  { $badge_class='badge-ok';  $badge_text='متوفر';        $bar_color='#2ecc71'; }
                            $bar_pct = ($stock >= 100) ? 100 : $stock;
                        ?>
                        <tr>
                            <td class="text-muted small"><?= $i++ ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="./Images/<?= htmlspecialchars($row['image']) ?>" width="42" height="42"
                                         style="object-fit:cover;border-radius:10px;border:2px solid #eee;"
                                         onerror="this.src='https://placehold.co/42x42?text=?'">
                                    <span class="fw-bold"><?= htmlspecialchars($row['name']) ?></span>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['category']) ?></span></td>
                            <td class="fw-bold text-primary"><?= number_format($row['price'], 2) ?>$</td>
                            <td><span class="stock-badge <?= $badge_class ?>"><?= $badge_text ?></span></td>
                            <td class="fw-bold fs-5"><?= $stock ?> <small class="text-muted fw-normal" style="font-size:.7rem;">قطعة</small></td>
                            <td style="min-width:130px;">
                                <div class="stock-bar">
                                    <div class="stock-bar-fill" style="width:<?= $bar_pct ?>%;background:<?= $bar_color ?>;"></div>
                                </div>
                                <small class="text-muted" style="font-size:.7rem;"><?= $stock ?>/100</small>
                            </td>
                            <td class="text-center">
                                <form method="POST" class="d-flex align-items-center justify-content-center gap-2">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="number" name="new_stock" class="qty-input" value="<?= $stock ?>" min="0" max="9999">
                                    <button type="submit" name="update_stock" class="btn btn-primary btn-save-stock" title="حفظ">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">لا توجد منتجات لعرضها.</p>
                                <a href="admin_add_product.php" class="btn btn-primary">اضافة منتج جديد</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div><!-- /table-card -->
</div><!-- /container -->

<script>
function filterTable() {
    const val = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#stockTable tbody tr').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
