
<!--Admin Sidebar -->
<nav class="sidebar">
    <ul>
        <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> <span>Home</span></a></li>
        <li><a href="{{ route('students.index') }}"><i class="fas fa-user"></i> <span>Manage Students</span></a></li>
        <li><a href="{{ route('elections.index') }}"><i class="fas fa-vote-yea"></i> <span>Manage Elections</span></a></li>
        <li><a href="{{ route('positions.index') }}"><i class="fas fa-users-cog"></i> <span>Manage Positions</span></a></li>
        <li><a href="{{ route('candidates.index') }}"><i class="fas fa-user-check"></i> <span>Manage Candidates</span></a></li>
        <li><a href="{{ route('results.index') }}"><i class="fas fa-chart-pie"></i> <span>View Results</span></a></li>
        <li><a href="{{ route('admin.voting-history') }}"><i class="fas fa-history"></i> <span>Voting History</span></a></li>
        <li><a href="{{ route('admins.index') }}"><i class="fas fa-user-shield"></i> <span>Manage Admins</span></a></li>
        <li><a href="{{ route('activity-logs.index') }}"><i class="fas fa-user-clock"></i> <span>Activity Logs</span></a></li>
        </li>
    </ul>
</nav>
