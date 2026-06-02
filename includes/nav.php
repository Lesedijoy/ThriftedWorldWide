<?php
// includes/nav.php  — drop into every page with: include 'includes/nav.php';
// Requires $conn and session_start() to already be called.
$_nav_loggedIn = isset($_SESSION['user_id']);
$_nav_user     = null;
$_nav_cartCount = 0;

if ($_nav_loggedIn) {
    $uid = (int)$_SESSION['user_id'];
    $r   = mysqli_query($conn, "SELECT id,first_name,last_name,username,role,avatar FROM users WHERE id=$uid LIMIT 1");
    $_nav_user = mysqli_fetch_assoc($r);

    // Pending buy requests on seller's products
    $buyReq = mysqli_fetch_row(mysqli_query($conn,
        "SELECT COUNT(*) FROM orders o
         JOIN products p ON o.product_id = p.id
         WHERE p.user_id = $uid AND o.status = 'pending'"))[0];
    $_nav_notifCount = (int)$buyReq;
}
?>
<!--- NAV SNIPPET — paste full <nav> block below --->
<nav class="tww-nav">
    <a class="tww-logo" href="index.php">Thrifted<span>Worldwide</span></a>

    <ul class="tww-nav-links">
        <li><a href="browse.php">Browse</a></li>
        <li><a href="seller-dashboard.php">Sell</a></li>
        <li><a href="index.php#how">How it works</a></li>
    </ul>

    <div class="tww-nav-right">
        <a href="cart.php" class="tww-cart-icon" title="Cart">
            🛒 <span class="tww-cart-badge" id="navCartBadge">0</span>
        </a>

        <?php if ($_nav_loggedIn && $_nav_user): ?>
        <!-- LOGGED IN: profile dropdown -->
        <div class="tww-profile-wrap" id="profileWrap">
            <button class="tww-profile-btn" onclick="toggleProfileMenu()" aria-haspopup="true">
                <?php if (!empty($_nav_user['avatar']) && file_exists('uploads/avatars/'.$_nav_user['avatar'])): ?>
                    <img src="uploads/avatars/<?= htmlspecialchars($_nav_user['avatar']) ?>"
                         class="tww-avatar-img" alt="Profile">
                <?php else: ?>
                    <div class="tww-avatar-initials">
                        <?= strtoupper(substr($_nav_user['first_name'],0,1).substr($_nav_user['last_name'],0,1)) ?>
                    </div>
                <?php endif; ?>
                <span class="tww-profile-name"><?= htmlspecialchars($_nav_user['first_name']) ?></span>
                <?php if ($_nav_notifCount > 0): ?>
                    <span class="tww-notif-dot"><?= $_nav_notifCount ?></span>
                <?php endif; ?>
                <span class="tww-chevron">▾</span>
            </button>

            <div class="tww-profile-dropdown" id="profileDropdown">
                <div class="tww-dropdown-header">
                    <?php if (!empty($_nav_user['avatar']) && file_exists('uploads/avatars/'.$_nav_user['avatar'])): ?>
                        <img src="uploads/avatars/<?= htmlspecialchars($_nav_user['avatar']) ?>"
                             class="tww-dd-avatar" alt="">
                    <?php else: ?>
                        <div class="tww-dd-initials">
                            <?= strtoupper(substr($_nav_user['first_name'],0,1).substr($_nav_user['last_name'],0,1)) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div class="tww-dd-name"><?= htmlspecialchars($_nav_user['first_name'].' '.$_nav_user['last_name']) ?></div>
                        <div class="tww-dd-username">@<?= htmlspecialchars($_nav_user['username']) ?></div>
                    </div>
                </div>

                <div class="tww-dropdown-links">
                    <a href="dashboard.php" class="tww-dd-link">
                        <span class="tww-dd-icon">👤</span> My dashboard
                    </a>
                    <a href="dashboard.php#listings" class="tww-dd-link">
                        <span class="tww-dd-icon">🛍️</span> My listings
                    </a>
                    <a href="dashboard.php#requests" class="tww-dd-link tww-dd-link-notif">
                        <span class="tww-dd-icon">📬</span> Buy requests
                        <?php if ($_nav_notifCount > 0): ?>
                            <span class="tww-dd-badge"><?= $_nav_notifCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="dashboard.php#orders" class="tww-dd-link">
                        <span class="tww-dd-icon">📦</span> My orders
                    </a>
                    <a href="profile.php" class="tww-dd-link">
                        <span class="tww-dd-icon">⚙️</span> Edit profile
                    </a>
                    <?php if ($_nav_user['role'] === 'admin'): ?>
                    <div class="tww-dd-divider"></div>
                    <a href="admin/dashboard.php" class="tww-dd-link tww-dd-admin">
                        <span class="tww-dd-icon">🔧</span> Admin panel
                    </a>
                    <?php endif; ?>
                    <div class="tww-dd-divider"></div>
                    <a href="logout.php" class="tww-dd-link tww-dd-logout">
                        <span class="tww-dd-icon">🚪</span> Sign out
                    </a>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- GUEST: login / signup -->
        <a href="login.php" class="tww-nav-link-plain">Login</a>
        <a href="register.php" class="tww-nav-btn">Sign up free</a>
        <?php endif; ?>
    </div>
</nav>

<style>
/* ── NAV BASE ── */
.tww-nav{display:flex;align-items:center;justify-content:space-between;padding:0 48px;height:68px;
  background:#F7F3EE;border-bottom:1px solid rgba(0,0,0,0.07);position:sticky;top:0;z-index:200;}
.tww-logo{font-family:'DM Sans',sans-serif;font-size:1.2rem;font-weight:700;color:#1A1A1A;text-decoration:none;letter-spacing:-0.02em;}
.tww-logo span{color:#C4622D;}
.tww-nav-links{display:flex;align-items:center;gap:32px;list-style:none;}
.tww-nav-links a{font-size:0.875rem;color:#6B6560;text-decoration:none;transition:color .2s;}
.tww-nav-links a:hover{color:#1A1A1A;}
.tww-nav-right{display:flex;align-items:center;gap:20px;}

/* ── CART ── */
.tww-cart-icon{position:relative;font-size:1.1rem;text-decoration:none;color:#1A1A1A;}
.tww-cart-badge{background:#C4622D;color:#fff;font-size:0.65rem;font-weight:700;padding:1px 5px;
  border-radius:100px;position:absolute;top:-6px;right:-8px;min-width:16px;text-align:center;line-height:16px;}

/* ── GUEST BUTTONS ── */
.tww-nav-link-plain{font-size:0.875rem;color:#6B6560;text-decoration:none;}
.tww-nav-link-plain:hover{color:#1A1A1A;}
.tww-nav-btn{background:#1A1A1A;color:#fff;padding:9px 20px;border-radius:100px;font-size:0.85rem;
  font-weight:500;text-decoration:none;transition:background .2s;}
.tww-nav-btn:hover{background:#C4622D;}

/* ── PROFILE BUTTON ── */
.tww-profile-wrap{position:relative;}
.tww-profile-btn{display:flex;align-items:center;gap:8px;background:transparent;border:none;
  cursor:pointer;font-family:'DM Sans',sans-serif;font-size:0.875rem;color:#1A1A1A;
  padding:6px 10px;border-radius:100px;transition:background .2s;position:relative;}
.tww-profile-btn:hover{background:rgba(0,0,0,0.05);}
.tww-avatar-img,.tww-avatar-initials{width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0;}
.tww-avatar-initials{background:#EDE6DC;color:#C4622D;font-size:0.8rem;font-weight:700;
  display:flex;align-items:center;justify-content:center;}
.tww-profile-name{font-weight:500;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.tww-notif-dot{background:#C4622D;color:#fff;font-size:0.62rem;font-weight:700;
  padding:2px 6px;border-radius:100px;min-width:18px;text-align:center;}
.tww-chevron{font-size:0.7rem;color:#6B6560;transition:transform .2s;}
.tww-profile-btn.open .tww-chevron{transform:rotate(180deg);}

/* ── DROPDOWN ── */
.tww-profile-dropdown{display:none;position:absolute;right:0;top:calc(100% + 8px);
  background:#fff;border:1px solid rgba(0,0,0,0.1);border-radius:16px;
  box-shadow:0 12px 40px rgba(0,0,0,0.12);width:240px;overflow:hidden;z-index:300;}
.tww-profile-dropdown.open{display:block;animation:ddIn .15s ease;}
@keyframes ddIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}

.tww-dropdown-header{display:flex;align-items:center;gap:12px;padding:16px;
  background:#F7F3EE;border-bottom:1px solid rgba(0,0,0,0.06);}
.tww-dd-avatar{width:40px;height:40px;border-radius:50%;object-fit:cover;}
.tww-dd-initials{width:40px;height:40px;border-radius:50%;background:#EDE6DC;
  color:#C4622D;font-size:0.9rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.tww-dd-name{font-size:0.88rem;font-weight:600;color:#1A1A1A;}
.tww-dd-username{font-size:0.75rem;color:#6B6560;margin-top:2px;}

.tww-dropdown-links{padding:8px 0;}
.tww-dd-link{display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:0.875rem;
  color:#1A1A1A;text-decoration:none;transition:background .15s;}
.tww-dd-link:hover{background:#F7F3EE;}
.tww-dd-icon{font-size:1rem;width:20px;text-align:center;}
.tww-dd-link-notif{justify-content:space-between;}
.tww-dd-link-notif span:first-of-type{display:flex;align-items:center;gap:10px;}
.tww-dd-badge{background:#C4622D;color:#fff;font-size:0.65rem;font-weight:700;
  padding:2px 7px;border-radius:100px;}
.tww-dd-divider{height:1px;background:rgba(0,0,0,0.07);margin:6px 0;}
.tww-dd-logout{color:#C4622D !important;}
.tww-dd-admin{color:#7B52AB !important;}
</style>

<script>
// Cart badge from localStorage
(function(){
  const c = JSON.parse(localStorage.getItem('tww_cart')||'[]');
  const b = document.getElementById('navCartBadge');
  if(b) b.textContent = c.length;
})();

function toggleProfileMenu(){
  const wrap = document.getElementById('profileWrap');
  const dd   = document.getElementById('profileDropdown');
  const btn  = wrap ? wrap.querySelector('.tww-profile-btn') : null;
  if(!dd) return;
  const open = dd.classList.toggle('open');
  if(btn) btn.classList.toggle('open', open);
}
document.addEventListener('click', function(e){
  const wrap = document.getElementById('profileWrap');
  if(wrap && !wrap.contains(e.target)){
    document.getElementById('profileDropdown')?.classList.remove('open');
    wrap.querySelector('.tww-profile-btn')?.classList.remove('open');
  }
});
</script>