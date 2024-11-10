document.addEventListener('DOMContentLoaded', () => {
    const voteForm = document.getElementById('voteForm');
    const maxVotes = {}; // Store max votes per position

    // Initialize max votes from the DOM
    document.querySelectorAll('.position-table').forEach(table => {
        const positionId = table.querySelector('.candidate-checkbox').dataset.positionId;
        const maxVote = parseInt(
            table.querySelector('thead tr:nth-child(2) th').textContent.match(/\d+/)[0]
        );
        maxVotes[positionId] = maxVote;
    });

    // Update checkbox state for each position based on selected count
    function updateCheckboxState(positionId) {
        const selectedCount = document.querySelectorAll(
            `.candidate-checkbox[data-position-id="${positionId}"]:checked`
        ).length;

        document.querySelectorAll(`.candidate-checkbox[data-position-id="${positionId}"]`).forEach(checkbox => {
            checkbox.disabled = !checkbox.checked && selectedCount >= maxVotes[positionId];
        });
    }

    // Add change event listeners to all candidate checkboxes
    document.querySelectorAll('.candidate-checkbox').forEach(checkbox => {
        const positionId = checkbox.dataset.positionId;
        checkbox.addEventListener('change', () => updateCheckboxState(positionId));
    });

    // Handle form submission
    voteForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (confirm('Are you sure you want to cast your vote? This action cannot be undone.')) {
            const selectedCandidates = Array.from(
                document.querySelectorAll('input[type="checkbox"]:checked')
            ).map(checkbox => ({
                candidate_id: checkbox.value,
                position_id: checkbox.dataset.positionId || null
            }));

            try {
                const response = await fetch(voteStoreRoute, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        election_id: electionId,
                        candidates: selectedCandidates,
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    alert('Vote cast successfully!');
                    window.location.href = studentHomeRoute;
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An unexpected error occurred. Please try again.');
            }
        }
    });
});
