const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://ecommercego.dev';
const STORE = '/stylique';
const SCREENSHOT_DIR = path.resolve('.ai/screenshots');

if (!fs.existsSync(SCREENSHOT_DIR)) fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });

async function main() {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({
    viewport: { width: 390, height: 844 },
    deviceScaleFactor: 2,
  });
  const page = await context.newPage();
  const results = [];
  const allErrors = [];

  page.on('pageerror', err => allErrors.push(err.message));
  page.on('console', msg => {
    if (msg.type() === 'error') allErrors.push(msg.text());
  });

  function log(test, status, detail = '') {
    results.push({ test, status, detail });
    console.log(`${status === 'PASS' ? '✅' : status === 'FAIL' ? '❌' : '⚠️'} ${test}: ${detail}`);
  }

  function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

  try {
    // Navigate to product page
    console.log('Navigating to product page...');
    await page.goto(`${BASE_URL}${STORE}/product/test-cotton-shirt`, { waitUntil: 'networkidle', timeout: 30000 });
    await sleep(2000);

    // ─── Test 1: Add to cart, drawer opens with item ───
    console.log('\n=== TEST 1: Add to cart → drawer opens ===');
    await page.click('.addcart-btn.addcart-btn-globaly');
    await sleep(3000);
    
    const badgeText = await page.$eval('#cart-count', el => el.textContent);
    log('1a. Cart badge updated', parseInt(badgeText) > 0 ? 'PASS' : 'FAIL', `badge = ${badgeText}`);
    
    await page.click('#cart-toggle');
    await sleep(3000);
    
    const drawerOpen = await page.$eval('#cart-panel', el => el.classList.contains('open'));
    log('1b. Drawer opens', drawerOpen ? 'PASS' : 'FAIL', drawerOpen ? 'open class present' : 'no open class');
    
    const hasItem = await page.$eval('#cart-panel', el => el.innerHTML.includes('border-b pb-4'));
    log('1c. Item visible in drawer', hasItem ? 'PASS' : 'FAIL', hasItem ? 'item found' : 'no item');
    
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'claude15-01-cart-with-item.png'), fullPage: false });

    // ─── Test 2: Quantity + → total updates ───
    console.log('\n=== TEST 2: Quantity change ===');
    const qtyBefore = await page.$eval('#cart-panel .quantity', el => el.value);
    log('2a. Initial qty', 'PASS', `qty = ${qtyBefore}`);
    
    await page.click('#cart-panel .quantity-increment');
    await sleep(3000);
    
    const qtyAfter = await page.$eval('#cart-panel .quantity', el => el.value);
    log('2b. Qty incremented', parseInt(qtyAfter) > parseInt(qtyBefore) ? 'PASS' : 'FAIL', `${qtyBefore} → ${qtyAfter}`);

    // ─── Test 3: Remove item ───
    console.log('\n=== TEST 3: Remove item ===');
    await sleep(1000);
    
    // Re-find the remove button (DOM may have changed after qty update)
    const removeBtn = await page.$('#cart-panel .remove_item');
    if (removeBtn) {
      await removeBtn.click();
      await sleep(3000);
      
      const emptyState = await page.$eval('#cart-panel', el => el.innerHTML.includes('Your cart is empty'));
      log('3. Item removed', emptyState ? 'PASS' : 'FAIL', emptyState ? 'empty state shown' : 'item still visible');
    } else {
      log('3. Item removed', 'FAIL', 'Remove button not found');
    }
    
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'claude15-02-empty-cart.png'), fullPage: false });

    // ─── Close drawer before continuing ───
    const closeBtn = await page.$('#cart-panel .text-2xl, #cart-close');
    if (closeBtn) {
      await closeBtn.click();
      await sleep(1000);
    }

    // ─── Test 4: Variant selector attribute ───
    console.log('\n=== TEST 4: Variant selector ===');
    const hasVariantIsland = await page.$eval('.variant_form', el => el?.getAttribute('data-island'));
    log('4. Variant island attr', hasVariantIsland === 'variant-selector' ? 'PASS' : 'FAIL', `data-island = ${hasVariantIsland}`);

    // ─── Test 5: Add to cart button state ───
    console.log('\n=== TEST 5: Add to cart button ===');
    const addBtn = await page.$('.addcart-btn.addcart-btn-globaly');
    if (addBtn) {
      const disabled = await addBtn.getAttribute('disabled');
      const variantId = await addBtn.getAttribute('variant_id');
      log('5. Add to cart button', !disabled ? 'PASS' : 'FAIL', `disabled = ${disabled}, variant_id = ${variantId}`);
    } else {
      log('5. Add to cart button', 'FAIL', 'Button not found');
    }

    // ─── Test 6: Page reload → cart persists ───
    console.log('\n=== TEST 6: Page reload persistence ===');
    // Add item first
    await page.click('.addcart-btn.addcart-btn-globaly');
    await sleep(3000);
    
    const badgeBeforeReload = await page.$eval('#cart-count', el => el.textContent);
    log('6a. Badge before reload', parseInt(badgeBeforeReload) > 0 ? 'PASS' : 'FAIL', `badge = ${badgeBeforeReload}`);
    
    await page.reload({ waitUntil: 'networkidle', timeout: 30000 });
    await sleep(2000);
    
    const badgeAfterReload = await page.$eval('#cart-count', el => el.textContent);
    log('6b. Badge after reload', parseInt(badgeAfterReload) > 0 ? 'PASS' : 'FAIL', `badge = ${badgeAfterReload}`);

    // ─── Test 7: Turbo navigation → no duplicate mounts ───
    console.log('\n=== TEST 7: Turbo navigation ===');
    await page.goto(`${BASE_URL}${STORE}/home`, { waitUntil: 'networkidle', timeout: 30000 });
    await sleep(2000);
    await page.goto(`${BASE_URL}${STORE}/product/test-cotton-shirt`, { waitUntil: 'networkidle', timeout: 30000 });
    await sleep(2000);
    
    const toggleCount = await page.$$eval('#cart-toggle', els => els.length);
    log('7. No duplicate toggles', toggleCount === 1 ? 'PASS' : 'FAIL', `${toggleCount} #cart-toggle`);
    
    const overlayCount = await page.$$eval('#cart-overlay', els => els.length);
    log('7b. No duplicate overlays', overlayCount === 1 ? 'PASS' : 'FAIL', `${overlayCount} #cart-overlay`);

    // ─── Test 8: Kill-switch ───
    console.log('\n=== TEST 8: Kill-switch ===');
    const killSwitchVal = await page.evaluate(() => typeof window.__SPA_VUE_ISLANDS__ !== 'undefined' ? window.__SPA_VUE_ISLANDS__ : 'undefined');
    log('8. Kill-switch present', killSwitchVal !== 'undefined' ? 'PASS' : 'FAIL', `__SPA_VUE_ISLANDS__ = ${killSwitchVal}`);

    // ─── Test 9: No Vue errors ───
    console.log('\n=== TEST 9: Error handling ===');
    const vueErrors = allErrors.filter(e => 
      e.includes('island') || e.includes('vue') || 
      e.includes('Vue') || e.includes('insertBefore') ||
      e.includes('beforeUpdate')
    );
    log('9. No Vue errors', vueErrors.length === 0 ? 'PASS' : 'FAIL', `${vueErrors.length} errors: ${vueErrors.join(' | ')}`);

    // ─── Test 10: Checkout flow from drawer ───
    console.log('\n=== TEST 10: Checkout flow ===');
    // Open drawer with item already in cart
    await page.click('#cart-toggle');
    await sleep(3000);
    
    const hasCheckout = await page.$eval('#cart-panel', el => el.innerHTML.includes('Proceed to Checkout'));
    log('10a. Checkout button present', hasCheckout ? 'PASS' : 'FAIL', hasCheckout ? 'found' : 'not found');
    
    if (hasCheckout) {
      const checkoutHref = await page.$eval('#cart-panel a[href*="cart"]', el => el.href);
      log('10b. Checkout link correct', checkoutHref.includes('/cart') ? 'PASS' : 'FAIL', checkoutHref);
      await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'claude15-03-checkout.png'), fullPage: false });
      
      // Click through to checkout
      await page.click('#cart-panel a[href*="cart"]');
      await sleep(3000);
      const onCartPage = await page.url().includes('/cart');
      log('10c. Reached cart page', onCartPage ? 'PASS' : 'FAIL', `URL = ${page.url()}`);
    }

    // ─── Test 11: Memory leak check ───
    console.log('\n=== TEST 11: Memory after 10 navigations ===');
    const memBefore = await page.evaluate(() => performance.memory ? performance.memory.usedJSHeapSize : 0);
    
    for (let i = 0; i < 10; i++) {
      await page.goto(`${BASE_URL}${STORE}/home`, { waitUntil: 'domcontentloaded', timeout: 15000 });
      if (i % 3 === 0) {
        await page.goto(`${BASE_URL}${STORE}/product/test-cotton-shirt`, { waitUntil: 'domcontentloaded', timeout: 15000 });
      }
    }
    await sleep(2000);
    
    const memAfter = await page.evaluate(() => performance.memory ? performance.memory.usedJSHeapSize : 0);
    
    if (memBefore > 0) {
      const ratio = memAfter / memBefore;
      log('11. Memory stable', ratio < 3 ? 'PASS' : 'FAIL', `heap: ${Math.round(memBefore/1024/1024)}MB → ${Math.round(memAfter/1024/1024)}MB (ratio: ${ratio.toFixed(2)})`);
    } else {
      log('11. Memory stable', 'WARN', 'performance.memory not available');
    }

    // ─── Test 12: Island registry clean ───
    console.log('\n=== TEST 12: Island registry ===');
    const registryStats = await page.evaluate(() => {
      if (!window.__islandRegistry) return null;
      return window.__islandRegistry.stats;
    });
    log('12. Registry active', registryStats && registryStats.enabled ? 'PASS' : 'WARN', JSON.stringify(registryStats));

    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'claude15-04-final.png'), fullPage: false });

  } catch (e) {
    log('General', 'FAIL', e.message);
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'claude15-error.png'), fullPage: false });
  } finally {
    await browser.close();
  }

  const pass = results.filter(r => r.status === 'PASS').length;
  const fail = results.filter(r => r.status === 'FAIL').length;
  const warn = results.filter(r => r.status === 'WARN').length;

  console.log('\n═══════════════════════════════════════════');
  console.log(`CLAUDE-15 BROWSER TEST: ${pass} passed, ${fail} failed, ${warn} warned`);
  console.log('═══════════════════════════════════════════\n');

  fs.writeFileSync(path.join(SCREENSHOT_DIR, 'claude15-results.json'), JSON.stringify({
    timestamp: new Date().toISOString(),
    total: results.length,
    pass,
    fail,
    warn,
    results
  }, null, 2));
}

main().catch(err => {
  console.error('Test script failed:', err);
  process.exit(1);
});
