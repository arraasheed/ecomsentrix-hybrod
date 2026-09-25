const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
  const page = await context.newPage();
  const errors = [];
  page.on('pageerror', err => errors.push({ time: Date.now(), msg: err.message, stack: err.stack }));
  page.on('console', msg => {
    if (msg.type() === 'error') errors.push({ time: Date.now(), msg: msg.text() });
  });

  try {
    console.log('=== Phase 1: Product Page → Add to Cart ===');
    await page.goto('http://ecommercego.dev/stylique/product/test-cotton-shirt', { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));
    
    const errorsBefore = errors.length;
    await page.click('.addcart-btn.addcart-btn-globaly');
    await new Promise(r => setTimeout(r, 3000));
    const errorsAfterAdd = errors.length;
    console.log('Errors after add to cart:', errorsAfterAdd - errorsBefore);

    console.log('\n=== Phase 2: Open Cart Drawer ===');
    const errorsBeforeOpen = errors.length;
    await page.click('#cart-toggle');
    await new Promise(r => setTimeout(r, 3000));
    const errorsAfterOpen = errors.length;
    console.log('Errors after opening drawer:', errorsAfterOpen - errorsBeforeOpen);

    console.log('\n=== Phase 3: Increment Quantity ===');
    const errorsBeforeInc = errors.length;
    const incrementBtn = await page.$('.quantity-increment');
    if (incrementBtn) {
      // Check if button exists inside the drawer (Vue-rendered)
      const btnInfo = await page.evaluate(() => {
        const btn = document.querySelector('.quantity-increment');
        return { exists: !!btn, parent: btn?.parentElement?.className, inDrawer: !!document.querySelector('#cart-panel .quantity-increment') };
      });
      console.log('Increment button info:', btnInfo);
      await incrementBtn.click();
      await new Promise(r => setTimeout(r, 2000));
    }
    const errorsAfterInc = errors.length;
    console.log('Errors after increment:', errorsAfterInc - errorsBeforeInc);

    console.log('\n=== Phase 4: Close Drawer ===');
    const errorsBeforeClose = errors.length;
    const closeBtn = await page.$('#cart-panel .text-2xl, #cart-close');
    if (closeBtn) {
      await closeBtn.click();
      await new Promise(r => setTimeout(r, 1000));
    }
    const errorsAfterClose = errors.length;
    console.log('Errors after close:', errorsAfterClose - errorsBeforeClose);

    console.log('\n=== Phase 5: Turbo Navigation ===');
    const errorsBeforeNav = errors.length;
    await page.goto('http://ecommercego.dev/stylique/home', { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));
    const errorsAfterNav = errors.length;
    console.log('Errors after navigation:', errorsAfterNav - errorsBeforeNav);

    // Back to product page (should have Turbo cache)
    const errorsBeforeBack = errors.length;
    await page.goBack();
    await new Promise(r => setTimeout(r, 2000));
    const errorsAfterBack = errors.length;
    console.log('Errors after back:', errorsAfterBack - errorsBeforeBack);

    console.log('\n=== Phase 6: Re-open Drawer ===');
    const errorsBeforeReopen = errors.length;
    const cartToggle = await page.$('#cart-toggle');
    if (cartToggle) {
      await page.click('#cart-toggle');
      await new Promise(r => setTimeout(r, 2000));
    }
    const errorsAfterReopen = errors.length;
    console.log('Errors after re-open:', errorsAfterReopen - errorsBeforeReopen);

    console.log('\n=== Full Error List ===');
    errors.forEach((e, i) => {
      console.log(`[${i}] ${e.msg?.substring(0, 100)}`);
    });

    await page.screenshot({ path: '.ai/screenshots/claude15-full-flow.png', fullPage: false });

  } catch (e) {
    console.error('Test failed:', e.message);
    await page.screenshot({ path: '.ai/screenshots/claude15-error.png', fullPage: false });
  } finally {
    await browser.close();
  }
})();
