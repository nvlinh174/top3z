@php
    [
        'module' => $module,
        'controller' => $controller,
        'action' => $action,
        'routeBase' => $routeBase,
    ] = $params;
@endphp

@extends('admin.layouts.main')

@section('content')
    <x-page-header title="Chuyên mục">
    </x-page-header>
    <div class="page-body">
        <div class="container-xl">

        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset("admin-theme/js/{$controller}/{$action}.js?v=" . time()) }}"></script>
@endpush
