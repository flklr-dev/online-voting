function changeEntries(value) {
    const searchQuery = document.getElementById('search').value; // Keep the current search query
    window.location.href = `?limit=${value}&search=${searchQuery}`; // Reload the page with new limit and search query
}
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const header = document.querySelector('header');
    sidebar.classList.toggle('minimized');
    header.classList.toggle('minimized');
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('toggleDropdown').addEventListener('click', function (event) {
        event.stopPropagation(); // Prevent this click from closing the dropdown
        var dropdown = document.getElementById('dropdownContent');
        dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
    });

    // Hide dropdown when clicking outside
    window.onclick = function(event) {
        if (!event.target.matches('.dropbtn')) {
            var dropdowns = document.getElementsByClassName('dropdown-content');
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.style.display === 'block') {
                    openDropdown.style.display = 'none';
                }
            }
        }
    };
    
});

document.addEventListener('DOMContentLoaded', function () {
    const sidebarLinks = document.querySelectorAll('.sidebar a');

    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Remove active class from all links
            sidebarLinks.forEach(link => link.classList.remove('active'));

            // Add active class to the clicked link
            this.classList.add('active');
        });

        // Check if the link's href matches the current page URL
        if (link.href === window.location.href) {
            link.classList.add('active');
        }
    });
});

function searchVotingHistory(query) {
    const entries = document.getElementById('entries') ? document.getElementById('entries').value : 10;
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('search', query);
    urlParams.set('limit', entries);
    window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
}

// Ensure the active sidebar link is set after page reload
document.addEventListener('DOMContentLoaded', function () {
    const sidebarLinks = document.querySelectorAll('.sidebar a');
    sidebarLinks.forEach(link => {
        if (link.href === window.location.href.split('?')[0]) {
            link.classList.add('active');
        }
    });
});

