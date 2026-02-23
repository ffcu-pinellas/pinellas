<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }} ">
    <meta name="theme-color" content="#00549b">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link
        rel="shortcut icon"
        href="{{ asset(setting('site_favicon','global')) }}"
        type="image/x-icon"
    />
    <link rel="icon" href="{{ asset(setting('site_favicon','global')) }}" type="image/x-icon"/>
    <link rel="stylesheet" href="{{ asset('assets/global/css/fontawesome.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/backend/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/backend/css/animate.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/global/css/nice-select.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/global/css/datatables.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/global/css/simple-notify.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/global/css/daterangepicker.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/backend/css/summernote-lite.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/global/css/custom.css?v=1.0') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/backend/css/styles.css?v=1.0') }}"/>
    @yield('style')
    @stack('style')

    <title> @yield('title') - {{ setting('site_title', 'global') }}</title>
</head>
