import { chromium } from 'playwright';

const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
const page = await context.newPage();

const errors = [];
page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
page.on('pageerror', err => errors.push(err.message));

await page.goto('http://ecommercego.dev/stylique/product/test-cotton-shirt', { waitUntil: 'networkidle', timeout: 30000 });
await new Promise(r => setTimeout(r, 3000));

// Add to cart
await page.click('.addcart-btn.addcart-btn-globaly');
await new Promise(r => setTimeout(r, 3000));

// Get the cart HTML response
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

// Print the full HTML to see the element structure
console.log('Full HTML response:');
console.log(response.html);

await browser.close();
