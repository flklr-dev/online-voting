@extends('layouts.app')

@section('title', 'Manage Elections')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

    <h1>Election List</h1>

    <div class="container">
        <div class="top-controls">
            <button id="openAddElectionModal" class="btn-add-election">Add Election</button> <!-- Button to open modal -->
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
                <input type="text" id="search" placeholder="Search elections" onkeyup="searchElections(this.value)">
            </div>
        </div>

        <table class="table-common election-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Election Name</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Restriction</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="election-body">
                @if ($elections->count() > 0)
                    @foreach ($elections as $election) <!-- Ensure you are looping over $elections -->
                        <tr>
                            <td>{{ $election->election_id }}</td>
                            <td>{{ $election->election_name }}</td>
                            <td>{{ $election->description }}</td>
                            <td>{{ $election->election_type }}</td>
                            <td>{{ $election->restriction }}</td>
                            <td>{{ $election->start_date }}</td>
                            <td>{{ $election->end_date }}</td>
                            <td>{{ $election->election_status }}</td>
                            <td class="actions">
                                <button class="edit-btn" data-election="{{ json_encode($election) }}">Edit</button>
                                <button class="delete-btn" data-election-id="{{ $election->election_id }}">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" style="text-align: center;">No elections found.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="pagination">
            @if ($elections->onFirstPage())
                <span class="prev disabled">Prev</span>
            @else
                <a href="{{ $elections->previousPageUrl() . '&limit=' . request('limit') }}" class="prev">Prev</a>
            @endif

            <span class="current-page">{{ $elections->currentPage() }}</span>

            @if ($elections->hasMorePages())
                <a href="{{ $elections->nextPageUrl() . '&limit=' . request('limit') }}" class="next">Next</a>
            @else
                <span class="next disabled">Next</span>
            @endif
        </div>
    </div>

    <!-- Add Election Modal -->
    <div id="addElectionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" id="closeAddElectionModal">&times;</span>
                <h2>Add Election</h2>
            </div>
            <div class="modal-body">
                <form id="addElectionForm" method="POST" action="{{ route('elections.store') }}">
                    @csrf

                    <label for="election_name">Election Name:</label>
                    <input type="text" id="election_name" name="election_name" required>

                    <label for="election_description">Election Description:</label>
                    <textarea id="election_description" name="description" required></textarea>

                    <label for="election_type">Election Type:</label>
                    <select id="election_type" name="election_type" required>
                        <option value="" disabled selected>Select Type</option>
                        @foreach ($electionTypes as $election_type)
                            <option value="{{ $election_type }}">{{$election_type}}</option>
                        @endforeach
                    </select>
        
                    <label for="restriction">Restriction:</label>
                    <select id="restriction" name="restriction">
                        <option value="" disabled selected>Select Restriction</option>
                    </select>
                    
                    <label for="start_date">Start Date:</label>
                    <input type="text" id="start_date" name="start_date" class="flatpickr-datetime" required>

                    <label for="end_date">End Date:</label>
                    <input type="text" id="end_date" name="end_date" class="flatpickr-datetime" required>

                    <!-- Election Status Dropdown -->
                    <label for="election_status">Election Status:</label>
                    <select id="election_status" name="election_status" required>
                        <option value="" disabled selected>Select Status</option>
                        @foreach ($electionStatuses as $election_status)
                            <option value="{{ $election_status }}">{{$election_status}}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary">Add Election</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Election Modal -->
    <div id="editElectionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" id="closeEditElectionModal">&times;</span>
                <h2>Edit Election</h2>
            </div>
            <div class="modal-body">
                <form id="editElectionForm" method="POST" }}">
                    @csrf
                    @method('PUT')

                    <label for="edit_election_name">Election Name:</label>
                    <input type="text" id="edit_election_name" name="election_name" required>

                    <label for="edit_description">Election Description:</label>
                    <textarea id="edit_description" name="description" required></textarea>

                    <label for="edit_election_type">Election Type:</label>
                    <select id="edit_election_type" name="election_type" required>
                        <option value="" disabled selected>Select Type</option>
                        @foreach ($electionTypes as $election_type)
                            <option value="{{ $election_type }}">{{$election_type}}</option>
                        @endforeach
                    </select>
        
                    <label for="edit_restriction">Restriction:</label>
                    <select id="edit_restriction" name="restriction">
                        <option value="" disabled selected>Select Restriction</option>
                    </select>
                    
                    <label for="edit_start_date">Start Date:</label>
                    <input type="text" id="edit_start_date" name="start_date" class="flatpickr-datetime" required>

                    <label for="edit_end_date">End Date:</label>
                    <input type="text" id="edit_end_date" name="end_date" class="flatpickr-datetime" required>

                    <!-- Election Status Dropdown -->
                    <label for="edit_election_status">Election Status:</label>
                    <select id="edit_election_status" name="election_status" required>
                        <option value="" disabled selected>Select Status</option>
                        @foreach ($electionStatuses as $election_status)
                            <option value="{{ $election_status }}">{{$election_status}}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-primary">Updated Election</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('js/election.js') }}"></script>
@endsection