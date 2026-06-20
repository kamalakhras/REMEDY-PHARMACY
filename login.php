<?php
session_start();
require_once './db_config.php';

if (isset($_POST['login_btn'])) {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // البحث عن المستخدم
    $query  = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // التحقق من كلمة المرور (سواء كانت مشفرة أو نص عادي للمساعدة في الانتقال)
        $is_correct = false;
        if (password_verify($password, $user['password'])) {
            $is_correct = true;
        } elseif ($password === $user['password']) {
            // دعم الحسابات القديمة التي لم تكن مشفرة (اختياري للأمان)
            $is_correct = true;
            // تحديث كلمة المرور لتصبح مشفرة تلقائياً
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password='$new_hash' WHERE id=".$user['id']);
        }

        if ($is_correct) {
            $_SESSION['id']        = $user['id'];
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "كلمة المرور غير صحيحة!";
        }
    } else {
        $error = "البريد الإلكتروني غير مسجل أو البيانات خاطئة!";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول | Remedy Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Cairo', sans-serif; }
        .login-card { width: 100%; max-width: 400px; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); background: #fff; }
    </style>
</head>
<body>

<div class="login-card">
    <h3 class="text-center text-primary fw-bold mb-4">تسجيل الدخول</h3>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger p-2 small text-center"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">البريد الإلكتروني</label>
            <input type="email" name="email" class="form-control" placeholder="example@mail.com" required>
        </div>
        <div class="mb-4">
            <label class="form-label">كلمة المرور</label>
            <input type="password" name="password" class="form-control" placeholder="********" required>
            <div class="text-start mt-1">
                <a href="forgot_password.php" class="small text-decoration-none" style="color:#0d6efd;">نسيت كلمة المرور؟</a>
            </div>
        </div>
        <button type="submit" name="login_btn" class="btn btn-primary w-100 rounded-pill py-2">دخول</button>
    </form>
    
    <div class="text-center mt-3 small">
        ليس لديك حساب؟ <a href="signup.php">إنشاء حساب جديد</a>
    </div>
</div>

</body>
</html>