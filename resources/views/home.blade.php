<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Online Voting System</title>
</head>
<body>  
    @include('partials.header')
    @include('partials.sidebar')

    <main class="main-content">
        <h1>Dashboard</h1>
        <div class="quick-stats">
            <div class="stat-box">
                <div class="stat-item">
                    <h3>Total Elections <i class="fas fa-calendar-alt"></i></h3>
                    <p>{{ $totalElections }}</p>
                    <button class="view-more">
                        <a href="{{ route('elections.index') }}">View More</a>
                    </button>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-item">
                    <h3>Ongoing Elections <i class="fas fa-vote-yea"></i></h3>
                    <p>{{ $ongoingElections }}</p>
                    <button class="view-more">
                        <a href="{{ route('elections.index') }}">View More</a>
                    </button>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-item">
                    <h3>Total Voters <i class="fas fa-users"></i></h3>
                    <p>{{ $totalVoters }}</p>
                    <button class="view-more">
                        <a href="{{ route('students.index') }}">View More</a>
                    </button>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-item">
                    <h3>Total Votes Cast <i class="fas fa-envelope-open-text"></i></h3>
                    <p>{{ $totalVotesCast }}</p>
                    <button class="view-more">View More</button>
                </div>
            </div>
        </div>

        <!-- Elections Overview Section -->
        <div class="elections-overview">
            <h2>Elections Overview</h2>
            <table>
                <thead>
                    <tr>
                        <th>Election Name</th>
                        <th>Election Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @if($recentElections->count() > 0)
                        @foreach($recentElections as $election)
                            <tr>
                                <td>{{ $election->election_name }}</td>
                                <td>{{ $election->election_type }}</td>
                                <td>{{ $election->start_date }}</td>
                                <td>{{ $election->end_date }}</td>
                                <td>{{ $election->election_status }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" style="text-align: center;">No elections found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <!-- Centered View More Button -->
            <div class="election-overview">
                <button class="view-more">
                    <a href="{{ route('elections.index') }}">View More</a>
                </button>
            </div>
        </div>

        <!-- Voting Results Overview Section -->
        <div class="voting-results-overview">
            <h2>Voting Results Overview</h2>
            <div class="filter-form">
                <label for="electionSelect">Select Election:</label>
                <select id="electionSelect" onchange="filterResults()">
                    <option value="">--Select an Election--</option>
                    <option value="election1">Election 1</option>
                    <option value="election2">Election 2</option>
                    <option value="election3">Election 3</option>
                </select>

                <label for="positionSelect">Select Position:</label>
                <select id="positionSelect" onchange="filterResults()">
                    <option value="">--Select a Position--</option>
                    <option value="position1">Position 1</option>
                    <option value="position2">Position 2</option>
                    <option value="position3">Position 3</option>
                </select>

                <div class="download-options">
                    <button onclick="downloadCSV()">Download CSV</button>
                    <button onclick="downloadPDF()">Download PDF</button>
                </div>
            </div>

            <div class="results-container">
                <div class="chart-container">
                    <canvas id="resultsChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    @include('partials.footer')

    <script src="{{ asset('js/script.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/results.js') }}"></script>
</body>
</html>
