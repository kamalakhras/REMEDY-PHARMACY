<?php
session_start();
require_once './db_config.php';
require_once './notify.php';   


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

mysqli_query($conn, "ALTER TABLE `midecal_consultation`
    ADD COLUMN IF NOT EXISTS `reply` TEXT DEFAULT NULL AFTER `p_notes`");
mysqli_query($conn, "ALTER TABLE `midecal_consultation`
    ADD COLUMN IF NOT EXISTS `replied_at` TIMESTAMP NULL DEFAULT NULL AFTER `reply`");


if (isset($_POST['send_reply']) && isset($_POST['consult_id']) && !empty($_POST['admin_reply'])) {
    $consult_id  = intval($_POST['consult_id']);
    $admin_reply = mysqli_real_escape_string($conn, trim($_POST['admin_reply']));

    
    $upd = mysqli_query($conn, "UPDATE midecal_consultation
                                SET reply='$admin_reply', replied_at=NOW()
                                WHERE id='$consult_id'");

    if ($upd) {
       
        $c_res = mysqli_query($conn, "SELECT mc.user_id, mc.user_email, u.firstname
                                      FROM midecal_consultation mc
                                      LEFT JOIN users u ON mc.user_id = u.id
                                      WHERE mc.id = '$consult_id' LIMIT 1");
        if ($c_row = mysqli_fetch_assoc($c_res)) {
            $uid        = $c_row['user_id'];
            $uemail     = $c_row['user_email'];
            $uname      = $c_row['firstname'] ?? 'عميلنا العزيز';

            if ($uid && $uemail) {
                
                notifyConsultationReply($conn, $uid, $uemail, $uname, $_POST['admin_reply']);
                $success_msg = "✅ تم إرسال الرد وإشعار المريض عبر البريد الإلكتروني.";
            } else {
                $success_msg = "✅ تم حفظ الرد (لا يوجد بريد إلكتروني مرتبط بهذا المريض).";
            }
        }
    } else {
        $error_msg = "❌ خطأ في حفظ الرد: " . mysqli_error($conn);
    }
}


$query  = "SELECT mc.*, u.firstname
           FROM midecal_consultation mc
           LEFT JOIN users u ON mc.user_id = u.id
           ORDER BY mc.id DESC";
$result = mysqli_query($conn, $query);

include './admin_navbar.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة الاستشارات | Remedy Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Cairo', sans-serif; }
        .table-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-top: 30px; }
        .rx-img { width: 90px; height: 90px; object-fit: cover; border-radius: 10px; cursor: pointer; border: 1px solid #ddd; transition: 0.3s; }
        .rx-img:hover { transform: scale(1.1); }
        .reply-badge { font-size: 0.78rem; padding: 4px 10px; border-radius: 50px; }
        /* Modal overlay */
        .reply-overlay {
            display: none;
            position: fixed; inset: 0; background: rgba(0,0,0,0.45);
            z-index: 1055; align-items: center; justify-content: center;
        }
        .reply-overlay.show { display: flex; }
        .reply-box {
            background: #fff; border-radius: 20px; padding: 35px 30px;
            width: 95%; max-width: 520px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            animation: slideIn .25s ease;
        }
        @keyframes slideIn { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body>

<div class="container py-4">

    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $success_msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger border-0 shadow-sm">
            <i class="fas fa-exclamation-circle me-2"></i><?= $error_msg ?>
        </div>
    <?php endif; ?>

    <div class="table-card">
        <h3 class="fw-bold text-primary mb-4 text-center">
            <i class="fas fa-file-prescription me-2"></i> استشارات المرضى والروشتات
        </h3>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>المريض</th>
                        <th>صورة الروشتة</th>
                        <th>الملاحظات</th>
                        <th>رد الصيدلاني</th>
                        <th>التاريخ</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="fw-bold text-muted">#<?= $row['id'] ?></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($row['p_name']) ?></div>
                                <?php if ($row['firstname']): ?>
                                    <small class="text-muted"><i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($row['firstname']) ?></small>
                                <?php endif; ?>
                                <?php if ($row['user_email']): ?>
                                    <br><small class="text-primary"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($row['user_email']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['rx_path'])): ?>
                                    <a href="./Images/Consultations/<?= $row['rx_path'] ?>" target="_blank">
                                        <img src="./Images/Consultations/<?= $row['rx_path'] ?>" class="rx-img" alt="Prescription">
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">لا يوجد صورة</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width:220px; font-size:0.9rem;">
                                <?= nl2br(htmlspecialchars($row['p_notes'])) ?>
                            </td>
                            <td style="max-width:200px;">
                                <?php if (!empty($row['reply'])): ?>
                                    <span class="badge bg-success reply-badge mb-1">
                                        <i class="fas fa-check-circle me-1"></i>تم الرد
                                    </span>
                                    <div class="small text-muted mt-1" style="font-size:0.82rem; max-width:180px;">
                                        <?= nl2br(htmlspecialchars(substr($row['reply'], 0, 100))) ?>
                                        <?= strlen($row['reply']) > 100 ? '...' : '' ?>
                                    </div>
                                    <?php if ($row['replied_at']): ?>
                                        <small class="text-muted d-block mt-1"><i class="far fa-clock me-1"></i><?= $row['replied_at'] ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark reply-badge">
                                        <i class="fas fa-clock me-1"></i>لم يُرد بعد
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><small class="text-muted"><?= $row['created_at'] ?></small></td>
                            <td class="text-center">
                                <!-- زر الرد -->
                                <button class="btn btn-sm btn-primary mb-1"
                                    onclick="openReply(<?= $row['id'] ?>, `<?= addslashes(htmlspecialchars($row['p_name'])) ?>`, `<?= addslashes(htmlspecialchars($row['reply'] ?? '')) ?>`)">
                                    <i class="fas fa-reply me-1"></i>
                                    <?= !empty($row['reply']) ? 'تعديل الرد' : 'ردّ' ?>
                                </button>
                                <!-- زر الحذف -->
                                <a href="delete_consultation.php?id=<?= $row['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('هل أنت متأكد من حذف هذه الاستشارة؟');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block text-light"></i>
                                لا توجد استشارات حتى الآن.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="reply-overlay" id="replyOverlay">
    <div class="reply-box">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-primary mb-0">
                <i class="fas fa-comment-medical me-2"></i>ردّ على استشارة: <span id="replyPatientName"></span>
            </h5>
            <button type="button" class="btn-close" onclick="closeReply()"></button>
        </div>
        <form method="POST">
            <input type="hidden" name="consult_id" id="replyConsultId">
            <div class="mb-3">
                <label class="form-label fw-bold small">رد الصيدلاني:</label>
                <textarea name="admin_reply" id="replyText" class="form-control"
                    rows="5" placeholder="اكتب ردّك الطبي هنا..." required
                    style="border-radius:12px; resize:none;"></textarea>
            </div>
            <div class="alert alert-info border-0 py-2 small">
                <i class="fas fa-info-circle me-1"></i>
                سيتلقّى المريض إشعاراً فورياً في حسابه وبريده الإلكتروني عند إرسال هذا الرد.
            </div>
            <div class="d-flex gap-2">
                <button name="send_reply" type="submit" class="btn btn-primary flex-fill rounded-pill">
                    <i class="fas fa-paper-plane me-1"></i> إرسال الرد وإشعار المريض
                </button>
                <button type="button" class="btn btn-light rounded-pill" onclick="closeReply()">إلغاء</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openReply(id, patientName, existingReply) {
    document.getElementById('replyConsultId').value  = id;
    document.getElementById('replyPatientName').textContent = patientName;
    document.getElementById('replyText').value        = existingReply || '';
    document.getElementById('replyOverlay').classList.add('show');
}
function closeReply() {
    document.getElementById('replyOverlay').classList.remove('show');
}

document.getElementById('replyOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeReply();
});
</script>
</body>
</html>