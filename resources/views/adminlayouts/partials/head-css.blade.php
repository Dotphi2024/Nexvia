<!-- Google Font Family link -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('build/assets/style-FSNSlcPp.css') }}">
<link rel="stylesheet" href="{{ asset('build/assets/icons-BTBTsgZJ.css') }}">

@yield('css')

@vite([ 'resources/scss/icons.scss', 'resources/scss/style.scss'])

{{-- Custom design system — loaded last so it takes highest priority --}}
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">

@vite([ 'resources/js/config.js'])