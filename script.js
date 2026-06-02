const button = document.getElementById("btn");
button.addEventListener("click", ( )=> { alert ("JavaScript is Working!");});

// ---- LISTINGS DATA (will come from PHP/MySQL in Week 2) ----
const listings = [
  {emoji:'👗',bg:'#F2EDE4',brand:'Zara',name:'Wrap Midi Dress',price:320,size:'M',badge:'Gently Worn',badgeClass:'badge-worn',seller:'@thrift_lex'},
  {emoji:'👟',bg:'#E8F0F5',brand:'Adidas',name:'Samba OG White/Black',price:1450,size:'UK 8',badge:'Verified',badgeClass:'badge-verified',seller:'@sneaker_za'},
  {emoji:'🧥',bg:'#FEF3ED',brand:"Levi's",name:"90s Denim Jacket",price:420,size:'L',badge:'Vintage',badgeClass:'badge-vintage',seller:'@vintage_jo'},
  {emoji:'👜',bg:'#F5EFF6',brand:'Guess',name:'Logo Crossbody Bag',price:1200,size:'One size',badge:'Verified',badgeClass:'badge-verified',seller:'@gucci_sa'},
  {emoji:'👔',bg:'#EBF3EB',brand:'Ralph Lauren',name:'Oxford Button-Down',price:280,size:'M',badge:'New with tags',badgeClass:'badge-nwt',seller:'@polo_thrift'},
  {emoji:'🕶️',bg:'#F5EFF6',brand:'Ray-Ban',name:'Wayfarer Sunglasses',price:550,size:'Unisex',badge:'Gently Worn',badgeClass:'badge-worn',seller:'@sunglasses_za'},
  {emoji:'💃',bg:'#FEF0F0',brand:'H&M',name:'Floral Midi Dress',price:180,size:'S',badge:'Gently Worn',badgeClass:'badge-worn',seller:'@fashion_cape'},
  {emoji:'🧤',bg:'#F2EDE4',brand:'Nike',name:'Tech Fleece Hoodie',price:650,size:'L',badge:'Gently Worn',badgeClass:'badge-worn',seller:'@nike_thrift'},
  {emoji:'👒',bg:'#FFFBF0',brand:'Woolworths',name:'Wide Brim Sun Hat',price:120,size:'One size',badge:'New with tags',badgeClass:'badge-nwt',seller:'@sunny_style'},
  {emoji:'👠',bg:'#FEF3ED',brand:'Steve Madden',name:'Block Heel Pumps',price:390,size:'UK 6',badge:'Pre-Owned',badgeClass:'badge-worn',seller:'@heels_za'},
  {emoji:'🎽',bg:'#EBF3EB',brand:'Puma',name:'Vintage Track Jacket',price:340,size:'XL',badge:'Vintage',badgeClass:'badge-vintage',seller:'@retro_run'},
  {emoji:'🧣',bg:'#E8EEF8',brand:'Polo',name:'Cashmere Blend Scarf',price:210,size:'One size',badge:'Gently Worn',badgeClass:'badge-worn',seller:'@polo_thrift'},
];

let currentListings = [...listings];

// Render listing cards into the grid
function renderListings(data) {
  const grid = document.getElementById('listingsGrid');
  if (data.length === 0) {
    grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1;">
      <div class="emoji">🔍</div><p>No listings found. Try different filters.</p></div>`;
    return;
  }
  grid.innerHTML = data.map(l => `
    <div class="listing-card" onclick="window.location='product.html'">
      <div class="listing-img" style="background:${l.bg};">${l.emoji}
        <span class="listing-badge ${l.badgeClass}">${l.badge}</span>
        <span class="listing-heart" onclick="toggleHeart(event,this)">🤍</span>
      </div>
      <div class="listing-info">
        <div class="listing-brand">${l.brand}</div>
        <div class="listing-name">${l.name}</div>
        <div class="listing-meta">
          <span class="listing-price">R ${l.price.toLocaleString()}</span>
          <span class="listing-cond">${l.size}</span>
        </div>
        <div class="listing-seller">${l.seller}</div>
      </div>
    </div>
  `).join('');
  document.getElementById('resultsCount').textContent = `Showing ${data.length} of 3,847 results`;
}

// Toggle wishlist heart icon
function toggleHeart(e, el) {
  e.stopPropagation();
  el.textContent = el.textContent === '🤍' ? '❤️' : '🤍';
}

// Toggle size pill selected state
function toggleSize(el) {
  el.classList.toggle('active');
}

// Search by name or brand
function doSearch() {
  const q = document.getElementById('searchInput').value.toLowerCase().trim();
  currentListings = q
    ? listings.filter(l => l.name.toLowerCase().includes(q) || l.brand.toLowerCase().includes(q))
    : [...listings];
  renderListings(currentListings);
}

// Search on Enter key
document.getElementById('searchInput').addEventListener('keyup', function(e) {
  if (e.key === 'Enter') doSearch();
});

// Sort listings
function sortListings() {
  const val = document.getElementById('sortSelect').value;
  let sorted = [...currentListings];
  if (val === 'Price: low to high') sorted.sort((a,b) => a.price - b.price);
  if (val === 'Price: high to low') sorted.sort((a,b) => b.price - a.price);
  renderListings(sorted);
}

// Filter by price range
function applyPriceFilter() {
  const min = parseFloat(document.getElementById('minPrice').value) || 0;
  const max = parseFloat(document.getElementById('maxPrice').value) || Infinity;
  currentListings = listings.filter(l => l.price >= min && l.price <= max);
  renderListings(currentListings);
}

// Clear all filters and reset
function clearFilters() {
  document.querySelectorAll('.filter-option input[type=checkbox]').forEach(cb => cb.checked = false);
  document.getElementById('minPrice').value = '';
  document.getElementById('maxPrice').value = '';
  document.querySelectorAll('.size-pill').forEach(p => p.classList.remove('active'));
  document.getElementById('activeFilters').innerHTML = '';
  currentListings = [...listings];
  renderListings(currentListings);
}

// Pagination button highlight
document.querySelectorAll('.page-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.page-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
  });
});

// Initial render on page load
renderListings(listings);
            