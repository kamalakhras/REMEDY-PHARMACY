<?php
session_start();
require_once './db_config.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
    header("Location: login.php"); 
    exit(); 
}

$success = false;
$error = "";

if (isset($_POST['submit_product'])) {
    $p_name  = mysqli_real_escape_string($conn, $_POST['p_name']);
    $p_price = mysqli_real_escape_string($conn, $_POST['p_price']);
    $p_cat   = mysqli_real_escape_string($conn, $_POST['p_category']);
    $p_stock = max(0, intval($_POST['p_stock']));

    $image_raw_name = $_FILES['p_image']['name'];
    $temp_name      = $_FILES['p_image']['tmp_name'];
    $file_ext       = strtolower(pathinfo($image_raw_name, PATHINFO_EXTENSION));
    $image_new_name = time() . "_" . uniqid() . "." . $file_ext; 
    $folder         = "./Images/" . $image_new_name;

    $allowed = array("jpg", "jpeg", "png", "webp", "gif");
    if (in_array($file_ext, $allowed)) {
        // ✅ Check for duplicate product name (case-insensitive)
        $check_query = "SELECT id FROM products WHERE LOWER(name) = LOWER('$p_name') LIMIT 1";
        $check_result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($check_result) > 0) {
            $error = "⚠️ يوجد منتج بنفس الاسم بالفعل! يرجى اختيار اسم مختلف.";
        } else {
            if (move_uploaded_file($temp_name, $folder)) {
                $query = "INSERT INTO products (name, price, category, image, stock) VALUES ('$p_name', '$p_price', '$p_cat', '$image_new_name', '$p_stock')";
                if (mysqli_query($conn, $query)) {
                    $success = true;
                } else { $error = "خطأ في قاعدة البيانات: " . mysqli_error($conn); }
            } else { $error = "فشل رفع الصورة للمجلد."; }
        }
    } else { $error = "يسمح فقط بالصور (JPG, PNG, WEBP)."; }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة منتج | Remedy Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f0f4f8; color: #333; }
        .admin-header { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); padding: 60px 0; color: #fff; border-bottom-left-radius: 40px; border-bottom-right-radius: 40px; margin-bottom: -60px; }
        .add-card { border: none; border-radius: 25px; box-shadow: 0 15px 35px rgba(0,0,0,0.08); background: #fff; overflow: hidden; }
        .form-label { font-weight: 700; color: #444; font-size: 0.85rem; }
        .form-control, .form-select { border-radius: 12px; padding: 12px; border: 2px solid #eee; transition: 0.3s; }
        .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 15px rgba(13,110,253,0.1); }
        .btn-add { border-radius: 15px; padding: 15px; font-weight: 800; background: linear-gradient(135deg, #0d6efd, #0a58ca); border: none; transition: 0.3s; }
        .btn-add:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(13,110,253,0.3); }
        .preview-box { width: 100%; height: 200px; background: #f8f9fa; border: 2px dashed #ddd; border-radius: 20px; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; }
        .preview-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
    </style>
</head>
<body>

<div class="admin-header text-center">
    <h2 class="fw-extrabold mb-1">إضافة منتج جديد</h2>
    <p class="opacity-75">قم بإضافة الأدوية والمنتجات لمتجر Remedy Pharmacy</p>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card add-card p-4 p-lg-5">
                
                <?php if($success): ?>
                    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-3 mb-4" style="border-radius:15px;">
                        <i class="fas fa-check-circle fa-2x"></i>
                        <div><strong>تم بنجاح!</strong> المنتج الآن معروض في الموقع.</div>
                    </div>
                <?php endif; ?>

                <?php if($error): ?>
                    <div class="alert alert-danger border-0 shadow-sm" style="border-radius:15px;"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="form-label"><i class="fas fa-pills text-primary me-1"></i> اسم المنتج / الدواء</label>
                        <input type="text" name="p_name" class="form-control" placeholder="مثلاً: فيتامين سي فوار" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label class="form-label"><i class="fas fa-tag text-primary me-1"></i> السعر ($)</label>
                            <input type="number" step="0.01" name="p_price" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label"><i class="fas fa-boxes text-primary me-1"></i> الكمية في المخزون</label>
                            <input type="number" name="p_stock" class="form-control" placeholder="مثال: 50" min="0" value="0" required>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label"><i class="fas fa-layer-group text-primary me-1"></i> القسم</label>
                            <select name="p_category" class="form-select" required>
                                <option value="Skin">Skin (بشرة)</option>
                                <option value="Hair">Hair (شعر)</option>
                                <option value="Makeup">Makeup (مكياج)</option>
                                <option value="Personal">Personal (عناية شخصية)</option>
                                <option value="Kids">Kids (أطفال)</option>
                                <option value="Vitamins">Vitamins (فيتامينات)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="fas fa-image text-primary me-1"></i> صورة المنتج</label>
                        <div class="preview-box mb-2" id="previewContainer">
                            <div class="text-center text-muted" id="previewPlaceholder">
                                <i class="fas fa-cloud-upload-alt fa-3x mb-2 d-block"></i>
                                <span class="small">معاينة الصورة ستظهر هنا</span>
                            </div>
                            <img id="imagePreview" src="" style="display:none;">
                        </div>
                        <input type="file" name="p_image" class="form-control" id="p_image" accept="image/*" required onchange="previewImage(event)">
                    </div>

                    <button type="submit" name="submit_product" class="btn btn-primary w-100 btn-add text-white">
                        حفظ ونشر المنتج <i class="fas fa-paper-plane ms-2"></i>
                    </button>
                    
                    <div class="text-center mt-4">
                        <a href="admin_dashboard.php" class="text-decoration-none small text-muted">
                            <i class="fas fa-arrow-right me-1"></i> العودة للوحة التحكم
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('imagePreview');
            const placeholder = document.getElementById('previewPlaceholder');
            output.src = reader.result;
            output.style.display = "block";
            placeholder.style.display = "none";
        }
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>