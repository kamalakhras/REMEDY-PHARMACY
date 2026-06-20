<?php
// ================================================================
// notify.php - مركز الإشعارات الرئيسي | Remedy Pharmacy
// ================================================================

require_once __DIR__ . '/brevo_config.php';

// تحميل PHPMailer بأمان (مرة واحدة فقط)
if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    require_once __DIR__ . '/mail/Exception.php';
    require_once __DIR__ . '/mail/PHPMailer.php';
    require_once __DIR__ . '/mail/SMTP.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ──────────────────────────────────────────
// 1. حفظ الإشعار في قاعدة البيانات
// ──────────────────────────────────────────
function saveNotification($conn, $user_id, $type, $title, $message) {
    $type    = mysqli_real_escape_string($conn, $type);
    $title   = mysqli_real_escape_string($conn, $title);
    $message = mysqli_real_escape_string($conn, $message);
    $sql = "INSERT INTO notifications (user_id, type, title, message)
            VALUES ('$user_id', '$type', '$title', '$message')";
    return mysqli_query($conn, $sql);
}

// ──────────────────────────────────────────
// 2. قالب HTML للبريد الإلكتروني
// ──────────────────────────────────────────
function buildEmailTemplate($title, $message, $icon = '💊') {
    $site_url = SITE_URL;
    return "<!DOCTYPE html>
<html dir='rtl' lang='ar'>
<head>
<meta charset='UTF-8'>
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f4ff;direction:rtl}
  .wrap{max-width:580px;margin:30px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)}
  .hdr{background:linear-gradient(135deg,#0d6efd,#0a58ca);padding:36px 24px;text-align:center}
  .hdr .ico{font-size:40px;margin-bottom:10px}
  .hdr h1{color:#fff;font-size:22px;font-weight:700}
  .hdr p{color:rgba(255,255,255,.8);font-size:13px;margin-top:4px}
  .bdy{padding:32px 28px}
  .box{background:#f0f7ff;border-right:4px solid #0d6efd;padding:18px 20px;border-radius:8px;margin-bottom:22px}
  .box h2{color:#0d6efd;font-size:17px;margin-bottom:8px}
  .box p{color:#444;font-size:14px;line-height:1.7}
  .btn{display:inline-block;background:#0d6efd;color:#fff!important;padding:11px 28px;border-radius:50px;text-decoration:none;font-size:14px;font-weight:600;margin-top:16px}
  .ftr{background:#f8f9fa;padding:18px 24px;text-align:center;border-top:1px solid #eee}
  .ftr p{color:#888;font-size:12px}
</style>
</head>
<body>
<div class='wrap'>
  <div class='hdr'>
    <div class='ico'>$icon</div>
    <h1>Remedy Pharmacy</h1>
    <p>صيدليتك الموثوقة في طرابلس - لبنان</p>
  </div>
  <div class='bdy'>
    <div class='box'>
      <h2>$title</h2>
      <p>$message</p>
    </div>
    <a href='$site_url' class='btn'>زيارة الموقع &rarr;</a>
  </div>
  <div class='ftr'>
    <p>&copy; 2026 Remedy Pharmacy | Tripoli, Lebanon</p>
    <p style='margin-top:5px'>&#128222; 0096170340715 &nbsp;|&nbsp; &#128140; remedypharmcy@gmail.com</p>
  </div>
</div>
</body>
</html>";
}

// ──────────────────────────────────────────
// 3. إرسال البريد عبر Brevo SMTP
// ──────────────────────────────────────────
function sendEmailNotification($to_email, $to_name, $subject, $html_body) {
    if (empty($to_email) || !filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
        file_put_contents(__DIR__ . '/mail_log.txt', "[" . date('Y-m-d H:i:s') . "] Invalid email: $to_email\n", FILE_APPEND);
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug  = 0;
        $mail->isSMTP();
        $mail->Host       = BREVO_HOST;
        $mail->SMTPAuth   = true;
        $mail->AuthType   = 'LOGIN';
        $mail->Username   = BREVO_USERNAME;
        $mail->Password   = BREVO_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        // استخدام البريد المُتحقَّق منه في Brevo كمرسل (وليس اسم مستخدم SMTP)
        $mail->setFrom(BREVO_FROM_EMAIL, BREVO_FROM_NAME);
        $mail->addAddress($to_email, $to_name ?: $to_email);
        $mail->isHTML(true);
        $mail->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $mail->Body    = $html_body;
        $mail->AltBody = strip_tags(str_replace('<br>', "\n", $html_body));

        $mail->send();
        file_put_contents(__DIR__ . '/mail_log.txt', "[" . date('Y-m-d H:i:s') . "] Success: Sent to $to_email\n", FILE_APPEND);
        return true;
    } catch (Exception $e) {
        file_put_contents(__DIR__ . '/mail_log.txt', "[" . date('Y-m-d H:i:s') . "] Error to $to_email: " . $mail->ErrorInfo . "\n", FILE_APPEND);
        return false;
    }
}

// ══════════════════════════════════════════
// دوال الأحداث
// ══════════════════════════════════════════

// ① تنبيه إضافة موعد دواء جديد
function notifyMedicationAdded($conn, $user_id, $user_email, $user_name, $med_name, $med_time) {
    $title   = "تم ضبط تنبيه دوائك";
    $message = "سيتم تذكيرك بموعد دواء <strong>$med_name</strong> في الساعة <strong>$med_time</strong> يومياً.";
    saveNotification($conn, $user_id, 'medication_added', $title, strip_tags($message));
    file_put_contents(__DIR__ . '/mail_log.txt',
        "[" . date('Y-m-d H:i:s') . "] Alert Added: '$med_name' at $med_time → sending confirm to $user_email\n", FILE_APPEND);
    sendEmailNotification($user_email, $user_name,
        "تم ضبط تنبيه دوائك | Remedy Pharmacy",
        buildEmailTemplate($title, $message, '⏰'));
}

// ② تذكير بحلول موعد الدواء
function notifyMedicationTime($conn, $user_id, $user_email, $user_name, $med_name, $med_time) {
    $title   = "حان موعد دوائك الآن!";
    $message = "حان الآن موعد أخذ جرعة <strong>$med_name</strong> (الساعة $med_time). لا تتأخر!";
    saveNotification($conn, $user_id, 'medication_reminder', $title, strip_tags($message));
    sendEmailNotification($user_email, $user_name,
        "حان موعد دوائك | Remedy Pharmacy",
        buildEmailTemplate($title, $message, '💊'));
}

// ③ استشارة جديدة من مريض (إشعار للأدمن) - تم إصلاح bug الـ global $conn
function notifyNewConsultation($conn, $patient_name, $notes_preview) {
    $title   = "استشارة طبية جديدة";
    $message = "المريض <strong>$patient_name</strong> أرسل استشارة طبية جديدة.<br><br><strong>الملاحظات:</strong> "
               . htmlspecialchars(substr($notes_preview, 0, 150)) . "...";
    // إشعار DB لكل أدمن
    $admins = mysqli_query($conn, "SELECT id FROM users WHERE role='admin'");
    if ($admins) {
        while ($admin = mysqli_fetch_assoc($admins)) {
            saveNotification($conn, $admin['id'], 'consultation_new', $title, "استشارة جديدة من $patient_name");
        }
    }
    sendEmailNotification(ADMIN_EMAIL, 'Remedy Admin',
        "استشارة جديدة من $patient_name | Remedy Pharmacy",
        buildEmailTemplate($title, $message, '📋'));
}

// ④ رد الأدمن على الاستشارة (إشعار للمريض)
function notifyConsultationReply($conn, $user_id, $user_email, $user_name, $admin_reply) {
    $title   = "رد الصيدلاني على استشارتك";
    $message = "أجاب الصيدلاني على استشارتك الطبية:<br><br><em>\"" . htmlspecialchars($admin_reply) . "\"</em>";

    // حفظ إشعار داخلي في قاعدة البيانات (يظهر في جرس الإشعارات للعميل)
    saveNotification($conn, $user_id, 'consultation_reply', $title, strip_tags($message));

    // تسجيل العملية في ملف اللوج
    file_put_contents(__DIR__ . '/mail_log.txt',
        "[" . date('Y-m-d H:i:s') . "] Admin Replying to User: $user_email\n", FILE_APPEND);

    // إرسال بريد إلكتروني للمريض
    sendEmailNotification($user_email, $user_name,
        "رد على استشارتك | Remedy Pharmacy",
        buildEmailTemplate($title, $message, '💬'));
}

// ⑤ تأكيد استلام طلب (للمستخدم + الأدمن)
function notifyOrderPlaced($conn, $user_id, $user_email, $user_name, $order_id) {
    $title   = "تم استلام طلبك بنجاح!";
    $message = "تم تسجيل طلبك رقم <strong>#$order_id</strong>. سيتم التواصل معك قريباً لتأكيد التوصيل.";
    saveNotification($conn, $user_id, 'order_placed', $title, strip_tags($message));
    sendEmailNotification($user_email, $user_name,
        "تم استلام طلبك | Remedy Pharmacy",
        buildEmailTemplate($title, $message, '📦'));

    $adm_msg = "طلب جديد رقم <strong>#$order_id</strong> من العميل <strong>$user_name</strong>. راجع الطلبات الآن.";
    $admins  = mysqli_query($conn, "SELECT id FROM users WHERE role='admin'");
    if ($admins) {
        while ($admin = mysqli_fetch_assoc($admins)) {
            saveNotification($conn, $admin['id'], 'order_new', "طلب جديد #$order_id", "طلب من $user_name");
        }
    }
    sendEmailNotification(ADMIN_EMAIL, 'Remedy Admin',
        "طلب جديد #$order_id | Remedy Pharmacy",
        buildEmailTemplate("طلب جديد", $adm_msg, '🛒'));
}

// ⑥ إشعار شحن الطلب (للمستخدم)
function notifyOrderShipped($conn, $user_id, $user_email, $user_name, $order_id) {
    $title   = "تم شحن طلبك!";
    $message = "يسرنا إخبارك أن طلبك رقم <strong>#$order_id</strong> تم شحنه وهو في طريقه إليك الآن!";
    saveNotification($conn, $user_id, 'order_shipped', $title, strip_tags($message));
    sendEmailNotification($user_email, $user_name,
        "تم شحن طلبك | Remedy Pharmacy",
        buildEmailTemplate($title, $message, '🚀'));
}

// ⑦ رسالة تواصل من مستخدم (Contact Us) - للأدمن
function notifyContactMessage($conn, $sender_name, $sender_email, $subject, $msg_body) {
    $title   = "رسالة جديدة من: $sender_name";
    $message = "<strong>الاسم:</strong> $sender_name<br>"
             . "<strong>البريد:</strong> $sender_email<br><br>"
             . "<strong>الموضوع:</strong> $subject<br><br>"
             . "<strong>الرسالة:</strong><br>" . nl2br(htmlspecialchars($msg_body));

    // إشعار DB للأدمن
    $admins = mysqli_query($conn, "SELECT id FROM users WHERE role='admin'");
    if ($admins) {
        while ($admin = mysqli_fetch_assoc($admins)) {
            saveNotification($conn, $admin['id'], 'contact_message', $title, "رسالة من $sender_name: " . substr($msg_body, 0, 80));
        }
    }
    sendEmailNotification(ADMIN_EMAIL, 'Remedy Admin',
        "رسالة تواصل من $sender_name | Remedy Pharmacy",
        buildEmailTemplate($title, $message, '✉️'));

    // رسالة تأكيد للمرسِل
    $confirm_title = "تم استلام رسالتك";
    $confirm_msg   = "شكراً <strong>$sender_name</strong>! استلمنا رسالتك بخصوص \"$subject\" وسنرد عليك في أقرب وقت.";
    sendEmailNotification($sender_email, $sender_name,
        "تم استلام رسالتك | Remedy Pharmacy",
        buildEmailTemplate($confirm_title, $confirm_msg, '✅'));
}
