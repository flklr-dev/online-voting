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
});

