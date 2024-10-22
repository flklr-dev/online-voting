@extends('layouts.app')

@section('title', 'Manage Admins')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<h1>Admin List</h1>

<div class="container">
    <div class="top-controls">
        <button id="openAddAdminModal" class="btn-add-admin">Add Admin</button>
        <div class="search-container">
            <label for="search">Search:</label>
            <input type="text" id="search" placeholder="Search admins" onkeyup="searchAdmins(this.value)">
        </div>
    </div>

    <table class="table-common admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="admin-body">
            @if ($admins->count() > 0)
                @foreach ($admins as $admin)
                    <tr>
                        <td>{{ $admin->admin_id }}</td>
                        <td>{{ $admin->username }}</td>
                        <td class="actions">
                            <button class="edit-btn" data-admin="{{ json_encode($admin) }}">Edit</button>
                            <button class="delete-btn" data-admin-id="{{ $admin->admin_id }}">Delete</button>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3" style="text-align: center;">No admins found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<!-- Add Admin Modal -->
<div id="addAdminModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close" id="closeAddAdminModal">&times;</span>
            <h2>Add Admin</h2>
        </div>
        <div class="modal-body">
            <form id="addForm" method="POST" action="{{ route('admins.store') }}">
                @csrf

                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit" class="btn btn-primary">Add Admin</button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Admin Modal -->
<div id="editAdminModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close" id="closeEditAdminModal">&times;</span>
            <h2>Edit Admin</h2>
        </div>
        <div class="modal-body">
            <form id="editForm" method="POST" }}">
                @csrf
                @method('PUT')

                <label for="edit_username">Username:</label>
                <input type="text" id="edit_username" name="username" required>

                <label for="edit_password">Password:</label>
                <input type="text" id="edit_password" name="password" required> <!-- Decrypted password -->

                <button type="submit" class="btn btn-primary">Update Admin</button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/admin.js') }}"></script>
@endsection
