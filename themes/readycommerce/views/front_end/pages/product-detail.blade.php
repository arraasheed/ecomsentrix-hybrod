@extends('front_end.layouts.app')

@section('page-title')
    {{ $product->name ?? __('Product Details') }}
@endsection

@section('content')
    @include('front_end.pages.rc-product-detail-inline')
@endsection

