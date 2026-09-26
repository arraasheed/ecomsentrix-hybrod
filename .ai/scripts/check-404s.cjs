const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
  const page = await context.newPage();
  
  const notFoundUrls = [];
  page.on('response', resp => {
    if (resp.status() === 404) notFoundUrls.push(resp.url());
  });

  try {
    await page.goto('http://ecommercego.dev/stylique/home', { waitUntil: 'networkidle', timeout: 30000 });
    await new Promise(r => setTimeout(r, 3000));

    console.log('404 URLs:');
    notFoundUrls.forEach(u => console.log('  -', u));

    // Filter for Vue-related 404s
    const vue404s = notFoundUrls.filter(u => 
      u.includes('vue') || u.includes('island') || u.includes('CartDrawer') || 
      u.includes('VariantSelector') || u.includes('build/assets')
    );
    console.log('\nVue-related 404s:', vue404s.length);
    if (vue404s.length) vue404s.forEach(u => console.log('  -', u));

  } finally {
    await browser.close();
  }
})();
