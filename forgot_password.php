<?php
session_start();
date_default_timezone_set('Asia/Beirut');
require_once 'db_config.php';
require_once 'notify.php';

// إنشاء جدول password_resets تلقائياً إذا لم يكن موجوداً
$conn->query("CREATE TABLE IF NOT EXISTS `password_resets` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `email`      VARCHAR(255) NOT NULL,
    `token`      VARCHAR(6)   NOT NULL,
    `expires_at` DATETIME     NOT NULL,
    `used`       TINYINT(1)   DEFAULT 0,
    `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_code_btn'])) {
    $email = trim($_POST['email']);

    // التحقق من وجود البريد في قاعدة البيانات
    $stmt = $conn->prepare("SELECT id, firstname FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error = "هذا البريد الإلكتروني غير مسجل في النظام.";
    } else {
        $user = $result->fetch_assoc();

        // حذف الرموز القديمة لهذا البريد
        $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $del->bind_param("s", $email);
        $del->execute();

        // توليد رمز 6 أرقام
        $token      = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // حفظ الرمز في قاعدة البيانات
        $ins = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $ins->bind_param("sss", $email, $token, $expires_at);
        $ins->execute();

        // إرسال البريد الإلكتروني
        $subject = 'رمز إعادة تعيين كلمة المرور - Remedy Pharmacy';
        $body    = "
        <div dir='rtl' style='font-family:Cairo,sans-serif;max-width:500px;margin:auto;background:#f4f7f6;padding:30px;border-radius:16px;'>
            <div style='text-align:center;margin-bottom:20px;'>
                <h2 style='color:#0d6efd;'>💊 Remedy Pharmacy</h2>
            </div>
            <p style='font-size:16px;'>مرحباً <strong>{$user['firstname']}</strong>،</p>
            <p>لقد طلبت إعادة تعيين كلمة المرور. استخدم الرمز التالي:</p>
            <div style='background:#fff;border:2px dashed #0d6efd;border-radius:12px;padding:20px;text-align:center;margin:20px 0;'>
                <span style='font-size:36px;font-weight:bold;letter-spacing:10px;color:#0d6efd;'>{$token}</span>
            </div>
            <p style='color:#888;font-size:13px;'>⏱ الرمز صالح لمدة <strong>15 دقيقة</strong> فقط.</p>
            <p style='color:#888;font-size:13px;'>إذا لم تطلب هذا، تجاهل هذا البريد.</p>
            <hr style='border-color:#eee;margin:20px 0;'>
            <p style='color:#aaa;font-size:12px;text-align:center;'>Remedy Pharmacy &copy; 2026</p>
        </div>";

        $sent = sendEmailNotification($email, $user['firstname'], $subject, $body);

        if ($sent) {
            // حفظ البريد في الجلسة للانتقال إلى صفحة التحقق
            $_SESSION['reset_email'] = $email;
            header("Location: reset_password.php");
            exit();
        } else {
            $error = "فشل إرسال البريد الإلكتروني. يرجى المحاولة لاحقاً.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نسيت كلمة المرور | Remedy Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #e8f0fe 0%, #f4f7f6 100%);
            font-family: 'Cairo', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .card-box {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 12px 40px rgba(13,110,253,0.12);
            padding: 40px 35px;
            animation: fadeUp 0.4s ease;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0);    }
        }
        .icon-circle {
            width: 70px; height: 70px;
            background: linear-gradient(135deg,#0d6efd,#6ea8fe);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px;
            font-size: 30px;
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 1.5px solid #dee2e6;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
        }
        .btn-primary {
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 0.3px;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13,110,253,0.3);
        }
        .back-link { font-size: 14px; color: #6c757d; }
        .back-link a { color: #0d6efd; text-decoration: none; font-weight: 600; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="card-box">
    <div class="icon-circle">🔑</div>
    <h4 class="text-center fw-bold mb-1" style="color:#1a1a2e;">نسيت كلمة المرور؟</h4>
    <p class="text-center text-muted small mb-4">أدخل بريدك الإلكتروني وسنرسل لك رمز التحقق</p>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center py-2 small"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success text-center py-2 small"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-4">
            <label class="form-label fw-600">البريد الإلكتروني</label>
            <input type="email" name="email" id="email" class="form-control"
                   placeholder="name@example.com"
                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                   required>
        </div>
        <button type="submit" name="send_code_btn" id="send_code_btn" class="btn btn-primary w-100">
            إرسال رمز التحقق
        </button>
    </form>

    <p class="text-center back-link mt-4">
        تذكرت كلمة المرور؟ <a href="login.php">تسجيل الدخول</a>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
