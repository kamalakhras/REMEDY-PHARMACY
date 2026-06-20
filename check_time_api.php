<?php
// check_time_api.php - فحص مواعيد الدواء + إرسال إشعار بريد عند الحلول
ob_start(); // ← يمنع أي output من كسر JSON الرد

session_start();
require_once './db_config.php';
require_once './notify.php';

date_default_timezone_set('Asia/Beirut');

$response = ['alert' => false, 'debug' => ''];

if (!isset($_SESSION['id'])) {
    // المستخدم غير مسجل دخوله
    $response['debug'] = 'no_session';
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$u_id         = intval($_SESSION['id']);
$current_time = date("H:i");                                   // الدقيقة الحالية
$prev_time    = date("H:i", strtotime("-1 minute"));            // الدقيقة السابقة (لتفادي تأخر الـ JS)

// ─── البحث عن تنبيه يطابق الدقيقة الحالية أو السابقة ───
// (يغطي حالات تأخر الـ polling حتى 59 ثانية)
$sql    = "SELECT id, medication_name, alert_time
           FROM medication_alerts
           WHERE user_id = '$u_id'
             AND (
               TIME_FORMAT(alert_time, '%H:%i') = '$current_time'
               OR TIME_FORMAT(alert_time, '%H:%i') = '$prev_time'
             )
           ORDER BY alert_time DESC
           LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result) {
    file_put_contents(__DIR__ . '/mail_log.txt',
        "[" . date('Y-m-d H:i:s') . "] DB Error (alerts): " . mysqli_error($conn) . "\n", FILE_APPEND);
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['alert' => false, 'debug' => 'db_error']);
    exit();
}

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    $response['alert']      = true;
    $response['med_name']   = $row['medication_name'];
    $response['alert_id']   = $row['id'];
    $response['alert_time'] = $row['alert_time'];

    // ─── مفتاح الجلسة: يعتمد على وقت التنبيه الفعلي (مش الوقت الحالي) ───
    // بهذا الشكل حتى لو اشتُعل بـ prev_time ما يتكرر الإرسال
    $alert_minute = date("H:i", strtotime($row['alert_time']));
    $session_key  = 'alert_sent_' . $row['id'] . '_' . date('Y-m-d') . '_' . $alert_minute;

    if (!isset($_SESSION[$session_key])) {
        // جلب بيانات المستخدم لإرسال البريد
        $u_res = mysqli_query($conn, "SELECT email, firstname FROM users WHERE id='$u_id' LIMIT 1");
        if ($u_row = mysqli_fetch_assoc($u_res)) {
            // تسجيل في اللوج قبل الإرسال
            file_put_contents(__DIR__ . '/mail_log.txt',
                "[" . date('Y-m-d H:i:s') . "] Triggering Alert: {$row['medication_name']} for User: {$u_row['email']}\n",
                FILE_APPEND);

            // إرسال بريد + حفظ إشعار في DB
            notifyMedicationTime(
                $conn,
                $u_id,
                $u_row['email'],
                $u_row['firstname'],
                $row['medication_name'],
                $row['alert_time']
            );
        } else {
            file_put_contents(__DIR__ . '/mail_log.txt',
                "[" . date('Y-m-d H:i:s') . "] User not found for id=$u_id\n", FILE_APPEND);
        }

        // تأشير الجلسة حتى لا يُرسَل مجدداً في نفس الدقيقة
        $_SESSION[$session_key] = true;
        $response['email_sent'] = true;
    } else {
        $response['email_sent'] = false; // سبق إرساله هذه الدقيقة
    }
}

ob_end_clean();
header('Content-Type: application/json');
echo json_encode($response);