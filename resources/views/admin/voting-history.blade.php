@extends('layouts.app')

@section('title', 'Voting History')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<h1>Voting History</h1>

<div class="container">
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
            <input type="text" id="search" name="search" placeholder="Search..." value="{{ request('search') }}">
        </div>
    </div>

    <table class="table-common">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Election</th>
                <th>Position</th>
                <th>Candidate</th>
                <th>Partylist</th>
                <th>Date Voted</th>
            </tr>
        </thead>
        <tbody>
            @forelse($votingHistory as $vote)
                <tr>
                    <td>{{ $vote->student->fullname }}</td>
                    <td>{{ $vote->election->election_name }}</td>
                    <td>{{ $vote->position->position_name }}</td>
                    <td>{{ $vote->candidate->student->fullname }}</td>
                    <td>{{ $vote->candidate->partylist->name ?? 'Independent' }}</td>
                    <td>{{ $vote->vote_date->format('M d, Y h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No voting history found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-container">
        <div class="pagination-info">
            Showing {{ $votingHistory->firstItem() ?? 0 }} to {{ $votingHistory->lastItem() ?? 0 }} of {{ $votingHistory->total() }} entries
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

@endsection

@section('scripts')
<script src="{{ asset('js/script.js') }}"></script>
@endsection 