function changeEntries(value) {
    const searchQuery = document.getElementById('search').value; // Get current search query
    window.location.href = `?limit=${value}&search=${searchQuery}`; // Reload page with new limit and search query
}

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content, .main-content2');
    
    if (window.innerWidth <= 768) {
        // Mobile behavior
        if (sidebar.style.display === 'block') {
            sidebar.style.display = 'none';
        } else {
            sidebar.style.display = 'block';
        }
    } else {
        // Desktop behavior
        sidebar.classList.toggle('minimized');
        if (mainContent) {
            mainContent.classList.toggle('minimized');
        }
    }
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

// Ensure the active sidebar link is set after page reload
document.addEventListener('DOMContentLoaded', function () {
    const sidebarLinks = document.querySelectorAll('.sidebar a');
    sidebarLinks.forEach(link => {
        if (link.href === window.location.href.split('?')[0]) {
            link.classList.add('active');
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.querySelector('.sidebar');
    if (window.innerWidth <= 768) {
        sidebar.classList.remove('minimized');
    }
});

window.addEventListener("resize", function () {
    const sidebar = document.querySelector('.sidebar');
    if (window.innerWidth <= 768) {
        sidebar.classList.remove('minimized');
    }
});

// Add click event listener to close sidebar when clicking outside (mobile only)
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const burger = document.querySelector('.burger');
    
    if (window.innerWidth <= 768 && 
        !sidebar.contains(event.target) && 
        !burger.contains(event.target)) {
        sidebar.style.display = 'none';
    }
});

// Prevent sidebar clicks from closing it
document.querySelector('.sidebar').addEventListener('click', function(event) {
    event.stopPropagation();
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.sidebar');
    if (window.innerWidth > 768) {
        sidebar.style.display = 'block'; // Always show sidebar on desktop
    } else {
        sidebar.style.display = 'none'; // Hide sidebar on mobile by default
    }
});

// Initialize sidebar state on page load
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    if (window.innerWidth > 768) {
        sidebar.style.display = 'block';
    } else {
        sidebar.style.display = 'none';
    }
});

