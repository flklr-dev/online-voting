<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Ongoing Elections</title>
</head>
<body>
    @include('partials.header')
    @include('partials.student-sidebar')

    <div class="main-content2">
        <h1>Ongoing Elections</h1>

        <!-- Flash message for errors -->
        @if(session('error'))
            <div class="flash-message error-message">
                {{ session('error') }}
            </div>
        @endif

        <div class="election-grid">
            @forelse($ongoingElections as $election)
                <div class="stat-box">
                    <h3>{{ $election->election_name }}</h3>
                    <p>Voting ends on {{ $election->end_date->format('F d, Y') }}</p>
                    <button class="vote-now">
                        <a href="{{ route('vote.interface', $election->election_id) }}">Vote Now</a>
                    </button>
                </div>
            @empty
                <p>No ongoing elections at the moment.</p>
            @endforelse
        </div>
    </div>

    @include('partials.footer')
    <script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
