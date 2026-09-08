{{--
    Keep Turbo away from placeholder links (CLAUDE-6)

    Included by layouts/includes/turbo.blade.php.

    ── The problem ───────────────────────────────────────────────────────────

    This codebase uses `<a href="#">` as the trigger for anything that is not
    really a link: every `data-ajax-popup="true"` modal, dropdown toggles, tab
    switches, print buttons. Counted across resources/views and themes:

        href="#"                207
        href="javascript:..."   135

    Turbo resolves `href="#"` against the current document, so on /coupon it
    becomes https://ecommercego.dev/coupon# — same origin, same path, therefore a
    visitable location. Turbo treats the click as a navigation and reloads the page
    it is already on.

    Observed on /coupon, clicking "Add Coupon":

        turbo:click → turbo:before-visit → turbo:visit
                    → turbo:before-cache → turbo:before-render → turbo:load

        modal class:  "modal fade" → "modal fade show" → "modal fade"

    The jQuery handler opened the modal, the ajax body arrived (8.5 KB), and then
    Turbo swapped the body out from under it. The modal was gone in about 200 ms.
    turbo-cleanup.js ran twice during that sequence — doing exactly what it is
    supposed to do, on a page swap nobody asked for. With TURBO_ADMIN_ENABLED=false
    the same click keeps the modal open (verified), which isolates the cause.

    That would have taken out all 101 ajax modals in the admin.

    ── The fix ───────────────────────────────────────────────────────────────

    turbo:click is cancelable, and cancelling it makes Turbo leave the click alone
    without touching the element's own handlers. So a bare "#" or a
    "javascript:" href never becomes a navigation.

    Deliberately narrow: only href="#", href="" and href="javascript:*". A real
    in-page anchor like href="#section-2" still scrolls through Turbo, which is the
    behaviour it should have. Adding data-turbo="false" to 342 anchors across 127
    files would do the same job with 342 chances to miss one.

    ── KILL-SWITCH ───────────────────────────────────────────────────────────

    window.__TURBO_HASH_GUARD_DISABLED__ = true
    TURBO_ENABLED=false                        (nothing to guard)
--}}
<script>
    (function () {
        if (window.__TURBO_DISABLED__ === true) return;

        document.addEventListener('turbo:click', function (event) {
            if (window.__TURBO_HASH_GUARD_DISABLED__ === true) return;

            var link = event.target && event.target.closest ? event.target.closest('a') : null;
            if (!link) return;

            // The raw attribute, not link.href: the browser has already resolved the
            // latter to an absolute URL, so "#" is indistinguishable from a real
            // anchor by then.
            var href = (link.getAttribute('href') || '').trim();

            if (href === '' || href === '#' || href.toLowerCase().indexOf('javascript:') === 0) {
                event.preventDefault();
            }
        });
    })();
</script>
