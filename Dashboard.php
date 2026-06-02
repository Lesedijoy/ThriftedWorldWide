<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); exit;
}
$uid = (int)$_SESSION['user_id'];

// ── Load current user ──────────────────────────────────────────────────────
$userRes  = mysqli_query($conn, "SELECT * FROM users WHERE id=$uid LIMIT 1");
$user     = mysqli_fetch_assoc($userRes);

// ── My listings ───────────────────────────────────────────────────────────
$listingsRes = mysqli_query($conn,
    "SELECT p.*,
            (SELECT COUNT(*) FROM orders WHERE product_id=p.id AND status='pending') as pending_requests,
            (SELECT COUNT(*) FROM orders WHERE product_id=p.id) as total_orders
     FROM products p WHERE p.user_id=$uid ORDER BY p.created_at DESC");
$myListings = [];
while ($r = mysqli_fetch_assoc($listingsRes)) $myListings[] = $r;

// ── Buy requests ON my listings ──────────────────────────────────────────
$requestsRes = mysqli_query($conn,
    "SELECT o.*, p.title as product_title, p.price as product_price, p.image as product_image,
            p.category, u.first_name, u.last_name, u.username, u.email, u.phone, u.province as buyer_province
     FROM orders o
     JOIN products p ON o.product_id = p.id
     JOIN users u ON o.buyer_id = u.id
     WHERE p.user_id = $uid
     ORDER BY o.created_at DESC");
$buyRequests = [];
while ($r = mysqli_fetch_assoc($requestsRes)) $buyRequests[] = $r;

// ── My own orders (things I bought) ──────────────────────────────────────
$myOrdersRes = mysqli_query($conn,
    "SELECT o.*, p.title as product_title, p.price as product_price,
            p.image as product_image, p.category, p.brand,
            u.username as seller_username, u.first_name as seller_first, u.last_name as seller_last
     FROM orders o
     JOIN products p ON o.product_id = p.id
     JOIN users u ON p.user_id = u.id
     WHERE o.buyer_id = $uid
     ORDER BY o.created_at DESC");
$myOrders = [];
while ($r = mysqli_fetch_assoc($myOrdersRes)) $myOrders[] = $r;

// ── Stats ─────────────────────────────────────────────────────────────────
$totalListings     = count($myListings);
$totalBuyRequests  = count($buyRequests);
$pendingRequests   = count(array_filter($buyRequests, fn($r) => $r['status'] === 'pending'));
$totalRevenue      = array_sum(array_column(array_filter($buyRequests, fn($r) => $r['status']==='completed'), 'total'));
$totalSpent        = array_sum(array_column($myOrders, 'total'));

// ── Handle status update (seller accepting/rejecting) ────────────────────
if (isset($_POST['update_request'])) {
    $oid    = (int)$_POST['order_id'];
    $status = mysqli_real_escape_string($conn, $_POST['new_status']);
    // Make sure this order is on one of MY products
    mysqli_query($conn,
        "UPDATE orders o JOIN products p ON o.product_id=p.id
         SET o.status='$status' WHERE o.id=$oid AND p.user_id=$uid");
    header("Location: dashboard.php#requests"); exit;
}

// ── Emojis ────────────────────────────────────────────────────────────────
$catEmojis = ["Women's Clothing"=>'👗',"Men's Clothing"=>'👔',"Sneakers"=>'👟',
              "Bags & Purses"=>'👜',"Accessories"=>'💍',"Outerwear"=>'🧥',"Designer"=>'✨',"Vintage"=>'🕰️'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Dashboard — ThriftedWorldWide</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
/* ── DASHBOARD LAYOUT ── */
.dash-wrap { max-width: 1140px; margin: 0 auto; padding: 2.5rem 2rem 4rem; }

/* ── HERO / PROFILE BANNER ── */
.dash-banner {
    background: linear-gradient(135deg, #1A1A1A 0%, #2E2E2E 100%);
    border-radius: 20px;
    padding: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}
.dash-banner::before {
    content: 'tw';
    font-family: 'Playfair Display', serif;
    font-size: 14rem;
    font-weight: 900;
    color: rgba(255,255,255,0.04);
    position: absolute;
    right: -1rem;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    line-height: 1;
}
.dash-profile-left { display: flex; align-items: center; gap: 1.5rem; position: relative; z-index: 1; }
.dash-big-avatar {
    width: 80px; height: 80px; border-radius: 50%;
    background: #EDE6DC; color: #C4622D;
    font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    border: 3px solid rgba(255,255,255,0.2); object-fit: cover; flex-shrink: 0;
}
.dash-profile-name { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 600; color: #fff; }
.dash-profile-username { font-size: 0.85rem; color: rgba(255,255,255,0.55); margin-top: 3px; }
.dash-profile-province { font-size: 0.8rem; color: rgba(255,255,255,0.4); margin-top: 2px; }
.dash-role-badge {
    display: inline-block; margin-top: 8px;
    background: rgba(196,98,45,0.3); color: #F09365;
    border: 1px solid rgba(196,98,45,0.4);
    font-size: 0.7rem; font-weight: 600; letter-spacing: 0.07em; text-transform: uppercase;
    padding: 3px 12px; border-radius: 100px;
}
.dash-banner-actions { display: flex; gap: 10px; position: relative; z-index: 1; flex-wrap: wrap; }
.btn-banner { padding: 10px 22px; border-radius: 100px; font-size: 0.85rem; font-weight: 500;
    font-family: 'DM Sans', sans-serif; text-decoration: none; cursor: pointer; border: none; transition: all .2s; }
.btn-banner-primary { background: #C4622D; color: #fff; }
.btn-banner-primary:hover { background: #A85126; }
.btn-banner-ghost { background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2); }
.btn-banner-ghost:hover { background: rgba(255,255,255,0.18); }

/* ── STATS ROW ── */
.dash-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 2.5rem; }
.stat-card {
    background: #fff; border: 1px solid rgba(0,0,0,0.07); border-radius: 16px;
    padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 4px;
}
.stat-label { font-size: 0.72rem; font-weight: 600; letter-spacing: 0.07em; text-transform: uppercase; color: #6B6560; }
.stat-value { font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 700; color: #1A1A1A; line-height: 1; margin-top: 4px; }
.stat-sub { font-size: 0.75rem; color: #6B6560; margin-top: 2px; }
.stat-card.highlight { border-color: rgba(196,98,45,0.25); }
.stat-card.highlight .stat-value { color: #C4622D; }

/* ── SECTION ── */
.dash-section { margin-bottom: 3rem; }
.dash-section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
.dash-section-title { font-family: 'Playfair Display', serif; font-size: 1.3rem; font-weight: 600; color: #1A1A1A; }
.dash-section-title em { font-style: italic; color: #C4622D; }
.section-link { font-size: 0.82rem; color: #C4622D; text-decoration: none; }
.section-link:hover { text-decoration: underline; }

/* ── LISTINGS GRID ── */
.listings-grid-dash { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
.listing-dash-card {
    background: #fff; border: 1px solid rgba(0,0,0,0.07); border-radius: 16px; overflow: hidden;
    transition: box-shadow .2s, transform .2s; cursor: pointer;
}
.listing-dash-card:hover { box-shadow: 0 8px 28px rgba(0,0,0,0.08); transform: translateY(-2px); }
.listing-dash-thumb {
    height: 160px; display: flex; align-items: center; justify-content: center;
    font-size: 4rem; background: #F7F3EE; position: relative; overflow: hidden;
}
.listing-dash-thumb img { width: 100%; height: 100%; object-fit: cover; }
.listing-dash-body { padding: 1rem; }
.listing-dash-brand { font-size: 0.68rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.07em; color: #6B6560; margin-bottom: 3px; }
.listing-dash-title { font-size: 0.9rem; font-weight: 500; color: #1A1A1A;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 8px; }
.listing-dash-meta { display: flex; justify-content: space-between; align-items: center; }
.listing-dash-price { font-family: 'Playfair Display', serif; font-size: 1rem; font-weight: 600; color: #1A1A1A; }
.listing-req-badge {
    font-size: 0.68rem; font-weight: 600; padding: 3px 9px; border-radius: 100px;
    background: #FDEEE8; color: #C4622D;
}
.listing-req-badge.zero { background: #F2EDE4; color: #6B6560; }
.listing-dash-actions { display: flex; gap: 6px; margin-top: 10px; }
.btn-dash-sm {
    flex: 1; padding: 7px; border-radius: 100px; font-size: 0.75rem; font-weight: 500;
    font-family: 'DM Sans', sans-serif; border: none; cursor: pointer; text-align: center;
    text-decoration: none; transition: background .2s;
}
.btn-dash-view { background: #EDE6DC; color: #1A1A1A; }
.btn-dash-view:hover { background: #DDD6CC; }
.btn-dash-del { background: #FDEEEE; color: #C43D3D; }
.btn-dash-del:hover { background: #F9D9D9; }

/* ── BUY REQUESTS TABLE ── */
.requests-table { width: 100%; border-collapse: collapse; }
.requests-table th {
    text-align: left; font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.07em; color: #6B6560; padding: 0.75rem 1rem;
    border-bottom: 1px solid rgba(0,0,0,0.07); background: #FAFAFA; white-space: nowrap;
}
.requests-table th:first-child { border-radius: 12px 0 0 0; }
.requests-table th:last-child  { border-radius: 0 12px 0 0; }
.requests-table td {
    padding: 1rem; border-bottom: 1px solid rgba(0,0,0,0.05); font-size: 0.875rem;
    vertical-align: middle; background: #fff;
}
.requests-table tr:last-child td { border-bottom: none; }
.requests-table tr:hover td { background: #FAFAF8; }
.table-wrap { border: 1px solid rgba(0,0,0,0.07); border-radius: 16px; overflow: hidden; }

.req-product-cell { display: flex; align-items: center; gap: 10px; }
.req-thumb { width: 44px; height: 44px; border-radius: 10px; background: #F2EDE4;
    display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
    flex-shrink: 0; overflow: hidden; }
.req-thumb img { width: 100%; height: 100%; object-fit: cover; }
.req-product-name { font-size: 0.85rem; font-weight: 500; color: #1A1A1A;
    max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.req-product-price { font-size: 0.75rem; color: #6B6560; }

.buyer-cell { display: flex; align-items: center; gap: 8px; }
.buyer-initials { width: 32px; height: 32px; border-radius: 50%; background: #EDE6DC;
    color: #C4622D; font-size: 0.72rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.buyer-name { font-size: 0.85rem; font-weight: 500; }
.buyer-username { font-size: 0.72rem; color: #6B6560; }

.status-pill { display: inline-block; padding: 4px 12px; border-radius: 100px;
    font-size: 0.7rem; font-weight: 600; }
.status-pending   { background: #FFF8E1; color: #B37200; }
.status-paid      { background: #E8F5E9; color: #2E7D32; }
.status-shipped   { background: #E3F2FD; color: #1565C0; }
.status-completed { background: #E8F5E9; color: #2E7D32; }
.status-cancelled { background: #FFEBEE; color: #C62828; }

.req-actions { display: flex; gap: 6px; }
.btn-accept { background: #E8F5E9; color: #2E7D32; border: none; padding: 6px 14px;
    border-radius: 100px; font-size: 0.75rem; font-weight: 600;
    cursor: pointer; font-family: 'DM Sans', sans-serif; transition: background .2s; }
.btn-accept:hover { background: #C8E6C9; }
.btn-reject { background: #FFEBEE; color: #C62828; border: none; padding: 6px 14px;
    border-radius: 100px; font-size: 0.75rem; font-weight: 600;
    cursor: pointer; font-family: 'DM Sans', sans-serif; transition: background .2s; }
.btn-reject:hover { background: #FFCDD2; }
.btn-ship { background: #E3F2FD; color: #1565C0; border: none; padding: 6px 14px;
    border-radius: 100px; font-size: 0.75rem; font-weight: 600;
    cursor: pointer; font-family: 'DM Sans', sans-serif; transition: background .2s; }
.btn-ship:hover { background: #BBDEFB; }

/* ── MY ORDERS ── */
.orders-list { display: flex; flex-direction: column; gap: 12px; }
.order-card {
    background: #fff; border: 1px solid rgba(0,0,0,0.07); border-radius: 16px;
    padding: 1.25rem; display: flex; align-items: center; gap: 1.25rem;
}
.order-thumb { width: 60px; height: 60px; border-radius: 12px; background: #F2EDE4;
    display: flex; align-items: center; justify-content: center; font-size: 2rem;
    flex-shrink: 0; overflow: hidden; }
.order-thumb img { width: 100%; height: 100%; object-fit: cover; }
.order-info { flex: 1; }
.order-title { font-size: 0.95rem; font-weight: 500; color: #1A1A1A; margin-bottom: 3px; }
.order-meta { font-size: 0.78rem; color: #6B6560; }
.order-price { font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 600; color: #1A1A1A; }
.order-date { font-size: 0.72rem; color: #6B6560; margin-top: 2px; }

/* ── EMPTY STATE ── */
.empty-dash { text-align: center; padding: 3rem; background: #fff;
    border: 1px solid rgba(0,0,0,0.07); border-radius: 16px; color: #6B6560; }
.empty-dash-emoji { font-size: 2.5rem; margin-bottom: 0.75rem; }
.empty-dash-text { font-size: 0.9rem; }
.empty-dash-link { color: #C4622D; text-decoration: none; font-weight: 500; }

/* ── TABS ── */
.dash-tabs { display: flex; gap: 4px; background: #F2EDE4; padding: 4px; border-radius: 12px;
    margin-bottom: 2rem; width: fit-content; }
.dash-tab { padding: 8px 20px; border-radius: 9px; font-size: 0.85rem; font-weight: 500;
    color: #6B6560; cursor: pointer; border: none; background: transparent;
    font-family: 'DM Sans', sans-serif; transition: all .2s; }
.dash-tab.active { background: #fff; color: #1A1A1A; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.dash-panel { display: none; }
.dash-panel.active { display: block; }

/* ── RESPONSIVE ── */
@media(max-width: 768px){
    .dash-banner { flex-direction: column; align-items: flex-start; }
    .dash-stats { grid-template-columns: repeat(2,1fr); }
    .requests-table { display: block; overflow-x: auto; }
    .dash-wrap { padding: 1.5rem 1rem; }
}
</style>
</head>
<body>
<?php include 'includes/nav.php'; ?>

<div class="dash-wrap">

    <!-- ── PROFILE BANNER ── -->
    <div class="dash-banner">
        <div class="dash-profile-left">
            <?php if (!empty($user['avatar']) && file_exists('uploads/avatars/'.$user['avatar'])): ?>
                <img src="uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>"
                     class="dash-big-avatar" alt="Profile photo">
            <?php else: ?>
                <div class="dash-big-avatar">
                    <?= strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)) ?>
                </div>
            <?php endif; ?>
            <div>
                <div class="dash-profile-name"><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></div>
                <div class="dash-profile-username">@<?= htmlspecialchars($user['username']) ?></div>
                <?php if ($user['province']): ?>
                    <div class="dash-profile-province">📍 <?= htmlspecialchars($user['province']) ?></div>
                <?php endif; ?>
                <span class="dash-role-badge"><?= ucfirst($user['role']) ?></span>
            </div>
        </div>
        <div class="dash-banner-actions">
            <a href="seller-dashboard.php" class="btn-banner btn-banner-primary">+ List an item</a>
            <a href="profile.php" class="btn-banner btn-banner-ghost">Edit profile</a>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="admin/dashboard.php" class="btn-banner btn-banner-ghost">🔧 Admin</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── STATS ── -->
    <div class="dash-stats">
        <div class="stat-card">
            <div class="stat-label">My listings</div>
            <div class="stat-value"><?= $totalListings ?></div>
            <div class="stat-sub">items for sale</div>
        </div>
        <div class="stat-card <?= $pendingRequests > 0 ? 'highlight' : '' ?>">
            <div class="stat-label">Pending requests</div>
            <div class="stat-value"><?= $pendingRequests ?></div>
            <div class="stat-sub">awaiting your response</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total earned</div>
            <div class="stat-value">R <?= number_format($totalRevenue,0) ?></div>
            <div class="stat-sub">from completed sales</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">My purchases</div>
            <div class="stat-value"><?= count($myOrders) ?></div>
            <div class="stat-sub">orders placed</div>
        </div>
    </div>

    <!-- ── TABS ── -->
    <div class="dash-tabs">
        <button class="dash-tab active" onclick="switchTab('listings',this)">🛍️ My Listings</button>
        <button class="dash-tab" onclick="switchTab('requests',this)">
            📬 Buy Requests <?php if($pendingRequests>0): ?><span style="background:#C4622D;color:#fff;font-size:0.65rem;padding:1px 7px;border-radius:100px;margin-left:4px;"><?= $pendingRequests ?></span><?php endif; ?>
        </button>
        <button class="dash-tab" onclick="switchTab('orders',this)">📦 My Orders</button>
    </div>

    <!-- ── TAB: MY LISTINGS ── -->
    <div class="dash-panel active" id="tab-listings">
        <div class="dash-section">
            <div class="dash-section-header">
                <div class="dash-section-title">Your <em>listings</em></div>
                <a href="seller-dashboard.php" class="section-link">+ Add new listing</a>
            </div>

            <?php if (empty($myListings)): ?>
            <div class="empty-dash">
                <div class="empty-dash-emoji">🛍️</div>
                <div class="empty-dash-text">You haven't listed anything yet.<br>
                    <a href="seller-dashboard.php" class="empty-dash-link">List your first item →</a>
                </div>
            </div>
            <?php else: ?>
            <div class="listings-grid-dash">
                <?php foreach ($myListings as $l):
                    $emoji = $catEmojis[$l['category']] ?? '🏷️';
                    $hasPending = $l['pending_requests'] > 0;
                ?>
                <div class="listing-dash-card" onclick="window.location='product.php?id=<?= $l['id'] ?>'">
                    <div class="listing-dash-thumb">
                        <?php if ($l['image'] && file_exists('uploads/products/'.$l['image'])): ?>
                            <img src="uploads/products/<?= htmlspecialchars($l['image']) ?>"
                                 alt="<?= htmlspecialchars($l['title']) ?>">
                        <?php else: ?>
                            <?= $emoji ?>
                        <?php endif; ?>
                    </div>
                    <div class="listing-dash-body">
                        <div class="listing-dash-brand"><?= htmlspecialchars($l['brand']) ?></div>
                        <div class="listing-dash-title"><?= htmlspecialchars($l['title']) ?></div>
                        <div class="listing-dash-meta">
                            <span class="listing-dash-price">R <?= number_format($l['price'],0) ?></span>
                            <span class="listing-req-badge <?= $hasPending ? '' : 'zero' ?>">
                                <?= $l['pending_requests'] > 0 ? $l['pending_requests'].' request'.($l['pending_requests']>1?'s':'') : 'No requests' ?>
                            </span>
                        </div>
                        <div class="listing-dash-actions" onclick="event.stopPropagation()">
                            <a href="product.php?id=<?= $l['id'] ?>" class="btn-dash-sm btn-dash-view">View</a>
                            <a href="delete-listing.php?id=<?= $l['id'] ?>"
                               onclick="return confirm('Delete this listing?')"
                               class="btn-dash-sm btn-dash-del">Delete</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── TAB: BUY REQUESTS ── -->
    <div class="dash-panel" id="tab-requests">
        <div class="dash-section" id="requests">
            <div class="dash-section-header">
                <div class="dash-section-title">Buy <em>requests</em></div>
                <span style="font-size:0.82rem;color:#6B6560;"><?= count($buyRequests) ?> total request<?= count($buyRequests)!==1?'s':'' ?></span>
            </div>

            <?php if (empty($buyRequests)): ?>
            <div class="empty-dash">
                <div class="empty-dash-emoji">📬</div>
                <div class="empty-dash-text">No buy requests yet.<br>
                    <a href="browse.php" class="empty-dash-link">Browse to see how your listing looks →</a>
                </div>
            </div>
            <?php else: ?>
            <div class="table-wrap">
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Buyer</th>
                            <th>Contact</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($buyRequests as $req):
                        $emoji  = $catEmojis[$req['category']] ?? '🏷️';
                        $initials = strtoupper(substr($req['first_name'],0,1).substr($req['last_name'],0,1));
                    ?>
                    <tr>
                        <!-- Item -->
                        <td>
                            <div class="req-product-cell">
                                <div class="req-thumb">
                                    <?php if ($req['product_image'] && file_exists('uploads/products/'.$req['product_image'])): ?>
                                        <img src="uploads/products/<?= htmlspecialchars($req['product_image']) ?>" alt="">
                                    <?php else: ?>
                                        <?= $emoji ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="req-product-name"><?= htmlspecialchars($req['product_title']) ?></div>
                                    <div class="req-product-price">R <?= number_format($req['product_price'],0) ?></div>
                                </div>
                            </div>
                        </td>

                        <!-- Buyer -->
                        <td>
                            <div class="buyer-cell">
                                <div class="buyer-initials"><?= $initials ?></div>
                                <div>
                                    <div class="buyer-name"><?= htmlspecialchars($req['first_name'].' '.$req['last_name']) ?></div>
                                    <div class="buyer-username">@<?= htmlspecialchars($req['username']) ?></div>
                                </div>
                            </div>
                        </td>

                        <!-- Contact -->
                        <td>
                            <div style="font-size:0.78rem;color:#6B6560;">
                                <div>✉️ <?= htmlspecialchars($req['email']) ?></div>
                                <?php if ($req['phone']): ?>
                                    <div style="margin-top:2px;">📞 <?= htmlspecialchars($req['phone']) ?></div>
                                <?php endif; ?>
                                <?php if ($req['buyer_province']): ?>
                                    <div style="margin-top:2px;">📍 <?= htmlspecialchars($req['buyer_province']) ?></div>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- Amount -->
                        <td>
                            <span style="font-family:'Playfair Display',serif;font-size:1rem;font-weight:600;">
                                R <?= number_format($req['total'],0) ?>
                            </span>
                        </td>

                        <!-- Status -->
                        <td>
                            <span class="status-pill status-<?= $req['status'] ?>">
                                <?= ucfirst($req['status']) ?>
                            </span>
                        </td>

                        <!-- Date -->
                        <td style="font-size:0.78rem;color:#6B6560;white-space:nowrap;">
                            <?= date('d M Y', strtotime($req['created_at'])) ?>
                        </td>

                        <!-- Actions -->
                        <td>
                            <div class="req-actions">
                                <?php if ($req['status'] === 'pending'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="order_id" value="<?= $req['id'] ?>">
                                        <input type="hidden" name="new_status" value="paid">
                                        <button type="submit" name="update_request" class="btn-accept">✓ Accept</button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="order_id" value="<?= $req['id'] ?>">
                                        <input type="hidden" name="new_status" value="cancelled">
                                        <button type="submit" name="update_request" class="btn-reject">✗ Decline</button>
                                    </form>
                                <?php elseif ($req['status'] === 'paid'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="order_id" value="<?= $req['id'] ?>">
                                        <input type="hidden" name="new_status" value="shipped">
                                        <button type="submit" name="update_request" class="btn-ship">📦 Mark Shipped</button>
                                    </form>
                                <?php elseif ($req['status'] === 'shipped'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="order_id" value="<?= $req['id'] ?>">
                                        <input type="hidden" name="new_status" value="completed">
                                        <button type="submit" name="update_request" class="btn-accept">✓ Completed</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size:0.75rem;color:#6B6560;">
                                        <?= ucfirst($req['status']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── TAB: MY ORDERS ── -->
    <div class="dash-panel" id="tab-orders">
        <div class="dash-section" id="orders">
            <div class="dash-section-header">
                <div class="dash-section-title">My <em>purchases</em></div>
            </div>

            <?php if (empty($myOrders)): ?>
            <div class="empty-dash">
                <div class="empty-dash-emoji">📦</div>
                <div class="empty-dash-text">You haven't bought anything yet.<br>
                    <a href="browse.php" class="empty-dash-link">Start browsing →</a>
                </div>
            </div>
            <?php else: ?>
            <div class="orders-list">
                <?php foreach ($myOrders as $o):
                    $emoji = $catEmojis[$o['category']] ?? '🏷️';
                ?>
                <div class="order-card">
                    <div class="order-thumb">
                        <?php if ($o['product_image'] && file_exists('uploads/products/'.$o['product_image'])): ?>
                            <img src="uploads/products/<?= htmlspecialchars($o['product_image']) ?>" alt="">
                        <?php else: ?>
                            <?= $emoji ?>
                        <?php endif; ?>
                    </div>
                    <div class="order-info">
                        <div class="order-title"><?= htmlspecialchars($o['product_title']) ?></div>
                        <div class="order-meta">
                            <?= htmlspecialchars($o['brand'] ?? '') ?> ·
                            Seller: @<?= htmlspecialchars($o['seller_username']) ?>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div class="order-price">R <?= number_format($o['total'],0) ?></div>
                        <div class="order-date"><?= date('d M Y', strtotime($o['created_at'])) ?></div>
                        <span class="status-pill status-<?= $o['status'] ?>" style="margin-top:6px;display:inline-block;">
                            <?= ucfirst($o['status']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div><!-- end dash-wrap -->

<script>
function switchTab(id, btn) {
    document.querySelectorAll('.dash-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.dash-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + id).classList.add('active');
    btn.classList.add('active');
}

// If URL has a hash, auto-open matching tab
const hash = window.location.hash;
if (hash === '#requests') {
    document.querySelectorAll('.dash-tab')[1].click();
} else if (hash === '#orders') {
    document.querySelectorAll('.dash-tab')[2].click();
}
</script>
</body>
</html>