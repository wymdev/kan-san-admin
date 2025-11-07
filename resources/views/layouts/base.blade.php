<!DOCTYPE html>
<html lang="en" @yield('html_attribute')>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.partials/title-meta')

    @include('layouts.partials/head-css')
</head>
<body>
    @yield('content')

    @include('layouts.partials/customizer')
</body>
</html>