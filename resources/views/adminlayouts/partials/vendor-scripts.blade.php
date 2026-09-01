
<!-- Material Design Icons -->
<link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.0.96/css/materialdesignicons.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- jquery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<!-- custom scripts -->
<script src="{{ URL::asset('js/deleteModel.js') }}"></script>
<script src="{{ asset('js/generateslug.js') }}"></script>
<script src="https://code.iconify.design/3/3.2.1/iconify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.7/dist/iconify-icon.min.js"></script>
<!-- Flash Sucess Message Display -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert" id="flashMessage"
    style="position: fixed; bottom: 20px; right: 20px; width: auto; max-width: 300px; z-index: 1050;">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Flash Failed Message Display -->
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert" id="flashMessage"
    style="position: fixed; bottom: 20px; right: 20px; width: auto; max-width: 300px; z-index: 1050;">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@vite('resources/js/app.js')

@yield('scripts')