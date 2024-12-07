document.addEventListener("DOMContentLoaded", function () {
    
    // Handle Add Election Modal Opening and Closing
    const addElectionModal = document.getElementById('addElectionModal');
    const openAddElectionModal = document.getElementById('openAddElectionModal');
    const closeAddElectionModal = document.getElementById('closeAddElectionModal');

    openAddElectionModal.onclick = () => {
        addElectionModal.style.display = 'block';
    };

    closeAddElectionModal.onclick = () => {
        addElectionModal.style.display = 'none';
    };

    window.onclick = function (event) {
        if (event.target === addElectionModal) {
            addElectionModal.style.display = 'none';
        }
    };

    const addElectionType = document.getElementById('election_type');
    const addRestrictionDropdown = document.getElementById('restriction');
    addElectionType.addEventListener('change', function () {
        handleRestrictionDropdown(addElectionType.value, addRestrictionDropdown);
    });

    const editElectionModal = document.getElementById('editElectionModal');
    const editElectionType = document.getElementById('edit_election_type');
    const editRestrictionDropdown = document.getElementById('edit_restriction');
    editElectionType.addEventListener('change', function () {
        handleRestrictionDropdown(editElectionType.value, editRestrictionDropdown);
    });

    function handleRestrictionDropdown(electionType, restrictionDropdown) {
        restrictionDropdown.innerHTML = ''; // Clear existing options

        if (electionType === 'General') {
            const option = document.createElement('option');
            option.value = 'None';
            option.textContent = 'None';
            option.selected = true;
            restrictionDropdown.appendChild(option);
            restrictionDropdown.setAttribute('disabled', true);
        } else {
            restrictionDropdown.removeAttribute('disabled');
            let restrictions = [];
            if (electionType === 'Faculty') {
                restrictions = ['FaCET', 'FALS', 'FBM', 'FTED', 'FGCE', 'FNAHS'];
            } else if (electionType === 'Program') {
                restrictions = ['BSIT', 'BSCE', 'BSITM', 'BSM', 'BSBIO', 'BSES', 'BSBA', 'BSA'];
            }

            restrictionDropdown.innerHTML = '<option value="" disabled selected>Select Restriction</option>';
            restrictions.forEach(restriction => {
                const option = document.createElement('option');
                option.value = restriction;
                option.textContent = restriction;
                restrictionDropdown.appendChild(option);
            });
        }
    }

    document.getElementById('election_type').dispatchEvent(new Event('change'));
    document.getElementById('edit_election_type').dispatchEvent(new Event('change'));

    function updateTableElectionStatuses() {
        const rows = document.querySelectorAll('#election-body tr');
    
        rows.forEach(row => {
            const electionId = row.getAttribute('data-election-id');
            const startDate = row.getAttribute('data-start-date');
            const endDate = row.getAttribute('data-end-date');
            const statusCell = row.querySelector('.election-status');
    
            const newStatus = determineElectionStatus(startDate, endDate);
    
            if (statusCell && statusCell.textContent !== newStatus) {
                // Update status on the frontend
                statusCell.textContent = newStatus;
    
                // Send status update to the backend
                fetch(`/elections/${electionId}/update-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ election_status: newStatus }),
                })
                .then(response => {
                    if (!response.ok) {
                        console.error(`Failed to update status for election ${electionId}`);
                    }
                })
                .catch(error => {
                    console.error(`Error updating status for election ${electionId}:`, error);
                });
            }
        });
    }
    

    function determineElectionStatus(startDate, endDate) {
        const now = new Date();
        const start = new Date(startDate);
        const end = new Date(endDate);

        if (!startDate) return '';
        if (start > now) return 'Upcoming';
        if (start <= now && (!endDate || end >= now)) return 'Ongoing';
        if (endDate && end < now) return 'Completed';
        return '';
    }

    // Update Election Status
    function updateElectionStatus(startDate, endDate, statusField) {
        const status = determineElectionStatus(startDate, endDate);
        if (statusField) {
            statusField.value = status;
        }
    }

    setInterval(updateTableElectionStatuses, 10000);


    // Initialize Flatpickr for the start date with restriction (No past dates allowed)
    const startDatePicker = flatpickr("#start_date", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: new Date(),
        onChange: function (selectedDates) {
            const startDate = selectedDates[0];
            endDatePicker.set('minDate', startDate);
            updateElectionStatus(startDatePicker.input.value, endDatePicker.input.value, document.getElementById('election_status'));
        }
    });

    const endDatePicker = flatpickr("#end_date", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: new Date(),
        onChange: function () {
            updateElectionStatus(startDatePicker.input.value, endDatePicker.input.value, document.getElementById('election_status'));
        }
    });


    // Add Election Form Submission
    document.getElementById("addElectionForm").onsubmit = function (e) {
        e.preventDefault();

        const electionType = document.getElementById('election_type').value;
        const restriction = document.getElementById('restriction').value;

        // Validate restriction for Faculty and Program
        if ((electionType === 'Faculty' || electionType === 'Program') && !restriction) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select a restriction for Faculty or Program election types.'
            });
            return;
        }

        const startDate = startDatePicker.input.value;
        const endDate = endDatePicker.input.value;

        if (!startDate || !endDate) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select both start and end dates.'
            });
            return;
        }

        const formData = new FormData(this);
        formData.set('start_date', startDate);
        formData.set('end_date', endDate);

        fetch('/elections', {
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
                    text: 'Election added successfully!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    addElectionModal.style.display = "none";
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Error adding election'
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

    function initEditModal(election) {
        const startDateInput = document.getElementById("edit_start_date");
        const endDateInput = document.getElementById("edit_end_date");
        const statusField = document.getElementById("edit_election_status");

        // Destroy existing flatpickr instances if they exist
        if (startDateInput._flatpickr) startDateInput._flatpickr.destroy();
        if (endDateInput._flatpickr) endDateInput._flatpickr.destroy();

        // Format the dates properly
        const startDate = new Date(election.start_date);
        const endDate = new Date(election.end_date);

        // Initialize end date picker first
        const endDatePickerEdit = flatpickr(endDateInput, {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            defaultDate: endDate,
            onChange: function () {
                updateElectionStatus(startDateInput.value, endDateInput.value, statusField);
            }
        });

        // Initialize start date picker
        flatpickr(startDateInput, {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            defaultDate: startDate,
            onChange: function (selectedDates) {
                const startDate = selectedDates[0];
                endDatePickerEdit.set('minDate', startDate);
                updateElectionStatus(startDateInput.value, endDateInput.value, statusField);
            }
        });

        updateElectionStatus(startDateInput.value, endDateInput.value, statusField);
    }

    
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.onclick = function () {
            const election = JSON.parse(button.getAttribute('data-election'));
            document.getElementById('edit_election_name').value = election.election_name;
            document.getElementById('edit_description').value = election.description;
            document.getElementById('edit_election_type').value = election.election_type;

            if (election.election_type === 'General') {
                loadRestrictions('General', 'None');
            } else {
                loadRestrictions(election.election_type, election.restriction);
            }

            initEditModal(election);
            document.getElementById('editElectionForm').setAttribute('data-election-id', election.election_id);

            editElectionModal.style.display = 'block';
        };
    });

    const closeEditElectionModal = document.getElementById('closeEditElectionModal');
    closeEditElectionModal.onclick = () => {
        editElectionModal.style.display = 'none';
    };
    
    window.onclick = (event) => {
        if (event.target === editElectionModal) {
            editElectionModal.style.display = 'none';
        }
    };
    
    document.getElementById('edit_election_type').addEventListener('change', function() {
        const selectedElectionType = this.value;

        loadRestrictions(selectedElectionType);
    });

    function loadRestrictions(election_type, selectedRestriction = null){
        let restrictions =[];

        switch (election_type) {
            case 'Faculty':
                restrictions = ['FaCET', 'FALS', 'FBM', 'FTED', 'FGCE', 'FNAHS'];
                break;
            case 'Program':
                restrictions = ['BSIT', 'BSCE', 'BSITM', 'BSM', 'BSBIO', 'BSES', 'BSBA', 'BSA'];
                break;
            case 'General':
                restrictions = ['None'];
                break;
        }

        const restrictionDropdown = document.getElementById('edit_restriction');

        restrictionDropdown.innerHTML = '<option value="" disabled selected>Select Restriction</option>';

        restrictions.forEach(restriction => {
            const option = document.createElement('option');
            option.value - restriction;
            option.textContent = restriction;

            if(restriction == selectedRestriction) {
                option.selected = true;
            }

            restrictionDropdown.appendChild(option);
        });
    }

    // Trigger the restriction dropdown to load properly on page load.
    document.getElementById('election_type').dispatchEvent(new Event('change'));

    // Update Election Form Submission
    document.getElementById("editElectionForm").onsubmit = function (e) {
        e.preventDefault();

        const electionId = this.getAttribute('data-election-id');
        const formData = new FormData(this);
        formData.append('_method', 'PUT');

        fetch(`/elections/${electionId}`, {
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
                    text: 'Election updated successfully!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    editElectionModal.style.display = 'none';
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Error updating election'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while updating the election.'
            });
        });
    };
    
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.onclick = function () {
            const electionId = button.getAttribute('data-election-id');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this election?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/elections/${electionId}`, {
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
                                text: 'Election has been deleted.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'Error deleting election'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while deleting the election.'
                        });
                    });
                }
            });
        };
    });     

    function loadRestrictions(electionType, selectedRestriction = null) {
        let restrictions = [];
        if (electionType === 'Faculty') {
            restrictions = ['FaCET', 'FALS', 'FBM', 'FTED', 'FGCE', 'FNAHS'];
        } else if (electionType === 'Program') {
            restrictions = ['BSIT', 'BSCE', 'BSITM', 'BSM', 'BSBIO', 'BSES', 'BSBA', 'BSA'];
        } else if (electionType === 'General') {
            restrictions = ['None'];
        }

        const restrictionDropdown = document.getElementById('edit_restriction');
        restrictionDropdown.innerHTML = '';
        restrictions.forEach(restriction => {
            const option = document.createElement('option');
            option.value = restriction;
            option.textContent = restriction;
            if (restriction === selectedRestriction) {
                option.selected = true;
            }
            restrictionDropdown.appendChild(option);
        });

        if (electionType === 'General') {
            restrictionDropdown.setAttribute('disabled', true);
        } else {
            restrictionDropdown.removeAttribute('disabled');
        }
    }

    function rebindActions() {
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.onclick = function () {
                const election = JSON.parse(button.getAttribute('data-election'));

                // Populate fields with election data
                document.getElementById('edit_election_name').value = election.election_name;
                document.getElementById('edit_description').value = election.description;
                document.getElementById('edit_election_type').value = election.election_type;

                if (election.election_type === 'General') {
                    loadRestrictions('General', 'None');
                } else {
                    loadRestrictions(election.election_type, election.restriction);
                }

                initEditModal(election);

                document.getElementById('edit_election_status').value = election.election_status;

                // Correctly set the election_id in the form
                document.getElementById('editElectionForm').setAttribute('data-election-id', election.election_id); 


                // Display the modal
                editElectionModal.style.display = 'block';
            };
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.onclick = function () {
                const electionId = button.getAttribute('data-election-id');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to delete this election?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/elections/${electionId}`, {
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
                                    text: 'Election has been deleted.',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.message || 'Error deleting election'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An error occurred while deleting the election.'
                            });
                        });
                    }
                });
            };
        });
    }

    rebindActions(); 

        
    function searchElections(query) {
        // Fetch students based on the search query
        fetch(`/elections?search=${query}`)
            .then(response => response.text()) // Expect HTML as the response
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('#election-body').innerHTML;
    
                // Replace the table body with the new filtered data
                document.getElementById('election-body').innerHTML = newTableBody;
                rebindActions();
            })
            .catch(error => {
                console.error('Error fetching elections:', error);
            });
    }
    
    // Attach search function to the input field
    document.getElementById('search').addEventListener('input', function() {
        searchElections(this.value); // Call search function as user types
    });

    updateTableElectionStatuses();
    
});
