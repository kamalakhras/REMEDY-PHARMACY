<?php
session_start();
require_once './db_config.php';
require_once './notify.php';
include './admin_navbar.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("غير مسموح بالدخول");
}

// ─── إرسال الرد ───
if (isset($_POST['submit_reply'])) {
    $consultation_id = intval($_POST['consultation_id']);
    $reply_text      = mysqli_real_escape_string($conn, $_POST['admin_reply']);

    $update_query = "UPDATE midecal_consultation SET admin_reply='$reply_text' WHERE id=$consultation_id";
    if (mysqli_query($conn, $update_query)) {

        // ─── إشعار المريض بالبريد + DB ───
        $consult = mysqli_query($conn, "SELECT user_id, user_email, p_name FROM midecal_consultation WHERE id=$consultation_id");
        if ($row_c = mysqli_fetch_assoc($consult)) {
            $target_uid   = intval($row_c['user_id']);
            $target_email = $row_c['user_email'];
            $patient_name = $row_c['p_name'];

            if ($target_uid > 0 && !empty($target_email)) {
                notifyConsultationReply($conn, $target_uid, $target_email, $patient_name, $_POST['admin_reply']);
            }
        }

        echo "<script>alert('تم إرسال الرد بنجاح وتم إشعار المريض بالبريد الإلكتروني'); window.location='admin_messages.php';</script>";
    }
}

$result = mysqli_query($conn, "SELECT * FROM midecal_consultation ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الرد على الاستشارات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .consultation-card { background: white; border-radius: 15px; padding: 20px; margin-bottom: 20px; border: 1px solid #eee; }
        .reply-box { background: #e9ecef; padding: 15px; border-radius: 10px; margin-top: 10px; }
        .notif-sent { background:#d1f7c4; border:1px solid #28a745; padding:8px 12px; border-radius:8px; font-size:0.85rem; color:#155724; margin-top:8px; }
    </style>
</head>
<body>

<div class="container mt-5">
    <h3 class="mb-4 text-primary fw-bold">
        <i class="fas fa-reply me-2"></i>الرد على استشارات المرضى
    </h3>

    <?php while($row = mysqli_fetch_assoc($result)): ?>
    <div class="consultation-card shadow-sm">
        <div class="row">
            <div class="col-md-8">
                <h5><strong>المريض:</strong> <?php echo htmlspecialchars($row['p_name']); ?></h5>
                <p class="text-muted small">التاريخ: <?php echo $row['created_at']; ?></p>
                <p><strong>وصف الحالة:</strong> <?php echo nl2br(htmlspecialchars($row['p_notes'])); ?></p>

                <?php if (!empty($row['admin_reply'])): ?>
                    <div class="alert alert-success mt-2">
                        <strong>ردك السابق:</strong> <?php echo htmlspecialchars($row['admin_reply']); ?>
                    </div>
                    <?php if (!empty($row['user_email'])): ?>
                        <div class="notif-sent">
                            <i class="fas fa-envelope-circle-check me-1"></i>
                            تم إرسال الرد بالبريد لـ: <?php echo htmlspecialchars($row['user_email']); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <form method="POST" class="mt-3">
                    <input type="hidden" name="consultation_id" value="<?php echo $row['id']; ?>">
                    <div class="input-group">
                        <textarea name="admin_reply" class="form-control" placeholder="اكتب ردك الطبي هنا..." required></textarea>
                        <button type="submit" name="submit_reply" class="btn btn-primary">
                            إرسال الرد <i class="fas fa-paper-plane ms-1"></i>
                        </button>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            سيصل الرد تلقائياً للمريض عبر البريد الإلكتروني والإشعارات.
                        </small>
                        <a href="delete_consultation.php?id=<?php echo $row['id']; ?>" 
                           class="btn btn-sm btn-outline-danger border-0" 
                           onclick="return confirm('هل تريد حذف هذه الاستشارة؟');">
                           <i class="fas fa-trash-alt me-1"></i> حذف الاستشارة
                        </a>
                    </div>
                </form>
            </div>

            <div class="col-md-4 text-center">
                <?php if (!empty($row['rx_path'])): ?>
                    <p class="fw-bold small">صورة الروشتة:</p>
                    <a href="./Images/Consultations/<?php echo $row['rx_path']; ?>" target="_blank">
                        <img src="./Images/Consultations/<?php echo $row['rx_path']; ?>" style="width:150px;border-radius:10px;" class="border">
                    </a>
                <?php endif; ?>
                <?php if (!empty($row['user_email'])): ?>
                    <p class="small text-muted mt-2"><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($row['user_email']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>