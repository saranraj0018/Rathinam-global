@auth
    @include('partials.navbar-auth')
@else
    @include('partials.navbar-guest')
@endauth
