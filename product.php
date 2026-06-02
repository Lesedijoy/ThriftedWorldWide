<?php
session_start();
include 'includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: browse.php"); exit; }

$res = mysqli_query($conn,
    "SELECT p.*, u.username, u.first_name, u.last_name, u.province as seller_province
     FROM products p
     JOIN users u ON p.user_id = u.id
     WHERE p.id = $id LIMIT 1");

if (mysqli_num_rows($res) === 0) { header("Location: browse.php"); exit; }
$p = mysqli_fetch_assoc($res);

$isLoggedIn = isset($_SESSION['user_id']);
$isSeller   = $isLoggedIn && $_SESSION['user_id'] == $p['user_id'];

$emoji = '👗';
$catEmojis = ["Women's Clothing"=>'👗',"Men's Clothing"=>'👔',"Sneakers"=>'👟',
              "Bags & Purses"=>'👜',"Accessories"=>'💍',"Outerwear"=>'🧥',"Designer"=>'✨',"Vintage"=>'🕰️'];
$emoji = $catEmojis[$p['category']] ?? '🏷️';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($p['title']) ?> — ThriftedWorldWide</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
.product-page { display:grid; grid-template-columns:1fr 1fr; gap:3rem; padding:3rem 5%; max-width:1100px; margin:0 auto; }
.product-image-box { background:#F2EDE4; border-radius:20px; aspect-ratio:1; display:flex; align-items:center; justify-content:center; font-size:8rem; position:relative; overflow:hidden; }
.product-image-box img { width:100%; height:100%; object-fit:cover; }
.product-meta-box { display:flex; flex-direction:column; gap:1rem; }
.product-condition-badge { display:inline-block; background:#1A1A1A; color:#fff; font-size:0.7rem; font-weight:600; padding:4px 12px; border-radius:100px; }
.product-title { font-family:'Playfair Display',serif; font-size:1.8rem; font-weight:600; line-height:1.2; }
.product-price-big { font-family:'Playfair Display',serif; font-size:2.2rem; font-weight:700; color:var(--rust,#C4622D); }
.product-details-table { background:#F7F3EE; border-radius:12px; padding:1.25rem; display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; }
.detail-item { display:flex; flex-direction:column; gap:2px; }
.detail-label { font-size:0.7rem; font-weight:600; letter-spacing:0.07em; text-transform:uppercase; color:#6B6560; }
.detail-value { font-size:0.9rem; font-weight:500; color:#1A1A1A; }
.seller-card { background:#fff; border:1px solid rgba(0,0,0,0.07); border-radius:14px; padding:1rem 1.25rem; display:flex; align-items:center; gap:1rem; }
.seller-avatar { width:44px; height:44px; border-radius:50%; background:#EDE6DC; display:flex; align-items:center; justify-content:center; font-size:1.2rem; font-weight:700; color:#C4622D; flex-shrink:0; }
.btn-add-cart { width:100%; background:#1A1A1A; color:#fff; padding:15px; border:none; border-radius:100px; font-size:1rem; font-weight:500; cursor:pointer; font-family:'DM Sans',sans-serif; transition:background .2s; }
.btn-add-cart:hover { background:#C4622D; }
.btn-wishlist { width:100%; background:transparent; color:#1A1A1A; padding:13px; border:1.5px solid rgba(0,0,0,0.2); border-radius:100px; font-size:0.95rem; cursor:pointer; font-family:'DM Sans',sans-serif; transition:border-color .2s; }
.btn-wishlist:hover { border-color:#C4622D; color:#C4622D; }
@media(max-width:768px){.product-page{grid-template-columns:1fr;gap:2rem;padding:1.5rem;}}
</style>
</head>
<body>

<!--<nav>

<?php include 'includes/nav.php'; ?>
    <a href="index.php" class="logo">Thrifted<span style="color:#C4622D;">Worldwide</span></a>
    <ul class="nav-links">
        <li><a href="browse.php">Browse</a></li>
        <li><a href="seller-dashboard.php">Sell</a></li>
        <li><a href="cart.php" class="nav-cart">🛒 <span class="cart-badge" id="cartCount">0</span></a></li>
        <?php if ($isLoggedIn): ?>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php" class="btn-primary">Sign Up</a></li>
        <?php endif; ?>
    </ul>
</nav>-->

<div style="padding:1rem 5%;font-size:0.82rem;color:#6B6560;">
    <a href="browse.php" style="color:inherit;text-decoration:none;">← Back to listings</a>
</div>

<div class="product-page">
    <!-- IMAGE -->
    <div>
        <div class="product-image-box">
            <?php if ($p['image'] && file_exists('uploads/products/'.$p['image'])): ?>
                <img src="uploads/products/<?= htmlspecialchars($p['image']) ?>"
                     alt="<?= htmlspecialchars($p['title']) ?>">
            <?php else: ?>
                <?= $emoji ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- INFO -->
    <div class="product-meta-box">
        <span class="product-condition-badge"><?= htmlspecialchars($p['product_condition']) ?></span>
        <h1 class="product-title"><?= htmlspecialchars($p['title']) ?></h1>
        <div class="product-price-big">R <?= number_format($p['price'], 2) ?></div>

        <div class="product-details-table">
            <div class="detail-item">
                <span class="detail-label">Brand</span>
                <span class="detail-value"><?= htmlspecialchars($p['brand']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Category</span>
                <span class="detail-value"><?= htmlspecialchars($p['category']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Size</span>
                <span class="detail-value"><?= htmlspecialchars($p['size']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Colour</span>
                <span class="detail-value"><?= htmlspecialchars($p['colour'] ?: '—') ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Province</span>
                <span class="detail-value"><?= htmlspecialchars($p['province']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Listed</span>
                <span class="detail-value"><?= date('d M Y', strtotime($p['created_at'])) ?></span>
            </div>
        </div>

        <?php if ($p['description']): ?>
        <div style="font-size:0.9rem;line-height:1.7;color:#6B6560;">
            <?= nl2br(htmlspecialchars($p['description'])) ?>
        </div>
        <?php endif; ?>

        <!-- Seller -->
        <div class="seller-card">
            <div class="seller-avatar"><?= strtoupper(substr($p['username'],0,1)) ?></div>
            <div>
                <div style="font-size:0.88rem;font-weight:600;">@<?= htmlspecialchars($p['username']) ?></div>
                <div style="font-size:0.78rem;color:#6B6560;"><?= htmlspecialchars($p['seller_province']) ?></div>
            </div>
        </div>

        <?php if (!$isSeller): ?>
        <button class="btn-add-cart" onclick="addToCart(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['title'])) ?>', <?= $p['price'] ?>)">
            🛒 Add to cart
        </button>
        <button class="btn-wishlist">🤍 Save to wishlist</button>
        <?php else: ?>
        <div style="background:#EDE6DC;border-radius:12px;padding:1rem;text-align:center;font-size:0.88rem;color:#6B6560;">
            This is your listing. <a href="seller-dashboard.php" style="color:#C4622D;">Manage it →</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const cart = JSON.parse(localStorage.getItem('tww_cart') || '[]');
document.getElementById('cartCount').textContent = cart.length;

function addToCart(id, title, price) {
    const cart = JSON.parse(localStorage.getItem('tww_cart') || '[]');
    const existing = cart.find(i => i.id === id);
    if (!existing) {
        cart.push({ id, title, price, qty: 1 });
        localStorage.setItem('tww_cart', JSON.stringify(cart));
    }
    document.getElementById('cartCount').textContent = cart.length;
    const btn = document.querySelector('.btn-add-cart');
    btn.textContent = '✓ Added to cart!';
    btn.style.background = '#4CAF50';
    setTimeout(() => {
        btn.textContent = '🛒 Add to cart';
        btn.style.background = '';
    }, 2000);
}
</script>
</body>
</html>