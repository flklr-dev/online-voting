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


