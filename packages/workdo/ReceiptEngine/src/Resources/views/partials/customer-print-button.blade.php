<script>
(() => {
    const mount = () => {
        const actionBar = document.querySelector('#printableArea')?.closest('main')?.querySelector('.flex.gap-2');
        if (!actionBar || actionBar.querySelector('[data-receipt-engine]')) return;

        const encodedOrder = @json(\Illuminate\Support\Facades\Crypt::encryptString($order['id'] ?? ''));
        const printLabel = @json(__('Print Receipt'));
        const wrapper = document.createElement('div');
        wrapper.dataset.receiptEngine = 'true';
        wrapper.className = 'relative';
        wrapper.innerHTML = `<select aria-label="${printLabel}" class="border border-gray-300 rounded-md px-3 py-2 text-sm" onchange="if(this.value){window.open(this.value,'_blank');this.value='';}"><option value="">${printLabel}</option>${['minimalist','corporate','thermal','premium'].map((template) => `<option value="{{ url('/receipt-engine') }}/${encodedOrder}?template=${template}&print=1">${template}</option>`).join('')}</select>`;
        actionBar.prepend(wrapper);
    };

    if (window.onTurboLoad) {
        window.onTurboLoad(mount);
    } else {
        document.addEventListener('DOMContentLoaded', mount, { once: true });
    }
})();
</script>
