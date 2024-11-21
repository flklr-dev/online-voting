<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">
    <title>Voting History</title>
</head>
<body>
    @include('partials.header')
    @include('partials.student-sidebar')

    <div class="main-content2">
        <h1>Voting History</h1>
        <div class="filter-container">
            <label for="electionFilter">Filter by Election:</label>
            <select id="electionFilter">
                <option value="">All Elections</option>
                @foreach($electionNames as $electionName)
                    <option value="{{ $electionName }}">{{ $electionName }}</option>
                @endforeach
            </select>
        </div>

        <!-- Top Controls: Show Entries and Search -->
        <div class="top-controls">
            <div class="dropdown-show-entries">
                <label for="entries">Show</label>
                <select id="entries" onchange="changeEntries(this.value)">
                    <option value="10" {{ request('limit') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('limit') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('limit') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('limit') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
            <div class="search-container">
                <label for="search">Search:</label>
                <input type="text" id="search" placeholder="Search voting history" onkeyup="searchVotingHistory(this.value)">
            </div>
        </div>

        <!-- Voting History Table -->
        <div class="voting-history-table">
            <table>
                <thead>
                    <tr>
                        <th>Election Name</th>
                        <th>Position Name</th>
                        <th>Candidate Name</th>
                        <th>Vote Date</th>
                    </tr>
                </thead>
                <tbody id="voting-history-body">
                    @if ($votingHistory->count() > 0)
                        @foreach ($votingHistory as $vote)
                            <tr>
                                <td>{{ $vote->election_name }}</td>
                                <td>{{ $vote->position_name }}</td>
                                <td>{{ $vote->candidate_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($vote->vote_date)->format('F d, Y h:i A') }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" style="text-align: center;">No voting history available.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->

        <div class="pagination-container">
            <div class="pagination-info">
                Showing {{ $votingHistory->firstItem() }} to {{ $votingHistory->lastItem() }} of {{ $votingHistory->total() }} entries
            </div>
            <div class="pagination">
                @if ($votingHistory->onFirstPage())
                    <span class="prev disabled">Prev</span>
                @else
                    <a href="{{ $votingHistory->previousPageUrl() }}" class="prev">Prev</a>
                @endif

                <span class="current-page">{{ $votingHistory->currentPage() }}</span>

                @if ($votingHistory->hasMorePages())
                    <a href="{{ $votingHistory->nextPageUrl() }}" class="next">Next</a>
                @else
                    <span class="next disabled">Next</span>
                @endif
            </div>
        </div>
    </div>

    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/voting-history.js') }}"></script>
    
    @include('partials.footer')
</body>
</html>
