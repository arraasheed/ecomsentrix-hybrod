{{-- Backed up original Single Module Card snippet from WorkDo --}}
{{-- Source: resources/views/module/index.blade.php before custom cleanup --}}

<div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 d-flex mb-4 module-card" data-name="{{ strtolower($module->name) }}">
    <div class="card w-100 shadow-sm border rounded-3 {{ $module->isEnabled() ? 'border-success' : 'border-light' }}">
        <div class="card-body p-3 d-flex flex-column justify-between">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="theme-avtar p-2 rounded bg-light border" style="width: 56px; height: 56px;">
                    <img src="{{ $module->image ? asset($module->image) : asset('assets/images/user-avatar.png') }}"
                         alt="{{ $module->name }}" class="w-100 h-100 object-fit-contain">
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <h5 class="mb-1 text-truncate font-weight-bold" title="{{ $module->alias ?? $module->name }}">
                        {{ $module->alias ?? $module->name }}
                    </h5>
                    <span class="badge {{ $module->isEnabled() ? 'bg-success' : 'bg-danger' }}">
                        {{ $module->isEnabled() ? __('Enabled') : __('Disabled') }}
                    </span>
                </div>
            </div>
            
            <p class="text-muted small mb-3 text-truncate-2" style="min-height: 38px;">
                {{ $module->description ?? __('Integrated system extension module.') }}
            </p>

            <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto">
                <span class="small text-muted">v{{ $module->version ?? '1.0.0' }}</span>
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
