<?php 
session_start();
require_once './db_config.php';
require_once './notify.php';

// 1. حماية الصفحة وجلب البيانات (قبل أي إخراج HTML)
if(!isset($_SESSION['id'])) { 
    header('location: login.php'); 
    exit(); 
}
$u_id = $_SESSION['id'];

// 2. كود الحذف (قبل تضمين الهيدر)
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM medication_alerts WHERE id = '$id' AND user_id = '$u_id'");
    header("Location: medication_alerts.php");
    exit();
}

include "header.php";

// --- كود التعديل ---
if (isset($_POST['update_alert'])) {
    $id = $_POST['alert_id'];
    $name = mysqli_real_escape_string($conn, $_POST['med_name']);
    $time = $_POST['med_time'];
    
    mysqli_query($conn, "UPDATE medication_alerts SET medication_name='$name', alert_time='$time' WHERE id='$id' AND user_id='$u_id'");
    echo "<script>alert('تم التعديل بنجاح'); window.location='medication_alerts.php';</script>";
}

// --- حفظ موعد جديد ---
if(isset($_POST['add_alert'])) {
    $med_name = mysqli_real_escape_string($conn, $_POST['med_name']);
    $m_time = $_POST['med_time'];
    
    mysqli_query($conn, "INSERT INTO medication_alerts (user_id, medication_name, alert_time) VALUES ('$u_id', '$med_name', '$m_time')");

    // ── إرسال إشعار بريد + حفظ في DB ──
    $user_info = mysqli_query($conn, "SELECT email, firstname FROM users WHERE id='$u_id'");
    if ($user_row = mysqli_fetch_assoc($user_info)) {
        notifyMedicationAdded($conn, $u_id, $user_row['email'], $user_row['firstname'], $med_name, $m_time);
    }

    echo "<script>alert('تم ضبط المنبه بنجاح وسيصلك بريد تأكيد'); window.location='medication_alerts.php';</script>";
}

// --- جلب مواعيد المستخدم الحالي فقط ---
$my_alerts = mysqli_query($conn, "SELECT * FROM medication_alerts WHERE user_id = '$u_id' ORDER BY alert_time ASC");
?>

<div class="container my-5" style="font-family: 'Cairo', sans-serif;">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm p-4 border-0" style="border-radius: 15px;">
                <h5 class="fw-bold mb-3 text-primary"><i class="fas fa-clock"></i> ضبط موعد دواء</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small">اسم الدواء</label>
                        <input type="text" name="med_name" class="form-control" placeholder="مثلاً: بانادول" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">الوقت</label>
                        <input type="time" name="med_time" class="form-control" required>
                    </div>
                    <button name="add_alert" class="btn btn-primary w-100 rounded-pill">إضافة التنبيه</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <h4 class="fw-bold mb-4"><i class="fas fa-list-ul text-primary"></i> مواعيد أدويتي</h4>
            <div class="table-responsive bg-white shadow-sm rounded-4">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الدواء</th>
                            <th>الوقت</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($my_alerts) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($my_alerts)): ?>
                            <tr>
                                <td class="fw-bold"><?php echo $row['medication_name']; ?></td>
                                <td><span class="badge bg-info text-dark p-2"><i class="far fa-clock"></i> <?php echo $row['alert_time']; ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary rounded-circle" onclick="editAlert(<?php echo $row['id']; ?>, '<?php echo $row['medication_name']; ?>', '<?php echo $row['alert_time']; ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="medication_alerts.php?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger rounded-circle ms-1" 
                                       onclick="return confirm('هل أنت متأكد من حذف هذا التنبيه؟')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">لا يوجد مواعيد مضافة حالياً.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="editModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); z-index:1000; background:white; padding:30px; border-radius:20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 90%; max-width: 400px;">
    <h5 class="fw-bold mb-3 text-success">تعديل الموعد</h5>
    <form method="POST">
        <input type="hidden" name="alert_id" id="edit_id">
        <div class="mb-2">
            <label class="small">اسم الدواء</label>
            <input type="text" name="med_name" id="edit_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="small">الوقت</label>
            <input type="time" name="med_time" id="edit_time" class="form-control" required>
        </div>
        <button name="update_alert" class="btn btn-success w-100 mb-2 rounded-pill">حفظ التغييرات</button>
        <button type="button" class="btn btn-light w-100 rounded-pill" onclick="document.getElementById('editModal').style.display='none'">إلغاء</button>
    </form>
</div>

<script>
function editAlert(id, name, time) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_time').value = time;
    document.getElementById('editModal').style.display = 'block';
}
</script>

<?php include "footer.php"; ?>