@extends('front_end.layouts.app')

@section('page-title')
    {{ __('Checkout') }}
@endsection

@section('content')
    @include('front_end.pages.rc-checkout-inline')
@endsection

