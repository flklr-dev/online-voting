<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">
    <title>Unauthorized Access</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <style>
        .error-container {
            text-align: center;
            padding: 40px 20px;
        }

        .error-code {
            font-size: 72px;
            color: #003399;
            margin-bottom: 20px;
        }

        .error-message {
            color: #666;
            font-size: 18px;
            margin-bottom: 30px;
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
            <div class="error-code">403</div>
            <div class="error-message">
                <h2>Access Denied</h2>
                <p>Sorry, you don't have permission to access this page.</p>
            </div>
            <a href="{{ route('student-home') }}" class="back-button">
                Back to Home
            </a>
        </div>
    </div>
</body>
</html> 