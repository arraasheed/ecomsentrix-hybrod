const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://ecommercego.dev';
const STORE = '/stylique';

async function main() {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
  const page = await context.newPage();
  
  page.on('pageerror', err => console.log('[PAGE ERROR]', err.message));
  page.on('console', msg => {
    if (msg.type() === 'error') console.log('[CONSOLE ERR]', msg.text());
  });

  try {
    await page.goto(`${BASE_URL}${STORE}/product/test-cotton-shirt`, { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    // Add to cart
    console.log('Adding to cart...');
    await page.click('.addcart-btn.addcart-btn-globaly');
    await new Promise(r => setTimeout(r, 3000));

    // Open drawer
    console.log('Opening drawer...');
    await page.click('#cart-toggle');
    await new Promise(r => setTimeout(r, 3000));

    // Check drawer state BEFORE remove
    const beforeRemove = await page.$eval('#cart-panel', el => ({
      hasItem: el.innerHTML.includes('border-b pb-4'),
      hasRemoveBtn: el.innerHTML.includes('remove_item'),
      innerHTML: el.innerHTML.substring(0, 500)
    }));
    console.log('Before remove:', JSON.stringify(beforeRemove, null, 2));

    // Click remove
    console.log('Clicking remove...');
    const removeBtn = await page.$('#cart-panel .remove_item');
    if (removeBtn) {
      await removeBtn.click();
      await new Promise(r => setTimeout(r, 4000));
    }

    // Check drawer state AFTER remove
    const afterRemove = await page.$eval('#cart-panel', el => ({
      hasItem: el.innerHTML.includes('border-b pb-4'),
      hasEmpty: el.innerHTML.includes('cart is empty'),
      hasRemoveBtn: el.innerHTML.includes('remove_item'),
      innerHTML: el.innerHTML.substring(0, 500)
    }));
    console.log('After remove:', JSON.stringify(afterRemove, null, 2));

    // Check cart badge
    const badge = await page.$eval('#cart-count', el => el.textContent);
    console.log('Cart badge:', badge);

    // Check the network response for change-cart
    console.log('\n--- Checking if the remove request was made ---');

  } catch (e) {
    console.error('Error:', e.message);
  } finally {
    await browser.close();
  }
}

main().catch(console.error);
