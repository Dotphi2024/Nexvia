<!DOCTYPE html>
<html @yield('html-attribute')>

<head>
    @include('adminlayouts.partials/title-meta')

    @include('adminlayouts.partials/head-css')
    <script src="https://code.iconify.design/3/3.2.1/iconify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.7/dist/iconify-icon.min.js"></script>
</head>

<body @yield('body-attribuet')>

    @yield('content')

    @include('adminlayouts.partials/vendor-scripts')

</body>

</html>