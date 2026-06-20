<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./css/partials.css">
    <style>
        .header-main-box {
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 0;
            min-height: 120px;
            display: flex;
            align-items: center;
        }
        .logo-box { display: flex; align-items: center; gap: 15px; }
        .pharmacy-logo-img { height: 120px; width: auto; object-fit: contain; }
        .brand-text { font-size: 1.4rem; margin: 0; color: #0d6efd; font-weight: 700; white-space: nowrap; }
        .lang-container { text-align: center; font-size: 0.9rem; color: #6c757d; }
        .auth-container { text-align: right; }
        html, body { max-width: 100%; overflow-x: hidden; }
        .row { margin-right: 0; margin-left: 0; }

        /* ===== أيقونة الإشعارات ===== */
        .notif-wrapper { position: relative; display: inline-block; margin-left: 10px; }
        .notif-btn {
            background: none; border: none; cursor: pointer;
            font-size: 1.3rem; color: #555; padding: 4px 8px;
            position: relative; transition: color 0.2s;
        }
        .notif-btn:hover { color: #0d6efd; }
        .notif-badge {
            position: absolute; top: -4px; right: -4px;
            background: #dc3545; color: #fff;
            font-size: 0.65rem; font-weight: 700;
            border-radius: 50%; min-width: 18px; height: 18px;
            display: flex; align-items: center; justify-content: center;
            padding: 0 4px; display: none;
        }
        .notif-dropdown {
            display: none; position: absolute; top: calc(100% + 10px);
            right: 0; width: 320px; background: #fff;
            border-radius: 14px; box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            z-index: 9999; overflow: hidden; border: 1px solid #e9ecef;
        }
        .notif-dropdown.show { display: block; animation: fadeIn 0.2s ease; }
        @keyframes fadeIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
        .notif-header {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            padding: 14px 18px; color: #fff;
            display: flex; justify-content: space-between; align-items: center;
        }
        .notif-header h6 { margin: 0; font-size: 0.9rem; font-weight: 700; }
        .notif-header a { color: rgba(255,255,255,0.85); font-size: 0.75rem; text-decoration: none; cursor: pointer; }
        .notif-header a:hover { color: #fff; }
        .notif-list { max-height: 340px; overflow-y: auto; }
        .notif-item {
            padding: 12px 16px; border-bottom: 1px solid #f0f0f0;
            cursor: pointer; transition: background 0.15s;
            display: flex; gap: 12px; align-items: flex-start;
        }
        .notif-item:hover { background: #f8f9ff; }
        .notif-item.unread { background: #f0f7ff; border-right: 3px solid #0d6efd; }
        .notif-icon { font-size: 1.4rem; flex-shrink: 0; margin-top: 2px; }
        .notif-title { font-size: 0.82rem; font-weight: 700; color: #222; margin-bottom: 2px; }
        .notif-msg { font-size: 0.78rem; color: #666; line-height: 1.4; }
        .notif-time { font-size: 0.7rem; color: #aaa; margin-top: 3px; }
        .notif-empty { text-align: center; padding: 30px 16px; color: #aaa; font-size: 0.85rem; }
        .notif-empty i { font-size: 2rem; display: block; margin-bottom: 8px; }

        /* Bell shake animation */
        @keyframes bellShake {
            0%,100%{transform:rotate(0)} 20%{transform:rotate(-15deg)} 40%{transform:rotate(15deg)}
            60%{transform:rotate(-10deg)} 80%{transform:rotate(10deg)}
        }
        .bell-ring { animation: bellShake 0.6s ease; }
    </style>
</head>
<body>

<header class="header-main-box shadow-sm">
    <div class="container-fluid px-4">
        <div class="row align-items-center">

            <!-- الشعار -->
            <div class="col-4">
                <a href="index.php" class="text-decoration-none logo-box">
                    <img src="./Images/WhatsApp Image 2026-02-17 at 10.48.46 PM.jpeg" alt="Logo" class="pharmacy-logo-img">
                </a>
            </div>

            <div class="col-4 lang-container"></div>

            <!-- أزرار الدخول + الجرس + السلة -->
            <div class="col-4 auth-container d-flex align-items-center justify-content-end gap-2">

                <?php if (!isset($_SESSION['id'])): ?>
                    <a href="login.php" class="btn btn-primary rounded-pill px-4">Sign In</a>
                <?php else: ?>
                    <span class="fw-bold me-1 d-none d-md-inline">Hello, <?php echo htmlspecialchars($_SESSION['firstname']); ?></span>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Logout</a>

                    <!-- 🔔 أيقونة الإشعارات -->
                    <div class="notif-wrapper" id="notifWrapper">
                        <button class="notif-btn" id="notifBtn" title="الإشعارات">
                            <i class="fas fa-bell" id="bellIcon"></i>
                            <span class="notif-badge" id="notifBadge">0</span>
                        </button>

                        <div class="notif-dropdown" id="notifDropdown">
                            <div class="notif-header">
                                <h6><i class="fas fa-bell me-1"></i> الإشعارات</h6>
                                <a onclick="markAllRead()">تحديد الكل كمقروء</a>
                            </div>
                            <div class="notif-list" id="notifList">
                                <div class="notif-empty"><i class="fas fa-bell-slash"></i>لا توجد إشعارات</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 🛒 أيقونة السلة -->
                <div class="cart-icon-wrapper" style="display:inline-block;position:relative;">
                    <a href="cart.php" class="btn btn-outline-primary rounded-pill border-0 position-relative">
                        <i class="fas fa-shopping-basket fa-lg"></i>
                        <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.7rem;">
                            <?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>
                        </span>
                    </a>
                </div>

            </div>
        </div>
    </div>
</header>

<?php if (isset($_SESSION['id'])): ?>
<script>
// ======================================================
// نظام الإشعارات في النافبار
// ======================================================
const notifBtn      = document.getElementById('notifBtn');
const notifDropdown = document.getElementById('notifDropdown');
const notifBadge    = document.getElementById('notifBadge');
const notifList     = document.getElementById('notifList');
const bellIcon      = document.getElementById('bellIcon');

let lastCount = 0;

// فتح/إغلاق الدروب داون
notifBtn.addEventListener('click', function(e) {
    e.stopPropagation();
    notifDropdown.classList.toggle('show');
    if (notifDropdown.classList.contains('show')) loadNotifications();
});

// إغلاق عند الضغط خارجاً
document.addEventListener('click', function(e) {
    if (!document.getElementById('notifWrapper').contains(e.target)) {
        notifDropdown.classList.remove('show');
    }
});

// ─── تحميل الإشعارات من السيرفر ───
function loadNotifications() {
    fetch('get_notifications.php')
    .then(r => r.json())
    .then(data => {
        // تحديث البادج
        const cnt = data.count;
        if (cnt > 0) {
            notifBadge.style.display = 'flex';
            notifBadge.textContent   = cnt > 99 ? '99+' : cnt;
        } else {
            notifBadge.style.display = 'none';
        }

        // تأثير الجرس عند وصول إشعار جديد
        if (cnt > lastCount && lastCount >= 0) {
            bellIcon.classList.add('bell-ring');
            setTimeout(() => bellIcon.classList.remove('bell-ring'), 700);
            // Browser Notification
            if (cnt > lastCount && Notification.permission === 'granted' && data.notifications.length > 0) {
                const n = data.notifications[0];
                new Notification('🔔 ' + n.title, {
                    body: n.message,
                    icon: './Images/WhatsApp Image 2026-02-17 at 10.48.46 PM.jpeg'
                });
            }
        }
        lastCount = cnt;

        // رسم قائمة الإشعارات
        if (!data.notifications || data.notifications.length === 0) {
            notifList.innerHTML = '<div class="notif-empty"><i class="fas fa-bell-slash"></i>لا توجد إشعارات</div>';
            return;
        }

        const icons = {
            medication_added:    '⏰',
            medication_reminder: '💊',
            consultation_new:    '📋',
            consultation_reply:  '💬',
            order_placed:        '📦',
            order_new:           '🛒',
            order_shipped:       '🚀'
        };

        let html = '';
        data.notifications.forEach(n => {
            const ic  = icons[n.type] || '🔔';
            const cls = n.is_read == '0' ? 'unread' : '';
            const dt  = new Date(n.created_at).toLocaleString('ar-LB');
            html += `<div class="notif-item ${cls}" onclick="markOneRead(${n.id}, this)">
                        <div class="notif-icon">${ic}</div>
                        <div>
                            <div class="notif-title">${n.title}</div>
                            <div class="notif-msg">${n.message}</div>
                            <div class="notif-time"><i class="far fa-clock me-1"></i>${dt}</div>
                        </div>
                     </div>`;
        });
        notifList.innerHTML = html;
    })
    .catch(err => console.log('Notif error:', err));
}

// ─── تحديد إشعار واحد كمقروء ───
function markOneRead(id, el) {
    fetch('mark_read.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'notif_id=' + id
    });
    el.classList.remove('unread');
    const cur = parseInt(notifBadge.textContent) || 0;
    if (cur > 1) { notifBadge.textContent = cur - 1; }
    else { notifBadge.style.display = 'none'; }
    lastCount = Math.max(0, lastCount - 1);
}

// ─── تحديد الكل كمقروء ───
function markAllRead() {
    fetch('mark_read.php', { method: 'POST' });
    document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
    notifBadge.style.display = 'none';
    lastCount = 0;
}

// ─── طلب إذن Browser Notification ───
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}

// ─── فحص دوري كل 30 ثانية ───
loadNotifications();
setInterval(loadNotifications, 30000);
</script>
<?php endif; ?>