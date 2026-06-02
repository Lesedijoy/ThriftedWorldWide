<?php
session_start();
include 'includes/db.php';
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart & Checkout — ThriftedWorldWide</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="cart.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'includes/nav.php'; ?>
   

<!-- STEP TABS -->
<div class="checkout-tabs">
    <div class="checkout-tabs-inner">
        <div class="checkout-tab active" id="t1" onclick="goStep(1)">1. Cart</div>
        <div class="checkout-tab" id="t2" onclick="goStep(2)">2. Delivery</div>
        <div class="checkout-tab" id="t3" onclick="goStep(3)">3. Payment</div>
        <div class="checkout-tab" id="t4">4. Confirmation</div>
    </div>
</div>

<!-- STEP 1: CART -->
<div id="cs1" class="checkout-wrap">
    <div class="checkout-grid">
        <div>
            <div class="checkout-title">Your cart <span id="cartCountTitle"></span></div>
            <div class="cart-list" id="cartItems"></div>
            <a href="browse.php" class="back-link">← Continue shopping</a>
        </div>
        <div>
            <div class="summary-card">
                <div class="summary-title">Order summary</div>
                <div class="summary-rows">
                    <div class="summary-row"><span class="label">Subtotal</span><span id="summarySubtotal">R 0</span></div>
                    <div class="summary-row"><span class="label">Shipping</span><span id="summaryShipping">R 79</span></div>
                    <div class="summary-row"><span class="label">Buyer protection</span><span style="color:#4CAF50;">Free</span></div>
                </div>
                <div class="promo-row">
                    <input class="promo-input" type="text" placeholder="Promo code" id="promoInput">
                    <button class="promo-btn" onclick="applyPromo()">Apply</button>
                </div>
                <div class="promo-msg" id="promoMsg" style="display:none;"></div>
                <div class="summary-divider"></div>
                <div class="summary-total">
                    <span>Total</span>
                    <span class="summary-total-amount" id="summaryTotal">R 0</span>
                </div>
                <button class="btn-checkout-next" style="width:100%;" onclick="proceedToDelivery()">Proceed to delivery →</button>
                <div class="trust-mini">
                    <span>🔒 Secure checkout</span>
                    <span>🛡️ Buyer protected</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STEP 2: DELIVERY -->
<div id="cs2" class="checkout-wrap" style="display:none;">
    <div class="checkout-grid">
        <div>
            <div class="checkout-title">Delivery details</div>
            <div class="form-card-checkout">
                <div class="two-col-checkout">
                    <div class="form-group-checkout"><label>First name</label>
                        <input type="text" id="del_first" placeholder="Lesedi"
                               value="<?= htmlspecialchars($_SESSION['full_name'] ?? '') ?>"></div>
                    <div class="form-group-checkout"><label>Last name</label>
                        <input type="text" id="del_last" placeholder="Dlamini"></div>
                </div>
                <div class="form-group-checkout"><label>Street address</label>
                    <input type="text" id="del_street" placeholder="123 Main Street, Hatfield"></div>
                <div class="two-col-checkout">
                    <div class="form-group-checkout"><label>City</label>
                        <input type="text" id="del_city" placeholder="Pretoria"></div>
                    <div class="form-group-checkout"><label>Postal code</label>
                        <input type="text" id="del_postal" placeholder="0001"></div>
                </div>
                <div class="form-group-checkout">
                    <label>Province</label>
                    <select id="del_province">
                        <option>Gauteng</option><option>Western Cape</option><option>KwaZulu-Natal</option>
                        <option>Eastern Cape</option><option>Limpopo</option><option>Mpumalanga</option>
                        <option>North West</option><option>Free State</option><option>Northern Cape</option>
                    </select>
                </div>
                <div class="form-group-checkout"><label>Phone number</label>
                    <input type="tel" id="del_phone" placeholder="+27 82 000 0000"></div>

                <div style="font-size:0.9rem;font-weight:500;margin-top:0.5rem;">Shipping method</div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <label class="ship-option selected" id="ship1" onclick="selectShipping(this,'79')">
                        <div class="ship-option-left"><input type="radio" name="ship" checked>
                            <div><div class="ship-option-name">🚚 Pargo</div><div class="ship-option-time">3–5 business days</div></div></div>
                        <span class="ship-option-price">R 79</span>
                    </label>
                    <label class="ship-option" id="ship2" onclick="selectShipping(this,'110')">
                        <div class="ship-option-left"><input type="radio" name="ship">
                            <div><div class="ship-option-name">📦 Courier Guy</div><div class="ship-option-time">2–3 business days</div></div></div>
                        <span class="ship-option-price">R 110</span>
                    </label>
                    <label class="ship-option" id="ship3" onclick="selectShipping(this,'0')">
                        <div class="ship-option-left"><input type="radio" name="ship">
                            <div><div class="ship-option-name">🤝 Local pickup</div><div class="ship-option-time">Pretoria CBD</div></div></div>
                        <span class="ship-option-price free">Free</span>
                    </label>
                </div>
            </div>
            <div class="checkout-nav">
                <button class="btn-checkout-back" onclick="goStep(1)">← Back</button>
                <button class="btn-checkout-next" onclick="goStep(3)">Continue to payment →</button>
            </div>
        </div>
        <div>
            <div class="summary-card">
                <div class="summary-title">Order summary</div>
                <div class="summary-rows">
                    <div class="summary-row"><span class="label">Subtotal</span><span id="sub2">R 0</span></div>
                    <div class="summary-row"><span class="label">Shipping</span><span id="ship2cost">R 79</span></div>
                </div>
                <div class="summary-divider"></div>
                <div class="summary-total"><span>Total</span><span class="summary-total-amount" id="total2">R 0</span></div>
            </div>
        </div>
    </div>
</div>

<!-- STEP 3: PAYMENT -->
<div id="cs3" class="checkout-wrap" style="display:none;">
    <div class="checkout-grid">
        <div>
            <div class="checkout-title">Payment</div>
            <div class="form-card-checkout">
                <div class="pay-methods">
                    <button class="pay-method-btn active" id="payCard" onclick="selectPay('card')">💳 Card</button>
                    <button class="pay-method-btn" id="payEft" onclick="selectPay('eft')">🏦 EFT</button>
                    <button class="pay-method-btn" id="paySnap" onclick="selectPay('snap')">📱 SnapScan</button>
                </div>
                <div id="cardForm" style="display:flex;flex-direction:column;gap:1rem;">
                    <div class="form-group-checkout"><label>Name on card</label>
                        <input type="text" placeholder="Lesedi Dlamini"></div>
                    <div class="form-group-checkout"><label>Card number</label>
                        <input type="text" placeholder="1234 5678 9012 3456" maxlength="19" oninput="formatCard(this)"></div>
                    <div class="two-col-checkout">
                        <div class="form-group-checkout"><label>Expiry date</label>
                            <input type="text" placeholder="MM / YY" maxlength="7"></div>
                        <div class="form-group-checkout"><label>CVV</label>
                            <input type="text" placeholder="123" maxlength="3"></div>
                    </div>
                </div>
                <div id="eftForm" style="display:none;" class="eft-details">
                    <div class="eft-title">Bank transfer details</div>
                    <div class="eft-row"><span>Bank:</span><span>FNB</span></div>
                    <div class="eft-row"><span>Account:</span><span>62 123 456 789</span></div>
                    <div class="eft-row"><span>Branch code:</span><span>250655</span></div>
                    <div class="eft-row"><span>Reference:</span><span class="ref" id="eftRef">TWW-000000</span></div>
                </div>
                <div id="snapForm" style="display:none;text-align:center;padding:1rem;">
                    <div style="font-size:3rem;">📱</div>
                    <p style="font-size:0.9rem;color:var(--bark);margin-top:0.75rem;">Open SnapScan and scan the QR code to pay <strong id="snapTotal">R 0</strong></p>
                </div>
                <div class="security-note">🔒 Your payment is secured by 256-bit SSL encryption and held in escrow until you confirm delivery.</div>
            </div>
            <div class="checkout-nav">
                <button class="btn-checkout-back" onclick="goStep(2)">← Back</button>
                <button class="btn-checkout-pay" onclick="placeOrder()">Place order →</button>
            </div>
        </div>
        <div>
            <div class="summary-card">
                <div class="summary-title">Order summary</div>
                <div class="summary-rows">
                    <div class="summary-row"><span class="label">Subtotal</span><span id="sub3">R 0</span></div>
                    <div class="summary-row"><span class="label">Shipping</span><span id="ship3">R 79</span></div>
                </div>
                <div class="summary-divider"></div>
                <div class="summary-total"><span>Total</span><span class="summary-total-amount" id="total3">R 0</span></div>
            </div>
        </div>
    </div>
</div>

<!-- STEP 4: CONFIRMATION -->
<div id="cs4" class="confirm-wrap" style="display:none;">
    <div class="confirm-emoji">🎉</div>
    <h2 class="confirm-title">Order confirmed!</h2>
    <p class="confirm-text">Your order <strong id="orderRef"></strong> has been placed. The seller has been notified and payment is held safely in escrow until you confirm delivery.</p>
    <div class="confirm-steps">
        <div style="font-size:0.85rem;font-weight:500;margin-bottom:1rem;">What happens next?</div>
        <div class="confirm-step"><span class="confirm-step-num">1</span> Seller packs and ships your item within 2 business days.</div>
        <div class="confirm-step"><span class="confirm-step-num">2</span> You receive tracking info via email and SMS.</div>
        <div class="confirm-step"><span class="confirm-step-num">3</span> Once you confirm delivery, payment is released to the seller.</div>
    </div>
    <div class="confirm-btns">
        <a href="browse.php" class="btn-checkout-next" style="text-decoration:none;">Continue shopping</a>
    </div>
</div>

<script>
// ── State ──────────────────────────────────────────────
let cartData     = JSON.parse(localStorage.getItem('tww_cart') || '[]');
let shippingCost = 79;
let discount     = 0;

// ── Init ───────────────────────────────────────────────
renderCart();
updateNav();

function updateNav() {
    document.getElementById('cartCount').textContent = cartData.length;
}

function subtotal() {
    return cartData.reduce((s, i) => s + i.price * (i.qty || 1), 0);
}

function total(ship) {
    return subtotal() + (ship ?? shippingCost) - discount;
}

function fmt(n) { return 'R ' + Math.round(n).toLocaleString('en-ZA'); }

function renderCart() {
    const el = document.getElementById('cartItems');
    const ct = document.getElementById('cartCountTitle');
    if (!cartData.length) {
        el.innerHTML = '<div style="text-align:center;padding:3rem;color:#6B6560;">Your cart is empty. <a href="browse.php" style="color:#C4622D;">Browse listings →</a></div>';
        ct.textContent = '(0 items)';
    } else {
        ct.textContent = `(${cartData.length} item${cartData.length!==1?'s':''})`;
        el.innerHTML = cartData.map((item,i) => `
          <div class="cart-item">
            <div class="cart-item-img" style="background:#F2EDE4;font-size:2rem;">🛍️</div>
            <div class="cart-item-info">
              <div class="cart-item-name">${item.title}</div>
              <div class="cart-item-meta">R ${item.price}</div>
            </div>
            <div class="qty-wrap">
              <button class="qty-btn" onclick="changeQty(${i},-1)">−</button>
              <span class="qty-val">${item.qty||1}</span>
              <button class="qty-btn" onclick="changeQty(${i},1)">+</button>
            </div>
            <div class="cart-item-price">${fmt(item.price*(item.qty||1))}</div>
            <button class="cart-item-remove" onclick="removeItem(${i})">✕</button>
          </div>`).join('');
    }
    updateTotals();
}

function changeQty(i, d) {
    cartData[i].qty = Math.max(1, (cartData[i].qty||1) + d);
    save();
    renderCart();
}

function removeItem(i) {
    cartData.splice(i,1);
    save();
    renderCart();
    updateNav();
}

function save() {
    localStorage.setItem('tww_cart', JSON.stringify(cartData));
}

function updateTotals() {
    const sub = subtotal();
    const tot = total();
    document.getElementById('summarySubtotal').textContent = fmt(sub);
    document.getElementById('summaryShipping').textContent = shippingCost === 0 ? 'Free' : fmt(shippingCost);
    document.getElementById('summaryTotal').textContent = fmt(tot);
    // also sync other steps
    ['sub2','sub3'].forEach(id => {const e=document.getElementById(id);if(e)e.textContent=fmt(sub);});
    ['total2','total3'].forEach(id => {const e=document.getElementById(id);if(e)e.textContent=fmt(tot);});
    ['ship3'].forEach(id => {const e=document.getElementById(id);if(e)e.textContent=shippingCost===0?'Free':fmt(shippingCost);});
    const snap=document.getElementById('snapTotal');if(snap)snap.textContent=fmt(tot);
}

function applyPromo() {
    const code = document.getElementById('promoInput').value.trim().toUpperCase();
    const msg  = document.getElementById('promoMsg');
    msg.style.display='block';
    if (code === 'THRIFT50') {
        discount=50; msg.className='promo-msg success'; msg.textContent='✓ R 50 off applied!'; updateTotals();
    } else {
        msg.className='promo-msg error'; msg.textContent='✗ Invalid promo code. Try THRIFT50';
    }
}

function proceedToDelivery() {
    <?php if (!$isLoggedIn): ?>
        if (!confirm('You need to be logged in to checkout. Go to login?')) return;
        window.location = 'login.php';
        return;
    <?php endif; ?>
    if (!cartData.length) { alert('Your cart is empty.'); return; }
    goStep(2);
}

function goStep(n) {
    [1,2,3,4].forEach(i => {
        document.getElementById('cs'+i).style.display = i===n ? 'block' : 'none';
        const t = document.getElementById('t'+i);
        if (t) t.className = 'checkout-tab' + (i===n?' active':i<n?' done':'');
    });
    window.scrollTo({top:0,behavior:'smooth'});
    updateTotals();
}

function selectShipping(el, cost) {
    ['ship1','ship2','ship3'].forEach(id => document.getElementById(id).classList.remove('selected'));
    el.classList.add('selected');
    shippingCost = parseInt(cost)||0;
    document.getElementById('ship2cost').textContent = shippingCost===0?'Free':fmt(shippingCost);
    updateTotals();
}

function selectPay(type) {
    ['card','eft','snap'].forEach(t => {
        const cap = t.charAt(0).toUpperCase()+t.slice(1);
        document.getElementById('pay'+cap).classList.remove('active');
        document.getElementById(t+'Form').style.display='none';
    });
    const cap = type.charAt(0).toUpperCase()+type.slice(1);
    document.getElementById('pay'+cap).classList.add('active');
    const form = document.getElementById(type+'Form');
    form.style.display = type==='card'?'flex':'block';
    if (type==='card') form.style.flexDirection='column';
}

function formatCard(el) {
    let v = el.value.replace(/\D/g,'').substring(0,16);
    el.value = v.replace(/(.{4})/g,'$1 ').trim();
}

function placeOrder() {
    // Generate order reference and save order via AJAX
    const ref = 'TWW-' + Math.floor(10000+Math.random()*90000);
    document.getElementById('orderRef').textContent = '#' + ref;

    // Send order to server
    const orderData = {
        cart: cartData,
        shipping: shippingCost,
        total: total(),
        ref: ref
    };
    fetch('place-order.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(orderData)
    }).then(() => {
        localStorage.removeItem('tww_cart');
        cartData = [];
        goStep(4);
    }).catch(() => {
        // Still show confirmation even on network error
        localStorage.removeItem('tww_cart');
        cartData = [];
        goStep(4);
    });
}
</script>
</body>
</html>