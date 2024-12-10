<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <header class="login-header">
        <p>Online Voting System</p>
    </header>

    <div class="main-container">
        <!-- Logo -->
        <img src="{{ asset('logo.png') }}" alt="University Logo" class="university-logo">

        <!-- University Name -->
        <h2 class="university-name">Davao Oriental State University</h2>

        <!-- Verification Section -->
        <div class="verification-section">
            <h2 class="verify-title">Verify Your Identity</h2>
            <p class="verify-instruction">Please enter the verification code sent to your email.</p>

            <form action="{{ route('verify.otp') }}" method="POST" class="login-form">
                @csrf
                <div class="otp-input-container">
                    <input type="text" name="otp" placeholder="Enter verification code" required>
                </div>
                <button type="submit">Verify</button>
            </form>

            @if ($errors->has('otp'))
                <div class="error-message">
                    {{ $errors->first('otp') }}
                </div>
            @endif
        </div>
    </div>
</body>
</html> 