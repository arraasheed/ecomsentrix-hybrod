# API Contract — Cart Drawer & Variant Selector Vue Islands (CLAUDE-15)

## Decision: session-based `routes/customer.php` over `routes/api.php`

The task asked us to inspect `routes/api.php` (161 endpoints) and document the
cart / variant / search / wishlist contracts there. We did. **However**, every
one of those endpoints is guarded by the `custom.auth` middleware and requires a
Sanctum token — it reads `auth('customers')->user()` and, when the user is a
guest, falls back to a `customer_id` field that must be passed per request.

The storefront runs in a **session** (cookie) context, not a token context. The
existing jQuery code (`public/assets/js/front-theme.js`) never talks to
`routes/api.php` at all. It uses the **session-auth** routes in
`routes/customer.php`, which were designed for exactly this:

| Purpose | `routes/customer.php` (CHOSEN) | `routes/api.php` (rejected) |
|---|---|---|
| Add to cart | `POST /{slug}/product_cart` → `CartController::product_cartlist` | `POST /{slug}/add-cart` → `ApiController::addtocart` |
| Change qty / remove | `POST /{slug}/change-cart` → `CartController::change_cart` | `POST /{slug}/cart-qty` → `ApiController::cart_qty` |
| Cart validation | (implicit in change_cart response) | `POST /{slug}/cart-check` → `ApiController::cart_check` |
| Cart list + HTML | `POST/GET /{slug}/cart-list-sidebar` → `CartController::cart_list_sidebar` | `POST /{slug}/cart-list` → `ApiController::cart_list` |
| Variant price/stock | `POST /{slug}/product_price` → `ProductController::product_price` | `POST /{slug}/check-variant-stock` → `ApiController::check_variant_stock` |
| Search | `GET /{slug}/search-product` → `HomeController::search_products` | `POST /{slug}/search` → `ApiController::search` |
| Wishlist | `POST /{slug}/product-wishlist` → `WishlistController::product_wishlist` | `POST /{slug}/wishlist` → `ApiController::wishlist` |

**Why session-based is correct:**
- No new auth logic. The Vue island runs inside a Blade page that already has a
  session cookie. `auth('customers')->user()` resolves automatically.
- The `cart-list-sidebar` endpoint returns **pre-rendered HTML** for the sidebar
  (`response.html`), so progressive enhancement is trivial: the Vue component
  fetches JSON-shaped cart data via the same endpoint, or we ask it for the
  structured data. Either way, the existing cookie/session cart persists across
  reload (test #7, #12).
- The Sanctum-token endpoints would require injecting a token into the page and
  sending `Authorization: Bearer` — that is new auth surface area in licensed
  core code, which the rules forbid touching.

The `routes/api.php` contracts are documented below for completeness, but the
Vue islands will NOT call them. They are available if a future task needs a
token-based headless integration.

---

## A. Cart endpoints

### A1. `POST /{slug}/product_cart`  —  add to cart  (session)
**Controller:** `App\Http\Controllers\CartController::product_cartlist`
**Route:** `routes/customer.php:89`  name `product.cart`

**Request** (form-encoded, CSRF + session):
```
product_id   int    required
variant_id   int    nullable (must be non-zero for variant products)
qty          int    required
size_data    str    optional (only when SizeGuideline module active)
```

**Response** — `ApiResponser::success` / `error` (status + message + data):
```json
{
  "status": 1,
  "message": "test-cotton-shirt added successfully.",
  "data": {
    "message": "test-cotton-shirt added successfully.",
    "count": 3            // total cart items (badge count)
  }
}
```
Error (`status: 0`) when out of stock / variant not selected:
```json
{ "status": 0, "message": "Product has out of stock.", "data": { "message": "..." } }
```

### A2. `POST /{slug}/change-cart`  —  qty increase/decrease/remove  (session)
**Controller:** `App\Http\Controllers\CartController::change_cart`
**Route:** `routes/customer.php:90`  name `change.cart`

**Request:**
```
cart_id        int    required
quantity_type  string required | in:increase,decrease,remove
product_id     int
variant_id     int
shipping_final_price, coupon_code, stateId, countryId, cityId, tax_id_value  (optional checkout context)
```

**Response:**
```json
{
  "status": 1,
  "message": "Cart successfully updated.",
  "data":    { "count": 2 }     // only present on `remove`
}
```
Note: the controller returns `Utility::error()` / `Utility::success()` which
use `status` 0/1 — **same shape as ApiResponser**. The `count` field is present
in the `data` object on remove, and as a top-level key on quantity change.

### A3. `POST|GET /{slug}/cart-list-sidebar`  —  cart list + pre-rendered HTML  (session)
**Controller:** `App\Http\Controllers\CartController::cart_list_sidebar`
**Route:** `routes/customer.php:94`  name `cart.list.sidebar`

This is the **primary endpoint** for the reactive cart drawer. It returns the
full cart as structured data **and** the rendered sidebar HTML (for
progressive-enhancement fallback).

**Request:**
```
method_id, shipping_price, coupon_code, stateId, countryId, cityId, tax_id_value,
order_type, shipping_final_price
```

**Response:**
```json
{
  "status": 1,
  "message": "Cart items get successfully.",
  "sub_total": 999,
  "cart_total_product": 1,
  "html": "<div class=\"flex flex-col h-full\">...</div>",      // pre-rendered sidebar
  "checkout_html": "...",
  "checkout_html_2": "..."
}
```
`status: 0` → empty cart:
```json
{ "status": 0, "message": "Something went wrong", "sub_total": 0 }
```

The `cart-list-sidebar` Blade (`resources/views/front_end/pages/cart-list-sidebar.blade.php`)
returns product_list items with these keys per product:
```
cart_id, product_id, image, name, qty, variant_id, variant_name,
orignal_price, total_orignal_price, per_product_discount_price,
discount_price, final_price, return, shipping_price
```
And totals: `cart_total_qty`, `final_price`, `total_sub_price`, `tax_price`,
`original_price`, `cart_total_product`, `coupon_info`.

### A4. `POST /{slug}/cart-remove`  —  remove item  (session)
**Controller:** `App\Http\Controllers\CartController::cart_remove`
**Route:** `routes/customer.php:95`  name `cart.remove`

**Request:** `cart_id, product_id, variant_id`
**Response:** `{ "status": 1 }`

### A5. `POST /{slug}/cart-clear`  —  clear cart  (session)
**Controller:** `App\Http\Controllers\CartController::cartClear`
**Route:** `routes/customer.php:96`  name `cart.clear`

**Request:** `{ empty_cart: true }`
**Response:** `{ "status": 1 }`

---

## B. Variant price / stock endpoint (product page)

### B1. `POST /{slug}/product_price`  —  variant price + stock  (session)
**Controller:** `App\Http\Controllers\ProductController::product_price`
**Route:** `routes/customer.php:77`  name `product.price`

**Request** (serialized from `.variant_form`):
```
product_id  int   required
varint      str   variant string (e.g. "red-large") or null for non-variant
qty         int   required
```

**Response** (success):
```json
{
  "qty": 1,
  "variant_product": 1,
  "variant_id": 0,
  "product_original_price": 100,
  "final_price": 90,
  "final_price_text": "$90.00",
  "original_price": 100,
  "currency_name": "USD",
  "currency": "$",
  "total_tax_price": 8.1,
  "stock": 50,
  "stock_status": "in_stock",
  "stock_message": "In Stock",
  "description": "...",
  "variant_name": "red-large"
}
```

**Response** (out of stock / not available):
```json
{
  "status": "error",
  "stock_status": "out_of_stock",
  "stock_message": "Out Of Stock",
  "message": "Product has been reached max quantity.",
  "variant_id": 42
}
```

### B2. `POST /{slug}/check-variant-stock`  —  stock lookup by SKU  (token)
**Controller:** `App\Http\Controllers\Api\ApiController::check_variant_stock`
**Route:** `routes/api.php:64`

**Request:** `product_id (required), variant_sku (required)`

**Response:**
```json
{
  "status": 1,
  "message": "Product variant get successfully.",
  "data": {
    "id": 42,
    "variant": "red-large",
    "stock": 50,
    "original_price": 100,
    "discount_price": 10,
    "final_price": 90
  }
}
```
Not used by the island (the product_price endpoint already returns stock).

---

## C. Search endpoints

### C1. `GET /{slug}/search-product`  —  product name search  (session)
**Controller:** `App\Http\Controllers\HomeController::search_products`
**Route:** `routes/customer.php:65`  name `search.product`

**Request:** `?product=shirt`
**Response:** redirect to product page if exact match, else to 404 with `?search=`.

### C2. `POST /{slug}/search`  —  filtered product search  (token, api.php)
**Controller:** `App\Http\Controllers\Api\ApiController::search`

**Request:** `type (product_search|product_filter), name, tag, rating, min_price, max_price`
**Response:** paginated product list (uses `ApiResponser::success`).

**Decision:** Search-suggest is **out of scope for CLAUDE-15** (the task focuses on
cart drawer + variant selector). The search contract is documented here for
CLAUDE-16 or a future task. The session-based `search-product` route is the one to
use if autocomplete is added later.

---

## D. Wishlist endpoint

### D1. `POST /{slug}/product-wishlist`  —  toggle wishlist  (session)
**Controller:** `App\Http\Controllers\WishlistController::product_wishlist`
**Route:** `routes/customer.php:108`  name `product.wishlist`

**Request:** `product_id, variant_id`
**Response:** `{ status, message, ... }`

### D2. `POST /{slug}/wishlist`  —  toggle wishlist  (token, api.php)
**Controller:** `App\Http\Controllers\Api\ApiController::wishlist`

Requires `customer_id` + `product_id` + `wishlist_type (add|remove)`.
Same shape as D1 but token-guarded. Not used.

---

## Summary table

| Feature | Endpoint used | Auth | Response format |
|---|---|---|---|
| Add to cart | `POST /{slug}/product_cart` | session cookie | `{status, message, data:{count}}` |
| Qty + / − / remove | `POST /{slug}/change-cart` | session cookie | `{status, message, data:{count?}}` |
| Cart list (drawer data) | `POST|GET /{slug}/cart-list-sidebar` | session cookie | `{status, cart_total_product, html, sub_total, ...}` |
| Remove item | `POST /{slug}/cart-remove` | session cookie | `{status: 1}` |
| Clear cart | `POST /{slug}/cart-clear` | session cookie | `{status: 1}` |
| Variant price + stock | `POST /{slug}/product_price` | session cookie | `{variant_id, final_price, stock, stock_status, ...}` |

All endpoints accept standard `_token` CSRF via `meta[name="csrf-token"]` (already
set by the layouts). No bearer token injection required.
