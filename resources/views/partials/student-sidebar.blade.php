<!--Student Sidebar -->
<nav class="sidebar">
    <ul>
        <li><a href="{{ route('student-home') }}"><i class="fas fa-home"></i> <span>Home</span></a></li>
        <li><a href="{{ route('ongoing-elections.index') }}"><i class="fas fa-vote-yea"></i> <span>Ongoing Elections</span></a></li>
        <li><a href="{{ route('voting-history.index') }}"><i class="fas fa-history"></i> <span>Voting History</span></a></li>
        <li><a href="{{ route('student-results.index') }}"><i class="fas fa-chart-bar"></i> <span>View Results</span></a></li>
        <li><a href="{{ route('profile.index') }}"><i class="fas fa-user-cog"></i> <span>Manage Account</span></a></li>
    </ul>
</nav>
