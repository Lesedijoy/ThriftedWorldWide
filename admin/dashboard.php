<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit;
}

// Stats
$totalUsers    = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM users"))[0];
$totalProducts = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM products"))[0];
$totalOrders   = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM orders"))[0];
$totalRevenue  = mysqli_fetch_row(mysqli_query($conn,"SELECT COALESCE(SUM(total),0) FROM orders WHERE status='completed'"))[0];
$pendingOrders = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM orders WHERE status='pending'"))[0];

// Handle order status update
if (isset($_POST['update_status'])) {
    $oid    = (int)$_POST['order_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE orders SET status='$status' WHERE id=$oid");
    header("Location: dashboard.php?updated=1"); exit;
}

// Handle user role change
if (isset($_POST['update_role'])) {
    $tuid = (int)$_POST['target_user_id'];
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    if (in_array($role, ['admin','seller','buyer'])) {
        mysqli_query($conn, "UPDATE users SET role='$role' WHERE id=$tuid");
    }
    header("Location: dashboard.php?updated=1#users"); exit;
}

// Handle delete product
if (isset($_GET['del_product'])) {
    $pid = (int)$_GET['del_product'];
    $pr  = mysqli_query($conn, "SELECT image FROM products WHERE id=$pid LIMIT 1");
    if ($pr && $row = mysqli_fetch_assoc($pr)) {
        if ($row['image'] && file_exists('../uploads/products/'.$row['image'])) unlink('../uploads/products/'.$row['image']);
    }
    mysqli_query($conn, "DELETE FROM products WHERE id=$pid");
    header("Location: dashboard.php?updated=1#products"); exit;
}

// Recent orders
$orders = [];
$res = mysqli_query($conn,
    "SELECT o.*, u.first_name, u.last_name, u.email, p.title as product_title, p.user_id as seller_id,
            s.first_name as seller_first, s.last_name as seller_last
     FROM orders o
     JOIN users u ON o.buyer_id = u.id
     JOIN products p ON o.product_id = p.id
     JOIN users s ON p.user_id = s.id
     ORDER BY o.created_at DESC LIMIT 30");
while ($r = mysqli_fetch_assoc($res)) $orders[] = $r;

// All users
$users = [];
$ures = mysqli_query($conn,
    "SELECT u.*, (SELECT COUNT(*) FROM products WHERE user_id=u.id) as listing_count,
     (SELECT COUNT(*) FROM orders WHERE buyer_id=u.id) as order_count
     FROM users u ORDER BY u.created_at DESC");
while ($u = mysqli_fetch_assoc($ures)) $users[] = $u;

// All products
$products = [];
$pres = mysqli_query($conn,
    "SELECT p.*, u.username, u.first_name, u.last_name,
     (SELECT COUNT(*) FROM orders WHERE product_id=p.id) as order_count
     FROM products p JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC LIMIT 50");
while ($p = mysqli_fetch_assoc($pres)) $products[] = $p;

$catEmojis = ["Women's Clothing"=>'👗',"Men's Clothing"=>'👔',"Sneakers"=>'👟',
              "Bags & Purses"=>'👜',"Accessories"=>'💍',"Outerwear"=>'🧥',"Designer"=>'✨',"Vintage"=>'🕰️'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — ThriftedWorldWide</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'DM Sans',sans-serif;background:#F7F3EE;color:#1A1A1A;min-height:100vh;}

/* TOP BAR */
.admin-bar{background:#1A1A1A;color:#fff;display:flex;align-items:center;justify-content:space-between;
    padding:0 2rem;height:56px;position:sticky;top:0;z-index:100;}
.admin-bar h1{font-family:'Playfair Display',serif;font-size:1.05rem;font-weight:600;letter-spacing:-0.01em;}
.admin-bar a{color:rgba(255,255,255,0.65);text-decoration:none;font-size:0.82rem;transition:color .2s;}
.admin-bar a:hover{color:#fff;}
.admin-bar-links{display:flex;gap:1.5rem;align-items:center;}

/* LAYOUT */
.admin-body{display:flex;min-height:calc(100vh - 56px);}
.admin-sidebar{width:210px;flex-shrink:0;background:#fff;border-right:1px solid rgba(0,0,0,0.07);
    padding:1.5rem 1rem;position:sticky;top:56px;height:calc(100vh - 56px);overflow-y:auto;}
.sidebar-section{margin-bottom:1.5rem;}
.sidebar-label{font-size:0.65rem;font-weight:600;letter-spacing:0.09em;text-transform:uppercase;
    color:#6B6560;margin-bottom:0.5rem;padding:0 0.5rem;}
.sidebar-link{display:flex;align-items:center;gap:8px;padding:0.55rem 0.75rem;border-radius:10px;
    font-size:0.875rem;color:#6B6560;text-decoration:none;margin-bottom:2px;transition:all .15s;border:none;
    background:transparent;width:100%;cursor:pointer;font-family:'DM Sans',sans-serif;}
.sidebar-link:hover{background:#F7F3EE;color:#1A1A1A;}
.sidebar-link.active{background:#EDE6DC;color:#1A1A1A;font-weight:500;}
.sidebar-link .s-badge{margin-left:auto;background:#C4622D;color:#fff;font-size:0.62rem;
    font-weight:700;padding:1px 7px;border-radius:100px;}

.admin-main{flex:1;padding:2rem;overflow-y:auto;min-width:0;}

/* STATS */
.stats-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:2rem;}
.stat-card{background:#fff;border:1px solid rgba(0,0,0,0.07);border-radius:14px;padding:1.25rem;}
.stat-label{font-size:0.68rem;font-weight:600;text-transform:uppercase;letter-spacing:0.07em;color:#6B6560;margin-bottom:6px;}
.stat-value{font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:700;color:#1A1A1A;line-height:1;}
.stat-card.warn .stat-value{color:#C4622D;}

/* PANELS */
.admin-panel{display:none;}
.admin-panel.active{display:block;}
.panel-title{font-family:'Playfair Display',serif;font-size:1.2rem;font-weight:600;margin-bottom:1.25rem;}
.panel-title em{font-style:italic;color:#C4622D;}

/* TABLES */
.table-wrap{background:#fff;border:1px solid rgba(0,0,0,0.07);border-radius:16px;overflow:hidden;margin-bottom:1.5rem;}
table{width:100%;border-collapse:collapse;}
th{text-align:left;padding:0.75rem 1rem;font-size:0.68rem;text-transform:uppercase;letter-spacing:0.07em;
    color:#6B6560;border-bottom:1px solid rgba(0,0,0,0.07);background:#FAFAFA;white-space:nowrap;}
td{padding:0.85rem 1rem;border-bottom:1px solid rgba(0,0,0,0.04);font-size:0.85rem;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#FAFAF8;}

/* BADGES */
.badge{display:inline-block;padding:3px 10px;border-radius:100px;font-size:0.68rem;font-weight:600;}
.badge-pending  {background:#FFF8E1;color:#B37200;}
.badge-paid     {background:#E8F5E9;color:#2E7D32;}
.badge-shipped  {background:#E3F2FD;color:#1565C0;}
.badge-completed{background:#E8F5E9;color:#27AE60;}
.badge-cancelled{background:#FFEBEE;color:#C62828;}
.badge-admin    {background:#EDE0FF;color:#6B3FA0;}
.badge-seller   {background:#E3F2FD;color:#1565C0;}
.badge-buyer    {background:#F1F3F4;color:#5F6368;}

/* FORM CONTROLS IN TABLE */
.tbl-select{padding:5px 8px;border:1px solid rgba(0,0,0,0.15);border-radius:8px;
    font-size:0.78rem;font-family:'DM Sans',sans-serif;background:#fff;}
.tbl-btn{padding:5px 12px;border-radius:100px;font-size:0.72rem;font-weight:600;
    cursor:pointer;font-family:'DM Sans',sans-serif;border:none;transition:background .2s;}
.btn-upd{background:#1A1A1A;color:#fff;margin-left:5px;}
.btn-upd:hover{background:#C4622D;}
.btn-del{background:#FFEBEE;color:#C62828;}
.btn-del:hover{background:#FFCDD2;}

/* ALERT */
.admin-alert{background:#E8F5E9;color:#2E7D32;padding:0.75rem 1rem;border-radius:10px;
    font-size:0.85rem;margin-bottom:1rem;}

/* USER CELL */
.user-cell{display:flex;align-items:center;gap:8px;}
.user-init{width:30px;height:30px;border-radius:50%;background:#EDE6DC;color:#C4622D;
    font-size:0.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}

/* PRODUCT CELL */
.prod-cell{display:flex;align-items:center;gap:8px;}
.prod-thumb{width:36px;height:36px;border-radius:8px;background:#F2EDE4;display:flex;
    align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;overflow:hidden;}
.prod-thumb img{width:100%;height:100%;object-fit:cover;}

@media(max-width:900px){
    .admin-sidebar{display:none;}
    .stats-grid{grid-template-columns:repeat(2,1fr);}
}
</style>
</head>
<body>

<div class="admin-bar">
    <h1>🔧 ThriftedWorldWide — Admin</h1>
    <div class="admin-bar-links">
        <a href="../index.php">← View site</a>
        <a href="../dashboard.php">My dashboard</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="admin-body">
    <!-- SIDEBAR -->
    <div class="admin-sidebar">
        <div class="sidebar-section">
            <div class="sidebar-label">Overview</div>
            <button class="sidebar-link active" onclick="switchPanel('overview',this)">📊 Dashboard</button>
        </div>
        <div class="sidebar-section">
            <div class="sidebar-label">Manage</div>
            <button class="sidebar-link" onclick="switchPanel('orders',this)">
                🧾 Orders
                <?php if ($pendingOrders > 0): ?><span class="s-badge"><?= $pendingOrders ?></span><?php endif; ?>
            </button>
            <button class="sidebar-link" onclick="switchPanel('users',this)">👥 Users</button>
            <button class="sidebar-link" onclick="switchPanel('products',this)">🛍️ Products</button>
        </div>
    </div>

    <!-- MAIN -->
    <div class="admin-main">

        <?php if (isset($_GET['updated'])): ?>
            <div class="admin-alert">✓ Updated successfully.</div>
        <?php endif; ?>

        <!-- OVERVIEW PANEL -->
        <div class="admin-panel active" id="panel-overview">
            <div class="panel-title">Admin <em>overview</em></div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total users</div>
                    <div class="stat-value"><?= number_format($totalUsers) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total listings</div>
                    <div class="stat-value"><?= number_format($totalProducts) ?></div>
                </div>
                <div class="stat-card <?= $pendingOrders > 0 ? 'warn' : '' ?>">
                    <div class="stat-label">Pending orders</div>
                    <div class="stat-value"><?= number_format($pendingOrders) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total orders</div>
                    <div class="stat-value"><?= number_format($totalOrders) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Revenue (completed)</div>
                    <div class="stat-value" style="font-size:1.3rem;">R <?= number_format($totalRevenue,0) ?></div>
                </div>
            </div>

            <!-- Recent Orders Quick View -->
            <div class="panel-title" style="margin-top:1rem;">Recent orders</div>
            <div class="table-wrap">
                <table>
                    <thead><tr>
                        <th>Order</th><th>Buyer</th><th>Product</th><th>Seller</th>
                        <th>Amount</th><th>Status</th><th>Date</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach (array_slice($orders,0,8) as $o): ?>
                    <tr>
                        <td style="color:#6B6560;">#<?= $o['id'] ?></td>
                        <td><?= htmlspecialchars($o['first_name'].' '.$o['last_name']) ?></td>
                        <td style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?= htmlspecialchars($o['product_title']) ?></td>
                        <td><?= htmlspecialchars($o['seller_first'].' '.$o['seller_last']) ?></td>
                        <td style="font-family:'Playfair Display',serif;font-weight:600;">R <?= number_format($o['total'],0) ?></td>
                        <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                        <td style="font-size:0.75rem;color:#6B6560;"><?= date('d M Y',strtotime($o['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ORDERS PANEL -->
        <div class="admin-panel" id="panel-orders">
            <div class="panel-title">Manage <em>orders</em></div>
            <div class="table-wrap">
                <table>
                    <thead><tr>
                        <th>ID</th><th>Buyer</th><th>Product</th><th>Seller</th>
                        <th>Total</th><th>Status</th><th>Date</th><th>Update</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td style="color:#6B6560;">#<?= $o['id'] ?></td>
                        <td>
                            <div class="user-cell">
                                <div class="user-init"><?= strtoupper(substr($o['first_name'],0,1).substr($o['last_name'],0,1)) ?></div>
                                <div>
                                    <div style="font-size:0.85rem;"><?= htmlspecialchars($o['first_name'].' '.$o['last_name']) ?></div>
                                    <div style="font-size:0.72rem;color:#6B6560;"><?= htmlspecialchars($o['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:0.85rem;">
                            <?= htmlspecialchars($o['product_title']) ?></td>
                        <td style="font-size:0.82rem;"><?= htmlspecialchars($o['seller_first'].' '.$o['seller_last']) ?></td>
                        <td style="font-family:'Playfair Display',serif;font-weight:600;">R <?= number_format($o['total'],0) ?></td>
                        <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                        <td style="font-size:0.72rem;color:#6B6560;white-space:nowrap;"><?= date('d M Y',strtotime($o['created_at'])) ?></td>
                        <td>
                            <form method="POST" style="display:inline-flex;align-items:center;">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <select name="status" class="tbl-select">
                                    <?php foreach(['pending','paid','shipped','completed','cancelled'] as $st): ?>
                                        <option value="<?= $st ?>" <?= $o['status']===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_status" class="tbl-btn btn-upd">Save</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="8" style="text-align:center;color:#6B6560;padding:2rem;">No orders yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- USERS PANEL -->
        <div class="admin-panel" id="panel-users">
            <div class="panel-title">Manage <em>users</em></div>
            <div class="table-wrap">
                <table>
                    <thead><tr>
                        <th>User</th><th>Email</th><th>Province</th>
                        <th>Listings</th><th>Orders</th><th>Role</th><th>Joined</th><th>Change role</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-init"><?= strtoupper(substr($u['first_name'],0,1).substr($u['last_name'],0,1)) ?></div>
                                <div>
                                    <div style="font-size:0.85rem;font-weight:500;"><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></div>
                                    <div style="font-size:0.72rem;color:#6B6560;">@<?= htmlspecialchars($u['username']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:0.8rem;"><?= htmlspecialchars($u['email']) ?></td>
                        <td style="font-size:0.8rem;color:#6B6560;"><?= htmlspecialchars($u['province'] ?? '—') ?></td>
                        <td style="text-align:center;"><?= $u['listing_count'] ?></td>
                        <td style="text-align:center;"><?= $u['order_count'] ?></td>
                        <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td style="font-size:0.72rem;color:#6B6560;white-space:nowrap;"><?= date('d M Y',strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="display:inline-flex;align-items:center;">
                                <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                <select name="role" class="tbl-select">
                                    <?php foreach(['buyer','seller','admin'] as $ro): ?>
                                        <option value="<?= $ro ?>" <?= $u['role']===$ro?'selected':'' ?>><?= ucfirst($ro) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_role" class="tbl-btn btn-upd">Set</button>
                            </form>
                            <?php else: ?>
                                <span style="font-size:0.75rem;color:#6B6560;">You</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PRODUCTS PANEL -->
        <div class="admin-panel" id="panel-products">
            <div class="panel-title">Manage <em>products</em></div>
            <div class="table-wrap">
                <table>
                    <thead><tr>
                        <th>Product</th><th>Seller</th><th>Category</th>
                        <th>Price</th><th>Orders</th><th>Province</th><th>Listed</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($products as $p):
                        $emoji = $catEmojis[$p['category']] ?? '🏷️';
                    ?>
                    <tr>
                        <td>
                            <div class="prod-cell">
                                <div class="prod-thumb">
                                    <?php if ($p['image'] && file_exists('../uploads/products/'.$p['image'])): ?>
                                        <img src="../uploads/products/<?= htmlspecialchars($p['image']) ?>" alt="">
                                    <?php else: ?>
                                        <?= $emoji ?>
                                    <?php endif; ?>
                                </div>
                                <div style="max-width:150px;">
                                    <div style="font-size:0.82rem;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        <?= htmlspecialchars($p['title']) ?></div>
                                    <div style="font-size:0.72rem;color:#6B6560;"><?= htmlspecialchars($p['brand']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:0.82rem;">@<?= htmlspecialchars($p['username']) ?></td>
                        <td style="font-size:0.78rem;color:#6B6560;"><?= htmlspecialchars($p['category']) ?></td>
                        <td style="font-family:'Playfair Display',serif;font-weight:600;">R <?= number_format($p['price'],0) ?></td>
                        <td style="text-align:center;"><?= $p['order_count'] ?></td>
                        <td style="font-size:0.78rem;color:#6B6560;"><?= htmlspecialchars($p['province'] ?? '—') ?></td>
                        <td style="font-size:0.72rem;color:#6B6560;white-space:nowrap;"><?= date('d M Y',strtotime($p['created_at'])) ?></td>
                        <td>
                            <a href="../product.php?id=<?= $p['id'] ?>" class="tbl-btn" style="background:#EDE6DC;color:#1A1A1A;text-decoration:none;display:inline-block;">View</a>
                            <a href="dashboard.php?del_product=<?= $p['id'] ?>"
                               onclick="return confirm('Delete this product?')"
                               class="tbl-btn btn-del" style="text-decoration:none;display:inline-block;margin-left:4px;">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- end admin-main -->
</div><!-- end admin-body -->

<script>
function switchPanel(id, btn) {
    document.querySelectorAll('.admin-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
    document.getElementById('panel-' + id).classList.add('active');
    btn.classList.add('active');
}
// Handle hash navigation
const hash = window.location.hash;
if (hash === '#orders')   { document.querySelectorAll('.sidebar-link')[1].click(); }
if (hash === '#users')    { document.querySelectorAll('.sidebar-link')[2].click(); }
if (hash === '#products') { document.querySelectorAll('.sidebar-link')[3].click(); }
</script>
</body>
</html>