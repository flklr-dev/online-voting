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
        if (event.target === addPartylistModal) {
            addPartylistModal.style.display = 'none';
        }
        if (event.target === editCandidateModal) {
            editCandidateModal.style.display = 'none';
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
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Candidate added successfully!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message
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

    // Edit Candidate Modal logic
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.onclick = function () {
            const candidate = JSON.parse(button.getAttribute('data-candidate'));
            const editForm = document.getElementById('editForm');
            
            // Set form fields
            document.getElementById('edit_election_id').value = candidate.election_id;
            document.getElementById('edit_student_name').value = candidate.student_name;
            document.getElementById('edit_student_id').value = candidate.student_id;
            document.getElementById('edit_position_id').value = candidate.position_id;
            document.getElementById('edit_campaign_statement').value = candidate.campaign_statement;
            document.getElementById('edit_partylist').value = candidate.partylist_id;

            // Show image preview
            const imgPreview = document.getElementById('edit_picture_preview');
            if (candidate.picture) {
                imgPreview.src = `/images/candidates/${candidate.picture}`;
                imgPreview.style.display = 'block';
            } else {
                imgPreview.style.display = 'none';
            }

            // Set the form action with the correct candidate ID
            editForm.action = `/candidates/${candidate.candidate_id}`;
            document.getElementById('editCandidateModal').style.display = 'block';
        };
    });
    

    // Close modal logic for Edit Candidate
    const closeEditCandidateModal = document.getElementById('closeEditCandidateModal');
    closeEditCandidateModal.onclick = () => editCandidateModal.style.display = 'none';

    // Update Candidate Form Submission
    document.getElementById("editForm").onsubmit = function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('_method', 'PUT');

        // Get the candidate ID from the form action
        const candidateId = this.action.split('/').pop();

        fetch(`/candidates/${candidateId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Candidate updated successfully!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    document.getElementById('editCandidateModal').style.display = 'none';
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Error updating candidate'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while updating the candidate.'
            });
        });
    };

    // Handle delete candidate functionality
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.onclick = function() {
            const candidateId = this.getAttribute('data-id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this candidate?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    fetch(`/candidates/${candidateId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Candidate has been deleted.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'Error deleting candidate'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while deleting the candidate.'
                        });
                    });
                }
            });
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
    
    
    // Add Partylist Modal
    const addPartylistModal = document.getElementById('addPartylistModal');
    const openAddPartylistModal = document.getElementById('openAddPartylistModal');
    const closeAddPartylistModal = document.getElementById('closeAddPartylistModal');

    openAddPartylistModal.onclick = () => {
        addPartylistModal.style.display = 'block';
    };

    closeAddPartylistModal.onclick = () => {
        addPartylistModal.style.display = 'none';
    };

    // Add form submission handler for partylist
    document.getElementById("addPartylistForm").onsubmit = function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('/partylists', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new partylist to the table
                const partylistsContainer = document.getElementById('partylistsContainer');
                const newRow = document.createElement('tr');
                newRow.className = 'partylist-item';
                newRow.innerHTML = `
                    <td>${data.partylist.name}</td>
                    <td>
                        <button class="delete-partylist-btn" data-id="${data.partylist.partylist_id}">Delete</button>
                    </td>
                `;
                partylistsContainer.appendChild(newRow);
                
                // Clear the input
                this.reset();
                
                // Add delete event listener to new button
                attachDeletePartylistListener(newRow.querySelector('.delete-partylist-btn'));
                
                // Update the select options in the candidate forms
                updatePartylistSelects(data.partylist);

                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Partylist added successfully!',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message
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

    // Function to attach delete event listener
    function attachDeletePartylistListener(button) {
        button.onclick = function() {
            const partylistId = this.getAttribute('data-id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this partylist?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/partylists/${partylistId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('.partylist-item').remove();
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Partylist has been deleted.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while deleting the partylist.'
                        });
                    });
                }
            });
        };
    }

    // Attach delete listeners to existing buttons
    document.querySelectorAll('.delete-partylist-btn').forEach(button => {
        attachDeletePartylistListener(button);
    });

    // Function to update partylist select options
    function updatePartylistSelects(newPartylist) {
        const selects = document.querySelectorAll('select[name="partylist_id"]');
        selects.forEach(select => {
            const option = new Option(newPartylist.name, newPartylist.partylist_id);
            select.add(option);
        });
    }

    // Initialize Select2
    $('.select2-candidate').select2({
        placeholder: 'Select Candidate',
        allowClear: true,
        width: '100%'
    });

    // Election change handler
    $('#election_id').on('change', function() {
        const electionId = this.value;
        const candidateSelect = $('.select2-candidate');
        
        if (electionId) {
            candidateSelect.prop('disabled', false);
            
            // Fetch eligible students
            fetch(`/get-eligible-students/${electionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear and populate Select2
                        candidateSelect.empty().append('<option value="" disabled selected>Select Candidate</option>');
                        
                        data.students.forEach(student => {
                            const option = new Option(student.fullname, student.fullname, false, false);
                            candidateSelect.append(option);
                        });
                        
                        // Store students data for ID lookup
                        window.eligibleStudents = data.students;
                        
                        // Trigger Select2 to update
                        candidateSelect.trigger('change');
                    }
                })
                .catch(error => console.error('Error:', error));
        } else {
            candidateSelect.prop('disabled', true)
                .empty()
                .append('<option value="" disabled selected>First select an election</option>')
                .trigger('change');
        }
    });

    // Handle candidate selection
    $('.select2-candidate').on('select2:select', function(e) {
        const selectedName = e.params.data.text;
        const selectedStudent = window.eligibleStudents.find(student => 
            student.fullname === selectedName
        );
        if (selectedStudent) {
            $('#student_id').val(selectedStudent.student_id);
        }
    });

    $(document).ready(function() {
        // Initialize Select2 for both add and edit forms
        $('.select2-candidate').select2({
            placeholder: 'Search and select candidate',
            allowClear: true,
            width: '100%'
        });

        // Edit button click handler
        $('.edit-btn').on('click', function() {
            const candidateData = JSON.parse($(this).attr('data-candidate'));
            const editModal = $('#editCandidateModal');
            
            // Set the form action
            $('#editForm').attr('action', `/candidates/${candidateData.candidate_id}`);
            
            // Set initial values
            $('#edit_election_id').val(candidateData.election_id);
            $('#edit_student_id').val(candidateData.student_id);
            $('#edit_position_id').val(candidateData.position_id);
            $('#edit_campaign_statement').val(candidateData.campaign_statement);
            $('#edit_partylist').val(candidateData.partylist_id);

            // Handle candidate name Select2
            const candidateSelect = $('#edit_candidate_name');
            fetch(`/get-eligible-students/${candidateData.election_id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        candidateSelect.empty().append('<option value="" disabled>Select Candidate</option>');
                        
                        data.students.forEach(student => {
                            const option = new Option(student.fullname, student.fullname, 
                                student.fullname === candidateData.student_name,
                                student.fullname === candidateData.student_name);
                            candidateSelect.append(option);
                        });
                        
                        window.eligibleStudents = data.students;
                        candidateSelect.trigger('change');
                    }
                });

            // Show current picture if exists
            if (candidateData.picture) {
                $('#edit_picture_preview').attr('src', `/images/candidates/${candidateData.picture}`).show();
            } else {
                $('#edit_picture_preview').hide();
            }

            editModal.show();
        });

        // Election change handler for edit form
        $('#edit_election_id').on('change', function() {
            const electionId = this.value;
            const candidateSelect = $('#edit_candidate_name');
            
            if (electionId) {
                candidateSelect.prop('disabled', false);
                
                fetch(`/get-eligible-students/${electionId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            candidateSelect.empty().append('<option value="" disabled selected>Select Candidate</option>');
                            
                            data.students.forEach(student => {
                                const option = new Option(student.fullname, student.fullname, false, false);
                                candidateSelect.append(option);
                            });
                            
                            window.eligibleStudents = data.students;
                            candidateSelect.trigger('change');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                candidateSelect.prop('disabled', true)
                    .empty()
                    .append('<option value="" disabled selected>First select an election</option>')
                    .trigger('change');
            }
        });

        // Handle candidate selection in edit form
        $('#edit_candidate_name').on('select2:select', function(e) {
            const selectedName = e.params.data.text;
            const selectedStudent = window.eligibleStudents.find(student => 
                student.fullname === selectedName
            );
            if (selectedStudent) {
                $('#edit_student_id').val(selectedStudent.student_id);
            }
        });

        // Campaign Statement View Button
        document.querySelectorAll('.view-campaign-btn').forEach(button => {
            button.onclick = function() {
                const campaignStatement = this.getAttribute('data-campaign');
                
                Swal.fire({
                    title: 'Campaign Statement',
                    text: campaignStatement || 'No campaign statement available.',
                    icon: 'info',
                    confirmButtonText: 'Close',
                    customClass: {
                        container: 'campaign-statement-modal',
                        content: 'campaign-statement-content'
                    },
                    width: '50em', // Makes the modal wider
                    showCloseButton: true,
                });
            };
        });

        // Keep your existing code...
    });
});