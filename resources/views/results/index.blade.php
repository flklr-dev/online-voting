<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Election Results</title>
</head>
<body>  
    @include('partials.header')
    @include('partials.sidebar')

    <div class="main-content2">
        <h1>Election Results</h1>
        <div class="filter-container">
            <label for="academicYearFilter">Filter by Academic Year:</label>
            <select id="academicYearFilter">
                <option value="">All</option>
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <div class="election-grid" id="electionGrid">
            @forelse($elections as $election)
                <div class="stat-box">
                    <h3>{{ $election->election_name }}</h3>
                    <p>Ended on {{ $election->end_date->format('F d, Y') }}</p>
                    <button class="view-results">
                        <a href="{{ route('results.show', $election->election_id) }}">View Results</a>
                    </button>
                </div>
            @empty
                <p>No election results available.</p>
            @endforelse
        </div>
    </div>

    @include('partials.footer')
    <script src="{{ asset('js/results.js') }}"></script>
    <script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
