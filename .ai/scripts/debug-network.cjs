const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
  const page = await context.newPage();

  // Capture the cart-list-sidebar response
  page.on('response', async resp => {
    const url = resp.url();
    if (url.includes('cart-list-sidebar')) {
      const body = await resp.text();
      const data = JSON.parse(body);
      console.log('[cart-list-sidebar RESPONSE] keys:', Object.keys(data));
      console.log('  status:', data.status);
      console.log('  cart_total_product:', data.cart_total_product);
      console.log('  sub_total:', data.sub_total);
      console.log('  final_price:', data.final_price);
      console.log('  data object:', data.data ? JSON.stringify(data.data).substring(0, 300) : 'no data key');
      console.log('  html snippet:', data.html?.substring(0, 500));
      console.log('  html has data-id:', data.html?.includes('data-id'));
      console.log('  html data-id value:', data.html?.match(/data-id="([^"]+)"/g)?.slice(0, 5));
    }
    if (url.includes('change-cart')) {
      const body = await resp.text();
      console.log('[change-cart RESPONSE]:', body);
    }
  });

  try {
    await page.goto('http://ecommercego.dev/stylique/product/test-cotton-shirt', { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    console.log('Adding to cart...');
    await page.click('.addcart-btn.addcart-btn-globaly');
    await new Promise(r => setTimeout(r, 3000));

    console.log('\nOpening drawer (triggers fetchCart)...');
    await page.click('#cart-toggle');
    await new Promise(r => setTimeout(r, 4000));

    // Click remove
    console.log('\nClicking remove...');
    await page.click('#cart-panel .remove_item.text-red-600');
    await new Promise(r => setTimeout(r, 4000));

    const panelContent = await page.$eval('#cart-panel', el => el.innerHTML.substring(0, 200));
    console.log('\nPanel after remove:', panelContent);

  } catch (e) {
    console.error('Error:', e.message);
  } finally {
    await browser.close();
  }
})();
