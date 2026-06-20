<?php
session_start();
require_once './db_config.php';

// حماية الصفحة
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// كود ترقية المستخدم لأدمن (سريع)
if(isset($_POST['make_admin'])){
    $uid = mysqli_real_escape_string($conn, $_POST['u_id']);
    mysqli_query($conn, "UPDATE users SET role='admin' WHERE id='$uid'");
    header("Location: admin_users.php?admin_success=1");
}

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين | Remedy Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Cairo', sans-serif; }
        .table-card { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 25px; }
        .user-avatar { width: 40px; height: 40px; background: #e8f0fe; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #0d6efd; font-weight: bold; }
        .badge-admin { background: #fce8e6; color: #d93025; }
        .badge-user { background: #e6f4ea; color: #1e8e3e; }
    </style>
</head>
<body>

    <?php include("./admin_navbar.php"); ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">إدارة المستخدمين</h2>
                <p class="text-muted small">تحكم في حسابات المستخدمين وصلاحياتهم</p>
            </div>
            <div class="badge bg-white text-dark border p-2 rounded-3 shadow-sm">
                إجمالي المسجلين: <span class="fw-bold text-primary"><?= mysqli_num_rows($users) ?></span>
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>المستخدم</th>
                            <th>البريد الإلكتروني</th>
                            <th>رقم الهاتف</th>
                            <th>الرتبة</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar"><?= mb_substr($row['firstname'], 0, 1) ?></div>
                                    <div>
                                        <div class="fw-bold"><?= $row['firstname'] . ' ' . $row['lastname'] ?></div>
                                        <div class="text-muted small">ID: #<?= $row['id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= $row['email'] ?></td>
                            <td><?= $row['phone'] ?: '<span class="text-muted">---</span>' ?></td>
                            <td>
                                <span class="badge rounded-pill <?= ($row['role']=='admin')?'badge-admin':'badge-user' ?> p-2 px-3">
                                    <?= ($row['role']=='admin') ? 'مدير' : 'مستخدم' ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <!-- زر التعديل -->
                                    <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <!-- زر الترقية (إذا لم يكن أدمن) -->
                                    <?php if($row['role'] !== 'admin'): ?>
                                        <form method="POST" onsubmit="return confirm('هل تريد ترقية هذا المستخدم لمدير؟')" style="display:inline;">
                                            <input type="hidden" name="u_id" value="<?= $row['id'] ?>">
                                            <button name="make_admin" class="btn btn-sm btn-outline-warning rounded-circle" title="ترقية لأدمن">
                                                <i class="fas fa-user-shield"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <!-- زر الحذف -->
                                    <a href="delete_user.php?id=<?= $row['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger rounded-circle" 
                                       onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم نهائياً؟ لا يمكن التراجع!')"
                                       title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>