<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
require_once './db_config.php';
require_once './notify.php';

// ─── منطق الحفظ (قبل عرض الواجهة) ───
if (isset($_POST['submitConsultation'])) {
    $p_name  = mysqli_real_escape_string($conn, $_POST['p_name']);
    $p_notes = mysqli_real_escape_string($conn, $_POST['p_notes']);
    $u_id    = $_SESSION['id'] ?? null;
    $u_email = $_SESSION['email'] ?? null;

    $rx_file  = $_FILES['rx_file']['name'];
    $tmp_name = $_FILES['rx_file']['tmp_name'];
    $rx_path  = time() . "_" . basename($rx_file);

    if (move_uploaded_file($tmp_name, "./Images/Consultations/" . $rx_path)) {
        $sql = "INSERT INTO midecal_consultation (user_id, user_email, p_name, rx_path, p_notes)
                VALUES ('$u_id', '$u_email', '$p_name', '$rx_path', '$p_notes')";
        if (mysqli_query($conn, $sql)) {
            // إشعار للأدمن
            notifyNewConsultation($conn, $p_name, $p_notes);
            $success_msg = "تم إرسال استشارتك بنجاح! سيصلك رد الصيدلاني قريباً عبر الإشعارات وبريدك الإلكتروني.";
        }
    }
}

// ─── جلب استشارات المستخدم مع الردود ───
$my_consults = null;
if (isset($_SESSION['id'])) {
    $uid = intval($_SESSION['id']);
    $my_consults = mysqli_query($conn, "SELECT * FROM midecal_consultation
                                        WHERE user_id = '$uid'
                                        ORDER BY id DESC");
}

include ("./header.php");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استشارة طبية | Remedy Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-grad: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            --accent-color: #0dcaf0;
        }
        body {
            background: #f0f4f8;
            font-family: 'Cairo', sans-serif;
            color: #333;
        }
        .consult-header {
            background: var(--primary-grad);
            padding: 80px 0;
            color: #fff;
            border-bottom-left-radius: 50px;
            border-bottom-right-radius: 50px;
            margin-bottom: -100px;
            text-align: center;
        }
        .main-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .info-side {
            background: var(--primary-grad);
            color: #fff;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-label {
            font-weight: 700;
            font-size: 0.9rem;
            color: #444;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-control {
            border-radius: 15px;
            padding: 12px 20px;
            border: 2px solid #eee;
            transition: all 0.3s;
            background: #f9f9f9;
        }
        .form-control:focus {
            border-color: #0d6efd;
            background: #fff;
            box-shadow: 0 0 15px rgba(13, 110, 253, 0.1);
        }
        .btn-submit {
            background: var(--primary-grad);
            border: none;
            border-radius: 15px;
            padding: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(13, 110, 253, 0.3);
        }
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(13, 110, 253, 0.4);
        }
        .icon-box {
            width: 45px;
            height: 45px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>

    <section class="consult-header">
        <div class="container">
            <h1 class="fw-extrabold mb-3">الاستشارات الطبية الذكية</h1>
            <p class="opacity-75 fs-5">أرسل روشتتك الآن وسيقوم طاقمنا الصيدلاني بالرد عليك في أسرع وقت</p>
        </div>
    </section>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="card main-card">
                    <div class="row g-0">
                        <!-- جانب المعلومات -->
                        <div class="col-md-5 info-side">
                            <h3 class="fw-bold mb-4 text-white">كيف تعمل الخدمة؟</h3>
                            
                            <div class="step-item">
                                <div class="icon-box"><i class="fas fa-camera"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">صور الروشتة</h6>
                                    <p class="small opacity-75 mb-0">تأكد من وضوح الصورة والأسماء الطبية المكتوبة.</p>
                                </div>
                            </div>

                            <div class="step-item">
                                <div class="icon-box"><i class="fas fa-pen-fancy"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">أضف ملاحظاتك</h6>
                                    <p class="small opacity-75 mb-0">اذكر أي أعراض أو تفاصيل إضافية تهم الصيدلاني.</p>
                                </div>
                            </div>

                            <div class="step-item">
                                <div class="icon-box"><i class="fas fa-bell"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">انتظر التنبيه</h6>
                                    <p class="small opacity-75 mb-0">سوف نرسل لك إشعاراً فورياً وبريداً عند توفر الرد.</p>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-top border-white border-opacity-25">
                                <p class="small mb-0"><i class="fas fa-shield-alt me-2"></i> بياناتك محمية ومشفرة بالكامل لدى صيدلية Remedy.</p>
                            </div>
                        </div>

                        <!-- جانب الفورم -->
                        <div class="col-md-7 p-4 p-lg-5 bg-white">
                            <?php if(isset($success_msg)): ?>
                                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-3 mb-4" style="border-radius:15px;">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                    <div><?= $success_msg ?></div>
                                </div>
                            <?php endif; ?>

                            <form action="midecal_consultation.php" method="POST" enctype="multipart/form-data">
                                <div class="mb-4">
                                    <label class="form-label"><i class="fas fa-user text-primary"></i> اسم المريض بالكامل</label>
                                    <input type="text" name="p_name" class="form-control" placeholder="أدخل الاسم هنا..." required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label"><i class="fas fa-file-image text-primary"></i> تحميل صورة الروشتة (Rx)</label>
                                    <div class="p-4 border-2 border-dashed rounded-4 text-center bg-light border-primary border-opacity-25">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3 opacity-50"></i>
                                        <input type="file" name="rx_file" class="form-control" accept="image/*" required>
                                        <p class="text-muted small mt-2">يمكنك رفع صور بصيغة JPG, PNG أو PDF</p>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label"><i class="fas fa-comment-medical text-primary"></i> ملاحظات إضافية</label>
                                    <textarea name="p_notes" class="form-control" rows="4" placeholder="اشرح الحالة، الحساسية من أدوية معينة، أو أي استفسار..."></textarea>
                                </div>

                                <button type="submit" name="submitConsultation" class="btn btn-primary btn-submit w-100 text-white">
                                    إرسال الاستشارة الآن <i class="fas fa-paper-plane ms-2"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======= قسم استشاراتي السابقة ======= -->
    <?php if ($my_consults && mysqli_num_rows($my_consults) > 0): ?>
    <section style="background:#f0f4f8; padding: 50px 0 60px;">
        <div class="container">
            <h3 class="fw-bold text-center mb-5" style="font-family:'Cairo',sans-serif; color:#1a1a2e;">
                <i class="fas fa-history text-primary me-2"></i> استشاراتي السابقة
            </h3>

            <?php while ($c = mysqli_fetch_assoc($my_consults)): ?>
            <div class="card border-0 shadow-sm mb-4" style="border-radius:20px; overflow:hidden; font-family:'Cairo',sans-serif;">
                <div class="card-body p-0">
                    <div class="row g-0">

                        <!-- معلومات الاستشارة -->
                        <div class="col-md-7 p-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width:44px;height:44px;background:linear-gradient(135deg,#0d6efd,#0a58ca);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;">
                                    <i class="fas fa-file-prescription"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="color:#1a1a2e;">المريض: <?= htmlspecialchars($c['p_name']) ?></div>
                                    <small class="text-muted"><i class="far fa-calendar me-1"></i><?= $c['created_at'] ?></small>
                                </div>
                                <?php if (!empty($c['reply'])): ?>
                                    <span class="badge bg-success ms-auto" style="border-radius:50px;padding:6px 14px;font-size:0.8rem;">
                                        <i class="fas fa-check-circle me-1"></i>تم الرد
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark ms-auto" style="border-radius:50px;padding:6px 14px;font-size:0.8rem;">
                                        <i class="fas fa-hourglass-half me-1"></i>قيد المراجعة
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($c['p_notes'])): ?>
                            <div style="background:#f8f9fa;border-radius:12px;padding:12px 16px;font-size:0.88rem;color:#555;">
                                <strong class="d-block mb-1" style="color:#333;"><i class="fas fa-comment-dots me-1 text-primary"></i> ملاحظاتك:</strong>
                                <?= nl2br(htmlspecialchars($c['p_notes'])) ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- رد الصيدلاني -->
                        <div class="col-md-5 p-4" style="background:<?= !empty($c['reply']) ? 'linear-gradient(135deg,#e8f5e9,#f1f8e9)' : '#fafafa' ?>;border-right:1px solid #eee;">
                            <?php if (!empty($c['reply'])): ?>
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div style="width:36px;height:36px;background:#28a745;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:0.9rem;">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="font-size:0.85rem;color:#1a5c2a;">رد الصيدلاني</div>
                                        <?php if (!empty($c['replied_at'])): ?>
                                            <small class="text-muted" style="font-size:0.75rem;"><?= $c['replied_at'] ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div style="background:#fff;border-radius:12px;padding:14px 16px;border-right:4px solid #28a745;font-size:0.9rem;color:#333;line-height:1.7;">
                                    <?= nl2br(htmlspecialchars($c['reply'])) ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-clock fa-2x mb-2 d-block" style="color:#ffc107;"></i>
                                    <p class="small mb-0">في انتظار رد الصيدلاني...<br>ستصلك إشعار فور الرد.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php include ("./footer.php"); ?>