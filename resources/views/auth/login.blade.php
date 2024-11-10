<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

    <!-- Header at the top -->
    <header class="login-header">
        <h1>University Online Voting System</h1>
    </header>

    <div class="main-container">
        <!-- Logo -->
        <img src="{{ asset('logo.png') }}" alt="University Logo" class="university-logo">

        <!-- University Name -->
        <h2 class="university-name">Davao Oriental State University</h2>

        <!-- University Vision -->
        <p class="university-vision">
            A university of excellence, innovation,<br>and inclusion
        </p>
        <div class="login-form">
        <!-- Login Form -->
        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <label for="username">Username</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Login</button>
        </form>
        </div>

        @if ($errors->any())
            <div class="error-message">
                {{ $errors->first('login') }}
            </div>
        @endif
    </div>

</body>
</html>
