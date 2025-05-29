<!DOCTYPE html>
<html>
    <head>
    <title>La3 Casdoor</title>
            <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            text-align: left;
        }
        .error {
            color: red;
            margin: 10px 0;
        }
            </style>
    </head>
<body>
    <h1>La3 Casdoor</h1>

    @if(session('error'))
        <div class="error">{{ session('error') }}</div>
    @endif

    @if(!request()->cookie('access_token_1'))
        <a href="{{ route('login') }}" class="button">Login with Casdoor</a>
    @else
        <a href="{{ route('logout') }}">Logout</a>
        <a href="{{ route('user.info') }}">User Info</a>
    @endif
    </body>
</html>
