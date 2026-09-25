const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
  const page = await context.newPage();
  
  page.on('console', msg => {
    if (msg.type() === 'error' && msg.text().includes('CartDrawer')) console.log('[Vue Error]', msg.text());
  });
  page.on('pageerror', err => console.log('[PAGE ERROR]', err.message.substring(0, 100)));

  // Capture change-cart response
  page.on('response', async resp => {
    if (resp.url().includes('change-cart')) {
      const body = await resp.text();
      console.log('[change-cart RESPONSE]:', body);
    }
  });

  try {
    await page.goto('http://ecommercego.dev/stylique/product/test-cotton-shirt', { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    await page.click('.addcart-btn.addcart-btn-globaly');
    await new Promise(r => setTimeout(r, 3000));

    await page.click('#cart-toggle');
    await new Promise(r => setTimeout(r, 4000));

    // Click the Vue-rendered remove button (class: remove_item text-red-600)
    console.log('Clicking remove...');
    await page.click('#cart-panel .remove_item.text-red-600');
    await new Promise(r => setTimeout(r, 4000));

    // Get FULL panel HTML
    const fullPanel = await page.$eval('#cart-panel', el => el.innerHTML);
    console.log('FULL PANEL HTML after remove:');
    console.log(fullPanel);

  } catch (e) {
    console.error('Error:', e.message);
  } finally {
    await browser.close();
  }
})();
