<!DOCTYPE html>
<html lang="en" @yield('html-attribute')>

<head>
    @include('adminlayouts.partials/title-meta')

    @include('adminlayouts.partials/head-css')
    <script src="https://code.iconify.design/3/3.2.1/iconify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.7/dist/iconify-icon.min.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <div class="app-wrapper">

        @include('adminlayouts.partials/sidebar')
        

        @include('adminlayouts.partials/topbar')

        <div class="page-content">

            <div class="container-fluid">

                @yield('content')

            </div>

            @include('adminlayouts.partials/footer')
        </div>

    </div>

    @include('adminlayouts.partials/vendor-scripts')

 @stack('scripts')
</body>

</html>