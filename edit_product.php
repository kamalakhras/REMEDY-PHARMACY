<?php
session_start();
require_once './db_config.php';
include './admin_navbar.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id = '$id'");
    $product = mysqli_fetch_assoc($res);
}

if (isset($_POST['update'])) {
    $name  = $_POST['p_name'];
    $price = $_POST['p_price'];
    $cat   = $_POST['p_category'];
    $stock = max(0, intval($_POST['p_stock']));
    
    // إذا رفع صورة جديدة، نحدثها، وإلا نبقي القديمة
    if (!empty($_FILES['p_image']['name'])) {
        $img = $_FILES['p_image']['name'];
        move_uploaded_file($_FILES['p_image']['tmp_name'], "./Images/" . $img);
        $update_img = ", image='$img'";
    } else { $update_img = ""; }

    $sql = "UPDATE products SET name='$name', price='$price', category='$cat', stock='$stock' $update_img WHERE id='$id'";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('تم التحديث!'); window.location='admin_stock.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="card mx-auto shadow p-4" style="max-width: 500px;">
        <h3>تعديل: <?= $product['name'] ?></h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="text"   name="p_name"     class="form-control mb-2" value="<?= $product['name'] ?>" required>
            <input type="number" name="p_price"    class="form-control mb-2" value="<?= $product['price'] ?>" step="0.01" required>
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="small fw-bold">الكمية في المخزون</label>
                    <input type="number" name="p_stock" class="form-control" value="<?= intval($product['stock'] ?? 0) ?>" min="0" required>
                </div>
                <div class="col-6">
                    <label class="small fw-bold">القسم</label>
                    <select name="p_category" class="form-select">
                        <option value="Skin"     <?= ($product['category'] == 'Skin')     ? 'selected' : '' ?>>Skin (بشرة)</option>
                        <option value="Hair"     <?= ($product['category'] == 'Hair')     ? 'selected' : '' ?>>Hair (شعر)</option>
                        <option value="Makeup"   <?= ($product['category'] == 'Makeup')   ? 'selected' : '' ?>>Makeup (مكياج)</option>
                        <option value="Personal" <?= ($product['category'] == 'Personal') ? 'selected' : '' ?>>Personal (عناية شخصية)</option>
                        <option value="Kids"     <?= ($product['category'] == 'Kids')     ? 'selected' : '' ?>>Kids (أطفال)</option>
                        <option value="Vitamins" <?= ($product['category'] == 'Vitamins') ? 'selected' : '' ?>>Vitamins (فيتامينات)</option>
                    </select>
                </div>
            </div>
            <img src="./Images/<?= $product['image'] ?>" width="80" class="mb-2">
            <input type="file" name="p_image" class="form-control mb-3">
            <button name="update" class="btn btn-primary w-100">حفظ التعديلات</button>
        </form>
    </div>
</body>
</html>