<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Online Voting System</title>
</head>
<body>
@include('partials.header')
@include('partials.student-sidebar')

<div class="main-content">
    <h1 id="top-header">Ongoing Elections</h1>
    <div class="quick-stats">
        @forelse($ongoingElections as $election)
            <div class="stat-box">
                <div class="stat-item">
                    <h3>{{ $election->election_name }}</h3>
                    <p>Voting ends on {{ $election->end_date->format('F d, Y') }}</p>
                    <button class="vote-now">
                        <a href="{{ route('vote.interface', $election->election_id) }}">Vote now</a>
                    </button>
                </div>
            </div>
        @empty
            <p>No ongoing elections at the moment.</p>
        @endforelse
    </div>

    <div class="view-more-container">
        <button class="view-more">
            <a href="{{ route('ongoing-elections.index') }}">View More</a>
        </button>
    </div>

    <h1 id="bottom-header">Upcoming Elections</h1>
    <div class="upcoming-stats">
        @forelse($upcomingElections as $election)
            <div class="stat-box-upcoming">
                <div class="stat-item-upcoming">
                    <h3>{{ $election->election_name }}</h3>
                    <p>
                        <i class="fas fa-calendar-alt"></i>
                        Starts on {{ $election->start_date->format('F d, Y') }}
                    </p>
                    <p>
                        <i class="fas fa-clock"></i>
                        {{ $election->start_date->format('h:i A') }}
                    </p>
                </div>
            </div>
        @empty
            <div class="no-elections">
                <p>No upcoming elections at the moment.</p>
            </div>
        @endforelse
    </div>

    <h1 id="bottom-header">Your Voting History</h1>
    <div class="voting-history-table">
        <table>
            <thead>
                <tr>
                    <th>Election Name</th>
                    <th>Position Name</th>
                    <th>Candidate Name</th>
                    <th>Partylist</th>
                    <th>Date Voted</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($votingHistory as $vote)
                    <tr>
                        <td>{{ $vote->election_name }}</td>
                        <td>{{ $vote->position_name }}</td>
                        <td>{{ $vote->candidate_name }}</td>
                        <td>{{ $vote->partylist }}</td>
                        <td>{{ \Carbon\Carbon::parse($vote->vote_date)->format('F d, Y h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center;">No voting history available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="view-more-container">
            <button class="view-more">
                <a href="{{ route('voting-history.index') }}">View More</a>
            </button>
        </div>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-error">
        {{ session('error') }}
    </div>
@endif

@include('partials.footer')

<script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
