<!DOCTYPE html>
<html lang="en">
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

<body class="hold-transition sidebar-mini layout-fixed">
    @guest 
        @include('layouts.guest')    
    @else
        <div class="wrapper">
            <!-- Header -->
            @include('layouts.header')

            <!-- Sidebar -->
            @include('layouts.sidebar')
                <!-- Content Wrapper. Contains page content -->
                <div class="content-wrapper">
                    <section class="content">
                        @yield('content')
                    </section>
                </div>
            @include('layouts.footer')
        </div>
    @endguest 

    <!-- Scripts -->
    @livewireScripts    
    <script type="text/javascript">
        window.livewire.on('closeModal', () => {
            $('#exampleModal').modal('hide');
        });
    </script>
</body>
</html>
