document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('voteForm');
    const checkboxes = document.querySelectorAll('.candidate-checkbox');

    // Handle checkbox limits per position
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const positionId = this.getAttribute('data-position-id');
            const maxVotes = parseInt(this.closest('table').querySelector('thead tr:nth-child(2) th').textContent.match(/\d+/)[0]);
            const checkedBoxes = document.querySelectorAll(`.candidate-checkbox[data-position-id="${positionId}"]:checked`);

            if (checkedBoxes.length > maxVotes) {
                this.checked = false;
                Swal.fire({
                    icon: 'warning',
                    title: 'Maximum Votes Reached',
                    text: `You can only select ${maxVotes} candidate(s) for this position.`
                });
            }
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Collect votes
        const votes = [];
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                votes.push({
                    candidate_id: checkbox.value,
                    position_id: checkbox.getAttribute('data-position-id')
                });
            }
        });

        // Confirm vote submission
        Swal.fire({
            title: 'Confirm Your Vote',
            text: 'Are you sure you want to submit your vote? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, submit my vote!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit votes
                fetch(voteStoreRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        election_id: electionId,
                        votes: votes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Your vote has been cast successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = studentHomeRoute;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Failed to cast vote. Please try again.'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while submitting your vote. Please try again.'
                    });
                });
            }
        });
    });
});
