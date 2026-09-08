@php
    if(auth()->user() && auth()->user()->type == 'admin') {
        $setting = getAdminAllSetting();
    } else {
        $setting = getSuperAdminAllSetting();
    }
 @endphp
 <footer class="dash-footer">
     <div class="footer-wrapper">
         <div class="py-1">
             <span class="text-muted">  @if(isset($setting['footer_text']) && (strpos($setting['footer_text'], "©") === false && strpos($setting['footer_text'], "&copy;") === false))
                    &copy;
                @endif

                {{ date('Y') }}
                {{ isset($setting['footer_text']) ? $setting['footer_text'] : config('app.name', 'E-CommerceGo') }}  </span>
         </div>
     </div>
 </footer>
                                                                                    
<!-- Required Js -->


{{--
    data-turbo-eval="false" on the library block below (CLAUDE-6).

    These are third-party libraries, loaded from <body>. Turbo re-executes body
    scripts on every navigation, and re-running a library is never useful — it is
    already loaded — while for several of these it is actively harmful:

      jquery-3.6.0.min.js  a fresh jQuery replaces window.jQuery, and with it the
                           `expando` key its event registry is stored under.
                           Measured: expando changed on every navigation, so
                           jQuery._data(document,'events') reported 26 → 13
                           handlers while the *old* instance's native listener
                           was still attached and still firing. Every delegated
                           handler in the app silently forked in two, and no
                           deduplication could see it. It also wiped the plugins
                           later scripts had attached to $.fn ($.fn.dataTable was
                           false right after a navigation, then true again once
                           datatable-js re-ran) — which is where
                           "$(...).DataTable is not a function" came from.

      dash.js              two top-level `let slideUp` / `let slideDown` →
                           "Identifier 'slideUp' has already been declared" on
                           every navigation, killing the rest of the file
                           (sidebar collapse, menu scroll, tooltips).

      apexcharts / summernote / select2 / flatpickr / dropzone / choices …
                           re-registering the same $.fn plugin is wasted work at
                           best; several also re-scan the DOM on load.

    Verified that the attribute works here: Turbo's activateScriptElement() checks
    it on the *incoming* element, and these tags are byte-identical between pages,
    so it survives. After the change jQuery's expando stayed constant across
    navigation and the two SyntaxErrors stopped.

    ⚠️ It is deliberately NOT on js/custom.js or js/loader.js — those hold app
    behaviour that has to re-register per page. Their duplicate delegation is
    handled by layouts/includes/turbo-dedupe.blade.php instead.

    KILL-SWITCH: delete the attributes. The errors above come back; nothing else
    changes.
--}}
<script src="{{ asset('js/jquery-3.6.0.min.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('assets/js/plugins/popper.min.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('assets/js/plugins/simplebar.min.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('assets/js/plugins/feather.min.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js')}}" data-turbo-eval="false"></script>
@if(module_is_active('SidebarCustomization'))
    {{-- side-dash.js still uses DOMContentLoaded, which never fires again under
         Turbo, so the sidebar would stop responding after the first navigation.
         It is the SidebarCustomization module's asset and that module is inactive
         here, so it is left untouched and unverified — noted in
         .ai/reports/turbo-console-errors.md. --}}
    <script src="{{ asset('assets/js/side-dash.js') }}" data-turbo-eval="false"></script>
@else
    {{-- dash.js was converted to onTurboLoad(dashInit) — see the header comment in
         that file. asset_v() so the .htaccess 7-day Cache-Control cannot pin the
         old copy. --}}
    <script src="{{ asset_v('assets/js/dash.js') }}" data-turbo-eval="false"></script>
@endif
<script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('assets/js/plugins/simple-datatables.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('assets/js/plugins/notifier.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('assets/js/pages/ac-notification.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset_v('assets/js/plugins/choices.min.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('assets/css/summernote/summernote-bs4.js')}}" data-turbo-eval="false"></script>
<script src="{{ asset('assets/js/plugins/flatpickr.min.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('js/socialSharing.js') }}" data-turbo-eval="false"></script>
{{-- js/custom.js stays evaluated: it holds app behaviour (modal wiring,
     comman_function, summernote/choices init) that must re-run per page. --}}
<script src="{{ asset_v('js/custom.js') }}"></script>
<script src="{{ asset('js/jquery.form.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('js/jspdf.umd.min.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('public/assets/js/plugins/dropzone.min.js') }}" data-turbo-eval="false"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pickadate.js/3.5.3/picker.js" data-turbo-eval="false"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pickadate.js/3.5.3/picker.date.js" data-turbo-eval="false"></script>
{{-- select2 --}}

<script src="{{ asset('public/assets/js/plugins/select2.min.js') }}" data-turbo-eval="false"></script>
{{-- js/loader.js also stays evaluated: it binds the submit-validation
     delegation the app depends on. turbo-dedupe keeps it from doubling. --}}
<script src="{{ asset('js/loader.js') }}"></script>
<script src="{{ asset('js/emojionearea.min.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('js/calendar.js') }}" data-turbo-eval="false"></script>
<script src="{{ asset('js/moment/min/moment.min.js') }}" data-turbo-eval="false"></script>

<script type="text/javascript" src="{{ asset('public/js/html2pdf.bundle.min.js') }}" data-turbo-eval="false"></script>

<script>
    window.onTurboLoad(function () {
    feather.replace();
    var pctoggle = document.querySelector("#pct-toggler");
    if (pctoggle) {
        pctoggle.addEventListener("click", function() {
            if (
                !document.querySelector(".pct-customizer").classList.contains("active")
            ) {
                document.querySelector(".pct-customizer").classList.add("active");
            } else {
                document.querySelector(".pct-customizer").classList.remove("active");
            }
        });
    }

    var themescolors = document.querySelectorAll(".themes-color > a");
    for (var h = 0; h < themescolors.length; h++) {
        var c = themescolors[h];

        c.addEventListener("click", function(event) {
            var targetElement = event.target;
            if (targetElement.tagName == "SPAN") {
                targetElement = targetElement.parentNode;
            }
            var temp = targetElement.getAttribute("data-value");
            removeClassByPrefix(document.querySelector("body"), "theme-");
            document.querySelector("body").classList.add(temp);
        });
    }

    var custthemebg = document.querySelector("#cust_theme_bg");
    if ($("#cust_theme_bg").length > 0) {
        custthemebg.addEventListener("click", function() {
            if (custthemebg.checked) {
                document.querySelector(".dash-sidebar").classList.add("transprent-bg");
                document
                    .querySelector(".dash-header:not(.dash-mob-header)")
                    .classList.add("transprent-bg");
            } else {
                document.querySelector(".dash-sidebar").classList.remove("transprent-bg");
                document
                    .querySelector(".dash-header:not(.dash-mob-header)")
                    .classList.remove("transprent-bg");
            }
        });
    }
    });

    var logoDark = "{{ isset($setting['logo_dark']) && $setting['logo_dark'] ? asset($setting['logo_dark']) : asset('storage/uploads/logo/logo-dark.png') }}";
    var logoLight = "{{ isset($setting['logo_light']) && $setting['logo_light'] ? asset($setting['logo_light']) : asset('storage/uploads/logo/logo-light.png') }}";
    window.onTurboLoad(function () {
    var custdarklayout = document.querySelector("#cust-darklayout");
    if ($("#cust-darklayout").length > 0) {
        custdarklayout.addEventListener("click", function() {
            var rtl = document.querySelector("#SITE_RTL");
            if (custdarklayout.checked) {
                document.querySelector(".m-header > .b-brand > .logo-lg").setAttribute("src",logoLight);
                    if (rtl.checked) {
                        document.querySelector("#main-style-link").setAttribute("href", "{{ asset('assets/css/rtl-style-dark.css') }}");
                        document.querySelector("#main-style-custom-link").setAttribute("href", "{{ asset('css/rtl-custom.css') }}");
                        document.querySelector('#html-dir-tag').setAttribute('dir', 'rtl');
                    } else if (!rtl.checked) {
                        document.querySelector("#main-style-link").setAttribute("href", "{{ asset('assets/css/style-dark.css') }}");
                        document.querySelector("#main-style-custom-link").setAttribute("href", "{{ asset('css/custom.css') }}");
                        document.querySelector('#html-dir-tag').setAttribute('dir', '');
                    } else {
                        document.querySelector("#main-style-link").setAttribute("href", "{{ asset('assets/css/style.css') }}");
                        document.querySelector("#main-style-custom-link").setAttribute("href", "{{ asset('css/custom.css') }}");
                        document.querySelector('#html-dir-tag').setAttribute('dir', '');
                    }

            } else {
                document.querySelector(".m-header > .b-brand > .logo-lg").setAttribute("src",logoDark);
                    if (rtl.checked) {
                        document.querySelector("#main-style-link").setAttribute("href", "{{ asset('assets/css/style-rtl.css') }}");
                        document.querySelector("#main-style-custom-link").setAttribute("href", "{{ asset('css/rtl-custom.css') }}");
                        document.querySelector('#html-dir-tag').setAttribute('dir', 'rtl');
                    } else if (!rtl.checked) {
                        document.querySelector("#main-style-link").setAttribute("href", "{{ asset('assets/css/style.css') }}");
                        document.querySelector("#main-style-custom-link").setAttribute("href", "{{ asset('css/custom.css') }}");
                        document.querySelector('#html-dir-tag').setAttribute('dir', '');
                    } else {
                        document.querySelector("#main-style-link").setAttribute("href", "{{ asset('assets/css/style.css') }}");
                        document.querySelector("#main-style-custom-link").setAttribute("href", "{{ asset('css/custom.css') }}");
                        document.querySelector('#html-dir-tag').setAttribute('dir', '');
                    }
            }
        });
    }
    });

    window.onTurboOnce(function () {
    $(document).on('click', '#cust_theme_bg',function() {
        if ($('#cust_theme_bg').is(":checked")) {
            document.querySelector(".dash-sidebar").classList.add("transprent-bg");
            document
                .querySelector(".dash-header:not(.dash-mob-header)")
                .classList.add("transprent-bg");
        } else {
            document.querySelector(".dash-sidebar").classList.remove("transprent-bg");
            document
                .querySelector(".dash-header:not(.dash-mob-header)")
                .classList.remove("transprent-bg");
        }
    });

    $(document).on('click', '#cust-darklayout',function() {
        var rtl = document.querySelector("#SITE_RTL");
            if ($('#cust-darklayout').is(":checked")) {
                document.querySelector(".m-header > .b-brand > .logo-lg").setAttribute("src",logoLight);
                    if (rtl.checked) {
                        document.querySelector("#main-style-link").setAttribute("href", "{{ asset('assets/css/rtl-style-dark.css') }}");
                        document.querySelector("#main-style-custom-link").setAttribute("href", "{{ asset('css/rtl-custom.css') }}");
                        document.querySelector('#html-dir-tag').setAttribute('dir', 'rtl');
                    } else if (!rtl.checked) {
                        document.querySelector("#main-style-link").setAttribute("href", "{{ asset('assets/css/style-dark.css') }}");
                        document.querySelector("#main-style-custom-link").setAttribute("href", "{{ asset('css/custom.css') }}");
                        document.querySelector('#html-dir-tag').setAttribute('dir', '');
                    } else {
                        document.querySelector("#main-style-link").setAttribute("href", "{{ asset('assets/css/style.css') }}");
                        document.querySelector("#main-style-custom-link").setAttribute("href", "{{ asset('css/custom.css') }}");
                        document.querySelector('#html-dir-tag').setAttribute('dir', '');
                    }

            } else {
                document.querySelector(".m-header > .b-brand > .logo-lg").setAttribute("src",logoDark);
                    if (rtl.checked) {
                        document.querySelector("#main-style-link").setAttribute("href", "{{ asset('assets/css/style-rtl.css') }}");
                        document.querySelector("#main-style-custom-link").setAttribute("href", "{{ asset('css/rtl-custom.css') }}");
                        document.querySelector('#html-dir-tag').setAttribute('dir', 'rtl');
                    } else if (!rtl.checked) {
                        document.querySelector("#main-style-link").setAttribute("href", "{{ asset('assets/css/style.css') }}");
                        document.querySelector("#main-style-custom-link").setAttribute("href", "{{ asset('css/custom.css') }}");
                        document.querySelector('#html-dir-tag').setAttribute('dir', '');
                    } else {
                        document.querySelector("#main-style-link").setAttribute("href", "{{ asset('assets/css/style.css') }}");
                        document.querySelector("#main-style-custom-link").setAttribute("href", "{{ asset('css/custom.css') }}");
                        document.querySelector('#html-dir-tag').setAttribute('dir', '');
                    }
            }
    });
    });

    function removeClassByPrefix(node, prefix) {
        for (let i = 0; i < node.classList.length; i++) {
            let value = node.classList[i];
            if (value.startsWith(prefix)) {
                node.classList.remove(value);
            }
        }
    }


    window.onTurboLoad(function () {
    var searchInput = document.getElementById("searchInput");
    if (searchInput) {
        searchInput.addEventListener("input", function() {
        var input, filter, ul, li, a, i, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        div = document.getElementById("storeList");
        a = div.getElementsByTagName("a");
        for (i = 0; i < a.length; i++) {
            txtValue = a[i].textContent || a[i].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                a[i].style.display = "";
            } else {
                a[i].style.display = "none";
            }
        }
    });
    }
    });

</script>
