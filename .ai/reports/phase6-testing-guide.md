# Phase 6: Demo Theme - Integration & Testing Guide

**Status**: ⬜ PENDING  
**Estimated Time**: 1.5 hours  
**Required**: Browser access + Turbo navigation testing

---

## Pre-Testing Checklist

### 1. Enable Demo Theme
```bash
# .env
DEMO_THEME_ENABLED=true
```

### 2. Clear Cache
```bash
php artisan view:clear
php artisan cache:clear
```

### 3. Rebuild Assets (if needed)
```bash
npm run build
```

---

## Testing Phases

### Phase 6.1: Visual Verification (Desktop)

**Viewport**: 1440px × 900px

**Checklist**:
- [ ] Header displays correctly (logo, search, icons)
- [ ] Header icons are pink on hover (pink-600: #e8007c)
- [ ] Product grid shows 5 columns
- [ ] Product cards display:
  - [ ] Discount badge (top-left, green)
  - [ ] Wishlist heart (top-right)
  - [ ] Product image
  - [ ] Title (2-line clamp)
  - [ ] Price (pink color)
  - [ ] "Add to Cart" button (pink bg)
  - [ ] "Buy Now" button (white bg)
- [ ] Footer displays 4 columns
- [ ] Footer links are white, hover to pink
- [ ] Social icons are pink background

**Console Check**:
```
❌ 0 errors
❌ 0 warnings (related to demo theme)
```

---

### Phase 6.2: Responsive Breakpoints

**Test at these widths**:

#### Tablet (768px × 600px)
- [ ] Product grid: 4 columns
- [ ] Header: search hidden, mobile search icon visible
- [ ] Footer: 2 columns
- [ ] Touch targets: minimum 44px

#### Mobile (425px × 800px)
- [ ] Product grid: 3 columns
- [ ] Header: compact, hamburger menu visible
- [ ] Footer: 1 column
- [ ] Bottom navbar visible (BULK-6)
- [ ] Spacing: gap-3 (instead of gap-4)

#### Small Mobile (375px × 667px)
- [ ] Product grid: 2 columns
- [ ] Cards fit without overflow
- [ ] Text readable (no truncation issues)
- [ ] Buttons clickable (44px minimum)

**Tools**:
- Chrome DevTools responsive design mode
- Test at actual device if possible

---

### Phase 6.3: Turbo Navigation Testing

**Test Flow**:
1. Visit `/stylique/home`
2. Click product link → product detail page
3. Click "Back" button (or browser back) → should return fast (cached)
4. Click category link → collection page
5. Repeat 5+ times across pages

**Expected Behavior**:
- ✅ Page transitions smooth (no white flash)
- ✅ Product cards remain styled (not broken by Turbo)
- ✅ Event handlers work (Add to Cart, Wishlist, etc)
- ✅ Scroll position restored on back nav
- ✅ URL updates correctly

**Performance**:
- Back navigation: ~10-17ms (cached)
- Forward navigation: ~500-700ms (server TTFB)

**Console Check**:
```
❌ 0 errors
❌ 0 warnings
✅ "Turbo is running" (if enabled)
```

---

### Phase 6.4: Interaction Testing

**Add to Cart**:
1. Click "Add to Cart" button on product card
2. Verify cart updates (badge count increases)
3. No page reload should occur

**Wishlist**:
1. Click heart icon on product card
2. Verify heart fills (active state)
3. No page reload

**Search**:
1. Type in search box
2. Verify suggestions appear
3. Click suggestion or submit

**Mobile Bottom Nav**:
1. View on mobile
2. Click Home, Wishlist, Account, etc
3. Verify active state highlights
4. Turbo navigation works

---

### Phase 6.5: Performance Baseline

**Measure using Chrome DevTools Lighthouse**:

**Desktop (1440px)**:
- [ ] LCP (Largest Contentful Paint): ~1296ms baseline (within 5% = ~1232-1360ms) ✅
- [ ] FCP (First Contentful Paint): < 1000ms
- [ ] CLS (Cumulative Layout Shift): 0 or < 0.1
- [ ] TBT (Total Blocking Time): < 200ms

**Mobile (375px)**:
- [ ] LCP: should be slower but < 2500ms
- [ ] FCP: < 1500ms
- [ ] CLS: 0 or < 0.1

**Network Profile**: Slow 4G

---

### Phase 6.6: Kill-Switch Verification

**Disable Demo Theme**:
```bash
# .env
DEMO_THEME_ENABLED=false
```

**Clear Cache**:
```bash
php artisan view:clear
```

**Refresh Page** and verify:
- [ ] Product grid shows Swiper carousel (instead of grid)
- [ ] Original product card component displays
- [ ] Original colors (orange, not pink)
- [ ] Poppins font (instead of Satoshi)
- [ ] Header/footer back to normal
- [ ] 0 console errors

---

## Troubleshooting

### Issue: Cards not showing discount badge
**Solution**: Check if product has `price` and `sale_price` set. Calculate discount percentage in `product-card-demo.blade.php`.

### Issue: Wishlist button not working
**Solution**: Verify jQuery selector and event handler. Check `wishlist-btn` class in DOM.

### Issue: Grid layout breaks
**Solution**: Check Tailwind breakpoints. Mobile-first approach: `grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5`.

### Issue: Colors not applying
**Solution**: Verify `DEMO_THEME_ENABLED=true` in `.env`. Clear cache: `php artisan cache:clear`.

### Issue: Turbo navigation broken
**Solution**: Check browser console for errors. Verify `TURBO_ENABLED=true`. Inspect network tab for failed requests.

---

## Test Report Template

```markdown
# Phase 6 Test Report

**Date**: [DATE]
**Tester**: [NAME]
**Browser**: [Chrome/Safari/Firefox] v[VERSION]

## Desktop (1440px)
- Visual: ✅ / ❌
- Console: ✅ 0 errors / ❌ [N] errors
- LCP: [TIME]ms
- Notes: [ANY ISSUES]

## Tablet (768px)
- Visual: ✅ / ❌
- Layout: ✅ / ❌
- Notes: [ANY ISSUES]

## Mobile (425px)
- Visual: ✅ / ❌
- Touch targets: ✅ / ❌
- Bottom nav: ✅ / ❌
- Notes: [ANY ISSUES]

## Turbo Navigation
- 5+ page test: ✅ / ❌
- Performance: ✅ / ❌
- Event handlers: ✅ / ❌
- Console: ✅ 0 errors / ❌ [N] errors

## Kill-Switch
- Disable: ✅ / ❌
- Fallback works: ✅ / ❌
- Console: ✅ 0 errors / ❌ [N] errors

## Summary
✅ ALL TESTS PASS / ❌ [N] ISSUES FOUND

Issues:
- [ISSUE 1]
- [ISSUE 2]
```

---

## Next Steps After Testing

1. **If All Tests Pass** ✅
   - Run final commit: `feat(stylique): complete demo theme - all tests passing [CLAUDE-DEMO-FINAL]`
   - Update `.ai/PROGRESS.md` with DONE status
   - Update `.ai/reports/demo-implementation-status.md` with test results

2. **If Issues Found** ❌
   - Document issues in test report
   - Create fix commits: `fix(demo): [ISSUE] [CLAUDE-DEMO-FIX-N]`
   - Re-test specific areas
   - Document in `.ai/PROGRESS.md` Blockers section

---

## Browser Compatibility

**Target Browsers**:
- Chrome 90+ ✅
- Safari 14+ ✅
- Firefox 88+ ✅
- Edge 90+ ✅

**Mobile Browsers**:
- iOS Safari 14+ ✅
- Android Chrome ✅

---

## Acceptance Criteria (Definition of Done)

- [ ] Visual design matches demo reference (within 95%)
- [ ] All Tailwind demo colors applied (pink #e8007c, green #00c153)
- [ ] Responsive grid: 5→4→3→2 columns
- [ ] Product cards: all elements present and styled
- [ ] Header/footer: demo styling applied
- [ ] Turbo navigation: 5+ pages tested, smooth
- [ ] Performance: LCP within 5% of baseline
- [ ] Console: 0 new errors
- [ ] Kill-switch: disable works, fallback functional
- [ ] Cross-browser: tested on Chrome, Safari, Firefox
- [ ] Mobile: tested at 3+ breakpoints

