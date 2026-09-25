const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
  const page = await context.newPage();
  
  page.on('console', msg => {
    if (msg.text().includes('CartDrawer')) console.log('[Vue]', msg.text());
  });

  try {
    await page.goto('http://ecommercego.dev/stylique/product/test-cotton-shirt', { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    await page.click('.addcart-btn.addcart-btn-globaly');
    await new Promise(r => setTimeout(r, 3000));
    await page.click('#cart-toggle');
    await new Promise(r => setTimeout(r, 2000));

    console.log('Removing item...');
    const removeBtn = await page.$('#cart-panel .remove_item');
    if (removeBtn) {
      await removeBtn.click();
      await new Promise(r => setTimeout(r, 4000));
    }

    const panelClass = await page.$eval('#cart-panel', el => el.className);
    const panelContent = await page.$eval('#cart-panel', el => el.innerHTML?.substring(0, 200));
    console.log('Panel class:', panelClass);
    console.log('Panel content (first 200):', panelContent);
    
  } catch (e) {
    console.error('Error:', e.message);
  } finally {
    await browser.close();
  }
})();
