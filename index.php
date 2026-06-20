<?php
session_start();
include 'db_config.php';
include ("header.php"); 

// --- 1. محرك الأقسام ---
$cat = isset($_GET['cat']) ? $_GET['cat'] : '';

if ($cat != '') {
    $query = "SELECT * FROM products WHERE category = '$cat' ORDER BY id DESC";
} else {
    $query = "SELECT * FROM products ORDER BY id DESC LIMIT 8";
}
$result = mysqli_query($conn, $query);

// فحص التنبيهات
if (isset($_SESSION['id'])) {
    $u_id = $_SESSION['id'];
    $current_time = date("H:i"); 
    $sql_alert = "SELECT * FROM medication_alerts 
                  WHERE user_id = '$u_id' 
                  AND ABS(TIMEDIFF(alert_time, '$current_time')) < '00:05:00' 
                  LIMIT 1";
    $res_alert = mysqli_query($conn, $sql_alert);

    if ($res_alert && mysqli_num_rows($res_alert) > 0) {
        $alert_row = mysqli_fetch_assoc($res_alert);
        echo '
        <div class="container mt-3">
            <div class="alert alert-danger shadow-lg border-0 d-flex align-items-center" role="alert" style="border-radius: 20px; background: linear-gradient(90deg, #ff4b2b, #ff416c); color: #fff;">
                <i class="fas fa-bell fa-2x me-3 animate-bell"></i>
                <div>
                    <h6 class="fw-bold mb-1">تذكير بموعد الدواء!</h6>
                    <p class="mb-0 small">حان الآن موعد: <strong>' . $alert_row['medication_name'] . '</strong> (' . $alert_row['alert_time'] . ')</p>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
            </div>
        </div>';
    }
}
?>

<style>
    /* لمسات خفيفة ورهيبة */
    .section-title {
        font-weight: 800;
        font-size: 1.4rem;
        position: relative;
        display: inline-block;
        padding-bottom: 8px;
        color: #222;
        margin-bottom: 25px;
    }
    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        right: 0;
        width: 40px;
        height: 4px;
        background: #0d6efd;
        border-radius: 10px;
    }
    
    .cat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none !important;
        border-radius: 20px !important;
        background: #fff;
    }
    .cat-card:hover {
        transform: translateY(-5px);
        background: #0d6efd !important;
        color: #fff !important;
        box-shadow: 0 10px 20px rgba(13, 110, 253, 0.2) !important;
    }
    .cat-card.active {
        background: #0d6efd !important;
        color: #fff !important;
    }

    .product-card {
        border: none !important;
        border-radius: 22px !important;
        background: #fff;
        transition: all 0.3s ease;
        position: relative;
    }
    .product-card:hover {
        box-shadow: 0 15px 35px rgba(0,0,0,0.08) !important;
    }
    .best-seller-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: linear-gradient(45deg, #f12711, #f5af19);
        color: #fff;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 50px;
        z-index: 2;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .price-tag {
        font-size: 1.1rem;
        color: #0d6efd;
        font-weight: 800;
    }
    .add-to-cart-btn {
        border-radius: 12px !important;
        font-weight: 700 !important;
        font-size: 0.85rem !important;
        padding: 10px !important;
        transition: 0.3s;
    }
    
    /* Slow down carousel slide transition animation */
    .carousel-item {
        transition: transform 2s ease-in-out !important;
    }
</style>

<!-- Carousel -->
<div id="pharmacyCarousel" class="carousel slide mb-5 shadow-sm rounded-bottom overflow-hidden" data-bs-ride="carousel" data-bs-interval="12000">
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="Images/slide01.jpg" class="d-block w-100" style="height: 350px; object-fit: cover;" alt="Slide 1">
        </div>
        <div class="carousel-item">
            <img src="Images/slide02.jpg" class="d-block w-100" style="height: 350px; object-fit: cover;" alt="Slide 2">
        </div>
        <div class="carousel-item">
            <img src="Images/slide03.jpg" class="d-block w-100" style="height: 350px; object-fit: cover;" alt="Slide 3">
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="container mb-5">
    <div class="row g-3 text-center">
        <?php 
        $links = [
            ['medication_alerts.php', 'fas fa-clock', 'text-primary', 'Alerts'],
            ['midecal_consultation.php', 'fas fa-comment-medical', 'text-success', 'Consult'],
            ['first_aid.php', 'fas fa-briefcase-medical', 'text-warning', 'First Aid'],
            ['contact.php', 'fas fa-phone', 'text-danger', 'Contact']
        ];
        foreach($links as $l) {
            echo '<div class="col-3">
                    <a href="'.$l[0].'" class="text-decoration-none text-dark d-block">
                        <div class="p-3 border-0 rounded-4 shadow-sm bg-white h-100 transition">
                            <i class="'.$l[1].' '.$l[2].' fs-4 mb-2 d-block"></i>
                            <span class="small fw-bold">'.$l[3].'</span>
                        </div>
                    </a>
                  </div>';
        }
        ?>
    </div>
</div>

<div class="container">
    <!-- Section (Categories) -->
    <div class="mb-5">
        <h5 class="section-title">Categories</h5>
        <div class="row g-2">
            <?php 
            $categories = ["بشرة" => "Skin", "شعر" => "Hair", "مكياج" => "Makeup", "شخصية" => "Personal", "أطفال" => "Kids", "فيتامينات" => "Vitamins"];
            foreach($categories as $name => $val) {
                $isActive = ($cat == $val) ? 'active' : '';
                echo "<div class='col-4 col-md-2'>
                        <a href='index.php?cat=$val#products-section' class='text-decoration-none text-dark'>
                            <div class='card cat-card shadow-sm text-center p-3 h-100 bg-white $isActive'>
                                <div class='small fw-bold'>$name</div>
                            </div>
                        </a>
                      </div>";
            }
            ?>
        </div>
    </div>

    <!-- Products (Best Seller) -->
    <div class="mt-5 mb-5" id="products-section">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="section-title m-0">
                <?php echo ($cat != '') ? "قسم: " . array_search($cat, $categories) : "The Best Seller"; ?>
            </h5>
            <a href="index.php" class="text-primary text-decoration-none small fw-bold">View All</a>
        </div>

        <div class="row g-4">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="col-6 col-md-3">
                        <div class="card product-card h-100 shadow-sm p-2 text-center">
                            <?php if($cat == ''): ?>
                                <span class="best-seller-badge">BEST SELLER</span>
                            <?php endif; ?>
                            
                            <div style="height: 160px; display: flex; align-items: center; justify-content: center;" class="mb-2">
                                <img src="./Images/<?php echo $row['image']; ?>" class="rounded" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                            </div>
                            
                            <div class="card-body p-2 d-flex flex-column">
                                <span class="text-muted mb-1" style="font-size: 0.7rem; text-transform: uppercase;"><?php echo $row['category']; ?></span>
                                <h6 class="fw-bold mb-2 text-dark" style="font-size: 0.9rem; min-height: 40px; overflow: hidden;"><?php echo $row['name']; ?></h6>
                                <p class="price-tag mb-3"><?php echo $row['price']; ?>$</p>
                                
                                <?php if (isset($row['stock']) && $row['stock'] <= 0): ?>
                                    <button class="btn btn-secondary mt-auto shadow-sm disabled" style="border-radius: 12px; font-weight: 700; font-size: 0.85rem; padding: 10px;">
                                        <i class="fas fa-times-circle me-2"></i>نفدت الكمية
                                    </button>
                                <?php else: ?>
                                    <a href="cart_handler.php?add=<?php echo $row['id']; ?>" class="btn btn-primary add-to-cart-btn mt-auto shadow-sm">
                                        <i class="fas fa-shopping-basket me-2"></i>إضافة للسلة
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 text-muted">لا يوجد منتجات في هذا القسم حالياً.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include ("footer.php"); ?>