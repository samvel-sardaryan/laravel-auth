<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> @yield('title', config('app.name')) </title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>

<body>
    <header class="site-header">
        @include('partials.nav')
    </header>
    <main class="container">
        <x-flash />
        @yield('content')
    </main>
    <footer class="site-footer">
        <div class="container">
            <p>&copy; {{ config('app.name') }}</p>
        </div>
    </footer>
</body>

</html>