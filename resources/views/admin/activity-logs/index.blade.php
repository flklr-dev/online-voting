// resources/views/admin/activity-logs/index.blade.php

@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<h1>Activity Logs</h1>

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
            <input type="text" id="search" placeholder="Search logs" value="{{ request('search') }}" onkeyup="searchLogs(this.value)">
            <input type="date" id="date-filter" value="{{ request('date') }}" onchange="filterByDate(this.value)">
        </div>
    </div>

    <table class="table-common">
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Admin</th>
                <th>Module</th>
                <th>Action</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody id="logs-body">
            @if ($logs->count() > 0)
                @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $log->admin ? $log->admin->username : 'System' }}</td>
                        <td>{{ $log->module }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->description }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" style="text-align: center;">No activity logs found.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="pagination-container">
        <div class="pagination-info">
            Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries
        </div>
        <div class="pagination">
            @if ($logs->onFirstPage())
                <span class="prev disabled">Prev</span>
            @else
                <a href="{{ $logs->previousPageUrl() }}" class="prev">Prev</a>
            @endif

            <span class="current-page">{{ $logs->currentPage() }}</span>

            @if ($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}" class="next">Next</a>
            @else
                <span class="next disabled">Next</span>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function changeEntries(value) {
    let currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('limit', value);
    window.location.href = currentUrl.toString();
}

function searchLogs(value) {
    let currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('search', value);
    
    // Debounce the search to prevent too many requests
    clearTimeout(window.searchTimeout);
    window.searchTimeout = setTimeout(() => {
        window.location.href = currentUrl.toString();
    }, 500);
}

function filterByDate(value) {
    let currentUrl = new URL(window.location.href);
    if (value) {
        currentUrl.searchParams.set('date', value);
    } else {
        currentUrl.searchParams.delete('date');
    }
    window.location.href = currentUrl.toString();
}
</script>
@endsection
