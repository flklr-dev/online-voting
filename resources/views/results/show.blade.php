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
                <table class="position-table">
                    <thead>
                        <tr>
                            <th colspan="3">{{ $positionName }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($candidates as $candidate)
                            <tr>
                                <td class="candidate-image">
                                    <img src="{{ asset('images/candidates/' . $candidate->picture) }}" width="100">
                                </td>
                                <td class="candidate-details">
                                    <h3>{{ $candidate->student_name }}</h3>
                                    <p><i>{{ $candidate->campaign_statement }}</i></p>
                                    <p><strong>Partylist:</strong> {{ $candidate->partylist }}</p>
                                </td>
                                <td class="candidate-votes">
                                    <p class="votes-count">{{ $candidate->total_votes }}</p>
                                    <p class="votes-label">Total Votes</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endif
    </div>

    @include('partials.footer')
    <script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
