import { chromium } from 'playwright';

const browser = await chromium.launch({ headless: false });
const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
const page = await context.newPage();

const errors = [];
const consoleMsgs = [];
page.on('console', msg => { consoleMsgs.push(msg.text()); if (msg.type() === 'error') errors.push(msg.text()); });
page.on('pageerror', err => errors.push(err.message));

console.log('Navigating...');
await page.goto('http://ecommercego.dev/stylique/product/test-cotton-shirt', { waitUntil: 'networkidle', timeout: 30000 });
await new Promise(r => setTimeout(r, 3000));

// Check island registry
const stats = await page.evaluate(() => window.__islandRegistry ? window.__islandRegistry.stats : null);
console.log('Island registry stats:', JSON.stringify(stats));

// Add to cart
console.log('Adding to cart...');
await page.click('.addcart-btn.addcart-btn-globaly');
await new Promise(r => setTimeout(r, 3000));

// Check badge
const badge = await page.$eval('#cart-count', el => el.textContent);
console.log('Cart badge:', badge);

// Open drawer
console.log('Opening drawer...');
await page.click('#cart-toggle');
await new Promise(r => setTimeout(r, 5000));

// Check panel content
const panelContent = await page.$eval('#cart-panel', el => el.innerHTML.substring(0, 500));
console.log('Panel innerHTML (first 500 chars):', panelContent);

// Check if Vue rendered anything
const hasVueContent = await page.$eval('#cart-panel', el => el.innerHTML.includes('Your Cart'));
console.log('Has Vue content (Your Cart):', hasVueContent);

console.log('Console errors:', errors);
console.log('Cart-related messages:', consoleMsgs.filter(m => m.includes('Cart') || m.includes('island') || m.includes('drawer') || m.includes('fetchCart') || m.includes('Vue') || m.includes('cart-toggle')));

await browser.close();
