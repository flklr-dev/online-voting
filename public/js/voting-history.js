document.addEventListener('DOMContentLoaded', function() {
    // Handle election filter
    document.getElementById('electionFilter').addEventListener('change', function() {
        const limit = document.getElementById('entries').value;
        const search = document.getElementById('search').value;
        window.location.href = `?filter=${this.value}&limit=${limit}&search=${search}`;
    });

    // Set the filter value from URL if exists
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('filter')) {
        document.getElementById('electionFilter').value = urlParams.get('filter');
    }
});

function searchVotingHistory(query) {
    const limit = document.getElementById('entries').value;
    const filter = document.getElementById('electionFilter').value;
    
    fetch(`/voting-history?search=${query}&limit=${limit}&filter=${filter}`)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update table body
            document.getElementById('voting-history-body').innerHTML = 
                doc.querySelector('#voting-history-body').innerHTML;
            
            // Update pagination
            const paginationContainer = document.querySelector('.pagination-container');
            paginationContainer.innerHTML = 
                doc.querySelector('.pagination-container').innerHTML;
        })
        .catch(error => {
            console.error('Error fetching voting history:', error);
        });
}

function changeEntries(value) {
    const search = document.getElementById('search').value;
    const filter = document.getElementById('electionFilter').value;
    window.location.href = `?limit=${value}&search=${search}&filter=${filter}`;
}

// Update pagination links to include current parameters
document.querySelectorAll('.pagination a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const url = new URL(this.href);
        const currentParams = new URLSearchParams(window.location.search);
        
        // Add current parameters to pagination links
        url.searchParams.set('limit', currentParams.get('limit') || '10');
        url.searchParams.set('search', currentParams.get('search') || '');
        url.searchParams.set('filter', currentParams.get('filter') || '');
        
        window.location.href = url.toString();
    });
});
