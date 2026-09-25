// CLAUDE-15 browser verification script
// Tests cart drawer + variant selector Vue islands on the storefront
import { chromium } from 'playwright';
import { writeFileSync, mkdirSync, existsSync } from 'fs';
import { resolve } from 'path';

const BASE_URL = 'http://ecommercego.dev';
const STORE = '/stylique';
const SCREENSHOT_DIR = resolve('.ai/screenshots');

if (!existsSync(SCREENSHOT_DIR)) mkdirSync(SCREENSHOT_DIR, { recursive: true });

async function main() {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({
    viewport: { width: 390, height: 844 }, // iPhone 12 viewport
    deviceScaleFactor: 2,
  });

  const page = await context.newPage();
  const results = [];

  const log = (test, status, detail = '') => {
    const entry = { test, status, detail };
    results.push(entry);
    console.log(`${status === 'PASS' ? '✅' : status === 'FAIL' ? '❌' : '⚠️'} [${test}] ${detail}`);
  };

  try {
    // 1. Navigate to storefront and check page loads
    console.log('Navigating to storefront...');
    await page.goto(`${BASE_URL}${STORE}/home`, { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });
    page.on('pageerror', err => consoleErrors.push(err.message));

    // Check for console errors
    if (consoleErrors.filter(e => e.includes('island') || e.includes('vue')).length === 0) {
      log('1. Page load', 'PASS', 'No Vue/island console errors on page load');
    } else {
      log('1. Page load', 'FAIL', `Console errors: ${consoleErrors.join(', ')}`);
    }

    // Check that cart-toggle exists
    const cartToggle = await page.$('#cart-toggle');
    if (cartToggle) {
      log('1. Cart toggle visible', 'PASS', 'cart-toggle button exists in header');
    } else {
      log('1. Cart toggle visible', 'FAIL', '#cart-toggle not found');
    }

    // 2. Check that island-registry loaded
    const islandRegistry = await page.evaluate(() => {
      return window.__islandRegistry ? {
        enabled: window.__islandRegistry.stats.enabled,
        instances: window.__islandRegistry.stats.instances,
        components: window.__islandRegistry.stats.componentNames
      } : null;
    });

    if (islandRegistry && islandRegistry.enabled) {
      log('2. Island registry loaded', 'PASS', `enabled=${islandRegistry.enabled}, components=${islandRegistry.components.join(',')}`);
    } else {
      log('2. Island registry loaded', 'FAIL', 'window.__islandRegistry not found or disabled');
    }

    // Check __SPA_VUE_ISLANDS__ flag
    const vueIslandsFlag = await page.evaluate(() => window.__SPA_VUE_ISLANDS__);
    if (vueIslandsFlag === true) {
      log('2b. Kill-switch flag', 'PASS', `__SPA_VUE_ISLANDS__ = ${vueIslandsFlag}`);
    } else {
      log('2b. Kill-switch flag', 'FAIL', `__SPA_VUE_ISLANDS__ = ${vueIslandsFlag}`);
    }

    // Take screenshot of initial state
    await page.screenshot({ path: resolve(SCREENSHOT_DIR, 'claude15-01-homepage.png'), fullPage: false });

    // 3. Click cart toggle to open drawer
    await page.click('#cart-toggle');
    await new Promise(r => setTimeout(r, 1500));

    const drawerPanel = await page.$('#cart-panel.open');
    if (drawerPanel) {
      log('3. Cart drawer opens', 'PASS', '#cart-panel has .open class');
    } else {
      log('3. Cart drawer opens', 'FAIL', '#cart-panel does not have .open class');
    }

    // Check drawer content
    const drawerContent = await page.$eval('#cart-panel', el => el.innerHTML);
    if (drawerContent && drawerContent.length > 50) {
      log('3b. Drawer has content', 'PASS', `innerHTML length: ${drawerContent.length}`);
    } else {
      log('3b. Drawer has content', 'FAIL', `innerHTML length: ${drawerContent?.length || 0}`);
    }

    await page.screenshot({ path: resolve(SCREENSHOT_DIR, 'claude15-02-cart-drawer.png'), fullPage: false });

    // 4. Close drawer
    await page.click('#cart-panel .text-2xl');
    await new Promise(r => setTimeout(r, 1000));
    const drawerClosed = await page.$eval('#cart-panel', el => !el.classList.contains('open'));
    if (drawerClosed) {
      log('4. Cart drawer closes', 'PASS', '.open class removed');
    } else {
      log('4. Cart drawer closes', 'FAIL', '.open class still present');
    }

    // 5. Navigate to a product page for variant selector
    console.log('Navigating to product page...');
    await page.goto(`${BASE_URL}${STORE}/test-cotton-shirt`, { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    // Check variant form has data-island
    const hasVariantIsland = await page.$eval('.variant_form', el => el.getAttribute('data-island'));
    if (hasVariantIsland === 'variant-selector') {
      log('5. Variant island attribute', 'PASS', 'form has data-island="variant-selector"');
    } else {
      log('5. Variant island attribute', 'FAIL', `data-island = ${hasVariantIsland}`);
    }

    // 6. Check that variant selects exist
    const variantSelects = await page.$$('.product_variatin_option');
    if (variantSelects.length > 0) {
      log('6. Variant selects exist', 'PASS', `${variantSelects.length} variant select(s) found`);
    } else {
      log('6. Variant selects exist', 'FAIL', 'No .product_variatin_option found');
    }

    // 7. Check add-to-cart button exists
    const addCartBtn = await page.$('.addcart-btn.addcart-btn-globaly');
    if (addCartBtn) {
      const variantIdAttr = await addCartBtn.getAttribute('variant_id');
      log('7. Add to cart button', 'PASS', `variant_id attr = ${variantIdAttr}`);
    } else {
      log('7. Add to cart button', 'FAIL', '.addcart-btn.addcart-btn-globaly not found');
    }

    // 8. Check that Vue vendor chunk is NOT loaded on homepage (lazy loading)
    console.log('Checking lazy loading on homepage...');
    await page.goto(`${BASE_URL}${STORE}/home`, { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    const vueVendorLoaded = await page.evaluate(() => {
      // Check if vue-vendor chunk was loaded
      const scripts = document.querySelectorAll('script[src*="vue-vendor"]');
      return scripts.length > 0;
    });
    if (!vueVendorLoaded) {
      log('8. Vue lazy-loaded (not on homepage)', 'PASS', 'vue-vendor chunk not downloaded on homepage');
    } else {
      log('8. Vue lazy-loaded (not on homepage)', 'FAIL', 'vue-vendor chunk was loaded on homepage');
    }

    // 9. After 10 navigations, check memory
    console.log('Testing 10 navigations...');
    const initialHeap = await page.evaluate(() => performance.memory ? performance.memory.usedJSHeapSize : 0);
    for (let i = 0; i < 10; i++) {
      await page.goto(`${BASE_URL}${STORE}/home`, { waitUntil: 'domcontentloaded', timeout: 15000 });
      if (i % 3 === 0) {
        await page.goto(`${BASE_URL}${STORE}/stylique/home`, { waitUntil: 'domcontentloaded', timeout: 15000 });
      }
      await new Promise(r => setTimeout(r, 500));
    }
    await new Promise(r => setTimeout(r, 2000));

    const finalHeap = await page.evaluate(() => performance.memory ? performance.memory.usedJSHeapSize : 0);
    const heapGrowthRatio = finalHeap / initialHeap;
    if (heapGrowthRatio < 3) {
      log('9. Memory under control', 'PASS', `heap: ${Math.round(initialHeap/1024/1024)}MB → ${Math.round(finalHeap/1024/1024)}MB (ratio: ${heapGrowthRatio.toFixed(2)})`);
    } else {
      log('9. Memory under control', 'FAIL', `heap grew ${heapGrowthRatio.toFixed(2)}x - possible leak`);
    }

    // 10. Check island registry stats after navigations
    const finalStats = await page.evaluate(() => {
      return window.__islandRegistry ? window.__islandRegistry.stats : null;
    });
    if (finalStats) {
      log('10. Island instances after 10 navigations', 'PASS', `instances: ${finalStats.instances}`);
    } else {
      log('10. Island instances after 10 navigations', 'WARN', 'No registry stats available');
    }

    // 11. Check for duplicate DOM elements (Turbo should not duplicate)
    const duplicateToggles = await page.$$('#cart-toggle');
    if (duplicateToggles.length === 1) {
      log('11. No DOM duplication', 'PASS', 'Only one #cart-toggle found');
    } else {
      log('11. No DOM duplication', 'FAIL', `${duplicateToggles.length} #cart-toggle elements found`);
    }

    // 12. Verify no network errors
    if (consoleErrors.filter(e => e.includes('fetch') || e.includes('network')).length === 0) {
      log('12. No network errors', 'PASS', 'No fetch/network console errors');
    } else {
      log('12. No network errors', 'WARN', `Errors: ${consoleErrors.join(', ')}`);
    }

    await page.screenshot({ path: resolve(SCREENSHOT_DIR, 'claude15-03-final.png'), fullPage: false });

  } catch (e) {
    log('General', 'FAIL', e.message);
    await page.screenshot({ path: resolve(SCREENSHOT_DIR, 'claude15-error.png'), fullPage: false });
  } finally {
    await browser.close();
  }

  // Write summary
  const summary = {
    timestamp: new Date().toISOString(),
    total: results.length,
    pass: results.filter(r => r.status === 'PASS').length,
    fail: results.filter(r => r.status === 'FAIL').length,
    warn: results.filter(r => r.status === 'WARN').length,
    results
  };

  console.log('\n═══════════════════════════════════════════');
  console.log(`CLAUDE-15 Test Summary: ${summary.pass}/${summary.total} passed, ${summary.fail} failed, ${summary.warn} warned`);
  console.log('═══════════════════════════════════════════\n');

  writeFileSync(resolve(SCREENSHOT_DIR, 'claude15-results.json'), JSON.stringify(summary, null, 2));
}

main().catch(err => {
  console.error('Test script failed:', err);
  process.exit(1);
});
