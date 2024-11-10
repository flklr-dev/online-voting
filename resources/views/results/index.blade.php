@extends('layouts.app')

@section('title', 'Election Results')

@section('content')
<div class="vote-content">
    <h1>Election Results</h1>

    <div class="filter-container">
        <label for="electionSelect">Choose Election:</label>
        <select id="electionSelect" class="filter-dropdown">
            <option value="">-- Select an Election --</option>
            @foreach($elections as $election)
                <option value="{{ $election->election_id }}">{{ $election->election_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="results-container" id="resultsContainer">
        <p class="no-results">No results to display. Please select an election.</p>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/results.js') }}"></script>
@endsection
