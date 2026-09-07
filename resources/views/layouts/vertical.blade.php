<!DOCTYPE html>
<html lang="en" @yield('html_attribute')>

<head>
    @include('layouts.partials/title-meta')

    @include('layouts.partials/head-css')
</head>

<body class="admin-theme">
    <div class="wrapper">

        @include('layouts.partials/sidenav')

        <div class="page-content">

            @include('layouts.partials/topbar')

            <main>

                <x-ui.flash-messages />
                @yield('content')

            </main>

            @include('layouts.partials/footer')
            
        </div>

    </div>

    @include('layouts.partials/customizer')
    
    @yield('script')
</body>

</html>
