{{--
  Ecom Sentrix — Live 3-Source Real-Time Fraud Shield Hook
  Zero core modification — injected directly into front_end.hooks.checkout_form
--}}

@if (env('FRAUD_PIXEL_SHIELD_ENABLED', true))
<div id="fraud-shield-blocked-modal" style="display: none; position: fixed; inset: 0; z-index: 99999; background: rgba(0, 0, 0, 0.65); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 16px; max-width: 440px; width: 100%; padding: 28px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); text-align: center; animation: modalPop 0.25s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;">
        <div style="width: 64px; height: 64px; background: #fef2f2; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
            <svg style="width: 34px; height: 34px; color: #ef4444;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h3 style="font-size: 20px; font-weight: 700; color: #111827; margin: 0 0 10px 0;">{{ __('অর্ডার সম্পন্ন করতে সহায়তা প্রয়োজন') }}</h3>
        <p style="font-size: 14px; line-height: 1.6; color: #4b5563; margin: 0 0 20px 0;">
            {{ __('কারিগরি বা অপ্রত্যাশিত কারণে ওয়েবসাইট থেকে সরাসরি অর্ডারটি প্রসেস করা সম্ভব হচ্ছে না আপনার পছন্দের প্রোডাক্টটি দ্রুত নিশ্চিত করতে অনুগ্রহ করে আমাদের WhatsApp সাপোর্টে যোগাযোগ করুন') }}
        </p>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <a id="fraud-modal-whatsapp-link" href="#" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: #25D366; color: #ffffff; font-weight: 600; font-size: 15px; padding: 12px 20px; border-radius: 10px; text-decoration: none; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.35);">
                <svg style="width: 20px; height: 20px;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                </svg>
                {{ __('WhatsApp-এ অর্ডার কনফার্ম করুন') }}
            </a>
            <button type="button" id="fraud-modal-close-btn" style="background: transparent; border: 1px solid #e5e7eb; color: #6b7280; font-size: 14px; font-weight: 500; padding: 10px 16px; border-radius: 10px; cursor: pointer;">
                {{ __('বাতিল করুন') }}
            </button>
        </div>
    </div>
</div>

<style>
@keyframes modalPop {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.fraud-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 4px;
    padding: 2px 8px;
    border-radius: 6px;
    transition: all 0.2s ease;
}
.fraud-badge-safe { background: #ecfdf5; color: #059669; }
.fraud-badge-warning { background: #fef2f2; color: #dc2626; }
.fraud-badge-verifying { background: #f3f4f6; color: #6b7280; }
</style>

<script>
window.onTurboLoad(function () {
    const storeSlug = '{{ $slug ?? ($storeSlug ?? ($store->slug ?? "")) }}';
    if (!storeSlug) return;

    let phoneInput = document.querySelector('input[name="billing_info[billing_user_telephone]"]') || 
                     document.querySelector('#phone') || 
                     document.querySelector('input[type="tel"]');

    if (!phoneInput) return;

    // State tracking
    window.__fraudCheckResult = null;
    window.__fraudCheckInFlight = false;
    let debounceTimer = null;
    let lastCheckedPhone = '';

    // Create inline feedback container right under phone field
    let feedbackEl = document.getElementById('fraud-phone-feedback');
    if (!feedbackEl && phoneInput.parentElement) {
        feedbackEl = document.createElement('div');
        feedbackEl.id = 'fraud-phone-feedback';
        phoneInput.parentElement.appendChild(feedbackEl);
    }

    function checkPhone(phoneVal) {
        let clean = phoneVal.replace(/[^0-9]/g, '');
        if (clean.startsWith('880')) clean = clean.substring(2);
        if (clean.startsWith('88')) clean = clean.substring(2);

        if (clean.length !== 11 || !clean.startsWith('01')) {
            if (feedbackEl) feedbackEl.innerHTML = '';
            window.__fraudCheckResult = null;
            return;
        }

        if (clean === lastCheckedPhone && window.__fraudCheckResult) {
            return;
        }

        lastCheckedPhone = clean;
        window.__fraudCheckInFlight = true;

        if (feedbackEl) {
            feedbackEl.innerHTML = '<span class="fraud-status-badge fraud-badge-verifying"><i class="fas fa-spinner fa-spin"></i> কুরিয়ার স্কোর যাচাই হচ্ছে...</span>';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        fetch('/' + storeSlug + '/fraud-check', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ phone: clean })
        })
        .then(res => res.json())
        .then(data => {
            window.__fraudCheckInFlight = false;
            window.__fraudCheckResult = data;

            if (!feedbackEl) return;

            if (data.status === 'success') {
                if (data.blocked) {
                    feedbackEl.innerHTML = '<span class="fraud-status-badge fraud-badge-warning"><i class="fas fa-exclamation-triangle"></i> কুরিয়ার ডেলিভারি রেট: ' + data.success_rate + '% (' + data.total_delivery + '/' + data.total_orders + ')</span>';
                } else if (data.total_orders > 0) {
                    feedbackEl.innerHTML = '<span class="fraud-status-badge fraud-badge-safe"><i class="fas fa-check-circle"></i> নির্ভরযোগ্য কাস্টমার (' + data.success_rate + '% ডেলিভারি রেট)</span>';
                } else {
                    feedbackEl.innerHTML = '';
                }
            } else {
                feedbackEl.innerHTML = '';
            }
        })
        .catch(err => {
            window.__fraudCheckInFlight = false;
            if (feedbackEl) feedbackEl.innerHTML = '';
        });
    }

    // 300ms Fast Debounce on phone typing
    phoneInput.addEventListener('input', function (e) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            checkPhone(phoneInput.value);
        }, 300);
    });

    // Also check on blur or paste
    phoneInput.addEventListener('blur', function () {
        checkPhone(phoneInput.value);
    });

    // Form Submit Guard
    const checkoutForm = document.querySelector('#formdata') || phoneInput.closest('form');
    const submitBtn = document.querySelector('.place_order_submit') || 
                      document.querySelector('#payfast_form') || 
                      document.querySelector('button[type="submit"]');

    if (!checkoutForm) return;

    checkoutForm.addEventListener('submit', function (e) {
        const currentPhone = phoneInput.value.replace(/[^0-9]/g, '');

        // If phone is 11 digits and check is currently running
        if (window.__fraudCheckInFlight) {
            e.preventDefault();
            e.stopImmediatePropagation();

            let originalBtnText = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> ' + '{{ __("যাচাই করা হচ্ছে...") }}';
            }

            // Poll every 150ms up to 3000ms
            let elapsed = 0;
            const pollInterval = setInterval(function () {
                elapsed += 150;
                if (!window.__fraudCheckInFlight || elapsed >= 3000) {
                    clearInterval(pollInterval);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }

                    // Check result
                    if (window.__fraudCheckResult && window.__fraudCheckResult.blocked) {
                        triggerBlockedModal();
                    } else {
                        // Safe to submit
                        checkoutForm.submit();
                    }
                }
            }, 150);

            return false;
        }

        // If already verified and marked blocked
        if (window.__fraudCheckResult && window.__fraudCheckResult.blocked) {
            e.preventDefault();
            e.stopImmediatePropagation();
            triggerBlockedModal();
            return false;
        }
    }, true);

    function triggerBlockedModal() {
        const modal = document.getElementById('fraud-shield-blocked-modal');
        const waLink = document.getElementById('fraud-modal-whatsapp-link');
        const closeBtn = document.getElementById('fraud-modal-close-btn');

        if (!modal) return;

        if (waLink && window.__fraudCheckResult && window.__fraudCheckResult.whatsapp_url) {
            waLink.href = window.__fraudCheckResult.whatsapp_url;
        }

        modal.style.display = 'flex';

        if (closeBtn) {
            closeBtn.onclick = function () {
                modal.style.display = 'none';
            };
        }

        // Asynchronously log lead into database so merchant can contact later
        try {
            const nameField = document.querySelector('input[name*="billing_user_name"]') || document.querySelector('input[name*="name"]');
            const addressField = document.querySelector('textarea[name*="address"]') || document.querySelector('input[name*="address"]');
            const subtotalField = document.querySelector('#sub_total_checkout_page');

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            fetch('/' + storeSlug + '/fraud-log-lead', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    phone: phoneInput.value,
                    name: nameField ? nameField.value : '',
                    address: addressField ? addressField.value : '',
                    cart_total: subtotalField ? subtotalField.value : 0,
                    success_rate: window.__fraudCheckResult ? window.__fraudCheckResult.success_rate : 0,
                    courier_details: window.__fraudCheckResult ? window.__fraudCheckResult.courier_details : ''
                })
            });
        } catch (e) {}
    }
});
</script>
@endif
