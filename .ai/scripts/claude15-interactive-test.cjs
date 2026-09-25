const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
  const page = await context.newPage();
  const errors = [];
  page.on('pageerror', err => errors.push(err.message));
  page.on('console', msg => {
    if (msg.type() === 'error' && (msg.text().includes('island') || msg.text().includes('vue') || msg.text().includes('Vue'))) {
      errors.push(msg.text());
    }
  });

  try {
    // Go to product page
    console.log('Navigating to product page...');
    await page.goto('http://ecommercego.dev/stylique/product/test-cotton-shirt', { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    // Add to cart
    console.log('Adding to cart...');
    await page.click('.addcart-btn.addcart-btn-globaly');
    await new Promise(r => setTimeout(r, 3000));

    // Check cart badge
    const badgeText = await page.$eval('#cart-count', el => el.textContent);
    console.log('Cart badge count:', badgeText);

    // Open drawer
    console.log('Opening cart drawer...');
    await page.click('#cart-toggle');
    await new Promise(r => setTimeout(r, 3000));

    // Check drawer content
    const drawerInfo = await page.evaluate(() => {
      const panel = document.getElementById('cart-panel');
      return {
        hasOpen: panel ? panel.classList.contains('open') : false,
        hasYourCart: panel ? panel.innerHTML.includes('Your Cart') : false,
        hasItems: panel ? panel.innerHTML.includes('border-b') : false,
        hasSubtotal: panel ? panel.innerHTML.includes('Subtotal') : false,
        hasCheckout: panel ? panel.innerHTML.includes('Proceed to Checkout') : false,
        contentLength: panel ? panel.innerHTML.length : 0,
      };
    });
    console.log('Drawer info:', JSON.stringify(drawerInfo, null, 2));

    // Test quantity change
    console.log('Testing quantity change...');
    const incrementBtn = await page.$('.quantity-increment');
    if (incrementBtn) {
      await incrementBtn.click();
      await new Promise(r => setTimeout(r, 2000));
      
      const qtyInput = await page.$eval('#cart-panel .quantity', el => el.value);
      console.log('Quantity after increment:', qtyInput);
    }

    // Take screenshot
    await page.screenshot({ path: '.ai/screenshots/claude15-cart-with-item.png', fullPage: false });

    // Summary
    console.log('\n=== Test Summary ===');
    console.log('Cart badge:', badgeText);
    console.log('Drawer has items:', drawerInfo.hasItems);
    console.log('Drawer has checkout:', drawerInfo.hasCheckout);
    console.log('Vue/island errors:', errors.length);
    if (errors.length > 0) console.log('Errors:', errors);

    // Memory check
    const memBefore = await page.evaluate(() => performance.memory ? performance.memory.usedJSHeapSize : 0);
    console.log('Memory before navigation:', Math.round(memBefore / 1024 / 1024) + 'MB');

    // Test Turbo navigation
    for (let i = 0; i < 5; i++) {
      await page.goto('http://ecommercego.dev/stylique/home', { waitUntil: 'domcontentloaded', timeout: 15000 });
      await new Promise(r => setTimeout(r, 500));
    }

    const memAfter = await page.evaluate(() => performance.memory ? performance.memory.usedJSHeapSize : 0);
    console.log('Memory after 5 navigations:', Math.round(memAfter / 1024 / 1024) + 'MB');
    console.log('Memory ratio:', (memAfter / memBefore).toFixed(2));

    // Check final state
    const finalStats = await page.evaluate(() => {
      return window.__islandRegistry ? window.__islandRegistry.stats : null;
    });
    console.log('Final island stats:', JSON.stringify(finalStats, null, 2));

    const toggleCount = await page.$$eval('#cart-toggle', els => els.length);
    console.log('Cart toggle count (should be 1):', toggleCount);

    await page.screenshot({ path: '.ai/screenshots/claude15-final-state.png', fullPage: false });

  } catch (e) {
    console.error('Test failed:', e.message);
    await page.screenshot({ path: '.ai/screenshots/claude15-error.png', fullPage: false });
  } finally {
    await browser.close();
  }
})();
