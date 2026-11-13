@extends('layouts.app')

@section('page-title', __('Add-on Theme'))

@section('action-button')
<div class="text-end d-flex all-button-box justify-content-md-end justify-content-center">
    <a href="{{ route('addon.create') }}" class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i>
    </a>
</div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">{{ __('Add-on Theme') }}</li>
@endsection

@section('content')
    <div class="row justify-content-center px-0">
        <div class="col-12">
            <h4 class="mb-4">{{ __('Installed Themes') }}</h4>
        </div>
    </div>
    <div class="event-cards row px-0">
            @php
                $theme_array = [];
            @endphp
        @foreach ($addon_themes as $key => $value)
            @php
                $theme_array[] = $value->theme_id;
            @endphp
            <div class="col-lg-3 col-md-4 col-sm-6 card-wrapper">
                <div class="product-card ">
                    <div class="product-card-inner">
                        <div class="product-card-image img-wrapper">
                            <a href="{{ asset('themes/'.$value->theme_id.'/theme_img/img_1.png') }}" class="pdp-img" target="_blank" tabindex="0">
                                <img src="{{ asset('themes/'.$value->theme_id.'/theme_img/img_1.png') }}">
                            </a>

                            <div class="checkbox-custom">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" style="">
                                        @if ($value->status == '1')
                                            <a href="#!" class="dropdown-item module_change" data-id="{{ $value->theme_id }}">
                                                <span>{{__('Disable')}}</span>
                                            </a>
                                        @else
                                            <a href="#!" class="dropdown-item module_change" data-id="{{ $value->theme_id }}">
                                                <span>{{__('Enable')}}</span>
                                            </a>
                                        @endif

                                        <form action="{{ route('theme.enable') }}" method="POST" id="form_{{ $value->theme_id }}">
                                            @csrf
                                            <input type="hidden"
                                                name="name" value="{{ $value->theme_id }}">
                                        </form>

                                        {!! Form::open(['method' => 'DELETE', 'route' => ['addon.destroy', $value->theme_id], 'class' => 'd-inline']) !!}
                                            <button type="button" class="dropdown-item show_confirm" data-confirm="{{ __('Are You Sure?') }}"
                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}" data-text-yes="{{ __('Yes') }}" data-text-no="{{ __('No') }}" >
                                                <span class="text-danger">{{__('Remove')}}</span>
                                            </button>
                                        {!! Form::close() !!}

                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="product-content">
                            <div class="product-content-top">
                                <small class="text-muted">
                                    @if($value->status == '1')
                                        <span class="badges bg-success">{{__('Enable')}}</span>
                                    @else
                                        <span class="badges bg-danger">{{__('Disable')}}</span>
                                    @endif
                                </small>
                                <h4 class="text-capitalize">{{ $value->theme_id }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Promotional 'Buy More Themes' section removed per Milestone 5 clean-up -->

@endsection
@push('custom-script')
    <script>
        window.onTurboOnce(function () {
        $(document).on('click','.module_change',function(){
            var id = $(this).data('id');
            $('#form_'+id).submit();
        });
        });
    </script>
@endpush



