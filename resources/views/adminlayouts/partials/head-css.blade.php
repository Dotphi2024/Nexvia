<!-- Google Font Family link -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('build/assets/style-FSNSlcPp.css') }}">
<link rel="stylesheet" href="{{ asset('build/assets/icons-BTBTsgZJ.css') }}">

@yield('css')

@vite([ 'resources/scss/icons.scss', 'resources/scss/style.scss'])

<style>
    :root {
        --bs-primary: #7c3aed !important;
        --bs-primary-rgb: 124, 58, 237 !important;
    }
    .btn-primary {
        background-color: #7c3aed !important;
        border-color: #7c3aed !important;
        color: #ffffff !important;
    }
    .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
        background-color: #6d28d9 !important;
        border-color: #6d28d9 !important;
        color: #ffffff !important;
    }
    .btn-outline-primary {
        color: #7c3aed !important;
        border-color: #7c3aed !important;
    }
    .btn-outline-primary:hover {
        background-color: #7c3aed !important;
        border-color: #7c3aed !important;
        color: #ffffff !important;
    }
    .btn-soft-primary {
        background-color: #f3e8ff !important;
        color: #6b21a8 !important;
        border: none !important;
    }
    .btn-soft-primary:hover {
        background-color: #7c3aed !important;
        color: #ffffff !important;
    }
    .badge.bg-primary-subtle, .badge.bg-primary {
        background-color: #f3e8ff !important;
        color: #7c3aed !important;
    }
    .text-primary {
        color: #7c3aed !important;
    }
    .page-item.active .page-link {
        background-color: #7c3aed !important;
        border-color: #7c3aed !important;
    }
</style>

{{-- Custom design system --}}
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">

@vite([ 'resources/js/config.js'])