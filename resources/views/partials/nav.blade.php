<nav>
    <a href="{{ route('posts.index') }}">{{ config('app.name') }}</a>
    <ul>
        <li>
            <a href="{{ route('posts.index') }}">Posts</a>
        </li>
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
        @can('create_post')
        <li>
            <a href="{{ route('posts.create') }}">New post</a>
        </li>
        @endcan
        <li>
            <a href="{{ route('profile.edit') }}">Profile</a>
        </li>
        @can('access_moderator_page')
        <li>
            <a href="{{ route('moderator') }}">Moderator</a>
        </li>
        @endcan
        @can('access_admin_page')
        <li>
            <a href="{{ route('admin.index') }}">Admin</a>
        </li>
        @endcan
        <li>
            <form action="{{ route('logout') }}" method="post">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </li>
        @endauth
    </ul>
</nav>