document.addEventListener("DOMContentLoaded", function () {
    const addPositionModal = document.getElementById('addPositionModal');
    const editPositionModal = document.getElementById('editPositionModal');
    const closeAddPositionModal = document.getElementById('closeAddPositionModal');
    const closeEditPositionModal = document.getElementById('closeEditPositionModal');

    // Open Add Position Modal
    document.getElementById('openAddPositionModal').onclick = () => {
        addPositionModal.style.display = 'block';
    };

    // Close Add Position Modal
    closeAddPositionModal.onclick = () => {
        addPositionModal.style.display = 'none';
    };

    // Close Edit Position Modal
    closeEditPositionModal.onclick = () => {
        editPositionModal.style.display = 'none';
    };

    // Close modals when clicking outside of them
    window.onclick = (event) => {
        if (event.target === addPositionModal) {
            addPositionModal.style.display = 'none';
        } else if (event.target === editPositionModal) {
            editPositionModal.style.display = 'none';
        }
    };

    // Set default max_vote value to 1 in add form
    const addMaxVoteInput = document.getElementById('max_vote');
    addMaxVoteInput.value = 1;
    addMaxVoteInput.min = 1; // Minimum value for add form
    
    // Set minimum value of max_vote in edit form
    const editMaxVoteInput = document.getElementById('edit_max_vote');
    editMaxVoteInput.min = 1;
    
    // Prevent max_vote from going below 1 in add form
    addMaxVoteInput.addEventListener('input', function() {
        if (this.value < 1) {
            this.value = 1;
        }
    });
    
    // Prevent max_vote from going below 1 in edit form
    editMaxVoteInput.addEventListener('input', function() {
        if (this.value < 1) {
            this.value = 1;
        }
    });

    // Add Position
    document.getElementById('addPositionForm').onsubmit = function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('/positions', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Position added successfully!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    addPositionModal.style.display = 'none';
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Error adding position'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred. Please try again.'
            });
        });
    };

    // Edit Position
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.onclick = function () {
            const position = JSON.parse(button.getAttribute('data-position'));

            document.getElementById('edit_position_name').value = position.position_name;
            document.getElementById('edit_max_vote').value = position.max_vote;
            document.getElementById('editPositionForm').setAttribute('data-position-id', position.position_id);

            editPositionModal.style.display = 'block';
        };
    });

    // Edit Position Form Submission
    document.getElementById('editPositionForm').onsubmit = function (e) {
        e.preventDefault();
        const positionId = this.getAttribute('data-position-id');
        const formData = new FormData(this);
        formData.append('_method', 'PUT');

        fetch(`/positions/${positionId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Position updated successfully!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    editPositionModal.style.display = 'none';
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Error updating position'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while updating the position.'
            });
        });
    };

    // Delete Position
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.onclick = function () {
            const positionId = button.getAttribute('data-id');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this position?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/positions/${positionId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Position has been deleted.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'Error deleting position'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while deleting the position.'
                        });
                    });
                }
            });
        };
    });

    // Search Positions
    document.getElementById('search').addEventListener('input', function () {
        const query = this.value;

        fetch(`/positions?search=${query}`)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            document.getElementById('position-body').innerHTML = doc.querySelector('#position-body').innerHTML;
        })
        .catch(error => console.error('Error:', error));
    });
});
