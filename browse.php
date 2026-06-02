<?php
session_start();
include 'includes/db.php';

// ── Filters ──────────────────────────────────────────────
$where   = ["1=1"];
$search  = mysqli_real_escape_string($conn, trim($_GET['q']        ?? ''));
$cat     = mysqli_real_escape_string($conn, trim($_GET['category'] ?? ''));
$cond    = mysqli_real_escape_string($conn, trim($_GET['condition'] ?? ''));
$size    = mysqli_real_escape_string($conn, trim($_GET['size']     ?? ''));
$prov    = mysqli_real_escape_string($conn, trim($_GET['province'] ?? ''));
$minP    = (float)($_GET['min_price'] ?? 0);
$maxP    = (float)($_GET['max_price'] ?? 0);
$sort    = $_GET['sort'] ?? 'newest';

if ($search)   $where[] = "(p.title LIKE '%$search%' OR p.brand LIKE '%$search%' OR p.description LIKE '%$search%')";
if ($cat)      $where[] = "p.category = '$cat'";
if ($cond)     $where[] = "p.product_condition = '$cond'";
if ($size)     $where[] = "p.size = '$size'";
if ($prov)     $where[] = "p.province = '$prov'";
if ($minP > 0) $where[] = "p.price >= $minP";
if ($maxP > 0) $where[] = "p.price <= $maxP";

$orderBy = match($sort) {
    'price_asc'  => "p.price ASC",
    'price_desc' => "p.price DESC",
    default      => "p.created_at DESC",
};

$whereStr = implode(" AND ", $where);
$sql = "SELECT p.*, u.username, u.province as seller_province
        FROM products p
        JOIN users u ON p.user_id = u.id
        WHERE $whereStr
        ORDER BY $orderBy";

$result   = mysqli_query($conn, $sql);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
$total = count($products);

// Category counts
$cats = [];
$cr   = mysqli_query($conn, "SELECT category, COUNT(*) as cnt FROM products GROUP BY category");
while ($c = mysqli_fetch_assoc($cr)) $cats[$c['category']] = $c['cnt'];

$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Browse Listings — ThriftedWorldWide</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'includes/nav.php'; ?>


<!-- HEADER + SEARCH -->
<div class="browse-header">
    <h1>Browse listings</h1>
    <form method="GET" action="browse.php" class="search-bar">
        <input class="search-input" type="text" name="q"
               placeholder="Search for brands, items, styles..."
               value="<?= htmlspecialchars($search) ?>">
        <button class="search-btn" type="submit">Search</button>
    </form>
    <div class="results-count">Showing <?= $total ?> listing<?= $total !== 1 ? 's' : '' ?></div>
</div>

<div class="browse-body">
    <!-- SIDEBAR FILTERS -->
    <div class="sidebar">
        <form method="GET" action="browse.php" id="filterForm">
            <?php if ($search): ?>
                <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
            <?php endif; ?>

            <div class="filter-section">
                <div class="filter-title">Category</div>
                <?php
                $categories = ["Women's Clothing","Men's Clothing","Sneakers","Bags & Purses","Accessories","Outerwear","Designer","Vintage"];
                foreach ($categories as $c):
                    $cnt  = $cats[$c] ?? 0;
                    $chk  = ($cat === $c) ? 'checked' : '';
                ?>
                <div class="filter-option">
                    <input type="radio" name="category" id="cat_<?= md5($c) ?>"
                           value="<?= htmlspecialchars($c) ?>" <?= $chk ?>
                           onchange="this.form.submit()">
                    <label for="cat_<?= md5($c) ?>"><?= htmlspecialchars($c) ?></label>
                    <span class="count"><?= $cnt ?></span>
                </div>
                <?php endforeach; ?>
                <?php if ($cat): ?>
                    <div class="filter-option">
                        <a href="browse.php<?= $search ? '?q='.urlencode($search) : '' ?>"
                           style="font-size:0.75rem;color:var(--accent);">Clear category ×</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="filter-section">
                <div class="filter-title">Condition</div>
                <?php
                $conditions = ['New with tags','Like new','Gently worn','Pre-owned','Vintage'];
                foreach ($conditions as $d):
                    $chk = ($cond === $d) ? 'checked' : '';
                ?>
                <div class="filter-option">
                    <input type="radio" name="condition" id="cond_<?= md5($d) ?>"
                           value="<?= $d ?>" <?= $chk ?> onchange="this.form.submit()">
                    <label for="cond_<?= md5($d) ?>"><?= $d ?></label>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="filter-section">
                <div class="filter-title">Price (ZAR)</div>
                <div class="price-range">
                    <input class="price-input" type="number" name="min_price"
                           placeholder="Min" value="<?= $minP ?: '' ?>">
                    <span class="price-sep">–</span>
                    <input class="price-input" type="number" name="max_price"
                           placeholder="Max" value="<?= $maxP ?: '' ?>">
                </div>
                <button class="apply-btn" type="submit">Apply price filter</button>
            </div>

            <div class="filter-section">
                <div class="filter-title">Province</div>
                <?php
                $provinces = ['Gauteng','Western Cape','KwaZulu-Natal','Eastern Cape','Limpopo','Mpumalanga','North West','Free State','Northern Cape'];
                foreach ($provinces as $pr):
                    $chk = ($prov === $pr) ? 'checked' : '';
                ?>
                <div class="filter-option">
                    <input type="radio" name="province" id="prov_<?= md5($pr) ?>"
                           value="<?= $pr ?>" <?= $chk ?> onchange="this.form.submit()">
                    <label for="prov_<?= md5($pr) ?>"><?= $pr ?></label>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="filter-section">
                <div class="filter-title">Sort by</div>
                <select name="sort" class="sort-select" style="width:100%;margin-top:0.5rem;"
                        onchange="this.form.submit()">
                    <option value="newest"     <?= $sort==='newest'     ? 'selected':'' ?>>Newest first</option>
                    <option value="price_asc"  <?= $sort==='price_asc'  ? 'selected':'' ?>>Price: low to high</option>
                    <option value="price_desc" <?= $sort==='price_desc' ? 'selected':'' ?>>Price: high to low</option>
                </select>
            </div>

            <a href="browse.php" class="clear-btn" style="display:block;text-align:center;text-decoration:none;padding:9px;margin-top:1rem;background:transparent;color:var(--bark);border:1px solid var(--stone);border-radius:100px;font-size:0.82rem;cursor:pointer;">
                Clear all filters
            </a>
        </form>
    </div>

    <!-- MAIN LISTINGS -->
    <div class="main-content">
        <?php if (empty($products)): ?>
            <div class="empty-state" style="text-align:center;padding:4rem;color:var(--stone);">
                <div style="font-size:3rem;margin-bottom:1rem;">🔍</div>
                <p>No listings found. Try different filters.</p>
                <a href="browse.php" style="color:var(--accent);margin-top:1rem;display:inline-block;">Clear all filters →</a>
            </div>
        <?php else: ?>
            <div class="listings-grid">
                <?php foreach ($products as $p):
                    $img     = $p['image'] ? 'uploads/products/' . htmlspecialchars($p['image']) : '';
                    $emoji   = '👗';
                    $catEmojis = ["Women's Clothing"=>'👗',"Men's Clothing"=>'👔',"Sneakers"=>'👟',"Bags & Purses"=>'👜',"Accessories"=>'💍',"Outerwear"=>'🧥',"Designer"=>'✨',"Vintage"=>'🕰️'];
                    $emoji   = $catEmojis[$p['category']] ?? '🏷️';
                    $badgeColors = ['New with tags'=>'badge-nwt','Like new'=>'badge-nwt','Gently worn'=>'badge-worn','Pre-owned'=>'badge-worn','Vintage'=>'badge-vintage'];
                    $badgeClass  = $badgeColors[$p['product_condition']] ?? 'badge-worn';
                ?>
                <div class="listing-card" onclick="window.location='product.php?id=<?= $p['id'] ?>'">
                    <div class="listing-img" style="background:#F2EDE4;position:relative;">
                        <?php if ($img && file_exists($img)): ?>
                            <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['title']) ?>"
                                 style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <span style="font-size:4rem;"><?= $emoji ?></span>
                        <?php endif; ?>
                        <span class="listing-badge <?= $badgeClass ?>"><?= htmlspecialchars($p['product_condition']) ?></span>
                        <span class="listing-heart" onclick="toggleWishlist(event,this,<?= $p['id'] ?>)">🤍</span>
                    </div>
                    <div class="listing-info">
                        <div class="listing-brand"><?= htmlspecialchars($p['brand']) ?></div>
                        <div class="listing-name"><?= htmlspecialchars($p['title']) ?></div>
                        <div class="listing-meta">
                            <span class="listing-price">R <?= number_format($p['price'], 0, '.', ' ') ?></span>
                            <span class="listing-cond"><?= htmlspecialchars($p['size']) ?></span>
                        </div>
                        <div class="listing-seller">@<?= htmlspecialchars($p['username']) ?> · <?= htmlspecialchars($p['province']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Cart badge
const cart = JSON.parse(localStorage.getItem('tww_cart') || '[]');
document.getElementById('cartCount').textContent = cart.length;

function toggleWishlist(e, el, productId) {
    e.stopPropagation();
    <?php if (!$isLoggedIn): ?>
        window.location = 'login.php';
        return;
    <?php endif; ?>
    el.textContent = el.textContent === '🤍' ? '❤️' : '🤍';
}
</script>
</body>
</html>