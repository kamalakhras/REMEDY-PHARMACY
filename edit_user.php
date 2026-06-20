<?php
session_start();
require_once './db_config.php';

// حماية الصفحة
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header("Location: admin_users.php");
    exit();
}

$user_id = mysqli_real_escape_string($conn, $user_id);
$res = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($res);

if (!$user) {
    header("Location: admin_users.php");
    exit();
}

if (isset($_POST['update_user'])) {
    $fname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role  = mysqli_real_escape_string($conn, $_POST['role']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    $update_sql = "UPDATE users SET firstname='$fname', lastname='$lname', email='$email', role='$role', phone='$phone' WHERE id='$user_id'";
    if (mysqli_query($conn, $update_sql)) {
        header("Location: admin_users.php?updated=1");
        exit();
    } else {
        $error = "خطأ في التحديث: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل مستخدم | Remedy Pharmacy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Cairo', sans-serif; background:#f4f7f6;}</style>
</head>
<body class="p-4">
    <div class="container" style="max-width: 600px;">
        <div class="card shadow border-0 p-4" style="border-radius:20px;">
            <h3 class="fw-bold mb-4 text-primary">تعديل بيانات المستخدم</h3>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">الاسم الأول</label>
                    <input type="text" name="firstname" class="form-control" value="<?= $user['firstname'] ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">الكنية</label>
                    <input type="text" name="lastname" class="form-control" value="<?= $user['lastname'] ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" class="form-control" value="<?= $user['email'] ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">رقم الهاتف</label>
                    <input type="text" name="phone" class="form-control" value="<?= $user['phone'] ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">الرتبة</label>
                    <select name="role" class="form-select">
                        <option value="user" <?= ($user['role']=='user')?'selected':'' ?>>مستخدم (User)</option>
                        <option value="admin" <?= ($user['role']=='admin')?'selected':'' ?>>مدير (Admin)</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" name="update_user" class="btn btn-primary px-4 rounded-pill">حفظ التغييرات</button>
                    <a href="admin_users.php" class="btn btn-light px-4 rounded-pill">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
