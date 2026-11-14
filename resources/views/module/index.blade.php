@extends('layouts.app')

@section('page-title')
    {{ __('Add-on Manager') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Add-on Manager') }}</li>
@endsection

@push('css')
    <style>
        .addon-tabs {
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 1.5rem;
        }
        
        .addon-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6c757d;
            font-weight: 500;
            padding: 0.75rem 1.25rem;
            margin-right: 0.5rem;
            border-radius: 0;
            transition: all 0.2s ease;
        }
        
        .addon-tabs .nav-link:hover {
            color: #495057;
            background-color: transparent;
        }
        
        .addon-tabs .nav-link.active {
            color: #667eea;
            border-bottom-color: #667eea;
            background-color: transparent;
        }
        
        .module-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        
        .theme-avtar img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            border-radius: 8px;
            background: #f8f9fa;
            padding: 6px;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Horizontal Filter Tabs -->
    <div class="addon-tabs d-flex justify-content-between align-items-center">
        <ul class="nav nav-pills justify-content-start" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab == 'all' ? 'active' : '' }}" 
                   href="{{ route('module.index', ['tab' => 'all']) }}">
                    <i class="ti ti-apps me-1"></i> {{ __('All Modules') }}
                    <span class="badge bg-secondary ms-2">{{ count($modules) }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab == 'enabled' ? 'active' : '' }}" 
                   href="{{ route('module.index', ['tab' => 'enabled']) }}">
                    <i class="ti ti-check me-1"></i> {{ __('Enabled') }}
                    <span class="badge bg-success ms-2">{{ $enabledCount }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab == 'disabled' ? 'active' : '' }}" 
                   href="{{ route('module.index', ['tab' => 'disabled']) }}">
                    <i class="ti ti-x me-1"></i> {{ __('Disabled') }}
                    <span class="badge bg-danger ms-2">{{ $disabledCount }}</span>
                </a>
            </li>
        </ul>
        
        <div class="search-box">
            <input type="text" id="moduleSearch" class="form-control" style="width: 250px;" 
                   placeholder="{{ __('Search Add-ons...') }}">
        </div>
    </div>

    <!-- Modules Grid (Only our system's active native & custom addons) -->
    <div class="row" id="modulesGrid">
        @if (!empty($modules) && count($modules) > 0)
            @foreach ($modules as $module)
                @php
                    $id = strtolower(preg_replace('/\s+/', '_', $module->name));
                @endphp
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 d-flex mb-4 module-card" data-name="{{ strtolower($module->alias ?? $module->name) }}">
                    <div class="card w-100 shadow-sm border rounded-3 {{ $module->isEnabled() ? 'border-success' : 'border-light' }}">
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="theme-avtar p-2 rounded bg-light border">
                                    <img src="{{ $module->image ? asset($module->image) : asset('assets/images/user-avatar.png') }}"
                                         alt="{{ $module->name }}" onerror="this.src='{{ asset('assets/images/user-avatar.png') }}'">
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <h5 class="mb-1 text-truncate font-weight-bold" title="{{ $module->alias ?? $module->name }}">
                                        {{ $module->alias ?? $module->name }}
                                    </h5>
                                    <span class="badge {{ $module->isEnabled() ? 'bg-success' : 'bg-danger' }}">
                                        {{ $module->isEnabled() ? __('Active') : __('Inactive') }}
                                    </span>
                                </div>
                            </div>
                            
                            <p class="text-muted small mb-3 text-truncate-2" style="min-height: 38px;">
                                {{ $module->description ?? __('Integrated system extension module.') }}
                            </p>

                            <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto">
                                <span class="small text-muted font-monospace">v{{ $module->version ?? '1.0.0' }}</span>
                                <form action="{{ route('module.enable') }}" method="POST" id="form_{{ $module->name }}">
                                    @csrf
                                    <input type="hidden" name="name" value="{{ $module->name }}">
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input module_toggle_switch" type="checkbox" role="switch"
                                               {{ $module->isEnabled() ? 'checked' : '' }}
                                               onchange="document.getElementById('form_{{ $module->name }}').submit()">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-12 empty-state">
                <i class="ti ti-box" style="font-size: 3rem;"></i>
                <h5 class="mt-2">{{ __('No add-ons found') }}</h5>
                <p class="text-muted">{{ __('No add-on matches the current filter.') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.onTurboLoad(function () {
            var searchInput = document.getElementById('moduleSearch');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    let query = this.value.toLowerCase();
                    let cards = document.querySelectorAll('.module-card');
                    cards.forEach(function(card) {
                        let name = card.getAttribute('data-name');
                        if (name.includes(query)) {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
@endpush
