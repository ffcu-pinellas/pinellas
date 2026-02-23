<script src="{{ asset('assets/global/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/global/js/jquery-migrate.js') }}"></script>
<script src="{{ asset('assets/backend/js/jquery-ui.js') }}"></script>

<script src="{{ asset('assets/backend/js/bootstrap.bundle.min.js') }}"></script>

<script src="{{ asset('assets/backend/js/scrollUp.min.js') }}"></script>
<script src="{{ asset('assets/global/js/waypoints.min.js') }}"></script>
<script src="{{asset('assets/global/js/jquery.counterup.min.js')}}"></script>
<script src="{{ asset('assets/backend/js/chart.js') }}"></script>
<script src="{{ asset('assets/global/js/lucide.min.js') }}"></script>
<script src="{{ asset('assets/global/js/jquery.nice-select.min.js') }}"></script>
<script src="{{ asset('assets/global/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/global/js/daterangepicker.min.js') }}"></script>

<script src="{{ asset('assets/global/js/simple-notify.min.js') }}"></script>
<script src="{{ asset('assets/backend/js/summernote-lite.min.js') }}"></script>

<script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/backend/js/main.js?v=1') }}"></script>
<script src="{{ asset('assets/global/js/pusher.min.js') }}"></script>
<script src="{{ asset('assets/global/js/custom.js?v=1.1') }}"></script>

@include('global.__notification_script',['for'=>'admin','userId' => ''])
@yield('script')
@stack('single-script')

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register("{{ asset('sw.js') }}")
                .then(reg => console.log('SW Registered'))
                .catch(err => console.log('SW Registration failed', err));
        });
    }
</script>


