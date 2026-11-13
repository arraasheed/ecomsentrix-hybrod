@extends('layouts.app')

@section('page-title')
    {{ __('System Notifications') }}
@endsection

@section('content')
<div class="container py-4">
    <h1>{{ __('System Notifications') }}</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('notificationengine.admin.store') }}" class="card">
        @csrf
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label" for="notification-title">{{ __('Title') }}</label>
                <input class="form-control" id="notification-title" name="title" maxlength="255" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="notification-message">{{ __('Message') }}</label>
                <textarea class="form-control" id="notification-message" name="message" rows="4" maxlength="5000" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="notification-type">{{ __('Type') }}</label>
                <select class="form-select" id="notification-type" name="type" required>
                    <option value="announcement">{{ __('Announcement') }}</option>
                    <option value="billing">{{ __('Billing') }}</option>
                    <option value="order">{{ __('Order') }}</option>
                    <option value="alert">{{ __('Alert') }}</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="notification-recipient">{{ __('Audience') }}</label>
                <select class="form-select" id="notification-recipient" name="recipient_type" required>
                    <option value="all_stores">{{ __('All Stores') }}</option>
                    <option value="store_admin">{{ __('Specific Store Admin') }}</option>
                </select>
            </div>
            <div class="mb-3" id="notification-store-wrapper" hidden>
                <label class="form-label" for="notification-store">{{ __('Store') }}</label>
                <select class="form-select" id="notification-store" name="store_id">
                    <option value="">{{ __('Select a store') }}</option>
                    @foreach ($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="notification-target">{{ __('Target URL (optional)') }}</label>
                <input class="form-control" id="notification-target" name="target_url" type="url" maxlength="2048">
            </div>
            <button class="btn btn-primary" type="submit">{{ __('Dispatch Notification') }}</button>
        </div>
    </form>
</div>

<script>
(() => {
    const audience = document.getElementById('notification-recipient');
    const wrapper = document.getElementById('notification-store-wrapper');
    const store = document.getElementById('notification-store');
    const syncStoreVisibility = () => {
        const specific = audience.value === 'store_admin';
        wrapper.hidden = !specific;
        store.required = specific;
    };
    audience.addEventListener('change', syncStoreVisibility);
    syncStoreVisibility();
})();
</script>
@endsection
