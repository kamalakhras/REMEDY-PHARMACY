<?php
session_start();
date_default_timezone_set('Asia/Beirut');
require_once 'db_config.php';

$success = '';
$error   = '';

// إذا لم يكن البريد محفوظاً في الجلسة، أعد التوجيه
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];

// ==================== التحقق من الرمز وتغيير كلمة المرور ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_btn'])) {
    $code      = trim($_POST['code']);
    $new_pass  = $_POST['new_password'];
    $conf_pass = $_POST['confirm_password'];

    if (strlen($new_pass) < 6) {
        $error = "كلمة المرور يجب أن تكون 6 أحرف على الأقل.";
    } elseif ($new_pass !== $conf_pass) {
        $error = "كلمة المرور وتأكيدها غير متطابقتين.";
    } else {
        // التحقق من الرمز
        $stmt = $conn->prepare(
            "SELECT * FROM password_resets 
             WHERE email = ? AND token = ? AND used = 0
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->bind_param("ss", $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = "الرمز غير صحيح. يرجى التأكد من الرمز المدخل.";
        } else {
            $row = $result->fetch_assoc();

            // التحقق من انتهاء صلاحية الرمز في PHP لتفادي اختلاف توقيت قاعدة البيانات
            $expiry = strtotime($row['expires_at']);
            if (time() > $expiry) {
                $error = "انتهت صلاحية الرمز. يرجى طلب رمز جديد.";
            } else {
                // تحديث كلمة المرور
                $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                $upd    = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $upd->bind_param("ss", $hashed, $email);
                $upd->execute();

                // تعليم الرمز كمستخدم
                $mark = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
                $mark->bind_param("i", $row['id']);
                $mark->execute();

                // مسح الجلسة
                unset($_SESSION['reset_email']);

                $success = "تم تغيير كلمة المرور بنجاح! يمكنك الآن تسجيل الدخول.";
            }
        }
    }
}

// ==================== إعادة إرسال الرمز ====================
if (isset($_GET['resend'])) {
    require_once 'notify.php';

    // حذف الرموز القديمة
    $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $del->bind_param("s", $email);
    $del->execute();

    // توليد رمز جديد
    $token      = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    $ins = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $ins->bind_param("sss", $email, $token, $expires_at);
    $ins->execute();

    // جلب الاسم
    $uq = $conn->prepare("SELECT firstname FROM users WHERE email = ?");
    $uq->bind_param("s", $email);
    $uq->execute();
    $urow = $uq->get_result()->fetch_assoc();
    $fname = $urow ? $urow['firstname'] : '';

    $subject = 'رمز إعادة تعيين كلمة المرور - Remedy Pharmacy';
    $body    = "
    <div dir='rtl' style='font-family:Cairo,sans-serif;max-width:500px;margin:auto;background:#f4f7f6;padding:30px;border-radius:16px;'>
        <div style='text-align:center;margin-bottom:20px;'>
            <h2 style='color:#0d6efd;'>💊 Remedy Pharmacy</h2>
        </div>
        <p>مرحباً <strong>{$fname}</strong>،</p>
        <p>رمز إعادة تعيين كلمة المرور الجديد هو:</p>
        <div style='background:#fff;border:2px dashed #0d6efd;border-radius:12px;padding:20px;text-align:center;margin:20px 0;'>
            <span style='font-size:36px;font-weight:bold;letter-spacing:10px;color:#0d6efd;'>{$token}</span>
        </div>
        <p style='color:#888;font-size:13px;'>⏱ الرمز صالح لمدة <strong>15 دقيقة</strong> فقط.</p>
        <hr style='border-color:#eee;'>
        <p style='color:#aaa;font-size:12px;text-align:center;'>Remedy Pharmacy &copy; 2026</p>
    </div>";

    sendEmailNotification($email, $fname, $subject, $body);
    $success = "تم إرسال رمز جديد إلى بريدك الإلكتروني.";
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور | Remedy Pharmacy</title>
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
            padding: 20px;
        }
        .card-box {
            width: 100%;
            max-width: 440px;
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
            color: #fff;
        }
        .email-badge {
            background: #e8f0fe;
            color: #0d6efd;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
        }
        /* حقول إدخال الكود - 6 خانات */
        .code-inputs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 24px;
            direction: ltr;
        }
        .code-inputs input {
            width: 48px;
            height: 56px;
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, transform 0.1s;
            color: #1a1a2e;
        }
        .code-inputs input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
            transform: scale(1.05);
        }
        .code-inputs input.filled {
            border-color: #0d6efd;
            background: #e8f0fe;
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
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13,110,253,0.3);
        }
        .btn-success {
            border-radius: 12px;
            padding: 13px;
            font-weight: 700;
            font-size: 15px;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(25,135,84,0.3);
        }
        .pass-wrapper { position: relative; }
        .pass-wrapper .toggle-pass {
            position: absolute;
            left: 12px; top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
            font-size: 18px;
            user-select: none;
        }
        .resend-link { font-size: 13px; color: #6c757d; text-align: center; margin-top: 12px; }
        .resend-link a { color: #0d6efd; text-decoration: none; font-weight: 600; }
        .resend-link a:hover { text-decoration: underline; }
        .step-indicator { display: flex; gap: 8px; justify-content: center; margin-bottom: 24px; }
        .step { width: 32px; height: 6px; border-radius: 3px; background: #dee2e6; transition: background 0.3s; }
        .step.active { background: #0d6efd; }
        .step.done   { background: #198754; }
    </style>
</head>
<body>

<div class="card-box">

    <?php if ($success && !isset($_POST['reset_btn'])): ?>
        <!-- رسالة نجاح إعادة الإرسال -->
        <div class="icon-circle" style="background:linear-gradient(135deg,#198754,#20c997);">✅</div>
        <div class="alert alert-success text-center py-2 small"><?= htmlspecialchars($success) ?></div>
        <p class="text-center text-muted small">أدخل الرمز الجديد الذي أرسلناه إلى بريدك.</p>
    <?php endif; ?>

    <?php if (!$success || isset($_POST['reset_btn'])): ?>
        <?php if ($success): ?>
            <!-- تم تغيير كلمة المرور بنجاح -->
            <div class="icon-circle" style="background:linear-gradient(135deg,#198754,#20c997);">🎉</div>
            <h4 class="text-center fw-bold mb-2" style="color:#198754;">تم التغيير بنجاح!</h4>
            <p class="text-center text-muted small mb-4">كلمة المرور الجديدة جاهزة. يمكنك الآن تسجيل الدخول.</p>
            <a href="login.php" class="btn btn-success w-100">الذهاب إلى تسجيل الدخول</a>
        <?php else: ?>
            <div class="icon-circle">🔐</div>
            <h4 class="text-center fw-bold mb-1" style="color:#1a1a2e;">إعادة تعيين كلمة المرور</h4>
            <p class="text-center text-muted small mb-2">تم إرسال رمز التحقق إلى</p>
            <div class="text-center">
                <span class="email-badge">📧 <?= htmlspecialchars($email) ?></span>
            </div>

            <!-- مؤشر الخطوات -->
            <div class="step-indicator">
                <div class="step done"></div>
                <div class="step active"></div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger text-center py-2 small"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" id="resetForm">
                <!-- حقل مخفي يجمع الأرقام -->
                <input type="hidden" name="code" id="codeHidden">

                <label class="form-label fw-bold text-center w-100 mb-2">رمز التحقق (6 أرقام)</label>
                <div class="code-inputs" id="codeInputs">
                    <input type="text" maxlength="1" class="digit" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                    <input type="text" maxlength="1" class="digit" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                    <input type="text" maxlength="1" class="digit" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                    <input type="text" maxlength="1" class="digit" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                    <input type="text" maxlength="1" class="digit" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                    <input type="text" maxlength="1" class="digit" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">كلمة المرور الجديدة</label>
                    <div class="pass-wrapper">
                        <input type="password" name="new_password" id="new_password" class="form-control"
                               placeholder="6 أحرف على الأقل" required>
                        <span class="toggle-pass" onclick="togglePass('new_password', this)">👁</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">تأكيد كلمة المرور</label>
                    <div class="pass-wrapper">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                               placeholder="أعد إدخال كلمة المرور" required>
                        <span class="toggle-pass" onclick="togglePass('confirm_password', this)">👁</span>
                    </div>
                </div>

                <button type="submit" name="reset_btn" id="reset_btn" class="btn btn-primary w-100">
                    تغيير كلمة المرور
                </button>
            </form>

            <div class="resend-link">
                لم يصلك الرمز؟ <a href="?resend=1">إعادة إرسال الرمز</a>
            </div>
            <div class="resend-link mt-2">
                <a href="forgot_password.php">← تغيير البريد الإلكتروني</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ===== التنقل بين خانات الرمز =====
const digits  = document.querySelectorAll('.digit');
const hidden  = document.getElementById('codeHidden');

digits.forEach((input, idx) => {
    input.addEventListener('input', function () {
        // السماح بالأرقام فقط
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value) {
            this.classList.add('filled');
            if (idx < digits.length - 1) digits[idx + 1].focus();
        } else {
            this.classList.remove('filled');
        }
        updateHidden();
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Backspace' && !this.value && idx > 0) {
            digits[idx - 1].focus();
            digits[idx - 1].value = '';
            digits[idx - 1].classList.remove('filled');
            updateHidden();
        }
    });

    // دعم اللصق (Paste)
    input.addEventListener('paste', function (e) {
        e.preventDefault();
        const pasted = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
        pasted.split('').forEach((ch, i) => {
            if (digits[i]) {
                digits[i].value = ch;
                digits[i].classList.add('filled');
            }
        });
        if (pasted.length < 6) digits[pasted.length]?.focus();
        else digits[5].focus();
        updateHidden();
    });
});

function updateHidden() {
    if (hidden) {
        hidden.value = Array.from(digits).map(d => d.value).join('');
    }
}

// إظهار/إخفاء كلمة المرور
function togglePass(fieldId, icon) {
    const field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
        icon.textContent = '🙈';
    } else {
        field.type = 'password';
        icon.textContent = '👁';
    }
}

// التحقق قبل الإرسال
const form = document.getElementById('resetForm');
if (form) {
    form.addEventListener('submit', function (e) {
        updateHidden();
        if (!hidden || hidden.value.length < 6) {
            e.preventDefault();
            alert('يرجى إدخال رمز التحقق كاملاً (6 أرقام).');
        }
    });
}
</script>
</body>
</html>
