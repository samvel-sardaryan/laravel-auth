<nav>
    <a href="/">{{ config('app.name') }}</a>
    <ul>
        @guest
        <li>
            <a href="{{ route('login') }}">Login</a>
        </li>
        <li>
            <a href="{{ route('register') }}">Register</a>
        </li>
        @endguest
        @auth
        <li>
            <a href="{{ route('dashboard') }}">Dashboard</a>
        </li>
        <li>
            <form action="{{ route('logout') }}" method="post">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </li>
        @endauth
    </ul>
</nav>