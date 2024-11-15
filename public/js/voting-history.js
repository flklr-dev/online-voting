function searchVotingHistory(query) {
    const limit = document.getElementById("entries").value; // Get selected entries limit
    fetch(`/voting-history?search=${query}&limit=${limit}`)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTableBody = doc.querySelector('#voting-history-body').innerHTML;
            const newPagination = doc.querySelector('.pagination').innerHTML;

            // Update table body and pagination with new data
            document.getElementById('voting-history-body').innerHTML = newTableBody;
            document.querySelector('.pagination').innerHTML = newPagination;
        })
        .catch(error => {
            console.error('Error fetching voting history:', error);
        });
}

// Attach event listeners
document.getElementById('search').addEventListener('input', function() {
    searchVotingHistory(this.value);
});

document.getElementById('entries').addEventListener('change', function() {
    searchVotingHistory(document.getElementById('search').value);
});
