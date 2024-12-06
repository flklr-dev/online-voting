<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Election Results</title>
</head>
<body>
    @include('partials.header')
    @include('partials.student-sidebar')

    <div class="main-content2">
        <div class="election-header">
            <h1>Election Results</h1>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        @if($elections->isEmpty())
            <div class="no-results">
                <i class="fas fa-info-circle"></i>
                <p>No election results available.</p>
            </div>
        @else
            <div class="results-grid">
                @foreach($elections as $election)
                    <div class="result-card">
                        <div class="result-card-header">
                            <div class="election-info">
                                <h3>{{ $election->election_name }}</h3>
                                <div class="election-meta">
                                    <span class="election-badge">
                                        <i class="fas fa-tag"></i> {{ $election->election_type }} Election
                                    </span>
                                    @if($election->election_type !== 'General')
                                        <span class="election-badge">
                                            <i class="fas fa-users"></i> {{ $election->restriction }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="status-badge {{ strtolower($election->election_status) }}">
                                <i class="fas fa-circle"></i>
                                {{ $election->election_status }}
                            </div>
                        </div>
                        
                        <div class="result-card-content">
                            <div class="date-info">
                                <i class="fas fa-calendar"></i>
                                <span>{{ $election->start_date->format('M d, Y') }} - {{ $election->end_date->format('M d, Y') }}</span>
                            </div>
                            
                            <a href="{{ route('student-results.show', $election->election_id) }}" class="view-results-button">
                                <i class="fas fa-chart-bar"></i> View Results
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @include('partials.footer')
    <script src="{{ asset('js/script.js') }}"></script>
</body>
</html>