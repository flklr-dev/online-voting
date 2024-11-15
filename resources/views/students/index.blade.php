@extends('layouts.app')

@section('title', 'Manage Students')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

    <h1>Student List</h1>
    
    <div class="container">
        <div class="top-controls">
            <button id="openAddStudentModal" class="btn-add-student">Add Student</button> <!-- Button to open modal -->
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
                <input type="text" id="search" placeholder="Search students" onkeyup="searchStudents(this.value)">
            </div>
        </div>

        <table class="table-common student-table">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>School Email</th>
                    <th>Faculty</th>
                    <th>Program</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="student-body">
                @if ($students->count() > 0)
                    @foreach ($students as $student)
                        <tr>
                            <td>{{ $student->student_id }}</td>
                            <td>{{ $student->fullname }}</td>
                            <td>{{ $student->school_email }}</td>
                            <td>{{ $student->faculty }}</td>
                            <td>{{ $student->program }}</td>
                            <td>{{ $student->status }}</td>
                            <td class="actions">
                                <button class="edit-btn" data-student="{{ json_encode($student) }}">Edit</button>
                                <button class="delete-btn" data-id="{{ $student->student_id }}">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" style="text-align: center;">No students found.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="pagination-container">
            <div class="pagination-info">
                Showing {{ $students->firstItem() }} to {{ $students->lastItem() }} of {{ $students->total() }} entries
            </div>
            <div class="pagination">
                @if ($students->onFirstPage())
                    <span class="prev disabled">Prev</span>
                @else
                    <a href="{{ $students->previousPageUrl() }}" class="prev">Prev</a>
                @endif

                <span class="current-page">{{ $students->currentPage() }}</span>

                @if ($students->hasMorePages())
                    <a href="{{ $students->nextPageUrl() }}" class="next">Next</a>
                @else
                    <span class="next disabled">Next</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" id="closeAddStudentModal">&times;</span>
                <h2>Add Student</h2>
            </div>
            <div class="modal-body">
                <form id="addStudentForm" method="POST" action="{{ route('students.store') }}">
                    @csrf
                    <label for="student_id">Student ID (Format: 9999-9999):</label>
                    <input type="text" id="student_id" name="student_id" required>

                    <label for="fullname">Full Name:</label>
                    <input placeholder="Ex. Juan Dela Cruz" type="text" id="fullname" name="fullname" required>

                    <label for="school_email">School Email:</label>
                    <input placeholder="juan.delacruz@dorsu.edu.ph" type="email" id="school_email" name="school_email" required>

                    <label for="faculty">Faculty:</label>
                    <select id="faculty" name="faculty" required>
                        <option value="" disabled selected>Select Faculty</option>
                        @foreach ($faculties as $faculty)
                            <option value="{{ $faculty }}">{{ $faculty }}</option>
                        @endforeach
                    </select>

                    <label for="program">Program:</label>
                    <select id="program" name="program" required>
                        <option value="" disabled selected>Select Program</option>
                    </select>

                    <button type="submit" class="btn btn-primary">Add Student</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div id="editStudentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" id="closeEditModal">&times;</span>
                <h2>Edit Student</h2>
            </div>
            <div class="modal-body">
                <form id="editStudentForm" method="POST">
                    @csrf
                    @method('PUT') <!-- This method is needed for updating -->
                    
                    <label for="edit_student_id">Student ID:</label>
                    <input type="text" id="edit_student_id" name="student_id" required>

                    <label for="edit_fullname">Full Name:</label>
                    <input type="text" id="edit_fullname" name="fullname" required>

                    <label for="edit_school_email">School Email:</label>
                    <input type="email" id="edit_school_email" name="school_email" required>

                    <label for="edit_faculty">Faculty:</label>
                    <select id="edit_faculty" name="faculty" required>
                        <option value="" disabled>Select Faculty</option>
                        @foreach ($faculties as $faculty)
                            <option value="{{ $faculty }}">{{ $faculty }}</option>
                        @endforeach
                    </select>

                    <label for="edit_program">Program:</label>
                    <select id="edit_program" name="program" required>
                        <option value="" disabled selected>Select Program</option>
                    </select>

                     <!-- Status Dropdown -->
                    <label for="edit_status">Status:</label>
                    <select id="edit_status" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>

                    <button type="submit" class="btn btn-primary">Update Student</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('js/student.js') }}"></script>
@endsection