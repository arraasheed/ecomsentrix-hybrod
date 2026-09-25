const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
  const page = await context.newPage();
  
  page.on('pageerror', err => console.log('[PAGE ERROR]', err.message));
  page.on('console', msg => {
    if (msg.type() === 'error') console.log('[CONSOLE ERR]', msg.text());
  });

  try {
    await page.goto('http://ecommercego.dev/stylique/product/test-cotton-shirt', { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    await page.click('.addcart-btn.addcart-btn-globaly');
    await new Promise(r => setTimeout(r, 3000));
    await page.click('#cart-toggle');
    await new Promise(r => setTimeout(r, 2000));

    // Check state before remove
    const beforeState = await page.evaluate(() => {
      if (!window.__islandRegistry) return { error: 'no registry' };
      return { message: 'check' };
    });
    console.log('Before remove:', beforeState);

    const removeBtn = await page.$('#cart-panel .remove_item');
    if (removeBtn) {
      await removeBtn.click();
      await new Promise(r => setTimeout(r, 4000));
    }

    // Full innerHTML of cart-panel
    const fullHTML = await page.$eval('#cart-panel', el => el.innerHTML);
    console.log('Full cart panel HTML after remove:');
    console.log(fullHTML);
    
    // Check for empty state text
    console.log('\nChecking text content:');
    console.log('  Includes "empty":', fullHTML.includes('empty'));
    console.log('  Includes "Your cart":', fullHTML.includes('Your cart'));
    console.log('  Includes "empty" (case-insensitive):', fullHTML.toLowerCase().includes('empty'));

  } catch (e) {
    console.error('Error:', e.message);
  } finally {
    await browser.close();
  }
})();
