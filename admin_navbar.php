

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
  <div class="container">
    <a class="navbar-brand fw-bold text-info" href="admin_dashboard.php">REMEDY ADMIN</a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="adminNav">
      <ul class="navbar-nav ms-auto text-end align-items-center">
        <li class="nav-item"><a class="nav-link" href="admin_orders.php">&#x1F4E6; الطلبات</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_messages.php">&#x1F4AC; الرسائل</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_users.php">&#x1F465; المستخدمين</a></li>
        <li class="nav-item"><a class="nav-link text-warning" href="index.php">&#x1F3E0; معاينة الموقع</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_add_product.php"><i class="fas fa-plus-circle"></i> إضافة منتج</a></li>
        <li class="nav-item"><a class="nav-link text-info" href="admin_stock.php"><i class="fas fa-boxes-stacked"></i> المخزون</a></li>

        <!-- أيقونة الإشعارات للأدمن -->
        <li class="nav-item" style="position:relative;margin:0 8px">
          <button id="adminNotifBtn" onclick="toggleAdminNotif()" style="background:none;border:none;color:#fff;font-size:1.2rem;cursor:pointer;position:relative;padding:4px 8px">
            <i class="fas fa-bell"></i>
            <span id="adminNotifBadge" style="position:absolute;top:-4px;right:-4px;background:#dc3545;color:#fff;font-size:0.6rem;font-weight:700;border-radius:50%;min-width:16px;height:16px;display:none;align-items:center;justify-content:center">0</span>
          </button>
          <div id="adminNotifDrop" style="display:none;position:absolute;top:calc(100% + 8px);right:0;width:300px;background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.2);z-index:9999;overflow:hidden">
            <div style="background:linear-gradient(135deg,#343a40,#495057);padding:12px 16px;color:#fff;display:flex;justify-content:space-between;align-items:center">
              <span style="font-size:.85rem;font-weight:700"><i class="fas fa-bell me-1"></i> إشعاراتك</span>
              <a onclick="adminMarkAll()" style="color:rgba(255,255,255,.8);font-size:.75rem;cursor:pointer">تحديد كلها</a>
            </div>
            <div id="adminNotifList" style="max-height:320px;overflow-y:auto">
              <div style="text-align:center;padding:24px;color:#aaa;font-size:.85rem">
                <i class="fas fa-bell-slash d-block mb-2" style="font-size:1.5rem"></i>لا توجد إشعارات
              </div>
            </div>
          </div>
        </li>

        <li class="nav-item"><a class="nav-link text-danger" href="logout.php">&#x1F6AA; خروج</a></li>
      </ul>
    </div>
  </div>
</nav>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleAdminNotif() {
    const drop = document.getElementById('adminNotifDrop');
    drop.style.display = drop.style.display === 'block' ? 'none' : 'block';
    if (drop.style.display === 'block') loadAdminNotifs();
}
document.addEventListener('click', function(e) {
    const btn  = document.getElementById('adminNotifBtn');
    const drop = document.getElementById('adminNotifDrop');
    if (btn && drop && !btn.contains(e.target) && !drop.contains(e.target)) {
        drop.style.display = 'none';
    }
});
function loadAdminNotifs() {
    fetch('get_notifications.php').then(r => r.json()).then(data => {
        const cnt   = data.count;
        const badge = document.getElementById('adminNotifBadge');
        if (cnt > 0) { badge.style.display = 'flex'; badge.textContent = cnt > 99 ? '99+' : cnt; }
        else { badge.style.display = 'none'; }

        const icons = { consultation_new: '&#x1F4CB;', order_new: '&#x1F6D2;', order_shipped: '&#x1F680;', medication_added: '&#x23F0;' };
        const list  = document.getElementById('adminNotifList');
        if (!data.notifications || data.notifications.length === 0) {
            list.innerHTML = '<div style="text-align:center;padding:24px;color:#aaa;font-size:.85rem"><i class="fas fa-bell-slash d-block mb-2" style="font-size:1.5rem"></i>لا توجد إشعارات</div>';
            return;
        }
        let html = '';
        data.notifications.forEach(function(n) {
            const ic  = icons[n.type] || '&#x1F514;';
            const bg  = n.is_read == '0' ? 'background:#f0f7ff;border-right:3px solid #0d6efd' : '';
            html += '<div onclick="adminMarkOne(' + n.id + ',this)" style="padding:11px 14px;border-bottom:1px solid #f0f0f0;cursor:pointer;display:flex;gap:10px;' + bg + '">'
                  + '<div style="font-size:1.2rem;flex-shrink:0">' + ic + '</div>'
                  + '<div><div style="font-size:.8rem;font-weight:700;color:#222">' + n.title + '</div>'
                  + '<div style="font-size:.75rem;color:#666">' + n.message + '</div></div></div>';
        });
        list.innerHTML = html;
    }).catch(function(e) { console.log('Admin notif error:', e); });
}
function adminMarkOne(id, el) {
    fetch('mark_read.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: 'notif_id=' + id });
    el.style.background = '#fff'; el.style.borderRight = 'none';
    const b = document.getElementById('adminNotifBadge');
    const cur = parseInt(b.textContent) || 0;
    if (cur > 1) b.textContent = cur - 1; else b.style.display = 'none';
}
function adminMarkAll() {
    fetch('mark_read.php', { method: 'POST' });
    document.getElementById('adminNotifBadge').style.display = 'none';
    loadAdminNotifs();
}
loadAdminNotifs();
setInterval(loadAdminNotifs, 30000);
</script>