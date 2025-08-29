=== Quick Variants ===
Contributors: crafely
Tags: woocommerce, product table, variants, quick view, ajax cart
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.2
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fast, filterable WooCommerce product table with expandable variants, AJAX add to cart, slide cart drawer & quick view.

== Description ==
Quick Variants is a lightweight WooCommerce enhancement that lets shoppers scan and purchase products (including variable products) from a compact, responsive table. Variable products can be expanded inline to reveal purchasable variation rows; each row supports direct quantity input and one‑click AJAX add to cart. A slide‑out mini cart (optional) updates in real time, and an accessible Quick View modal (optional) shows gallery, price, short description and variation selectors.

Designed for wholesale, B2B, bulk order, restaurant menu, or any catalog scenario where speed > visual browsing.

=== Key Features ===
* Responsive product table layout (Tailwind CSS based)
* Inline expandable variant rows ("Show variants" / "Hide variants")
* AJAX powered add to cart (simple + variation products)
* Optional slide cart drawer with live quantity update & remove controls
* Unified AJAX endpoint for search, alphabet filtering (A–Z), category restriction & progressive "Show more" pagination
* Optional Quick View modal (enables variation attribute selection + add to cart)
* Debounced live search box (reduces server load)
* Alphabet bar (toggleable) – filter by first letter of product title
* Dynamic progress bar + count: "Showing X–Y of Z total"
* Accessible focus handling & graceful fallbacks
* Customizable table column labels (Images, Product, Price, Qty, Options, Add to cart text)
* Color branding (primary/action color) via settings UI + presets
* Adjustable default per‑page rows (1–100) & optional max table width
* Shortcode generator in admin (with category multi‑select & per_page)
* Translation ready (text domain: quick-variants)
* Clean, namespaced settings stored in single option key

=== Performance Notes ===
The plugin intentionally performs two queries during initial shortcode render: one COUNT style query (optimized by disabling meta/term caches) for total products and one lightweight display query. Subsequent filtering/pagination is handled via a single consolidated AJAX action (`search_products`) using `no_found_rows` and offset pagination to minimize overhead.

=== Shortcode ===
Basic:

[quick_variants]

Limit to categories (comma separated slugs):

[quick_variants category="hoodies,tshirts"]

Override products per page (1–100):

[quick_variants per_page="25"]

Combine:

[quick_variants category="hoodies,tshirts" per_page="25"]

If omitted, `per_page` defaults to the value in Settings → Quick Variants.

=== Settings Overview ===
Admin Page: Dashboard → Quick Variants

General Tab:
* Alphabet Filter – show/hide A–Z bar
* Slide Cart Drawer – enable slide‑out cart after add
* Quick View – enable eye icon + modal
* Default Products Per Page – initial row count (affects shortcode default)
* Table Max Width – constrain layout (e.g. 1200px, 90%, 70rem) or leave blank for full width
* Primary Color – drives buttons, accents, progress and filters

Labels Tab: Customize: Images, Product, Price, Qty, Options, Add to cart button text.

Extras:
* Reset – restore all defaults (with nonce protection)
* Shortcode Generator – live builder with category search, select all / clear, per page override, copy button

=== Quick View Modal ===
If enabled, displays: product image, price, short description and (for variable products) attribute selectors. Variation matching occurs client‑side; add to cart is disabled until a complete set of attributes matches a variation. Successful add updates the shared slide cart (or mini cart UI if present) via AJAX.

=== Slide Cart Drawer ===
When enabled (and after first add), a panel renders via `wp_footer`. Supports:
* Live item list with image, name, variation attribute summary
* Inline quantity adjust (AJAX update)
* Remove line item
* Subtotal & total display
* Checkout & View Cart buttons (links to native WooCommerce pages)

=== Frontend Assets ===
Styles: Tailwind compiled to `assets/css/dist/style.css` (+ custom `table.css`). Inline CSS injects primary color variables.
Scripts (localized with nonces & AJAX URL):
* table.js (base interactions)
* filter.js (search/filter/pagination/variant toggle)
* cart.js (add, update, remove, slide cart UI)
* quick-view.js (modal + variation matching)
* pagination.js (reserved / optional future extensions)

=== Security / Nonce Usage ===
Each AJAX route validates an action‑specific nonce: `wc_filter_nonce` (search & quick view), `wc_cart_nonce` (cart operations). Inputs are sanitized and variation attributes filtered via `sanitize_text_field`.

=== Extensibility ===
You may override templates by copying files from `plugins/quick-variants/templates/` into `yourtheme/quick-variants/` (create the folder) maintaining the same filenames (product-row.php, table-wrapper.php, quick-view.php). If not present, core templates load from the plugin.

Filter/Action Hooks (primary ones):
* Shortcode: add_shortcode('quick_variants', ...)
* Assets: `wp_enqueue_scripts`, `admin_enqueue_scripts`
* Slide cart injection: `init` decides, then `wp_footer` outputs
* AJAX actions: add_to_cart, update_cart, remove_from_cart, get_cart, search_products, quick_view_product

(Additional custom filters can be added in future releases. Pull requests welcome.)

=== Installation ===
1. Ensure WooCommerce is active.
2. Upload the `quick-variants` folder to `/wp-content/plugins/` or install via zip.
3. Activate the plugin through the Plugins screen.
4. Visit Settings → Quick Variants to adjust defaults.
5. Insert `[quick_variants]` into a page or use the generator.

=== Minimum Requirements ===
* WordPress 5.0+
* WooCommerce 3.0+
* PHP 7.2+
* MySQL 5.6+ (or MariaDB equivalent)

=== Recommended ===
* WordPress 6.4+
* PHP 8.0+ for performance

=== FAQ ===
Q: How do I change the column labels?
A: Go to Dashboard → Quick Variants → Labels tab.

Q: Can I pre‑expand all variants?
A: Not yet via a built‑in setting. You could enqueue a small script that programmatically clicks all `.toggle-variants` buttons after load.

Q: How do I style the table further?
A: Add custom CSS in your theme or child theme; target `.qv-table-section`, `.variant-row`, `.add-to-cart`, etc. You can also copy template files into your theme for structural changes.

Q: Does it support caching plugins?
A: Yes. AJAX endpoints are dynamic; initial table HTML can be cached safely. Ensure nonces aren’t cached for logged-out if you expect guest add-to-cart (WooCommerce default allows it). If guest cart issues occur, exclude the page from aggressive cache or implement nonce regeneration.

Q: Can I disable the slide cart but keep AJAX add to cart?
A: Yes. Uncheck "Slide Cart Drawer" in settings. Standard WooCommerce mini cart widgets will still refresh on page load.

Q: How is performance with many products?
A: The table loads only the first page (default configurable). Users fetch more entries via incremental AJAX, keeping initial payload light. For very large catalogs consider narrowing by category or lowering per_page.

=== Developer Notes ===
Build Tailwind assets (development machine):

npm install
npm run build   # builds frontend + admin CSS
npm run watch   # watches frontend
npm run watch:admin

Tailwind sources: `assets/css/style.css`, `assets/css/admin.css` compiled into `assets/css/dist/`.

=== Changelog ===
= 1.0.0 =
* Initial public release.

=== Upgrade Notice ===
= 1.0.0 =
First release.

=== License ===
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License version 2 (GPL v2) as published by the Free Software Foundation.

=== Credits ===
Developed by Crafely (https://www.crafely.com). Tailwind CSS for utility classes. WooCommerce for eCommerce framework.
