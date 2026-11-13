@extends('layouts.app')
@section('page-title')
    {{ __('Add-on Manager') }}
@endsection
@section('page-breadcrumb')
    {{ __('Add-on Manager') }}
@endsection
@push('css')
    <style>
        .product-img {
            padding-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .system-version h5 {
            position: absolute;
            bottom: -44px;
            right: 27px;
        }

        .center-text {
            display: flex;
            flex-direction: column;
        }

        .center-text .text-primary {
            font-size: 14px;
            margin-top: 5px;
        }

        .theme-main {
            display: flex;
            align-items: center;
        }

        .theme-main .theme-avtar {
            margin-right: 15px;
        }

        .product-img .checkbox-custom .card-option {
            border: 0;
            outline: 0;
        }

        .product-img .checkbox-custom .card-option .btn.show {
            color: #000;
            border: 0;
        }

        .product-img .checkbox-custom .card-option .btn:focus {
            border: 0;
        }

        @media only screen and (max-width: 575px) {
            .system-version h5 {
                position: unset;
                margin-bottom: 0px;
            }

            .system-version {
                text-align: center;
                margin-bottom: -22px;
            }
        }

        /* Horizontal tabs styling */
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
        
        .module-card .card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .module-card .card.enable_module {
            border-left: 4px solid #28a745;
        }
        
        .module-card .card.disable_module {
            border-left: 4px solid #dc3545;
        }
        
        .theme-avtar img {
            width: 56px;
            height: 56px;
            object-fit: contain;
            border-radius: 8px;
            background: #f8f9fa;
            padding: 8px;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
    </style>
@endpush
@section('page-action')
@endsection

@section('content')
<div class="container-fluid">
    <!-- Horizontal Tabs -->
    <div class="addon-tabs">
        <ul class="nav nav-pills justify-content-start" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab == 'all' ? 'active' : '' }}" 
                   href="{{ route('module.index', ['tab' => 'all']) }}" 
                   role="tab">
                    <i class="ti ti-apps me-1"></i> {{ __('All Modules') }}
                    <span class="badge bg-secondary ms-2">{{ count($modules) }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab == 'enabled' ? 'active' : '' }}" 
                   href="{{ route('module.index', ['tab' => 'enabled']) }}" 
                   role="tab">
                    <i class="ti ti-check-circle me-1"></i> {{ __('Enabled') }}
                    <span class="badge bg-success ms-2">{{ $enabledCount }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab == 'disabled' ? 'active' : '' }}" 
                   href="{{ route('module.index', ['tab' => 'disabled']) }}" 
                   role="tab">
                    <i class="ti ti-x-circle me-1"></i> {{ __('Disabled') }}
                    <span class="badge bg-danger ms-2">{{ $disabledCount }}</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $tab == 'local' ? 'active' : '' }}" 
                   href="{{ route('module.index', ['tab' => 'local']) }}" 
                   role="tab">
                    <i class="ti ti-device-desktop me-1"></i> {{ __('Local Only') }}
                    <span class="badge bg-info ms-2">{{ $localCount }}</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Search -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0">{{ __('Installed Add-ons') }}</h4>
        </div>
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0"><i class="ti ti-search"></i></span>
                <input type="text" id="moduleSearch" class="form-control border-start-0" placeholder="{{ __('Search Add-ons...') }}">
            </div>
        </div>
    </div>

    <!-- Modules Grid -->
    <div class="row event-cards px-0" id="modulesContainer">
        @if(count($modules) > 0)
            @foreach ($modules as $module)
                @php
                    $module_name = $module->name;
                    $id = strtolower(preg_replace('/\s+/', '_', $module_name));
                @endphp
                @if (
                    (!isset($module->display) || $module->display == true || $module_name == 'GoogleCaptcha') &&
                    $module_name != 'LandingPage')
                    <div class="col-xl-3 col-md-4 col-sm-6 product-card h-auto module-card" data-name="{{ strtolower($module->alias) }}">
                        <div class="card {{ $module->isEnabled() ? 'enable_module' : 'disable_module' }} mb-0 h-100 justify-content-between">
                            <div class="product-img">
                                <div class="theme-main">
                                    <div class="theme-avtar">
                                        <img src="{{ $module->image ?? '' }}" alt="{{ $module->name }}" class="img-user" style="max-width: 100%">
                                    </div>
                                    <div class="center-text">
                                        <h5 class="mb-1">{{ $module->alias ?? $module->name }}</h5>
                                        <small class="text-muted">
                                            @if ($module->isEnabled())
                                                <span class="badge bg-success">{{ __('Enabled') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Disabled') }}</span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                <div class="theme-version">
                                    <small class="text-muted">{{ __('Version') }}: {{ $module->version ?? '1.0' }}</small>
                                </div>
                            </div>
                            <div class="card-body pt-0 pb-3">
                                <p class="text-muted small mb-2">{{ $module->description ?? '' }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    @if ($module->monthly_price > 0 || $module->yearly_price > 0)
                                        <div class="text-muted small">
                                            @if ($module->monthly_price > 0)
                                                <span class="text-primary">{{ $module->monthly_price }}/{{ __('month') }}</span>
                                            @endif
                                            @if ($module->yearly_price > 0)
                                                <span class="text-success ms-2">{{ $module->yearly_price }}/{{ __('year') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    <form action="{{ route('module.status', $module->alias) }}" id="form_{{ $id }}" method="POST">
                                        @csrf
                                        <div class="form-check form-switch">
                                            <input class="form-check-input module_change" type="checkbox" 
                                                   role="switch" name="is_enable" data-id="{{ $id }}"
                                                   @if ($module->isEnabled()) checked @endif
                                                   onclick="document.getElementById('form_{{ $id }}').submit()">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="col-12 empty-state">
                <i class="ti ti-box"></i>
                <h5>{{ __('No modules found') }}</h5>
                <p class="text-muted">{{ __('No modules match the current filter.') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.onTurboLoad(function () {
            // Search functionality
            document.getElementById('moduleSearch').addEventListener('keyup', function() {
                let query = this.value.toLowerCase();
                let modules = document.querySelectorAll('.module-card');

                modules.forEach(function(module) {
                    let moduleName = module.getAttribute('data-name');
                    if (moduleName.includes(query)) {
                        module.style.display = 'block';
                    } else {
                        module.style.display = 'none';
                    }
                });
            });
        });
    </script>
@endpush