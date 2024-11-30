<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>{{ $election->election_name }} - Results</title>
</head>
<body>
    @include('partials.header')
    @include('partials.sidebar')

    <div class="vote-content">
        <h1>{{ $election->election_name }} - Results</h1>

        <a href="{{ route('election.download', ['electionId' => $election->election_id]) }}" class="export-button">
            Download Results as CSV
        </a>

        <!-- Display error message if there's any -->
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if($positions->isEmpty())
            <div class="no-results">
                <p>No results available for this election.</p>
            </div>
        @else
            @foreach($positions as $positionName => $candidates)
                <div class="position-section">
                    <h2>{{ $positionName }}</h2>
                    <div class="candidates-grid">
                        @foreach($candidates as $candidate)
                            <div class="candidate-card">
                                @if($candidate->picture)
                                    <img src="{{ asset('images/candidates/' . $candidate->picture) }}" alt="{{ $candidate->student_name }}">
                                @else
                                    <img src="{{ asset('images/default-avatar.png') }}" alt="Default Avatar">
                                @endif
                                <h3>{{ $candidate->student_name }}</h3>
                                <p class="partylist">{{ $candidate->partylist_name }}</p>
                                <p class="votes">Total Votes: {{ $candidate->total_votes }}</p>
                                @if($candidate->campaign_statement)
                                    <p class="statement">{{ $candidate->campaign_statement }}</p>
                                @endif
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
