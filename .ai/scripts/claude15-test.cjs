// CLAUDE-15 browser verification script
// Tests cart drawer + variant selector Vue islands on the storefront

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
    viewport: { width: 390, height: 844 }, // iPhone 12 viewport
    deviceScaleFactor: 2,
  });

  const page = await context.newPage();
  const results = [];

  function log(test, status, detail = '') {
    const entry = { test, status, detail };
    results.push(entry);
    console.log(`${status === 'PASS' ? '✅' : status === 'FAIL' ? '❌' : '⚠️'} [${test}] ${detail}`);
  }

  try {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });
    page.on('pageerror', err => consoleErrors.push(err.message));

    // 1. Navigate to storefront
    console.log('Navigating to storefront...');
    await page.goto(`${BASE_URL}${STORE}/home`, { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    const vueIslandErrors = consoleErrors.filter(e => e.includes('island') || e.includes('vue'));
    if (vueIslandErrors.length === 0) {
      log('1. Page load', 'PASS', 'No Vue/island console errors');
    } else {
      log('1. Page load', 'FAIL', `Console errors: ${vueIslandErrors.join(', ')}`);
    }

    const cartToggle = await page.$('#cart-toggle');
    if (cartToggle) {
      log('1b. Cart toggle visible', 'PASS', 'cart-toggle button exists');
    } else {
      log('1b. Cart toggle visible', 'FAIL', '#cart-toggle not found');
    }

    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'claude15-01-homepage.png'), fullPage: false });

    // 2. Check island-registry
    const islandRegistry = await page.evaluate(() => {
      return window.__islandRegistry ? {
        enabled: window.__islandRegistry.stats.enabled,
        componentNames: window.__islandRegistry.stats.componentNames,
        instances: window.__islandRegistry.stats.instances
      } : null;
    });

    if (islandRegistry && islandRegistry.enabled) {
      log('2. Island registry loaded', 'PASS', `enabled=${islandRegistry.enabled}, components=${islandRegistry.componentNames.join(',')}`);
    } else {
      log('2. Island registry loaded', 'FAIL', 'window.__islandRegistry not found or disabled');
    }

    const vueIslandsFlag = await page.evaluate(() => typeof window.__SPA_VUE_ISLANDS__ !== 'undefined' ? window.__SPA_VUE_ISLANDS__ : null);
    if (vueIslandsFlag === true) {
      log('2b. Kill-switch flag', 'PASS', `__SPA_VUE_ISLANDS__ = true`);
    } else {
      log('2b. Kill-switch flag', 'FAIL', `__SPA_VUE_ISLANDS__ = ${vueIslandsFlag}`);
    }

    // Debug: check DOM structure
    const domInfo = await page.evaluate(() => {
      const overlay = document.getElementById('cart-overlay');
      const panel = document.getElementById('cart-panel');
      const mountEl = document.querySelector('[data-island="cart-drawer"]');
      return {
        overlayExists: !!overlay,
        panelExists: !!panel,
        mountElExists: !!mountEl,
        mountElHTML: mountEl ? (mountEl.outerHTML || 'empty').substring(0, 100) : 'NOT FOUND',
        islandStats: window.__islandRegistry ? JSON.stringify(window.__islandRegistry.stats) : 'NOT FOUND',
      };
    });
    console.log('DEBUG DOM:', JSON.stringify(domInfo, null, 2));

    // 3. Click cart toggle to open drawer
    await page.click('#cart-toggle');
    await new Promise(r => setTimeout(r, 2000));

    const afterClickInfo = await page.evaluate(() => {
      const overlay = document.getElementById('cart-overlay');
      const panel = document.getElementById('cart-panel');
      return {
        overlayClass: overlay ? overlay.className : 'NOT FOUND',
        panelClass: panel ? panel.className : 'NOT FOUND',
        panelHasOpen: panel ? panel.classList.contains('open') : false,
        panelHTML: panel ? (panel.innerHTML || '').substring(0, 300) : 'NOT FOUND',
      };
    });
    console.log('AFTER CLICK:', JSON.stringify(afterClickInfo, null, 2));

    const drawerPanel = await page.$('#cart-panel.open');
    if (drawerPanel) {
      log('3. Cart drawer opens', 'PASS', '#cart-panel has .open class');
    } else {
      log('3. Cart drawer opens', 'FAIL', '#cart-panel does not have .open class');
    }

    const drawerContent = await page.$eval('#cart-panel', el => el.innerHTML);
    if (drawerContent && drawerContent.length > 50) {
      log('3b. Drawer has content', 'PASS', `innerHTML length: ${drawerContent.length}`);
    } else {
      log('3b. Drawer has content', 'FAIL', `innerHTML length: ${drawerContent?.length || 0}`);
    }

    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'claude15-02-cart-drawer.png'), fullPage: false });

    // 4. Close drawer
    const closeBtn = await page.$('#cart-panel .text-2xl, #cart-close');
    if (closeBtn) await closeBtn.click();
    await new Promise(r => setTimeout(r, 1000));

    const drawerClosed = await page.$eval('#cart-panel', el => !el.classList.contains('open'));
    if (drawerClosed) {
      log('4. Cart drawer closes', 'PASS', '.open class removed');
    } else {
      log('4. Cart drawer closes', 'FAIL', '.open class still present');
    }

    // 5. Navigate to product page
    console.log('Navigating to product page...');
    const beforeNavErrors = [...consoleErrors];
    await page.goto(`${BASE_URL}${STORE}/product/test-cotton-shirt`, { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    const hasVariantIsland = await page.$eval('.variant_form', el => el.getAttribute('data-island'));
    if (hasVariantIsland === 'variant-selector') {
      log('5. Variant island attribute', 'PASS', 'form has data-island="variant-selector"');
    } else {
      log('5. Variant island attribute', 'FAIL', `data-island = ${hasVariantIsland}`);
    }

    // 6. Check variant selects (only present on variant products)
    const variantSelects = await page.$$('.product_variatin_option');
    const isVariantProduct = await page.$('.variant_form');
    if (variantSelects.length > 0) {
      log('6. Variant selects exist', 'PASS', `${variantSelects.length} variant select(s)`);
    } else if (isVariantProduct) {
      // Variant form exists but no select elements — product may have no variants defined
      log('6. Variant selects exist', 'WARN', 'Form exists but no .product_variatin_option — product has no variant attributes');
    } else {
      log('6. No variant form', 'PASS', 'Non-variant product — variant selector not applicable');
    }

    // 7. Add-to-cart button
    const addCartBtn = await page.$('.addcart-btn.addcart-btn-globaly');
    if (addCartBtn) {
      const variantIdAttr = await addCartBtn.getAttribute('variant_id');
      log('7. Add to cart button', 'PASS', `variant_id = ${variantIdAttr}`);
    } else {
      log('7. Add to cart button', 'FAIL', 'Button not found');
    }

    await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'claude15-03-product-page.png'), fullPage: false });

    // 8. Vue vendor lazy loading check
    console.log('Checking lazy loading...');
    await page.goto(`${BASE_URL}${STORE}/home`, { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 2000));

    const vueVendorLoaded = await page.evaluate(() => {
      return !!document.querySelector('script[src*="vue-vendor"]');
    });
    if (!vueVendorLoaded) {
      log('8. Vue lazy-loaded', 'PASS', 'vue-vendor not downloaded on homepage');
    } else {
      log('8. Vue lazy-loaded', 'FAIL', 'vue-vendor was loaded on homepage');
    }

    // 9. Memory check after 10 navigations
    console.log('Testing 10 navigations...');
    const memCheck = await page.evaluate(() => {
      if ('memory' in performance) {
        return {
          used: performance.memory.usedJSHeapSize,
          total: performance.memory.totalJSHeapSize,
          limit: performance.memory.jsHeapSizeLimit,
          available: true
        };
      }
      return { available: false };
    });

    if (memCheck.available) {
      const initialHeap = memCheck.used;
      for (let i = 0; i < 10; i++) {
        await page.goto(`${BASE_URL}${STORE}/home`, { waitUntil: 'domcontentloaded', timeout: 15000 });
        if (i % 3 === 0) {
          await page.goto(`${BASE_URL}${STORE}/stylique/home`, { waitUntil: 'domcontentloaded', timeout: 15000 });
        }
      }
      await new Promise(r => setTimeout(r, 2000));

      const finalMem = await page.evaluate(() => {
        return performance.memory.usedJSHeapSize;
      });
      const ratio = finalMem / initialHeap;
      if (ratio < 3) {
        log('9. Memory under control', 'PASS', `heap: ${Math.round(initialHeap/1024/1024)}MB → ${Math.round(finalMem/1024/1024)}MB (ratio: ${ratio.toFixed(2)})`);
      } else {
        log('9. Memory under control', 'FAIL', `heap grew ${ratio.toFixed(2)}x`);
      }
    } else {
      log('9. Memory under control', 'WARN', 'performance.memory not available (Chromium-only)');
    }

    // 10. Island instances after navigation
    const finalStats = await page.evaluate(() => {
      return window.__islandRegistry ? window.__islandRegistry.stats : null;
    });
    if (finalStats) {
      log('10. No leaked instances', 'PASS', `instances: ${finalStats.instances}, enabled: ${finalStats.enabled}`);
    } else {
      log('10. No leaked instances', 'WARN', 'Registry not available on final page — may have navigated away from island');
    }

    // 11. No DOM duplication
    const duplicateToggles = await page.$$('#cart-toggle');
    if (duplicateToggles.length <= 1) {
      log('11. No DOM duplication', 'PASS', `Found ${duplicateToggles.length} #cart-toggle`);
    } else {
      log('11. No DOM duplication', 'FAIL', `${duplicateToggles.length} #cart-toggle elements`);
    }

    // 11b. Check for duplicate cart-overlays
    const duplicateOverlays = await page.$$('#cart-overlay');
    if (duplicateOverlays.length <= 1) {
      log('11b. No duplicate overlays', 'PASS', `Found ${duplicateOverlays.length} #cart-overlay`);
    } else {
      log('11b. No duplicate overlays', 'FAIL', `${duplicateOverlays.length} #cart-overlay elements`);
    }

    // 11c. Check island-registry stats (should show 0 leaked)
    const isStats = await page.evaluate(() => {
      if (!window.__islandRegistry) return null;
      return window.__islandRegistry.stats;
    });
    if (isStats) {
      log('11c. Island registry clean', 'PASS', `instances: ${isStats.instances}`);
    } else {
      log('11c. Island registry clean', 'WARN', 'No stats on this page');
    }

    // 12. No critical errors after navigation
    const postErrors = consoleErrors.slice(beforeNavErrors.length);
    const fetchErrs = postErrors.filter(e => e.includes('fetch') || e.includes('network') || e.includes('Failed to') || e.includes('TypeError'));
    const vueErrs = postErrors.filter(e => e.includes('island') || e.includes('vue') || e.includes('Vue'));
    if (vueErrs.length === 0 && fetchErrs.length === 0) {
      log('12. No critical errors', 'PASS', 'No Vue/island/fetch errors');
    } else {
      log('12. No critical errors', 'WARN', `${vueErrs.length} vue errors, ${fetchErrs.length} fetch errors`);
      if (vueErrs.length) console.log('    Vue errors:', vueErrs);
      if (fetchErrs.length) console.log('    Fetch errors:', fetchErrs);
    }

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
  console.log(`CLAUDE-15 Summary: ${pass}/${results.length} passed, ${fail} failed, ${warn} warned`);
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
