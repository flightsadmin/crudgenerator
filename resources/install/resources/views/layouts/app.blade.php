<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title') @yield('title') | @endif {{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
     @livewireStyles
</head>
<body class="hold-transition sidebar-mini">
    @guest 
    @include('layouts.guest')    
    @else

    <div class="wrapper" id="app">
        <!-- Header -->
    @include('layouts.header')
        <!-- Sidebar -->
    @include('layouts.sidebar') 
        <div class="content-wrapper">
            @yield('content')
        </div>
        <!-- Footer -->
    <div>
        @include('layouts.footer')
    </div>
    <!-- ./wrapper -->
    @endguest 

    @livewireScripts
    
    <script type="text/javascript">
        window.livewire.on('closeModal', () => {
            $('#exampleModal').modal('hide');
        });
    </script>
</body>
</html>