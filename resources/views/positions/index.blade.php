@extends('layouts.app')

@section('title', 'Manage Positions')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

    <h1>Position List</h1>

    <div class="container">
        <div class="top-controls">
            <button id="openAddPositionModal" class="btn-add">Add Position</button>
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
                <input type="text" id="search" placeholder="Search positions" onkeyup="searchPositions(this.value)">
            </div>
        </div>

        <table class="table-common position-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Position Name</th>
                    <th>Maximum Votes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="position-body">
                @if ($positions->count() > 0)
                    @foreach ($positions as $position)
                        <tr>
                            <td>{{ $position->position_id }}</td>
                            <td>{{ $position->position_name }}</td>
                            <td>{{ $position->max_vote }}</td>
                            <td class="actions">
                                <button class="edit-btn" data-position="{{ json_encode($position) }}">Edit</button>
                                <button class="delete-btn" data-id="{{ $position->position_id }}">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" style="text-align: center;">No positions found</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="pagination">
            @if ($positions->onFirstPage())
                <span class="prev disabled">Prev</span>
            @else
                <a href="{{ $positions->previousPageUrl() }}" class="prev">Prev</a>
            @endif

            <span class="current-page">{{ $positions->currentPage() }}</span>

            @if ($positions->hasMorePages())
                <a href="{{ $positions->nextPageUrl() }}" class="next">Next</a>
            @else
                <span class="next disabled">Next</span>
            @endif
        </div>
    </div>

    <!-- Add Position Modal -->
    <div id="addPositionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" id="closeAddPositionModal">&times;</span>
                <h2>Add Position</h2>
            </div>
            <div class="modal-body">
                <form id="addPositionForm" method="POST" action="{{ route('positions.store') }}">
                    @csrf
                    <label for="position_name">Position Name:</label>
                    <input type="text" id="position_name" name="position_name" required>

                    <label for="max_vote">Maximum Vote:</label>
                    <input type="number" id="max_vote" name="max_vote" min="1" required> <!-- min set to 1 -->

                    <button type="submit" class="btn btn-primary">Add Position</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Position Modal -->
    <div id="editPositionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" id="closeEditPositionModal">&times;</span>
                <h2>Edit Position</h2>
            </div>
            <div class="modal-body">
                <form id="editPositionForm" method="POST">
                    @csrf
                    @method('PUT')

                    <label for="edit_position_name">Position Name:</label>
                    <input type="text" id="edit_position_name" name="position_name" required>

                    <label for="edit_max_vote">Maximum Vote:</label>
                    <input type="number" id="edit_max_vote" name="max_vote" min="1" required> <!-- min set to 1 -->

                    <button type="submit" class="btn btn-primary">Update Position</button>
                </form>
            </div>
        </div>
    </div>


@endsection

@section('scripts')
<script src="{{ asset('js/position.js') }}"></script>
@endsection