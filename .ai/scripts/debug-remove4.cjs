const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
  const page = await context.newPage();
  
  page.on('console', msg => console.log('[PAGE]', msg.type(), msg.text().substring(0, 200)));
  page.on('pageerror', err => console.log('[PAGE ERROR]', err.message.substring(0, 200)));

  try {
    await page.goto('http://ecommercego.dev/stylique/product/test-cotton-shirt', { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    console.log('Adding to cart...');
    await page.click('.addcart-btn.addcart-btn-globaly');
    await new Promise(r => setTimeout(r, 3000));
    
    console.log('Opening drawer...');
    await page.click('#cart-toggle');
    await new Promise(r => setTimeout(r, 3000));

    console.log('Checking remove button...');
    const removeInfo = await page.evaluate(() => {
      const btn = document.querySelector('#cart-panel .remove_item');
      return {
        exists: !!btn,
        hasClickHandler: btn ? (btn.onclick ? 'inline' : 'delegated') : false,
        className: btn?.className,
        parentClass: btn?.parentElement?.className,
      };
    });
    console.log('Remove button info:', JSON.stringify(removeInfo, null, 2));

    console.log('Clicking remove button...');
    await page.click('#cart-panel .remove_item');
    await new Promise(r => setTimeout(r, 4000));

    const panelContent = await page.$eval('#cart-panel', el => el.innerHTML?.substring(0, 400));
    console.log('After click, panel content:', panelContent);

  } catch (e) {
    console.error('Error:', e.message);
  } finally {
    await browser.close();
  }
})();
