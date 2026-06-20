<footer class="mt-5 shadow-lg" style="background-color: #ffffff; border-top: 3px solid #007bff; padding-top: 40px; border-radius: 40px 40px 0 0;">
    <div class="container">
        <div class="row text-md-end text-center justify-content-between">

            <div class="col-md-5 mb-4">
                <img src="./Images/WhatsApp Image 2026-02-17 at 10.48.46 PM.jpeg" alt="Logo" width="100" class="mb-3">
                <h5 class="fw-bold text-primary">Remedy Pharmacy</h5>
                <p class="text-muted small">
                    Your reliable pharmacy in Tripoli, Lebanon, we are here to serve you and provide the best healthcare to our community.
                </p>
                <div class="mt-3">
                    <a href="https://facebook.com" class="text-primary me-3 text-decoration-none"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="https://wa.me/96170340715" class="text-success me-3 text-decoration-none"><i class="fab fa-whatsapp fa-lg"></i></a>
                    <a href="#" class="text-info text-decoration-none"><i class="fab fa-linkedin fa-lg"></i></a>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2"> Technical support</h6>
                <ul class="list-unstyled text-muted small">
                    <li class="mb-2"><i class="fas fa-map-marker-alt text-primary me-2"></i>  TRIPOLI,LEBANON</li>
                    <li class="mb-2"><i class="fas fa-phone text-primary me-2"></i> +963968865952</li>
                    <li class="mb-2"><i class="fas fa-envelope text-primary me-2"></i> remedypharmcy@gmail.com</li>
                    <li class="mt-3"><a href="faq.php" class="btn btn-outline-primary btn-sm rounded-pill px-4"> Frequently asked question FAQ</a></li>
                </ul>
            </div>
        </div>

        <hr class="my-4" style="opacity: 0.1;">

        <div class="row pb-4 align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 small text-muted">
                    Copyright &copy; 2026 <span class="fw-bold text-primary">Remedy pharmacy</span>. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                <a href="t&c.php" class="text-decoration-none text-muted small me-3 hover-link"> Terms and conditions and Pharmacy</a>
                <a href="aboutUs.php" class="text-decoration-none text-muted small hover-link"></a>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Medication Alert Modal -->
<div class="modal fade" id="medicationAlertModal" tabindex="-1" aria-hidden="true" style="font-family: 'Cairo', sans-serif;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">
            <div class="modal-header border-0 text-white text-center d-block py-4" style="background: linear-gradient(135deg, #ff4b2b 0%, #ff416c 100%);">
                <div class="mb-2" style="font-size: 3rem; animation: pulseAlert 1.5s infinite;">⏰</div>
                <h4 class="modal-title fw-bold" id="medicationAlertTitle">تذكير بموعد الدواء!</h4>
            </div>
            <div class="modal-body text-center p-4">
                <p class="fs-5 mb-1" style="color: #333;">حان الآن موعد تناول دواءك:</p>
                <h3 class="fw-bold text-danger my-3" id="medicationAlertName" style="letter-spacing: 0.5px;"></h3>
                <div class="badge bg-light text-dark fs-6 border px-3 py-2" style="border-radius: 12px;">
                    <i class="far fa-clock text-danger me-1"></i> <span id="medicationAlertTime"></span>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-danger btn-lg px-5 fw-bold" data-bs-dismiss="modal" style="border-radius: 15px; box-shadow: 0 5px 15px rgba(255, 75, 43, 0.4);">
                    تم تناول الدواء 👍
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulseAlert {
    0% { transform: scale(1); }
    50% { transform: scale(1.15); }
    100% { transform: scale(1); }
}
.modal {
    backdrop-filter: blur(5px);
}
</style>

<script>
// 1. نظام تنبيهات الدواء الخاص بك
function startMedicationCheck() {
    console.log("Checking Medication Alerts...");
    fetch('check_time_api.php')
    .then(response => response.json())
    .then(data => {
        if (data.alert === true) {
            // تفادي التنبيه المتكرر في نفس اليوم والدقيقة
            let today = new Date().toISOString().split('T')[0];
            let alertKey = 'shown_' + data.alert_id + '_' + today + '_' + data.alert_time;
            
            if (sessionStorage.getItem(alertKey)) {
                return; // تم التنبيه مسبقاً
            }
            sessionStorage.setItem(alertKey, 'true');

            // تعبئة البيانات في المودال وعرضه
            document.getElementById('medicationAlertName').textContent = data.med_name;
            document.getElementById('medicationAlertTime').textContent = data.alert_time;
            
            let medModal = new bootstrap.Modal(document.getElementById('medicationAlertModal'));
            medModal.show();

            // تشغيل الصوت مرة واحدة فقط
            var audio = new Audio('https://notificationsounds.com/storage/sounds/file-sounds-1150-pristine.mp3');
            audio.play().catch(e => console.log("Sound enabled after user interaction."));
        }
    })
    .catch(error => console.error('API Error:', error));
}
setInterval(startMedicationCheck, 10000); // كل 10 ثواني (أسرع وأدق)
window.addEventListener('load', startMedicationCheck);

// 2. كود تشغيل السلايدر (Carousel) وتصحيح السرعة
document.addEventListener('DOMContentLoaded', function () {
    var myCarouselElement = document.querySelector('#pharmacyCarousel');
    if (myCarouselElement) {
        var carousel = new bootstrap.Carousel(myCarouselElement, {
          interval: 300, // التقليب كل 3 ثواني (الرقم 10 كان يمنع الحركة)
          ride: 'carousel',
          pause: 'hover'
        });
    }
});
</script>

<style>
    .hover-link:hover { color: #007bff !important; text-decoration: underline !important; }
</style>
<script>
$(document).ready(function() {
    $('.add-to-cart-btn').click(function(e) {
        e.preventDefault(); // منع فتح صفحة جديدة
        
        var url = $(this).attr('href');
        var btn = $(this);

        // تأثير بصري بسيط
        btn.html('<i class="fas fa-spinner fa-spin"></i>').addClass('disabled');

        $.ajax({
            url: url,
            type: 'GET',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            success: function(newCount) {
                // تحديث العداد في الهيدر
                $('#cart-badge').text(newCount);
                
                // تغيير شكل الزر لتأكيد الإضافة
                btn.html('تمت الإضافة ✔').removeClass('btn-primary').addClass('btn-success');
                
                // إعادة الزر لوضعه الطبيعي بعد ثانية
                setTimeout(function() {
                    btn.html('إضافة للسلة').removeClass('btn-success disabled').addClass('btn-primary');
                }, 1000);
            }
        });
    });
});
</script>
<script>
// تسجيل Service Worker للإشعارات
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('./sw.js').catch(err => console.log('SW:', err));
}
</script>
</body>
</html>