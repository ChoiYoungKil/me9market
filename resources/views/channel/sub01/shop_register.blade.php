@extends('layouts.channel')
@section('page_type','sub')
@push('styles')<link rel="stylesheet" href="{{ asset('channel_assets/css/shop-configuration.css') }}?v={{ filemtime(public_path('channel_assets/css/shop-configuration.css')) }}">@endpush
@section('content')
@include('channel.sub01.channel_form')
@endsection
@push('scripts')<script src="{{ asset('channel_assets/js/shop-configuration.js') }}?v={{ filemtime(public_path('channel_assets/js/shop-configuration.js')) }}" defer></script>@endpush
