<?php
session_start();
include 'db_config.php';
include 'header.php';
?>

<style>
/* ── Quantity Stepper ── */
.qty-stepper {
    display: inline-flex;
    align-items: center;
    border: 2px solid #dee2e6;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
    transition: border-color .2s;
}
.qty-stepper:focus-within { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,.15); }
.qty-btn {
    width: 34px; height: 34px;
    border: none; background: transparent;
    font-size: 1.1rem; font-weight: 700; color: #0d6efd;
    cursor: pointer; line-height: 1;
    transition: background .15s;
}
.qty-btn:hover { background: #eef3ff; }
.qty-btn:disabled { color: #ccc; cursor: not-allowed; }
.qty-num {
    width: 44px; text-align: center;
    border: none; outline: none;
    font-weight: 700; font-size: .95rem;
    background: transparent;
}
/* remove arrows from number input */
.qty-num::-webkit-inner-spin-button,
.qty-num::-webkit-outer-spin-button { -webkit-appearance: none; }
.qty-num { -moz-appearance: textfield; }

.stock-hint   { font-size: .72rem; color: #888; display: block; margin-top: 2px; }
.over-warn    { font-size: .78rem; color: #dc3545; font-weight: 600; margin-top: 4px; display:none;
                background:#fff5f5; border:1px solid #f5c6cb; border-radius:8px; padding:4px 8px; }
</style>

<div class="container my-5">

    <?php
    // Show stock error if redirected from checkout
    if (isset($_GET['stock_error']) && !empty($_SESSION['stock_errors'])):
    ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius:16px;">
        <h6 class="fw-bold mb-2"><i class="fas fa-exclamation-triangle me-2"></i>لا يمكن إتمام الطلب - مشكلة في المخزون:</h6>
        <ul class="mb-0 ps-3">
            <?php foreach ($_SESSION['stock_errors'] as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <div class="mt-2 small text-muted">يرجى تعديل الكميات أو إزالة المنتجات غير المتوفرة ثم المحاولة مجدداً.</div>
    </div>
    <?php unset($_SESSION['stock_errors']); endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <h4 class="fw-bold mb-4">🛒 سلة المشتريات</h4>
            <?php if (!empty($_SESSION['cart'])): ?>
                <div class="card shadow-sm border-0 p-3">
                    <table class="table align-middle" id="cartTable">
                        <thead>
                            <tr class="text-muted">
                                <th>المنتج</th>
                                <th>السعر</th>
                                <th>الكمية</th>
                                <th>المجموع</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $grand_total    = 0;
                            $has_stock_error = false;
                            foreach ($_SESSION['cart'] as $id => $qty):
                                $res         = mysqli_query($conn, "SELECT * FROM products WHERE id = '$id'");
                                $product     = mysqli_fetch_assoc($res);
                                $subtotal    = $product['price'] * $qty;
                                $grand_total += $subtotal;
                                $avail_stock = intval($product['stock'] ?? 0);
                                $over_stock  = ($qty > $avail_stock);
                                if ($over_stock) $has_stock_error = true;
                            ?>
                            <tr id="row-<?= $id ?>" class="<?= $over_stock ? 'table-danger' : '' ?>">
                                <td>
                                    <img src="./Images/<?= $product['image'] ?>" width="50" class="rounded me-2">
                                    <span class="fw-bold"><?= htmlspecialchars($product['name']) ?></span>
                                    <?php if ($avail_stock === 0): ?>
                                        <span class="badge bg-danger ms-1">نفد المخزون</span>
                                    <?php elseif ($over_stock): ?>
                                        <span class="badge bg-warning text-dark ms-1">متوفر <?= $avail_stock ?> فقط</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?= $product['price'] ?>$</td>
                                <td>
                                    <!-- ++ Quantity Stepper ++ -->
                                    <div class="qty-stepper" id="stepper-<?= $id ?>">
                                        <button type="button" class="qty-btn"
                                                onclick="changeQty(<?= $id ?>, -1, <?= $avail_stock ?>, <?= $product['price'] ?>)"
                                                id="btn-minus-<?= $id ?>">−</button>
                                        <input  type="number" class="qty-num"
                                                id="qty-<?= $id ?>"
                                                value="<?= $qty ?>"
                                                min="1" max="<?= $avail_stock ?>"
                                                onchange="setQty(<?= $id ?>, this.value, <?= $avail_stock ?>, <?= $product['price'] ?>)">
                                        <button type="button" class="qty-btn"
                                                onclick="changeQty(<?= $id ?>, +1, <?= $avail_stock ?>, <?= $product['price'] ?>)"
                                                id="btn-plus-<?= $id ?>">+</button>
                                    </div>
                                    <span class="stock-hint">متوفر: <?= $avail_stock ?> قطعة</span>
                                    <span class="over-warn" id="warn-<?= $id ?>"
                                          style="<?= $over_stock ? 'display:block' : 'display:none' ?>">
                                        ⚠ الكمية المطلوبة (<?= $qty ?>) غير متوفرة. المتاح: <?= $avail_stock ?> قطعة فقط.
                                    </span>
                                </td>
                                <td class="text-primary fw-bold" id="sub-<?= $id ?>"><?= number_format($subtotal, 2) ?>$</td>
                                <td>
                                    <button onclick="removeItem(<?= $id ?>)"
                                            class="btn btn-sm btn-outline-danger rounded-circle"
                                            style="width:34px;height:34px;"
                                            title="حذف">
                                        <i class="fas fa-trash" style="font-size:.8rem;"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 card border-0 shadow-sm" id="emptyCart">
                    <p class="text-muted">سلتك فارغة حالياً..</p>
                    <a href="index.php" class="btn btn-primary w-25 mx-auto">ابدأ التسوق</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($_SESSION['cart'])): ?>
        <div class="col-lg-4" id="summaryCol">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold mb-3">ملخص الحساب</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span>إجمالي المنتجات:</span>
                    <span id="grandTotal"><?= number_format($grand_total, 2) ?>$</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>التوصيل:</span>
                    <span class="text-success">مجاني</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold">المجموع الكلي:</span>
                    <span class="fw-bold text-primary fs-4" id="grandTotal2"><?= number_format($grand_total, 2) ?>$</span>
                </div>
                <a href="checkout.php"
                   id="checkoutBtn"
                   class="btn <?= $has_stock_error ? 'btn-secondary disabled' : 'btn-success' ?> w-100 py-3 rounded-pill fw-bold"
                   <?= $has_stock_error ? 'aria-disabled="true" tabindex="-1" style="pointer-events:none;opacity:.65;"' : '' ?>>
                    <i class="fas fa-lock me-2"></i>تأكيد الطلب والدفع
                </a>
                <div id="checkoutWarn" class="alert alert-danger mt-3 mb-0 py-2 px-3 small fw-bold text-center"
                     style="border-radius:12px; <?= $has_stock_error ? '' : 'display:none;' ?>">
                    ⚠ يرجى تعديل الكميات غير المتوفرة أولاً لإتمام الطلب.
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// price map for client-side subtotal updates
const prices = {
    <?php
    foreach ($_SESSION['cart'] ?? [] as $id => $qty) {
        $r = mysqli_query($conn, "SELECT price FROM products WHERE id='$id'");
        $p = mysqli_fetch_assoc($r);
        echo "$id: " . floatval($p['price']) . ",\n";
    }
    ?>
};

// stock map: id -> max available stock (injected from PHP)
const maxStocks = {
    <?php
    foreach ($_SESSION['cart'] ?? [] as $id => $qty) {
        $r = mysqli_query($conn, "SELECT stock FROM products WHERE id='$id'");
        $s = mysqli_fetch_assoc($r);
        echo "$id: " . intval($s['stock']) . ",\n";
    }
    ?>
};

function changeQty(id, delta, maxStock, price) {
    const input = document.getElementById('qty-' + id);
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    // No auto-cap: allow any value, warn instead
    input.value = val;
    sendUpdate(id, val, maxStock, price);
}

function setQty(id, val, maxStock, price) {
    val = parseInt(val);
    if (isNaN(val) || val < 1) val = 1;
    // No auto-cap: allow any value, warn instead
    document.getElementById('qty-' + id).value = val;
    sendUpdate(id, val, maxStock, price);
}

function sendUpdate(id, qty, maxStock, price) {
    const plusBtn  = document.getElementById('btn-plus-' + id);
    const minusBtn = document.getElementById('btn-minus-' + id);
    const warn     = document.getElementById('warn-' + id);
    const row      = document.getElementById('row-' + id);

    fetch('cart_handler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'update_qty=1&product_id=' + id + '&qty=' + qty
    })
    .then(r => r.json())
    .then(data => {
        if (!data.ok) return;

        const finalQty = data.qty;
        document.getElementById('qty-' + id).value = finalQty;

        // + button: only disable when exactly at or above max
        if (plusBtn)  plusBtn.disabled  = false;  // keep enabled so user can type
        if (minusBtn) minusBtn.disabled = (finalQty <= 1);

        if (data.over_stock) {
            // Show warning message with available stock
            if (warn) {
                warn.textContent = '⚠ الكمية المطلوبة (' + finalQty + ') غير متوفرة. المتاح: ' + data.max + ' قطعة فقط.';
                warn.style.display = 'block';
            }
            if (row) row.className = 'table-danger';
        } else {
            if (warn) warn.style.display = 'none';
            if (row) row.className = '';
        }

        // Update subtotal for this row
        const unitPrice = prices[id] || price;
        const subEl = document.getElementById('sub-' + id);
        if (subEl) subEl.textContent = (unitPrice * finalQty).toFixed(2) + '$';

        recalcTotal();
        updateCheckoutBtn();
    })
    .catch(e => console.error('qty update error:', e));
}

function updateCheckoutBtn() {
    // Check if any row is highlighted red (over stock)
    const btn  = document.getElementById('checkoutBtn');
    const warn = document.getElementById('checkoutWarn');
    const hasError = document.querySelector('tr.table-danger') !== null;
    if (!btn) return;
    if (hasError) {
        btn.classList.remove('btn-success');
        btn.classList.add('btn-secondary', 'disabled');
        btn.style.opacity = '0.65';
        btn.style.pointerEvents = 'none';
        if (warn) warn.style.display = 'block';
    } else {
        btn.classList.add('btn-success');
        btn.classList.remove('btn-secondary', 'disabled');
        btn.style.opacity = '';
        btn.style.pointerEvents = '';
        if (warn) warn.style.display = 'none';
    }
}

function recalcTotal() {
    let total = 0;
    for (const id in prices) {
        const input = document.getElementById('qty-' + id);
        if (input) total += prices[id] * parseInt(input.value || 1);
    }
    const fmt = total.toFixed(2) + '$';
    const el1 = document.getElementById('grandTotal');
    const el2 = document.getElementById('grandTotal2');
    if (el1) el1.textContent = fmt;
    if (el2) el2.textContent = fmt;
}

function removeItem(id) {
    if (!confirm('حذف هذا المنتج من السلة؟')) return;
    fetch('cart_handler.php?remove=' + id, {
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            const row = document.getElementById('row-' + id);
            if (row) row.remove();
            delete prices[id];
            recalcTotal();
            // If cart is now empty, reload to show empty state
            if (Object.keys(prices).length === 0) location.reload();
        }
    });
}
</script>

<?php include 'footer.php'; ?>