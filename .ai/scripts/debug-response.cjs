const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
  const page = await context.newPage();

  // Intercept the change-cart response
  await page.route('**/change-cart', async route => {
    const response = await route.fetch();
    const body = await response.text();
    console.log('[change-cart RESPONSE]', body);
    route.fulfill({
      response,
    });
  });

  page.on('pageerror', err => console.log('[PAGE ERROR]', err.message));

  try {
    await page.goto('http://ecommercego.dev/stylique/product/test-cotton-shirt', { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    // Add to cart
    await page.click('.addcart-btn.addcart-btn-globaly');
    await new Promise(r => setTimeout(r, 3000));
    
    // Open drawer
    await page.click('#cart-toggle');
    await new Promise(r => setTimeout(r, 2000));

    // Click remove
    console.log('Clicking remove...');
    const removeBtn = await page.$('#cart-panel .remove_item');
    if (removeBtn) {
      await removeBtn.click();
      await new Promise(r => setTimeout(r, 4000));
    }

    // Check final state
    const finalState = await page.evaluate(() => {
      const panel = document.getElementById('cart-panel');
      return {
        hasOpen: panel?.classList?.contains('open'),
        innerHTML: (panel?.innerHTML || '').substring(0, 300),
        hasEmpty: panel?.innerHTML?.includes('empty') || panel?.innerHTML?.includes('Empty'),
        badge: document.getElementById('cart-count')?.textContent,
      };
    });
    console.log('Final state:', JSON.stringify(finalState, null, 2));

  } catch (e) {
    console.error('Error:', e.message);
  } finally {
    await browser.close();
  }
})();
