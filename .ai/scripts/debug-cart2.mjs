import { chromium } from 'playwright';

const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
const page = await context.newPage();

const errors = [];
const consoleMsgs = [];
page.on('console', msg => { consoleMsgs.push(msg.text()); if (msg.type() === 'error') errors.push(msg.text()); });
page.on('pageerror', err => errors.push(err.message));

console.log('Navigating...');
await page.goto('http://ecommercego.dev/stylique/product/test-cotton-shirt', { waitUntil: 'networkidle', timeout: 30000 });
await new Promise(r => setTimeout(r, 3000));

// Add to cart
console.log('Adding to cart...');
await page.click('.addcart-btn.addcart-btn-globaly');
await new Promise(r => setTimeout(r, 3000));

// Check badge
const badge = await page.$eval('#cart-count', el => el.textContent);
console.log('Cart badge:', badge);

// Open the network to see the cart-list-sidebar response
const response = await page.evaluate(async () => {
  const slug = window.location.pathname.split('/').filter(Boolean)[0] || 'stylique';
  const formData = new URLSearchParams();
  formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
  formData.append('method_id', '');
  formData.append('shipping_price', '');
  formData.append('coupon_code', '');
  formData.append('stateId', '');
  formData.append('countryId', '');
  formData.append('cityId', '');
  formData.append('tax_id_value', '');
  formData.append('shipping_final_price', '0');
  formData.append('order_type', '');
  
  const r = await fetch(`/${slug}/cart-list-sidebar`, {
    method: 'POST',
    body: formData,
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
  return await r.json();
});

console.log('API response keys:', Object.keys(response));
console.log('Status:', response.status);
console.log('cart_total_product:', response.cart_total_product);
console.log('html length:', response.html?.length || 0);
console.log('html snippet (first 1000):', response.html?.substring(0, 1000));

// Look for data-id and data-product-id in the HTML
if (response.html) {
  const hasDataId = response.html.includes('data-id');
  const hasDataProductId = response.html.includes('data-product-id');
  const hasCartIdAttr = /data-id\s*=/.test(response.html);
  console.log('HTML has data-id:', hasDataId);
  console.log('HTML has data-product-id:', hasDataProductId);
  
  // Find remove button pattern
  const removeMatch = response.html.match(/data-id="[^"]*"/g);
  console.log('data-id attributes found:', removeMatch?.slice(0, 5));
}

// Open drawer
console.log('\nOpening drawer...');
await page.click('#cart-toggle');
await new Promise(r => setTimeout(r, 5000));

// Check panel content
const panelContent = await page.$eval('#cart-panel', el => el.innerHTML);
console.log('Panel innerHTML length:', panelContent.length);
console.log('Panel innerHTML (first 1000):', panelContent.substring(0, 1000));

const hasItem = panelContent.includes('Test Cotton Shirt') || panelContent.includes('border-b pb-4');
console.log('Has item in panel:', hasItem);

console.log('\nConsole errors:', errors);
console.log('\nCart-related messages:', consoleMsgs.filter(m => m.includes('Cart') || m.includes('island') || m.includes('fetchCart')));

await browser.close();
