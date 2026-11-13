<div class="dropdown d-inline-block me-2">
    <button class="btn btn-badge btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
        aria-expanded="false">
        <i class="ti ti-printer me-1"></i>{{ __('Print Receipt') }}
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @foreach (['minimalist' => __('Minimalist Modern'), 'corporate' => __('Corporate Brand'), 'thermal' => __('POS Thermal Slip'), 'premium' => __('eCommerce Premium')] as $template => $label)
            <li>
                <a class="dropdown-item" target="_blank"
                    href="{{ route('receiptengine.show', ['order' => \Illuminate\Support\Facades\Crypt::encryptString((string) $order['id']), 'template' => $template, 'print' => 1]) }}">
                    {{ $label }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
