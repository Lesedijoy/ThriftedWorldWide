<?php
session_start();
include 'includes/db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$error   = '';
$success = '';

// Handle listing submission
if (isset($_POST['publish']) && $isLoggedIn) {
    $user_id  = (int)$_SESSION['user_id'];
    $title    = mysqli_real_escape_string($conn, trim($_POST['title']));
    $category = mysqli_real_escape_string($conn, trim($_POST['category']));
    $brand    = mysqli_real_escape_string($conn, trim($_POST['brand']));
    $size     = mysqli_real_escape_string($conn, trim($_POST['size']));
    $colour   = mysqli_real_escape_string($conn, trim($_POST['colour']));
    $condition= mysqli_real_escape_string($conn, trim($_POST['product_condition']));
    $desc     = mysqli_real_escape_string($conn, trim($_POST['description']));
    $price    = (float)$_POST['price'];
    $province = mysqli_real_escape_string($conn, trim($_POST['province']));
    $imageName= '';

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $ext       = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed   = ['jpg','jpeg','png','webp','gif'];
        if (in_array($ext, $allowed)) {
            $imageName = uniqid('prod_') . '.' . $ext;
            $dest      = 'uploads/products/' . $imageName;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $imageName = '';
            }
        }
    }

    if (!$title || !$category || !$brand || !$price) {
        $error = 'Please fill in all required fields.';
    } else {
        $sql = "INSERT INTO products
                (user_id,title,category,brand,size,colour,product_condition,description,price,province,image)
                VALUES
                ($user_id,'$title','$category','$brand','$size','$colour','$condition','$desc',$price,'$province','$imageName')";
        if (mysqli_query($conn, $sql)) {
            $success = 'Your listing is live!';
        } else {
            $error = 'Failed to publish listing. Please try again.';
        }
    }
}

// Load seller's existing listings
$myListings = [];
if ($isLoggedIn) {
    $uid = (int)$_SESSION['user_id'];
    $res = mysqli_query($conn, "SELECT * FROM products WHERE user_id = $uid ORDER BY created_at DESC");
    while ($r = mysqli_fetch_assoc($res)) $myListings[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sell an Item — ThriftedWorldWide</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="seller-dashboard.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
.my-listings-section { padding:2rem 5%; max-width:860px; }
.my-listings-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:1rem; margin-top:1rem; }
.my-listing-card { background:#fff; border:1px solid var(--warm); border-radius:12px; overflow:hidden; }
.my-listing-thumb { height:140px; background:#F2EDE4; display:flex; align-items:center; justify-content:center; font-size:3rem; }
.my-listing-info { padding:0.75rem; }
.my-listing-title { font-size:0.88rem; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.my-listing-price { font-family:'Playfair Display',serif; font-size:1rem; font-weight:600; color:var(--rust,#C4622D); margin-top:4px; }
.delete-btn { background:none; border:none; color:#E24B4A; cursor:pointer; font-size:0.78rem; margin-top:4px; padding:0; }
.alert-error { background:#FFE8E8; color:#E24B4A; padding:0.75rem 1rem; border-radius:10px; font-size:0.88rem; margin-bottom:1rem; }
.alert-success { background:#E8F5E9; color:#27AE60; padding:0.75rem 1rem; border-radius:10px; font-size:0.88rem; margin-bottom:1rem; }
</style>
</head>
<body>

    <?php include 'includes/nav.php'; ?>
    

<?php if (!$isLoggedIn): ?>
<div style="text-align:center;padding:4rem 2rem;">
    <div style="font-size:3rem;margin-bottom:1rem;">🔒</div>
    <h2 style="font-family:'Playfair Display',serif;margin-bottom:0.75rem;">Login to start selling</h2>
    <p style="color:#6B6560;margin-bottom:1.5rem;">You need an account to list items on ThriftedWorldWide.</p>
    <a href="login.php" class="btn-next" style="text-decoration:none;">Sign in →</a>
    &nbsp;
    <a href="register.php" class="btn-back" style="text-decoration:none;">Create account</a>
</div>
<?php elseif ($success): ?>
<div class="sell-wrap">
    <div class="success-state" style="display:block;">
        <div class="success-emoji">🎉</div>
        <h2 class="success-title">Your listing is live!</h2>
        <p class="success-text">Buyers can now find your item on ThriftedWorldWide.<br>We'll notify you when someone makes an offer or buys.</p>
        <div class="success-btns">
            <a href="browse.php" class="btn-next" style="text-decoration:none;">View all listings</a>
            <a href="seller-dashboard.php" class="btn-back" style="text-decoration:none;">List another item</a>
        </div>
    </div>
</div>
<?php else: ?>
<div class="sell-wrap">
    <div class="sell-header">
        <div class="section-label">Seller dashboard</div>
        <h1>List a new <em>item</em></h1>
    </div>

    <!-- STEP TABS (visual only) -->
    <div class="step-tabs">
        <div class="step-tab active" id="tab1"><div class="step-tab-num">Step 1</div><div class="step-tab-label">Photos</div></div>
        <div class="step-tab" id="tab2"><div class="step-tab-num">Step 2</div><div class="step-tab-label">Details</div></div>
        <div class="step-tab" id="tab3"><div class="step-tab-num">Step 3</div><div class="step-tab-label">Pricing</div></div>
        <div class="step-tab" id="tab4"><div class="step-tab-num">Step 4</div><div class="step-tab-label">Submit</div></div>
    </div>

    <?php if ($error): ?>
        <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="seller-dashboard.php" enctype="multipart/form-data">

        <!-- STEP 1: PHOTO -->
        <div id="step1">
            <div class="form-card">
                <div class="card-title">Upload photo</div>
                <p style="font-size:0.83rem;color:var(--stone);margin-top:-0.5rem;">Upload your cover photo.</p>
                <div class="form-group">
                    <label>Cover photo</label>
                    <input type="file" name="image" id="imageInput" accept="image/*"
                           onchange="previewImage(this)" style="padding:8px;">
                    <div id="imagePreview" style="margin-top:0.75rem;"></div>
                </div>
                <div class="photo-tip">💡 Tip: Good lighting and a plain background get 3× more views.</div>
            </div>
            <div class="step-nav end">
                <button type="button" class="btn-next" onclick="goStep(2)">Next: Item details →</button>
            </div>
        </div>

        <!-- STEP 2: DETAILS -->
        <div id="step2" style="display:none;">
            <div class="form-card">
                <div class="card-title">Item details</div>
                <div class="form-group">
                    <label>Item title *</label>
                    <input type="text" name="title" placeholder="e.g. Zara Wrap Midi Dress — Dusty Rose" required>
                </div>
                <div class="two-col">
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category" required>
                            <option value="">Select category</option>
                            <?php foreach(["Women's Clothing","Men's Clothing","Sneakers","Bags & Purses","Accessories","Outerwear","Designer","Vintage"] as $c): ?>
                                <option><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Brand *</label>
                        <input type="text" name="brand" placeholder="e.g. Zara, Nike, H&M" required>
                    </div>
                </div>
                <div class="two-col">
                    <div class="form-group">
                        <label>Size *</label>
                        <select name="size" required>
                            <option value="">Select size</option>
                            <?php foreach(['XS','S','M','L','XL','XXL','UK 4','UK 5','UK 6','UK 7','UK 8','UK 9','UK 10','One size'] as $s): ?>
                                <option><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Colour</label>
                        <input type="text" name="colour" placeholder="e.g. Dusty Rose, Black, Navy">
                    </div>
                </div>
                <div class="form-group">
                    <label>Condition *</label>
                    <div class="cond-pills" id="condPills">
                        <?php foreach(['New with tags','Like new','Gently worn','Pre-owned','Vintage'] as $c): ?>
                            <span class="cond-pill <?= $c==='Gently worn'?'active':'' ?>"
                                  onclick="selectCond(this,'<?= $c ?>')"><?= $c ?></span>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="product_condition" id="conditionInput" value="Gently worn">
                </div>
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" rows="4"
                              placeholder="Describe your item — fabric, fit, any flaws, reason for selling..."
                              maxlength="500" oninput="document.getElementById('charCount').textContent=this.value.length+' / 500 characters'"></textarea>
                    <div class="char-count" id="charCount">0 / 500 characters</div>
                </div>
            </div>
            <div class="step-nav">
                <button type="button" class="btn-back" onclick="goStep(1)">← Back</button>
                <button type="button" class="btn-next" onclick="goStep(3)">Next: Pricing →</button>
            </div>
        </div>

        <!-- STEP 3: PRICING -->
        <div id="step3" style="display:none;">
            <div class="form-card">
                <div class="card-title">Set your price</div>
                <div class="two-col">
                    <div class="form-group">
                        <label>Asking price (ZAR) *</label>
                        <input type="number" name="price" id="askPrice"
                               placeholder="e.g. 320" min="1" oninput="calcFee()" required>
                    </div>
                    <div class="form-group">
                        <label>Original retail price (ZAR)</label>
                        <input type="number" placeholder="e.g. 799">
                    </div>
                </div>
                <div class="fee-box">
                    <div class="fee-title">Earnings breakdown</div>
                    <div class="fee-row"><span class="fee-label">Your price</span><span id="feePrice">R 0</span></div>
                    <div class="fee-row"><span class="fee-label">Platform fee (8%)</span><span id="feeFee" style="color:var(--accent);">- R 0</span></div>
                    <div class="fee-divider"></div>
                    <div class="fee-total"><span>You earn</span><span id="feeEarn">R 0</span></div>
                </div>
            </div>
            <div class="step-nav">
                <button type="button" class="btn-back" onclick="goStep(2)">← Back</button>
                <button type="button" class="btn-next" onclick="goStep(4)">Next: Submit →</button>
            </div>
        </div>

        <!-- STEP 4: PROVINCE + SUBMIT -->
        <div id="step4" style="display:none;">
            <div class="form-card">
                <div class="card-title">Your location</div>
                <div class="form-group">
                    <label>Province *</label>
                    <select name="province" required>
                        <option value="">Select your province</option>
                        <?php foreach(['Gauteng','Western Cape','KwaZulu-Natal','Eastern Cape','Limpopo','Mpumalanga','North West','Free State','Northern Cape'] as $pr): ?>
                            <option><?= $pr ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="publish-bar">
                <div>
                    <div class="pub-title">Ready to publish?</div>
                    <div class="pub-sub">Your listing will go live immediately.</div>
                </div>
                <button type="submit" name="publish" class="btn-publish">Publish listing 🚀</button>
            </div>
            <div class="step-nav" style="margin-top:0.75rem;">
                <button type="button" class="btn-back" onclick="goStep(3)">← Back</button>
            </div>
        </div>
    </form>
</div><!-- end sell-wrap -->

<!-- MY LISTINGS -->
<?php if (!empty($myListings)): ?>
<div class="my-listings-section">
    <h2 style="font-family:'Playfair Display',serif;font-size:1.4rem;margin-bottom:0.5rem;">Your listings</h2>
    <div class="my-listings-grid">
        <?php foreach ($myListings as $l):
            $catEmojis = ["Women's Clothing"=>'👗',"Men's Clothing"=>'👔',"Sneakers"=>'👟',"Bags & Purses"=>'👜',"Accessories"=>'💍',"Outerwear"=>'🧥',"Designer"=>'✨',"Vintage"=>'🕰️'];
            $emoji = $catEmojis[$l['category']] ?? '🏷️';
        ?>
        <div class="my-listing-card">
            <div class="my-listing-thumb">
                <?php if ($l['image'] && file_exists('uploads/products/'.$l['image'])): ?>
                    <img src="uploads/products/<?= htmlspecialchars($l['image']) ?>"
                         style="width:100%;height:100%;object-fit:cover;"
                         alt="<?= htmlspecialchars($l['title']) ?>">
                <?php else: ?>
                    <?= $emoji ?>
                <?php endif; ?>
            </div>
            <div class="my-listing-info">
                <div class="my-listing-title"><?= htmlspecialchars($l['title']) ?></div>
                <div class="my-listing-price">R <?= number_format($l['price'],0) ?></div>
                <a href="product.php?id=<?= $l['id'] ?>" style="font-size:0.75rem;color:var(--accent,#D4704A);text-decoration:none;">View →</a>
                &nbsp;
                <a href="delete-listing.php?id=<?= $l['id'] ?>"
                   onclick="return confirm('Delete this listing?')"
                   class="delete-btn">Delete</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
// Cart badge handled by nav.php

function goStep(n) {
    [1,2,3,4].forEach(i => {
        document.getElementById('step'+i).style.display = i===n ? 'block' : 'none';
        const tab = document.getElementById('tab'+i);
        tab.className = 'step-tab' + (i===n ? ' active' : i<n ? ' done' : '');
    });
    window.scrollTo({top:0,behavior:'smooth'});
}

function selectCond(el, val) {
    document.querySelectorAll('.cond-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('conditionInput').value = val;
}

function calcFee() {
    const price = parseFloat(document.getElementById('askPrice').value) || 0;
    const fee = price * 0.08;
    document.getElementById('feePrice').textContent = 'R ' + Math.round(price);
    document.getElementById('feeFee').textContent = '- R ' + Math.round(fee);
    document.getElementById('feeEarn').textContent = 'R ' + Math.round(price - fee);
}

function previewImage(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('imagePreview').innerHTML =
            `<img src="${e.target.result}" style="max-width:200px;border-radius:10px;border:1px solid #EDE6DC;">`;
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
</body>
</html>
<?php endif; ?>