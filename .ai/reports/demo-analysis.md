# Demo Site Analysis & Stylique Theme Migration Plan

## Date: 2026-10-10
**Task:** Adapt "Levo Leather" demo design to Stylique theme while maintaining SPA+PWA performance

---

## PART 1: DEMO DESIGN ANALYSIS

### Color Palette (From demo CSS)
```
:root {
  --shop-color-primary: #e8007c        (Pink - Accent)
  --shop-color-secondary: #00c153      (Green - Buttons)
  --shop-color-tertiary: #ff00ae       (Magenta - Highlights)
  --shop-color-primary-light: 20% opacity mix
  --shop-color-primary-light-2: 30% opacity mix
  --shop-color-primary-light-3: 10% opacity mix
}
```

### Typography
- **Font Family**: Satoshi (6 weights: 300, 400, 400-italic, 500, 700, 900)
- **Body**: font-family: Satoshi, sans-serif
- **Font Display**: swap (non-blocking)

### Layout Patterns (Desktop)
| Element | Grid | Gap | Notes |
|---------|------|-----|-------|
| Product Cards | 5 columns | 15px | Responsive: 4/3/2 on smaller screens |
| Categories | 8 columns | 15px | Horizontal scroll on mobile |
| Footer | 4 columns (lg) → 2 (md) → 1 (sm) | 8-15px | — |
| Time Counter | Flexbox, 7px gap | — | Hours/Mins/Secs display |

### Mobile UI (max-width: 599px)
- **Bottom Navigation Bar**: FIXED, height ~80px
  - Home, Messenger, Call, Facebook, Account
  - Icons 30px, labels below
  - Background: white, border-top: 1px solid gray
  - Active: color primary + background rgba(primary, 0.13)

- **Product Grid**: 2 columns (vs 5 desktop)
- **Footer**: Hidden, then shown below navbar
- **Spacing**: Uses viewport-width units (vw) for responsive scaling

### Product Card Components
```
Structure:
┌─ Card Container (100% width, shadow)
│  ├─ Card Box (padding 1rem 0.5rem)
│  │  ├─ Image Area
│  │  │  └─ Picture Wrapper (145px × 145px)
│  │  │     └─ Image (object-fit: cover)
│  │  └─ Product Tag (top-left, "OFF%" discount)
│  │
│  └─ Card Description
│     ├─ Product Title (14px, 2-line clamp)
│     ├─ Ratings (stars + count)
│     ├─ Product Price (primary color, bold)
│     ├─ Old Price (strikethrough)
│     └─ Button Row
│        ├─ Add to Cart (primary bg, white text)
│        └─ Buy Now (white bg, primary border)
```

### Key Design Elements

#### 1. **Product Tag Badge**
- Position: absolute top-left
- Background: `--shop-color-secondary` (green)
- Example: "-44% OFF"
- Style: 12px font, 7px 10px padding, white text, border-radius 5px

#### 2. **Wishlist Button** (top-right)
- SVG heart icon
- Hover: tooltip "Add to Wishlist"
- Active state: filled heart, color primary

#### 3. **Time Counter** (Flash Sale)
- Border: 2px solid `--shop-color-tertiary`
- Items: Hours / Mins / Secs
- Grid: flexbox with 7px gap
- Responsive: Full width mobile, max-width 430px desktop

#### 4. **Bottom Navbar** (Mobile Only, Fixed)
```css
.navbar-outside {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: white;
  box-shadow: 0 -2px 12px rgba(0,0,0,0.07);
  border-top: 1px solid rgba(148,143,143,0.63);
  padding: 0.5vw 4vw;
}

.nav-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 0;
}

.nav-item.active {
  background: rgba(--shop-color-primary, 0.13);
  box-shadow: 0 2px 8px rgba(--shop-color-primary, 0.08);
}
```

---

## PART 2: CURRENT STYLIQUE STATE

### What Exists
- ✅ Blade templating system
- ✅ Turbo Drive (SPA-like navigation)
- ✅ Tailwind CSS (Vite-compiled)
- ✅ Dynamic product rendering (@foreach loops)
- ✅ Mobile bottom nav (BULK-6 feature, already implemented)
- ✅ PWA/Service Worker
- ✅ Dark mode support

### What Needs to Change
- ❌ Header design (not matching demo)
- ❌ Product card styling
- ❌ Color palette (currently uses primary/secondary from theme settings)
- ❌ Footer layout
- ❌ Typography (Satoshi font not currently used universally)
- ❌ Time counter component (flash sale countdown)
- ❌ Responsive grid layout

---

## PART 3: IMPLEMENTATION STRATEGY

### Phase 1: Color & Typography (Non-Breaking)
**Status**: ⬜ PENDING  
**Files to Modify**:
- `themes/stylique/views/layouts/app.blade.php` (add Satoshi font)
- `resources/css/store.css` (color override)
- Theme settings configuration

**Task**:
1. Import Satoshi font family via Google Fonts
2. Add CSS variables for demo colors
3. Override Tailwind theme to use these colors
4. Maintain kill-switch for reverting to old colors

### Phase 2: Product Card Redesign
**Status**: ⬜ PENDING  
**Files to Modify**:
- `themes/stylique/views/components/product-card.blade.php`
- `themes/stylique/assets/css/` (if needed)

**Task**:
1. Restructure card DOM to match demo layout
2. Add discount tag badge
3. Add wishlist heart icon (top-right)
4. Update button styles (Add Cart / Buy Now)
5. Test with Turbo navigation (ensure event handlers survive cache)

### Phase 3: Time Counter Component
**Status**: ⬜ PENDING  
**Files to Create**:
- `themes/stylique/views/components/time-counter.blade.php`
- `themes/stylique/assets/js/time-counter.js`

**Task**:
1. Accept `deadline` parameter (date/time)
2. Calculate remaining hours/mins/secs
3. Update every 1 second
4. Style to match demo (bordered boxes, primary color)
5. Register with Turbo helpers

### Phase 4: Mobile Bottom Navbar Enhancements
**Status**: ✅ EXISTING (from BULK-6)  
**Files to Modify**:
- `themes/stylique/views/partison/mobile-bottom-nav.blade.php`
- `themes/stylique/assets/css/mobile-bottom-nav.css`

**Task**:
1. Verify current implementation matches demo
2. Add messenger/call/facebook icons if missing
3. Test active state styling with Turbo navigation
4. Ensure responsive mobile grid works with navbar offset

### Phase 5: Grid & Layout Updates
**Status**: ⬜ PENDING  
**Files to Modify**:
- `themes/stylique/views/front_end/pages/bestseller.blade.php`
- Tailwind config (if needed)

**Task**:
1. Change product grid: 5 columns (desktop) → 4 → 3 → 2 (mobile)
2. Apply responsive spacing (gap: 15px desktop, 7px mobile)
3. Use viewport-width (vw) units for mobile scaling
4. Test at breakpoints

### Phase 6: Header & Footer Redesign
**Status**: ⬜ PENDING  
**Files to Modify**:
- `themes/stylique/views/front_end/partison/header.blade.php`
- `themes/stylique/views/front_end/partison/footer.blade.php`

**Task**:
1. Update header to demo design (if different)
2. Update footer grid: 4 (lg) → 2 (md) → 1 (sm)
3. Add social icons matching demo
4. Update newsletter section styling

---

## PART 4: TECHNICAL CONSTRAINTS & SOLUTIONS

### Constraint 1: Turbo Drive Compatibility
**Problem**: JavaScript event handlers may not survive Turbo cache/navigation  
**Solution**:
- All event handlers must use `onTurboLoad` (page-specific)
- Use `onTurboOnce` for global delegation
- Test with 5+ navigations

### Constraint 2: SPA Performance
**Problem**: Don't want to regress from current benchmark  
**Current**: storefront JS −466 KB, LCP −272 ms (Day 1 results)  
**Solution**:
- No new external dependencies
- Inline critical CSS
- Use CSS variables (no runtime generation)
- Keep JS minimal (vanilla, no frameworks)

### Constraint 3: Dynamic vs Static
**Problem**: Demo is static HTML; Stylique is dynamic Blade  
**Solution**:
- Use Blade `@if` / `@foreach` to render exactly same HTML structure
- Apply CSS from demo (convert to Tailwind if possible)
- Maintain server-side data rendering (no API changes)

### Constraint 4: Mobile-First Responsive
**Problem**: Demo uses vw units; Tailwind uses fixed breakpoints  
**Solution**:
- Mix Tailwind breakpoints (for major layout changes)
- Use CSS `calc()` + vw units (for scaling typography/spacing)
- Test across devices (375px, 425px, 768px, 1024px, 1440px)

---

## PART 5: PRIORITY ROADMAP

| Phase | Files | Complexity | Time | Kill-Switch |
|-------|-------|-----------|------|------------|
| 1 | app.blade.php, store.css | Low | 30 min | `DEMO_COLORS=true` |
| 2 | product-card.blade.php | Medium | 1.5 hrs | `DEMO_CARD_STYLE=true` |
| 3 | time-counter.blade.php + .js | Medium | 1 hr | `DEMO_TIMER=true` |
| 4 | mobile-bottom-nav.* | Low | 30 min | Already has kill-switch |
| 5 | Grid CSS | Low | 45 min | `DEMO_GRID=true` |
| 6 | header/footer | Medium | 1 hr | `DEMO_LAYOUT=true` |

**Total Estimated Time**: ~5 hours  
**Risk Level**: Low (all changes reversible, kill-switches in place)

---

## PART 6: VERIFICATION CHECKLIST

### Before Starting
- [ ] Read demo HTML/CSS fully
- [ ] Screenshot comparison (side-by-side)
- [ ] Performance baseline (LCP, TBT)

### After Each Phase
- [ ] Visual pixel-diff (0 regressions on existing pages)
- [ ] Turbo navigation test (5+ pages)
- [ ] Mobile viewport test (375px, 768px)
- [ ] Console errors (0 new errors)
- [ ] Performance benchmark (LCP within 5%)

### Final Integration
- [ ] All kill-switches working (disable features one-by-one)
- [ ] Commit message format: `feat(stylique): <description> [CLAUDE-XXXX]`
- [ ] PROGRESS.md updated

---

## Questions for User

1. **Color Scheme**: Use demo colors (#e8007c primary) or keep current theme?
2. **Satoshi Font**: Install webfont or use Google Fonts CDN?
3. **Timeline**: Implement all 6 phases, or subset first (e.g., Phase 1-3 only)?
4. **Other Themes**: Also update greentic/techzonix, or just stylique?

