document.addEventListener("DOMContentLoaded", function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Handle Add Candidate Modal Opening and Closing
    const addCandidateModal = document.getElementById('addCandidateModal');
    const openAddCandidateModal = document.getElementById('openAddCandidateModal');
    const closeAddCandidateModal = document.getElementById('closeAddCandidateModal');

    openAddCandidateModal.onclick = () => {
        addCandidateModal.style.display = 'block';
    };

    closeAddCandidateModal.onclick = () => {
        addCandidateModal.style.display = 'none';
    };

    // Close modals when clicking outside
    window.onclick = function (event) {
        if (event.target === addCandidateModal) {
            addCandidateModal.style.display = 'none';
        }
    };

    // Handle form submission for adding candidate
    document.getElementById("addForm").onsubmit = function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('/candidates', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    };

    // Edit Candidate Modal logic
    const editCandidateModal = document.getElementById('editCandidateModal');
        
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.onclick = function () {
            const candidate = JSON.parse(button.getAttribute('data-candidate'));

            // Populate form fields with candidate data
            document.getElementById('edit_student_id').value = candidate.student_id;
            document.getElementById('edit_candidate_name').value = candidate.student_name;
            document.getElementById('edit_election_id').value = candidate.election_id;
            document.getElementById('edit_position_id').value = candidate.position_id;
            document.getElementById('edit_campaign_statement').value = candidate.campaign_statement;
            document.getElementById('edit_partylist').value = candidate.partylist;

            // Show image preview if picture exists
            const imgPreview = document.getElementById('edit_picture_preview');
            if (candidate.picture) {
                imgPreview.src = `/images/candidates/${candidate.picture}`;
                imgPreview.style.display = 'block';
            } else {
                imgPreview.style.display = 'none';
            }

            // Set the candidate ID on the form to use in the update request
            document.getElementById('editForm').setAttribute('data-candidate-id', candidate.candidate_id);

            // Open the edit modal
            editCandidateModal.style.display = 'block';
        };
    });
    

    // Close modal logic for Edit Candidate
    const closeEditCandidateModal = document.getElementById('closeEditCandidateModal');
    closeEditCandidateModal.onclick = () => editCandidateModal.style.display = 'none';

    // Handle form submission for editing candidate
    document.getElementById("editForm").onsubmit = function (e) {
        e.preventDefault();

        const candidateId = this.getAttribute('data-candidate-id');  // Get candidate ID from the form

        const formData = new FormData(this);

        fetch(`/candidates/${candidateId}`, {
            method: 'POST',  // Should be 'POST' if you're following Laravel's form handling with @method('PUT')
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                editCandidateModal.style.display = 'none';
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    };

    // Handle delete candidate functionality
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.onclick = function () {
            const candidateId = button.getAttribute('data-id');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if (confirm('Are you sure you want to delete this candidate?')) {
                fetch(`/candidates/${candidateId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the candidate.');
                });
            }
        };
    });

    function searchCandidates(query) {
        const limitValue = document.getElementById('entries').value || 10;
        const currentUrl = new URL(window.location.href);
    
        // Update URL parameters
        currentUrl.searchParams.set('search', query);
        currentUrl.searchParams.set('limit', limitValue);
    
        // Fetch candidates with updated search query
        fetch(currentUrl)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('#candidate-body').innerHTML;
    
                // Replace the table body with the new filtered data
                document.getElementById('candidate-body').innerHTML = newTableBody;
            })
            .catch(error => console.error('Error fetching candidates:', error));
    }
    
    // Attach search function to the input field
    document.getElementById('search').addEventListener('input', function() {
        searchCandidates(this.value); // Call search function as user types
    });
    
    
});