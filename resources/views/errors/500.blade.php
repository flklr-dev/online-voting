<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">
    <title>Error Occurred</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <style>
        .error-container {
            text-align: center;
            padding: 40px 20px;
        }
        .error-message {
            color: #666;
            margin: 20px 0;
        }
        .back-button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #003399;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header class="login-header">
        <p>Online Voting System</p>
    </header>

    <div class="main-container">
        <img src="{{ asset('logo.png') }}" alt="University Logo" class="university-logo">
        
        <div class="error-container">
            <h2>Oops! Something went wrong</h2>
            <div class="error-message">
                <p>{{ $message ?? 'An unexpected error occurred. Please try again later.' }}</p>
            </div>
            @if(session('user_role') === 'admin')
                <a href="{{ route('home') }}" class="back-button">Back to Dashboard</a>
            @elseif(session('user_role') === 'student')
                <a href="{{ route('student-home') }}" class="back-button">Back to Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="back-button">Back to Login</a>
            @endif
        </div>
    </div>
</body>
</html> 