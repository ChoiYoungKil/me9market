@extends('layouts.admin', ['legacyAdmin' => true])
@push('styles')
    <link rel="stylesheet" href="{{ asset('common/admin-compat.css').'?v='.filemtime(public_path('common/admin-compat.css')) }}">
    <link rel="stylesheet" href="{{ asset('admin/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/vendors/datatables.net-bs4/dataTables.bootstrap4.css') }}">
@endpush
@push('scripts')
    <script src="{{ asset('common/bootstrap-legacy.js').'?v='.filemtime(public_path('common/bootstrap-legacy.js')) }}"></script>
    <script src="{{ asset('admin/vendors/datatables.net/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('admin/vendors/datatables.net-bs4/dataTables.bootstrap4.js') }}"></script>
    <script src="{{ asset('admin/js/custom.js') }}"></script>
@endpush
