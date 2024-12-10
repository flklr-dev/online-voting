<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- Header at the top -->
    <header class="login-header">
        <p>Online Voting System</p>
    </header>

    <div class="main-container">
        <!-- Logo -->
        <img src="{{ asset('logo.png') }}" alt="University Logo" class="university-logo">

        <!-- University Name -->
        <h2 class="university-name">Davao Oriental State University</h2>

        <!-- University Vision -->
        <p class="university-vision">
            A university of excellence, innovation, <br> and inclusion
        </p>
        <div class="login-form">
        <!-- Login Form -->
        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <label for="username">Username</label>
            <input type="text" name="username" id="username" placeholder="Enter your DOrSU email address" required>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Login</button>
        </form>

        <div class="divider">
            <div class="divider-line"></div>
            <span>or</span>
            <div class="divider-line"></div>
        </div>

        <a href="{{ route('google.login') }}" class="google-btn">
            <div class="google-icon-wrapper">
                <img class="google-icon" src="{{ asset('images/google-logo.svg') }}" alt="Google logo"/>
            </div>
            <p class="btn-text">Sign in with Google</p>
        </a>
        </div>

        @if ($errors->has('login'))
            <div class="error-message">
                {{ $errors->first('login') }}
            </div>
        @endif

    </div>

</body>
</html>
