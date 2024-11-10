<header>
    <div class="logo-container">
        <div class="burger" onclick="toggleSidebar()">&#9776;</div>
        <img src="{{ asset('logo.png') }}" alt="Logo" class="logo">
        <div class="web-name">Online Voting System</div>
    </div>

    <nav class="user">
        <div class="profile-dropdown">
            <img src="{{ asset('userdefault.webp') }}" alt="Profile Picture" class="profile-pic">

            @if(session('user_role') === 'admin')
                <span class="username">{{ auth('admin')->user()->username }}</span>
            @elseif(session('user_role') === 'student')
                <span class="username">{{ auth('student')->user()->fullname }}</span>
            @else
                <span class="username">Guest</span>
            @endif

            
            <div class="dropdown">
                <button class="dropbtn" id="toggleDropdown">▼</button>
                <div class="dropdown-content" id="dropdownContent">
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" style="background: none; border: none; color: inherit; cursor: pointer;">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
</header>
