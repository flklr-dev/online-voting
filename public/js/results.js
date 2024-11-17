// results.js

document.addEventListener('DOMContentLoaded', () => {
    // Handle "View Results" button clicks
    const viewResultsButtons = document.querySelectorAll('.view-results-button');
    
    viewResultsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const electionId = this.dataset.electionId;
            
            // Redirect to the results page for the specific election
            if (electionId) {
                window.location.href = `/results/${electionId}`;
            }
        });
    });

    const academicYearFilter = document.getElementById('academicYearFilter');
    const electionGrid = document.getElementById('electionGrid');

    academicYearFilter.addEventListener('change', function () {
        const year = this.value;

        // Fetch filtered results via AJAX
        fetch(`/results?academic_year=${year}`)
            .then(response => response.text())
            .then(html => {
                // Replace the election grid with the new filtered data
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newGrid = doc.querySelector('#electionGrid').innerHTML;
                electionGrid.innerHTML = newGrid;
            })
            .catch(error => {
                console.error('Error fetching filtered results:', error);
            });
    });
});

