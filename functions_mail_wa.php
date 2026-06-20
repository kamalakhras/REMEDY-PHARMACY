<?php
// functions_mail_wa.php - محدّث ليستخدم Brevo بدلاً من Gmail
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/mail/Exception.php';
require __DIR__ . '/mail/PHPMailer.php';
require __DIR__ . '/mail/SMTP.php';

// تحميل إعدادات Brevo
require_once __DIR__ . '/brevo_config.php';

function sendNotifications($toEmail, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = BREVO_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = BREVO_USERNAME;
        $mail->Password   = BREVO_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = BREVO_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(BREVO_FROM_EMAIL, BREVO_FROM_NAME);
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $mail->Body    = $message;

        return $mail->send();
    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
        return false;
    }
}