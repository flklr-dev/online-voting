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

    document.getElementById('election_type').addEventListener('change', function(){
        const election_type = this.value;
        const restrictionDropdown = document.getElementById('restriction');

        let restrictions = [];

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

        restrictionDropdown.innerHTML = '<option value="" disabled selected>Select Restriction</option>';

        restrictions.forEach(function (restriction) {
            let option = document.createElement('option');
            option.value = restriction;
            option.textContent = restriction;
            restrictionDropdown.appendChild(option);
        })
    })

    // Get current date and time
    const currentDate = new Date();

    // Initialize Flatpickr for the start date with restriction (No past dates allowed)
    const startDatePicker = flatpickr("#start_date", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: currentDate, // Prevent selecting past dates for start date
        onChange: function(selectedDates) {
            const startDate = selectedDates[0];
            endDatePicker.set('minDate', startDate); // Set minimum date for end date
        }
    });

    const endDatePicker = flatpickr("#end_date", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        minDate: currentDate,
    });

    // Add Election Form Submission
    document.getElementById("addElectionForm").onsubmit = function (e) {
        e.preventDefault();

        const startDate = startDatePicker.input.value;
        const endDate = endDatePicker.input.value;

        if (!startDate || !endDate) {
            alert('Please select both start and end dates.');
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
                alert("Election added successfully!");
                addElectionModal.style.display = "none";
                location.reload();
            } else {
                alert("Error adding election: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred. Please try again.");
        });
    };

    // Handle Edit Election Modal
    const editElectionModal = document.getElementById('editElectionModal');

    function initEditModal(election) {
        const startDateInput = document.getElementById("edit_start_date");
        const endDateInput = document.getElementById("edit_end_date");
    
        // Clear any existing Flatpickr instances before creating a new one
        if (startDateInput._flatpickr) startDateInput._flatpickr.destroy();
        if (endDateInput._flatpickr) endDateInput._flatpickr.destroy();
    
        flatpickr(startDateInput, {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            defaultDate: election.start_date.replace(' ', 'T') // Ensure the date format is ISO-like
        });
    
        flatpickr(endDateInput, {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            defaultDate: election.end_date.replace(' ', 'T') // Ensure the date format is ISO-like
        });
    }
    
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
            console.log("Editing Election ID:", election.election_id); // Debug to ensure it's correct
    
            // Display the modal
            document.getElementById('editElectionModal').style.display = 'block';
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

      document.getElementById("editElectionForm").onsubmit = function (e) {
        e.preventDefault();
    
        const electionId = this.getAttribute('data-election-id');
        if (!electionId) {
            alert('Election ID is missing!'); // Error handling if ID is undefined
            return;
        }
    
        const formData = new FormData(this);
    
        const electionType = document.getElementById('edit_election_type').value;
        const restriction = document.getElementById('edit_restriction').value;
    
        if (electionType === 'General' && restriction === 'None') {
            formData.set('restriction', '');
        }
    
        formData.set('election_status', document.getElementById('edit_election_status').value);
    
        fetch(`/elections/${electionId}`, {
            method: 'POST', // Use POST instead of PUT
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData,
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error: ${response.status} ${response.statusText}`);
            }
            return response.json(); // Try parsing as JSON
        })
        .then(data => {
            if (data.success) {
                alert("Election updated successfully!");
                document.getElementById('editElectionModal').style.display = 'none';
                location.reload();
            } else {
                alert("Error updating election: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred. Please try again.");
        });
    };
    
    

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.onclick = function () {
            const electionId = button.getAttribute('data-election-id');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
            if (confirm('Are you sure you want to delete this election?')) {
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
                        alert("Election deleted successfully!");
                        location.reload();
                    } else {
                        alert("Error deleting election: " + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("An error occurred while deleting the election.");
                });
            }
        };
    });     

        
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
            })
            .catch(error => {
                console.error('Error fetching elections:', error);
            });
    }
    
    // Attach search function to the input field
    document.getElementById('search').addEventListener('input', function() {
        searchElections(this.value); // Call search function as user types
    });
    
});
