<?php
session_start();
require_once './db_config.php';
require_once './notify.php';

$success = false;
$error   = '';

if (isset($_POST['send_message'])) {
    $sender_name  = trim(mysqli_real_escape_string($conn, $_POST['sender_name']));
    $sender_email = trim($_POST['sender_email']);
    $subject      = trim(mysqli_real_escape_string($conn, $_POST['msg_subject']));
    $msg_body     = trim(mysqli_real_escape_string($conn, $_POST['msg_body']));

    if (empty($sender_name) || empty($sender_email) || empty($subject) || empty($msg_body)) {
        $error = 'يرجى ملء جميع الحقول المطلوبة.';
    } elseif (!filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'البريد الإلكتروني غير صالح.';
    } else {
        $sent = notifyContactMessage($conn, $sender_name, $sender_email, $subject, $msg_body);
        if ($sent !== false) {
            $success = true;
        } else {
            $error = 'حدث خطأ أثناء الإرسال، يرجى المحاولة مرة أخرى.';
        }
    }
}

include 'header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center g-4">

        <!-- فورم التواصل -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm" style="border-radius:20px;">
                <div class="card-body p-5">
                    <h2 class="fw-bold text-primary mb-2">تواصل معنا</h2>
                    <p class="text-muted mb-4">أرسل لنا رسالتك وسنرد عليك في أقرب وقت ممكن.</p>

                    <?php if ($success): ?>
                        <div class="alert alert-success d-flex align-items-center gap-3" style="border-radius:12px;">
                            <i class="fas fa-check-circle fa-2x"></i>
                            <div>
                                <strong>تم إرسال رسالتك بنجاح!</strong><br>
                                <small>ستصلك رسالة تأكيد على بريدك الإلكتروني وسنرد عليك قريباً.</small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" style="border-radius:12px;">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="contact.php">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">الاسم الكامل <span class="text-danger">*</span></label>
                                <input type="text" name="sender_name" class="form-control" placeholder="اسمك"
                                       value="<?php echo isset($_SESSION['firstname']) ? htmlspecialchars($_SESSION['firstname']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">البريد الإلكتروني <span class="text-danger">*</span></label>
                                <input type="email" name="sender_email" class="form-control" placeholder="example@mail.com"
                                       value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">الموضوع <span class="text-danger">*</span></label>
                                <input type="text" name="msg_subject" class="form-control" placeholder="موضوع رسالتك" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">الرسالة <span class="text-danger">*</span></label>
                                <textarea name="msg_body" class="form-control" rows="5"
                                          placeholder="اكتب رسالتك هنا..." required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="send_message" class="btn btn-primary w-100 py-3 fw-bold" style="border-radius:50px;">
                                    <i class="fas fa-paper-plane me-2"></i>إرسال الرسالة
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- معلومات التواصل -->
        <div class="col-md-4">
            <div class="d-flex flex-column gap-3">

                <div class="card border-0 shadow-sm p-4" style="border-radius:16px;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:50px;height:50px;background:#e8f0fe;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-phone text-primary fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold small">اتصل بنا</div>
                            <div class="text-muted small">0096170340715</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4" style="border-radius:16px;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:50px;height:50px;background:#e6f4ea;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <i class="fab fa-whatsapp text-success fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold small">واتساب</div>
                            <a href="https://wa.me/96170340715" target="_blank" class="text-muted small text-decoration-none">ابدأ محادثة الآن</a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4" style="border-radius:16px;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:50px;height:50px;background:#fce8e6;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-envelope text-danger fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold small">البريد الإلكتروني</div>
                            <div class="text-muted small">remedypharmcy@gmail.com</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4" style="border-radius:16px;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:50px;height:50px;background:#fff3e0;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-clock text-warning fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold small">ساعات العمل</div>
                            <div class="text-muted small">8 صباحاً - 10 مساءً</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4" style="border-radius:16px;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:50px;height:50px;background:#f3e8fd;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-map-marker-alt text-purple fa-lg" style="color:#7c3aed;"></i>
                        </div>
                        <div>
                            <div class="fw-bold small">العنوان</div>
                            <div class="text-muted small">طرابلس، لبنان</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>