<!doctype html>
<html lang="en">

@include('admin.layouts.head')

<body>
    <div class="page">
        <!-- BEGIN NAVBAR  -->
        @include('admin.layouts.navbar')
        <!-- END NAVBAR  -->
        <div class="page-wrapper">
            @yield('content')
        </div>
    </div>

    @include('admin.layouts.script')
</body>

</html>
