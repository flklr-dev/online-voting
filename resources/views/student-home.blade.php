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
        <div class="stat-box-upcoming">
            <div class="stat-item-upcoming">
                <h3>Student Council Election</h3>
                <p>Starts on [Date]</p>
            </div>
        </div>
        <div class="stat-box-upcoming">
            <div class="stat-item-upcoming">
                <h3>Student Council Election</h3>
                <p>Starts on [Date]</p>
            </div>
        </div>
        <div class="stat-box-upcoming">
            <div class="stat-item-upcoming">
                <h3>Student Council Election</h3>
                <p>Starts on [Date]</p>
            </div>
        </div>
    </div>

    <h1 id="bottom-header">Your Voting History</h1>
    <div class="voting-history-table">
        <table>
            <thead>
                <tr>
                    <th>Election ID</th>
                    <th>Election Name</th>
                    <th>Date Voted</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>001</td>
                    <td>Student Body Election</td>
                    <td>2024-10-05</td>
                </tr>
                <tr>
                    <td>002</td>
                    <td>Program Council Election</td>
                    <td>2024-09-30</td>
                </tr>
            </tbody>
        </table>
        <div class="view-more-container">
            <button class="view-more">
                <a href="#">View More</a>
            </button>
        </div>
    </div>
</div>

@include('partials.footer')

<script src="{{ asset('js/script.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>