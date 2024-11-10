<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/student-dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $election->election_name }} - Vote</title>
</head>
<body>
    @include('partials.header')
    @include('partials.student-sidebar')

    <div class="vote-content">
        <h1>{{ $election->election_name }}</h1>

        @if($election->candidates->isEmpty())
            <div class="no-candidates-message">
                <p>No candidates are available for this election yet.</p>
            </div>
        @else
            <form id="voteForm">
                @foreach($election->candidates->groupBy('position_id') as $positionId => $candidates)
                    <table class="position-table">
                        <thead>
                            <tr style="background-color: #003399; color: white;">
                                <th colspan="3">{{ $candidates->first()->position->position_name }}</th>
                            </tr>
                            <tr style="background-color: #FACB10; color: black;">
                                <th colspan="3">Select {{ $candidates->first()->position->max_vote }} candidate/s</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($candidates as $candidate)
                                <tr>
                                    <td class="candidate-image">
                                        <img src="{{ asset('images/candidates/' . $candidate->picture) }}" width="100">
                                    </td>
                                    <td class="candidate-details">
                                        <h3>{{ $candidate->student_name }}</h3>
                                        <p><i>{{ $candidate->campaign_statement }}</i></p>
                                        <p><strong>Partylist:</strong> {{ $candidate->partylist }}</p>
                                    </td>
                                    <td class="candidate-vote">
                                        <label>
                                            Vote
                                            <input 
                                                type="checkbox" 
                                                class="candidate-checkbox" 
                                                data-position-id="{{ $positionId }}" 
                                                value="{{ $candidate->candidate_id }}"
                                            >
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach

                <button type="submit" id="submitVote">Submit Vote</button>
            </form>
        @endif
    </div>

    <script>
        const voteStoreRoute = "{{ route('vote.store') }}";
        const electionId = "{{ $election->election_id }}";
        const studentHomeRoute = "{{ route('student-home') }}";
    </script>

    <script src="{{ asset('js/vote.js') }}"></script>
</body>
</html>
