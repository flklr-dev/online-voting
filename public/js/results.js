document.addEventListener('DOMContentLoaded', () => {
    const electionDropdown = document.getElementById('electionSelect');
    const resultsContainer = document.getElementById('resultsContainer');

    electionDropdown.addEventListener('change', async () => {
        const electionId = electionDropdown.value;
        console.log(`Fetching results for election ID: ${electionId}`);

        if (!electionId) {
            resultsContainer.innerHTML = '<p class="no-results">Please select an election to view results.</p>';
            return;
        }

        try {
            const response = await fetch(`/results/${electionId}`);
            if (!response.ok) {
                throw new Error(`Failed to fetch results: ${response.statusText}`);
            }

            const data = await response.json();
            if (data.results && Object.keys(data.results).length > 0) {
                displayResults(data);
            } else {
                resultsContainer.innerHTML = '<p class="no-results">No results available yet.</p>';
            }
        } catch (error) {
            console.error('Error fetching results:', error);
            resultsContainer.innerHTML = '<p class="error-message">An error occurred while fetching results. Please try again.</p>';
        }
    });

    function displayResults(data) {
        const { election, results } = data;
        resultsContainer.innerHTML = `<h2>${election.election_name}</h2>`;

        Object.entries(results).forEach(([positionId, candidates]) => {
            const maxVotes = candidates[0]?.max_vote || candidates.length;
            const topCandidates = candidates.slice(0, maxVotes);

            let positionHTML = `
                <div class="position-table" style="background-color: #003399; color: white; padding: 10px;">
                    <h3>${topCandidates[0].position_name}</h3>
                </div>
            `;

            topCandidates.forEach(candidate => {
                positionHTML += `
                    <div class="candidate-details" style="display: flex; align-items: center; gap: 20px; margin-top: 10px;">
                        <div class="candidate-image">
                            <img src="/images/candidates/${candidate.picture}" width="100">
                        </div>
                        <div>
                            <h3>${candidate.student_name}</h3>
                            <p><i>${candidate.campaign_statement}</i></p>
                            <p><strong>Partylist:</strong> ${candidate.partylist}</p>
                            <p><strong>Total Votes:</strong> ${candidate.total_votes}</p>
                        </div>
                    </div>
                `;
            });

            resultsContainer.innerHTML += positionHTML;
        });
    }
});
