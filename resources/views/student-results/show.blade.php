<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>{{ $election->election_name }} - Results</title>
</head>
<body>
    @include('partials.header')
    @include('partials.student-sidebar')

    <div class="main-content2">
        <div class="election-header">
            <h1>{{ $election->election_name }}</h1>
            <div class="election-meta">
                <span class="election-type">{{ $election->election_type }} Election</span>
                <span class="election-status {{ strtolower($election->election_status) }}">
                    {{ $election->election_status }}
                </span>
            </div>
            <div class="election-dates">
                {{ $election->start_date->format('M d, Y') }} - {{ $election->end_date->format('M d, Y') }}
            </div>
        </div>

        @if($positions->isEmpty())
            <div class="no-results">
                <i class="fas fa-info-circle"></i>
                <p>No results available for this election.</p>
            </div>
        @else
            @foreach($positions as $positionName => $candidates)
                <div class="position-section">
                    <div class="position-header">
                        <h2>{{ $positionName }}</h2>
                        <span class="candidate-count">{{ count($candidates) }} Candidates</span>
                    </div>
                    
                    <div class="candidates-grid">
                        @foreach($candidates->sortByDesc('total_votes') as $candidate)
                            <div class="candidate-card">
                                <div class="candidate-image">
                                    @if($candidate->picture)
                                        <img src="{{ asset('images/candidates/' . $candidate->picture) }}" alt="{{ $candidate->student_name }}">
                                    @else
                                        <img src="{{ asset('images/default-avatar.png') }}" alt="Default Avatar">
                                    @endif
                                </div>
                                <div class="candidate-info">
                                    <h3>{{ $candidate->student_name }}</h3>
                                    <div class="partylist-badge">
                                        <i class="fas fa-users"></i> {{ $candidate->partylist_name }}
                                    </div>
                                    @if($candidate->campaign_statement)
                                        <div class="statement">
                                            <i class="fas fa-quote-left"></i>
                                            <p>{{ $candidate->campaign_statement }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="vote-count">
                                    <span class="number">{{ $candidate->total_votes }}</span>
                                    <span class="label">votes</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    @include('partials.footer')
    <script src="{{ asset('js/script.js') }}"></script>
</body>
</html> 