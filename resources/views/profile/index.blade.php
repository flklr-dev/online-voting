<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">
    <title>Manage Account</title>
</head>
<body>
    @include('partials.header')
    @include('partials.student-sidebar')

    <div class="main-content2">
        <h1>Manage Account</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="profile-container">
            <div class="profile-section">
                <div class="profile-info">
                    <h2>{{ $student->fullname }}</h2>
                    <p>Student ID: {{ $student->student_id }}</p>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="profile-form">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="faculty">Faculty</label>
                        <select id="faculty" name="faculty" required>
                            <option value="" disabled>Select Faculty</option>
                            @foreach($faculties as $faculty)
                                <option value="{{ $faculty }}" {{ $student->faculty == $faculty ? 'selected' : '' }}>
                                    {{ $faculty }}
                                </option>
                            @endforeach
                        </select>
                        @error('faculty')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="program">Program</label>
                        <select id="program" name="program" required>
                            <option value="" disabled>Select Program</option>
                            @foreach($programs as $program)
                                <option value="{{ $program }}" {{ $student->program == $program ? 'selected' : '' }}>
                                    {{ $program }}
                                </option>
                            @endforeach
                        </select>
                        @error('program')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="password-section">
                        <h3>Change Password</h3>
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                            @error('current_password')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password">
                            @error('new_password')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_password_confirmation">Confirm New Password</label>
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="save-button">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('partials.footer')
    <script src="{{ asset('js/script.js') }}"></script>
    <script src="{{ asset('js/profile.js') }}"></script>
</body>
</html> 