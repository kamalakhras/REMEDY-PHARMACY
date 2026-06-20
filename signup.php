<?php
session_start();
include 'db_config.php'; 

if (isset($_POST['signup_btn'])) {
    // جلب البيانات وتنظيفها
    $fname   = trim($_POST['firstname']);
    $lname   = trim($_POST['lastname']);
    $email   = trim($_POST['email']);
    $pass    = $_POST['password'];
    $phone   = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // 1. التحقق من عدم تكرار البريد الإلكتروني باستخدام Prepared Statement
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // إذا وجد البريد الإلكتروني في القاعدة
        $error = "عذراً، هذا البريد الإلكتروني مسجل مسبقاً! يرجى استخدام بريد آخر.";
    } else {
        // 2. تشفير كلمة المرور (أمان عالي)
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
        $role = 'user';

        // 3. إدخال البيانات الجديدة
        $insert_stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sssssss", $fname, $lname, $email, $hashed_password, $phone, $address, $role);

        if ($insert_stmt->execute()) {
            echo "<script>alert('تم إنشاء الحساب بنجاح!'); window.location='login.php';</script>";
            exit();
        } else {
            $error = "حدث خطأ أثناء التسجيل. يرجى المحاولة لاحقاً.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد | Remedy Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: 'Cairo', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        .signup-card { width: 100%; max-width: 500px; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); background: #fff; }
        .form-control { border-radius: 10px; padding: 12px; border: 1px solid #dee2e6; }
        .form-control:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); border-color: #0d6efd; }
        .btn-register { border-radius: 10px; padding: 12px; font-weight: bold; transition: 0.3s; }
    </style>
</head>
<body>

<div class="signup-card shadow">
    <h3 class="text-center text-primary fw-bold mb-4">إنشاء حساب جديد</h3>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger text-center p-2 small"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label small">الاسم الأول</label>
                <input type="text" name="firstname" class="form-control" placeholder="أدخل الاسم" value="<?php echo isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : ''; ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small">الكنية</label>
                <input type="text" name="lastname" class="form-control" placeholder="أدخل الكنية" value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ''; ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small">البريد الإلكتروني</label>
            <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label small">رقم الهاتف</label>
            <input type="text" name="phone" class="form-control" placeholder="09xxxxxxxx" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label small">العنوان الكامل</label>
            <textarea name="address" class="form-control" rows="2" placeholder="المدينة، الشارع، المعالم القريبة" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
        </div>

        <div class="mb-4">
            <label class="form-label small">كلمة المرور</label>
            <input type="password" name="password" class="form-control" placeholder="********" required>
        </div>

        <button type="submit" name="signup_btn" class="btn btn-primary w-100 btn-register shadow-sm">تسجيل الحساب</button>
        
        <p class="text-center mt-3 small text-muted">
            لديك حساب بالفعل؟ <a href="login.php" class="text-decoration-none fw-bold">سجل دخولك هنا</a>
        </p>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>