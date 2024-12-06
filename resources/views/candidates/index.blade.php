@extends('layouts.app')

@section('title', 'Manage Candidates')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<h1>Candidate List</h1>

<div class="container">
    <div class="top-controls">
        <button id="openAddCandidateModal" class="btn-add">Add Candidate</button>
        <button id="openAddPartylistModal" class="btn-add">Manage Partylist</button>
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
            <input type="text" id="search" placeholder="Search Candidate" onkeyup="searchCandidate(this.value)">
        </div>
    </div>

    <table class="table-common candidate-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Picture</th>
                <th>Candidate Name</th>
                <th>Student ID</th>
                <th>Election</th>
                <th>Position</th>
                <th>Campaign Statement</th>
                <th>Partylist</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="candidate-body">
            @if ($candidates->count() > 0)
                @foreach ($candidates as $candidate)
                    <tr>
                        <td>{{ $loop->iteration + (($candidates->currentPage() - 1) * $candidates->perPage()) }}</td>
                        <td><img src="{{ asset('images/candidates/' . $candidate->picture) }}" alt="Candidate Image" width="50"></td>
                        <td>{{ $candidate->student_name }}</td>
                        <td>{{ $candidate->student_id }}</td>
                        <!-- Access the election and position names through the relationships -->
                        <td>{{ $candidate->election ? $candidate->election->election_name : 'N/A' }}</td>
                        <td>{{ $candidate->position ? $candidate->position->position_name : 'N/A' }}</td>
                        <td>
                            <button class="view-campaign-btn" 
                                    data-campaign="{{ $candidate->campaign_statement }}">
                                View
                            </button>
                        </td>
                        <td>{{ $candidate->partylist ? $candidate->partylist->name : 'N/A' }}</td>
                        <td class="actions">
                            <button 
                                class="edit-btn" 
                                data-candidate="{{ json_encode([
                                    'candidate_id' => $candidate->candidate_id,
                                    'student_id' => $candidate->student_id,
                                    'student_name' => $candidate->student_name,
                                    'election_id' => $candidate->election_id,
                                    'position_id' => $candidate->position_id,
                                    'campaign_statement' => $candidate->campaign_statement,
                                    'partylist_id' => $candidate->partylist_id,
                                    'picture' => $candidate->picture
                                ]) }}">
                                Edit
                            </button>
                            <button class="delete-btn" data-id="{{ $candidate->candidate_id }}">Delete</button>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="9" style="text-align: center;">No candidates found.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="pagination-container">
        <div class="pagination-info">
            Showing {{ $candidates->firstItem() }} to {{ $candidates->lastItem() }} of {{ $candidates->total() }} entries
        </div>
        <div class="pagination">
            @if ($candidates->onFirstPage())
                <span class="prev disabled">Prev</span>
            @else
                <a href="{{ $candidates->previousPageUrl() }}" class="prev">Prev</a>
            @endif

            <span class="current-page">{{ $candidates->currentPage() }}</span>

            @if ($candidates->hasMorePages())
                <a href="{{ $candidates->nextPageUrl() }}" class="next">Next</a>
            @else
                <span class="next disabled">Next</span>
            @endif
        </div>
    </div>

</div>

<!-- Add Candidate Modal -->
<div id="addCandidateModal" class="modal">
    <div class="modal-content">
            <div class="modal-header">
                <span class="close" id="closeAddCandidateModal">&times;</span>
                <h2>Add Candidate</h2>
            </div>
        <div class="modal-body">
            <form id="addForm" method="POST" action="{{ route('candidates.store') }}" enctype="multipart/form-data">
                @csrf
                <label for="election_id">Election:</label>
                <select name="election_id" id="election_id" required>
                    <option value="" disabled selected>Select Election</option>
                    @foreach($elections as $election)
                        <option value="{{ $election->election_id }}">
                            {{ $election->election_name }} ({{ $election->election_status }})
                        </option>
                    @endforeach
                </select>

                <label for="candidate_name">Candidate Name:</label>
                <select id="candidate_name" name="student_name" class="select2-candidate" style="width: 100%; height: 45px;" required disabled>
                    <option value="" disabled selected>First select an election</option>
                </select>

                <label for="student_id">Student ID:</label>
                <input type="text" id="student_id" name="student_id" readonly>

                <label for="position_id">Position:</label>
                <select name="position_id" required>
                    <option value="" disabled selected>Select Position</option>
                    @foreach($positions as $position)
                        <option value="{{ $position->position_id }}">{{ $position->position_name }}</option>
                    @endforeach
                </select>

                <label for="campaign_statement">Campaign Statement:</label>
                <textarea name="campaign_statement"></textarea>

                <label for="partylist">Partylist:</label>
                <select name="partylist_id" required>
                    <option value="" disabled selected>Select Partylist</option>
                    @foreach($partylists as $partylist)
                        <option value="{{ $partylist->partylist_id }}">{{ $partylist->name }}</option>
                    @endforeach
                </select>

                <label for="picture">Upload Picture:</label>
                <input type="file" name="picture" accept="image/*">

                <button type="submit">Add Candidate</button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Candidate Modal -->
<div id="editCandidateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close" id="closeEditCandidateModal">&times;</span>
            <h2>Edit Candidate</h2>
        </div>
        <div class="modal-body">
            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <label for="edit_election_id">Election:</label>
                <select name="election_id" id="edit_election_id" required>
                    <option value="" disabled selected>Select Election</option>
                    @foreach($elections as $election)
                        <option value="{{ $election->election_id }}">
                            {{ $election->election_name }} ({{ $election->election_status }})
                        </option>
                    @endforeach
                </select>

                <label for="edit_student_name">Candidate Name:</label>
                <input type="text" id="edit_student_name" name="student_name" required>

                <label for="edit_student_id">Student ID:</label>
                <input type="text" id="edit_student_id" name="student_id" readonly>

                <label for="edit_position_id">Position:</label>
                <select id="edit_position_id" name="position_id" required>
                    <option value="" disabled selected>Select Position</option>
                    @foreach($positions as $position)
                        <option value="{{ $position->position_id }}">{{ $position->position_name }}</option>
                    @endforeach
                </select>

                <label for="edit_campaign_statement">Campaign Statement:</label>
                <textarea id="edit_campaign_statement" name="campaign_statement"></textarea>

                <label for="edit_partylist">Partylist:</label>
                <select id="edit_partylist" name="partylist_id" required>
                    <option value="" disabled selected>Select Partylist</option>
                    @foreach($partylists as $partylist)
                        <option value="{{ $partylist->partylist_id }}">{{ $partylist->name }}</option>
                    @endforeach
                </select>

                <label for="edit_picture">Upload Picture:</label>
                <input type="file" id="edit_picture" name="picture" accept="image/*">

                <img id="edit_picture_preview" src="" alt="Candidate Image" style="width: 100px; height: auto; display:none;">

                <button type="submit">Update Candidate</button>
            </form>
        </div>
    </div>
</div>

<!-- Add Partylist Modal -->
<div id="addPartylistModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close" id="closeAddPartylistModal">&times;</span>
            <h2>Manage Partylists</h2>
        </div>
        <div class="modal-body">
            <form id="addPartylistForm">
                @csrf
                <div class="partylist-form">
                    <label for="partylist_name">Add New Partylist:</label>
                    <div class="partylist-input-group">
                        <input type="text" id="partylist_name" name="name" required placeholder="Enter partylist name">
                        <button type="submit">Add</button>
                    </div>
                </div>
            </form>

            <table class="table-common">
                <thead>
                    <tr>
                        <th>Partylist Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="partylistsContainer">
                    @foreach($partylists as $partylist)
                        <tr class="partylist-item">
                            <td>{{ $partylist->name }}</td>
                            <td>
                                <button class="delete-partylist-btn" data-id="{{ $partylist->partylist_id }}">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('scripts')
<!-- Make sure jQuery is loaded first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/candidate.js') }}"></script>
@endsection