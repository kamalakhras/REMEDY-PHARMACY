<?php
// ================================================================
// brevo_config.php - إعدادات Brevo SMTP
// ⚠️ تحتاج تعديل سطر واحد فقط: BREVO_USERNAME
// ================================================================

// ← ضع هنا الإيميل الذي سجّلت به في Brevo
define('BREVO_USERNAME', 'a88809001@smtp-brevo.com');

// مفتاح SMTP الخاص بك (لا تشاركه مع أحد)
define('BREVO_PASSWORD', '');

// إعدادات الخادم
define('BREVO_HOST',       'smtp-relay.brevo.com');
define('BREVO_PORT',       587);
define('BREVO_FROM_EMAIL', 'remedypharmcy@gmail.com'); // البريد المُتحقَّق منه في Brevo
define('BREVO_FROM_NAME',  'Remedy Pharmacy');

// بريد الأدمن لاستقبال الإشعارات
define('ADMIN_EMAIL', 'remedypharmcy@gmail.com');
define('SITE_URL',    'http://localhost/RemedyPharmacy');
