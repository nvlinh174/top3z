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
    <x-page-header title="Quản lý Chuyên mục">
    </x-page-header>
    <div class="page-body">
        <div class="container-xl">
            <div class="row">
                <div class="col-5">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Thêm chuyên mục</h3>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="space-y">
                                    <div>
                                        <label class="form-label"> Tên chuyên mục </label>
                                        <input type="text" name="name" class="form-control">
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-4 w-100">
                                            Lưu
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset("admin-theme/js/{$controller}/{$action}.js?v=" . time()) }}"></script>
@endpush
