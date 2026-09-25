const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
  const page = await context.newPage();

  page.on('console', msg => {
    if (msg.type() === 'log' && msg.text().includes('[CartDrawer')) {
      console.log('[Vue]', msg.text().substring(0, 200));
    }
  });
  page.on('pageerror', err => console.log('[PAGE ERROR]', err.message.substring(0, 200)));

  try {
    await page.goto('http://ecommercego.dev/stylique/product/test-cotton-shirt', { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    // Add to cart
    console.log('Step 1: Adding to cart...');
    await page.click('.addcart-btn.addcart-btn-globaly');
    await new Promise(r => setTimeout(r, 3000));

    // Check cart panel BEFORE opening drawer
    const panelBefore = await page.$eval('#cart-panel', el => el.innerHTML);
    console.log('Panel content BEFORE opening drawer:', panelBefore.substring(0, 200) || '(empty)');

    // Check if jQuery already populated the panel
    const jqPopulated = panelBefore.includes('remove_item_from_cart');
    console.log('jQuery populated panel:', jqPopulated);

    // Now open drawer
    console.log('\nStep 2: Opening drawer...');
    await page.click('#cart-toggle');
    await new Promise(r => setTimeout(r, 3000));

    // Check panel AFTER opening
    const panelAfter = await page.evaluate(() => {
      const panel = document.getElementById('cart-panel');
      const removeBtn = panel?.querySelector('.remove_item');
      return {
        hasOpen: panel?.classList?.contains('open'),
        removeBtnClass: removeBtn?.className,
        removeBtnText: removeBtn?.innerHTML?.substring(0, 100),
        allButtons: Array.from(panel?.querySelectorAll('button')).map(b => ({ class: b.className, text: b.textContent?.substring(0, 30) })),
      };
    });
    console.log('Panel AFTER opening:', JSON.stringify(panelAfter, null, 2));

  } catch (e) {
    console.error('Error:', e.message);
  } finally {
    await browser.close();
  }
})();
