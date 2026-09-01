<footer class="footer card mb-0 rounded-0 justify-content-center align-items-center">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 text-center">
                {{ isset($settings->copyright) ? $settings->copyright : '© ' . date('Y') . ' Nexvia' }}
            </div>
        </div>
    </div>
</footer>