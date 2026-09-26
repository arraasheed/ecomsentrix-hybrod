/* =====================================================================
   mobile-table.js  —  BULK-7 (Day 4, branch day4/mobile-polish)
   =====================================================================
   Adds data-label="<column name>" to every visible tbody td inside
   `.ecom-data-table table`, read from the matching thead th text.
   public/css/mobile-table.css turns those cells into label/value rows
   on phones (max-width: 767.98px).

   Rules implemented:
   - vanilla JS only (no jQuery)
   - runs only when window.innerWidth < 768
   - re-runs on turbo:load (Turbo Drive navigation)
   - re-runs when DataTable redraws rows over ajax: one MutationObserver
     on document.body (childList only) — DataTables' draw.dt cannot be
     caught natively (jQuery .trigger() does not dispatch real DOM
     events), so a DOM observer is the reliable hook
   - idempotent: a td whose data-label already equals the target label
     is skipped, and labels are re-corrected (not duplicated) when
     column order/visibility changes (e.g. ColVis)
   - Turbo-safe: everything lives inside one IIFE, no top-level
     const/let, guarded by a window flag so re-execution of this file
     never duplicates listeners (same pattern as the custom.js fix)

   Kill-switch: remove the <script> tag for this file (added by Claude).
   ===================================================================== */

(function () {
    'use strict';

    if (window.__ecomMobileTableCardView) {
        return; // already initialised in this document
    }
    window.__ecomMobileTableCardView = true;

    var BREAKPOINT = 768;
    var SELECTOR = '.ecom-data-table table';

    var lastMobile = null;
    var scheduled = false;
    var bodyObserver = null;

    function isMobile() {
        return window.innerWidth < BREAKPOINT;
    }

    function normalise(text) {
        return (text || '').replace(/\s+/g, ' ').trim();
    }

    /* remove every data-label inside the component (desktop / teardown) */
    function stripAll() {
        var cells = document.querySelectorAll('.ecom-data-table tbody td[data-label]');
        for (var i = 0; i < cells.length; i++) {
            cells[i].removeAttribute('data-label');
        }
    }

    function applyToTable(table) {
        var headers = table.querySelectorAll('thead th');
        if (!headers.length) {
            return;
        }

        var labels = [];
        for (var h = 0; h < headers.length; h++) {
            labels.push(normalise(headers[h].textContent));
        }

        var bodies = table.tBodies;
        for (var b = 0; b < bodies.length; b++) {
            var rows = bodies[b].rows;
            for (var r = 0; r < rows.length; r++) {
                var cells = rows[r].cells;
                for (var c = 0; c < cells.length; c++) {
                    var td = cells[c];
                    var colIndex = c;
                    if (td.colSpan > 1) {
                        continue; // colspan cells (e.g. dataTables_empty) get no label
                    }
                    if (c >= labels.length) {
                        break; // more cells than headers (rowspan edge) — stop
                    }
                    var label = labels[colIndex];
                    if (label) {
                        if (td.getAttribute('data-label') !== label) {
                            td.setAttribute('data-label', label);
                        }
                    } else if (td.hasAttribute('data-label')) {
                        // header has no text (checkbox column) — never label it
                        td.removeAttribute('data-label');
                    }
                }
            }
        }
    }

    function apply() {
        var mobile = isMobile();

        if (mobile === lastMobile) {
            /* same side of the breakpoint: only refresh labels, no strip */
            if (!mobile) {
                return;
            }
        } else {
            lastMobile = mobile;
            if (!mobile) {
                stripAll();
                return;
            }
        }

        var tables = document.querySelectorAll(SELECTOR);
        for (var i = 0; i < tables.length; i++) {
            applyToTable(tables[i]);
        }
    }

    /* debounce observer storms (ajax draws) to one run per frame */
    function scheduleApply() {
        if (scheduled) {
            return;
        }
        scheduled = true;
        requestAnimationFrame(function () {
            scheduled = false;
            apply();
        });
    }

    function startObserver() {
        if (bodyObserver || typeof MutationObserver === 'undefined' || !document.body) {
            return;
        }
        bodyObserver = new MutationObserver(scheduleApply);
        bodyObserver.observe(document.body, { childList: true, subtree: true });
    }

    function bootstrap() {
        lastMobile = isMobile();
        if (lastMobile) {
            apply();
        }
        startObserver();
    }

    document.addEventListener('turbo:load', function () {
        /* new DOM after navigation: re-scan (flag stays set on purpose —
           the listener is registered once per document, not per page) */
        scheduleApply();
    });

    window.addEventListener('resize', function () {
        scheduleApply();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrap);
    } else {
        bootstrap();
    }
})();
