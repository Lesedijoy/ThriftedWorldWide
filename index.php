<?php
session_start();
include 'includes/db.php';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>ThriftedWorldWide</title>

        <!-- Adding a link to the css file. css file is always in the head. -->
        <link rel="stylesheet" href="style.css">
    </head>

    <body>

    <?php include 'includes/nav.php'; ?>
  
<!--HERO SECTION(THE FIRST BIG SECTION ON TOP OF A WEBSITE) -->
    <section class="hero">

<!--LEFT-->
        <div class="hero-left">
            <div class="badge">South Africas Thrift Marketplace</div>


            <h1 class="hero-heading">
            Buy &amp; sell<br>
            <em>pre-loved</em><br>
            fashion.
            </h1>


            <p class="hero-sub">
            From Vintage gems to designer steals- ThriftedWorldWide
            connects everyday people to build a more sustainable wardrobe.
            </p>

        <div class="hero-actions">
            <a href="browse.php" class="btn-cta">Start Shopping →</a>
            <a href="seller-dashboard.php" class="btn-outline">List an Item</a>
        </div>
    </div>
      <!-- Right – listing cards -->
    <div class="hero-right">
 
      <div class="listing-card">
        <img class="card-image" src="Zara Wrap Dress.jpeg" alt="Zara Wrap Dress">
        <div class="card-info">
          <div class="card-title">Zara Wrap Dress</div>
          <div class="card-meta">Size M · Gently worn</div>
          <div class="card-price">R 320</div>
        </div>
        <span class="card-tag">New</span>
      </div>
 
      <div class="listing-card">
        <img class="card-image" src="AirForce1.jpeg" alt="Air Force 1">
        <div class="card-info">
          <div class="card-title">Nike Air Force 1</div>
          <div class="card-meta">Size 9 · Vintage</div>
          <div class="card-price">R 850</div>
        </div>
        <span class="card-tag">Hot</span>
      </div>
 
      <div class="listing-card">
        <img class="card-image" src="Guess Bag.jpeg" alt="Guess Bag">
        <div class="card-info">
          <div class="card-title">Guess Handbag</div>
          <div class="card-meta">Authenticated ✓</div>
          <div class="card-price">R 1,200</div>
        </div>
        <span class="card-tag verified">Verified</span>
      </div>
    </div>
  </section>
 
  <!-- STATS + TRUST BAR (Image 1) -->
  <section class="stats-section">
    <div class="stats-row">
      <div class="stat">
        <span class="stat-number">12K+</span>
        <span class="stat-label">Active Listings</span>
      </div>
      <div class="stat">
        <span class="stat-number">4.8K</span>
        <span class="stat-label">Verified Sellers</span>
      </div>
      <div class="stat">
        <span class="stat-number">98%</span>
        <span class="stat-label">Happy Buyers</span>
      </div>
    </div>
    <div class="trust-bar">
      <div class="trust-item"><span>🔒</span> Secure Escrow Payments</div>
      <div class="trust-item"><span>✅</span> Seller Verification</div>
      <div class="trust-item"><span>🛡️</span> Buyer Protection</div>
      <div class="trust-item"><span>🏷️</span> Price Negotiation</div>
      <div class="trust-item"><span>♻️</span> Eco-Friendly Fashion</div>
    </div>
  </section>
 
  <!-- SHOP BY CATEGORY (Images 1 & 2) -->
  <section class="categories-section">
    <p class="section-eyebrow">SHOP BY CATEGORY</p>
    <h2 class="section-heading">Find your <em>style</em></h2>
    <div class="categories-grid">
      <div class="category-card">
        <img class="card-image" src="womens clothing.jpeg" alt="womens clothing">
        <div class="category-name">Women's Clothing</div>
        <div class="category-count">3,412 items</div>
      </div>
      <div class="category-card">
        <img class="card-image" src="mens clothing.jpeg" alt="mens clothing">
        <div class="category-name">Men's Clothing</div>
        <div class="category-count">2,108 items</div>
      </div>
      <div class="category-card">
        <img class="card-image" src="sneakers .jpeg" alt="sneakers">
        <div class="category-name">Sneakers</div>
        <div class="category-count">984 items</div>
      </div>
      <div class="category-card">
        <img class="card-image" src="bags.jpeg" alt="bags">
        <div class="category-name">Bags &amp; Purses</div>
        <div class="category-count">671 items</div>
      </div>
      <div class="category-card">
        <img class="card-image" src="accessories.jpeg" alt="accessories">
        <div class="category-name">Accessories</div>
        <div class="category-count">1,230 items</div>
      </div>
      <div class="category-card">
        <img class="card-image" src="outwear.jpeg" alt="outwear">
        <div class="category-name">Outerwear</div>
        <div class="category-count">445 items</div>
      </div>
      <div class="category-card">
       <img class="card-image" src="designer.jpeg" alt="designer">
        <div class="category-name">Designer</div>
        <div class="category-count">312 items</div>
      </div>
      <div class="category-card">
        <img class="card-image" src="vintage.jpeg" alt="vintage">
        <div class="category-name">Vintage</div>
        <div class="category-count">890 items</div>
      </div>
    </div>
  </section>
 
  <!-- FRESH FINDS (Image 3) -->
  <section class="fresh-section">
    <div class="fresh-header">
      <div>
        <p class="section-eyebrow">JUST LISTED</p>
        <h2 class="section-heading">Fresh <em>finds</em></h2>
      </div>
      <a href="#" class="see-all">See all listings →</a>
    </div>
    <div class="listings-grid">
      <div class="product-card">
        <div class="product-image" style="background:#EDE8E0;">
          <span class="product-badge">Gently Worn</span>
          <button class="wishlist-btn">🤍</button>
          <img class="card-image" src="floral dress.jpeg" alt="floral dress">
        </div>
        <div class="product-info">
          <p class="product-brand">H&amp;M</p>
          <p class="product-name">Floral Midi Dress</p>
          <div class="product-footer">
            <span class="product-price">R 180</span>
            <span class="product-size">Size S</span>
          </div>
        </div>
      </div>
      <div class="product-card">
        <div class="product-image" style="background:#DFE8F0;">
          <span class="product-badge verified-badge">Verified</span>
          <button class="wishlist-btn">🤍</button>
          <img class="card-image" src="samba.jpeg" alt="samba">
        </div>
        <div class="product-info">
          <p class="product-brand">ADIDAS</p>
          <p class="product-name">Samba OG White/Black</p>
          <div class="product-footer">
            <span class="product-price">R 1,450</span>
            <span class="product-size">UK 8</span>
          </div>
        </div>
      </div>
      <div class="product-card">
        <div class="product-image" style="background:#EAE0F0;">
          <span class="product-badge">Pre-Owned</span>
          <button class="wishlist-btn">🤍</button>
          <img class="card-image" src="glasses.jpeg" alt="glasses">
        </div>
        <div class="product-info">
          <p class="product-brand">RAY-BAN</p>
          <p class="product-name">Wayfarer Sunglasses</p>
          <div class="product-footer">
            <span class="product-price">R 550</span>
            <span class="product-size">Unisex</span>
          </div>
        </div>
      </div>
      <div class="product-card">
        <div class="product-image" style="background:#F5EAE0;">
          <span class="product-badge">Vintage</span>
          <button class="wishlist-btn">🤍</button>
          <img class="card-image" src="90 jacket.jpeg" alt="90 jacket">
        </div>
        <div class="product-info">
          <p class="product-brand">LEVI'S</p>
          <p class="product-name">90s Denim Jacket</p>
          <div class="product-footer">
            <span class="product-price">R 420</span>
            <span class="product-size">Size L</span>
          </div>
        </div>
      </div>
    </div>
  </section>
 
  <!-- HOW IT WORKS (Image 4) -->
  <section class="how " id="how">
    <h2 class="section-heading">How it <em>works</em></h2>
    <div class="steps-grid">
      <div class="step-card">
        <span class="step-number">01</span>
        <div class="step-icon">📷</div>
        <h3 class="step-title">List your item</h3>
        <p class="step-desc">Take photos, add a description, set your price. It takes less than 5 minutes.</p>
      </div>
      <div class="step-card">
        <span class="step-number">02</span>
        <div class="step-icon">💬</div>
        <h3 class="step-title">Chat &amp; negotiate</h3>
        <p class="step-desc">Buyers can message you, make offers, or buy immediately at your listed price.</p>
      </div>
      <div class="step-card">
        <span class="step-number">03</span>
        <div class="step-icon">🔒</div>
        <h3 class="step-title">Secure payment</h3>
        <p class="step-desc">Funds are held in escrow until the buyer confirms receipt. Zero risk.</p>
      </div>
      <div class="step-card">
        <span class="step-number">04</span>
        <div class="step-icon">🚚</div>
        <h3 class="step-title">Ship &amp; earn</h3>
        <p class="step-desc">Drop off your parcel and get paid directly to your wallet or bank account.</p>
      </div>
    </div>
  </section>
 
  <!-- CTA BANNER + FOOTER (Image 5) -->
  <section class="cta-section">
    <div class="cta-banner">
      <h2 class="cta-heading">Ready to clear your <em>wardrobe</em> and earn?</h2>
      <a href="seller-dashboard.php" class="btn-cta-banner">Start Selling Today →</a>
    </div>
  </section>
 
  <footer class="footer">
    <div class="footer-inner">
      <div class="footer-brand">
        <p class="footer-logo">ThriftedWorldWide</p>
        <p class="footer-tagline">South Africa's trusted C2C fashion marketplace. Buy, sell, and thrift — sustainably.</p>
      </div>
      <div class="footer-col">
        <p class="footer-col-title">EXPLORE</p>
        <a href="#">Browse listings</a>
        <a href="#">Designer items</a>
        <a href="#">New arrivals</a>
        <a href="#">Trending</a>
      </div>
      <div class="footer-col">
        <p class="footer-col-title">SELLERS</p>
        <a href="#">Start selling</a>
        <a href="#">Seller guide</a>
        <a href="#">Authentication</a>
        <a href="#">Fees</a>
      </div>
      <div class="footer-col">
        <p class="footer-col-title">SUPPORT</p>
        <a href="#">Help centre</a>
        <a href="#">Buyer protection</a>
        <a href="#">Contact us</a>
        <a href="#">Privacy policy</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 ThriftedWorldWide (Pty) Ltd. All rights reserved.</span>
      <span>Made with ♻️ in South Africa</span>
    </div>
  </footer>


        <!-- Adding a link to the javascript file.The javascript file is always at the bottom so that the page loads faster. -->
        <script src="script.js"></script>
    </body>

</html>