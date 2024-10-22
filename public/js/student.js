document.addEventListener("DOMContentLoaded", function () {
    // Add Student Modal
    const addStudentModal = document.getElementById('addStudentModal');
    const openAddStudentModal = document.getElementById('openAddStudentModal');
    const closeAddStudentModal = document.querySelector('.close');

    // Open modal
    openAddStudentModal.onclick = () => {
        addStudentModal.style.display = 'block';
    };

    // Close modal
    closeAddStudentModal.onclick = () => {
        addStudentModal.style.display = 'none';
    };

    // Close modal when clicking outside of it
    window.onclick = (event) => {
        if (event.target === addStudentModal) {
            addStudentModal.style.display = 'none';
        }
    };
    // Load programs based on selected faculty
    document.getElementById('faculty').addEventListener('change', function () {
        const faculty = this.value;
        const programDropdown = document.getElementById('program');

        let programs = [];

        switch (faculty) {
            case 'FaCET':
                programs = ['BSIT', 'BSCE', 'BSITM', 'BSM'];
                break;
            case 'FALS':
                programs = ['BSBIO', 'BSES', 'BSA'];
                break;
            case 'FBM':
                programs = ['BSBA', 'BSHM'];
                break;
            case 'FNAHS':
                programs = ['BSN'];
                break;
            case 'FTED':
                programs = ['BEED', 'BSED'];
                break;
            case 'FCGE':
                programs = ['BSC'];
                break;
        }

        // Clear the current options in the program dropdown
        programDropdown.innerHTML = '<option value="" disabled selected>Select Program</option>';

        // Add new options to the dropdown
        programs.forEach(function (program) {
            let option = document.createElement('option');
            option.value = program;
            option.textContent = program; // Use textContent for safer assignment
            programDropdown.appendChild(option);
        });
    });

    // Handle Add Student form submission
    document.getElementById("addStudentForm").onsubmit = function (e) {
        e.preventDefault(); // Prevent default form submission
        var formData = new FormData(this); // Create FormData object

        fetch('/students', { // Adjust the endpoint as necessary
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), // Include CSRF token
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Student added successfully!");
                addStudentModal.style.display = "none"; // Close the modal
                location.reload(); // Refresh the page to see the new student
            } else {
                alert("Error adding student: " + data.message);
            }
        })
        .catch(error => {
            alert("An error occurred while adding the student. Please try again.");
        });
    };
    
    const editStudentModal = document.getElementById('editStudentModal');
    // Handle Edit Student
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.onclick = function () {
            const student = JSON.parse(button.getAttribute('data-student'));

            // Populate the form with student data
            document.getElementById('edit_student_id').value = student.student_id;
            document.getElementById('edit_fullname').value = student.fullname;
            document.getElementById('edit_school_email').value = student.school_email;
            document.getElementById('edit_faculty').value = student.faculty;

            // Load programs based on the selected faculty
            loadPrograms(student.faculty, student.program);

            // Set the student's status (Active/Inactive)
            document.getElementById('edit_status').value = student.status;

            // Show the edit modal
            document.getElementById('editStudentModal').style.display = 'block';

            // Store student ID for update request
            document.getElementById('editStudentForm').setAttribute('data-student-id', student.student_id);
        };
    });

        // Close the Edit modal
        const closeEditModal = document.getElementById('closeEditModal');
        closeEditModal.onclick = () => {
            document.getElementById('editStudentModal').style.display = 'none';
        };
    
         // Close modal when clicking outside of it
         window.onclick = (event) => {
            if (event.target === editStudentModal) {
                editStudentModal.style.display = 'none';
            }
        };
    
        // Handle faculty change in the edit form
        document.getElementById('edit_faculty').addEventListener('change', function () {
            const selectedFaculty = this.value;
    
            // When the faculty changes, reset the program dropdown to "Select Program" and load relevant programs
            loadPrograms(selectedFaculty);
        });
        
    
        function loadPrograms(faculty, selectedProgram = null) {
            let programs = [];
        
            // Define programs for each faculty
            switch (faculty) {
                case 'FaCET':
                    programs = ['BSIT', 'BSCE', 'BSITM'];
                    break;
                case 'FALS':
                    programs = ['BSBIO', 'BSES', 'BSA'];
                    break;
                case 'FBM':
                    programs = ['BSBA', 'BSHM'];
                    break;
                case 'FNAHS':
                    programs = ['BSN'];
                    break;
                case 'FTED':
                    programs = ['BEED', 'BSED'];
                    break;
                case 'FCGE':
                    programs = ['BSC'];
                    break;
            }
        
            // Get the program dropdown element
            const programDropdown = document.getElementById('edit_program');
        
            // Reset the dropdown options to default
            programDropdown.innerHTML = '<option value="" disabled selected>Select Program</option>';
        
            // Populate the dropdown with the programs for the selected faculty
            programs.forEach(program => {
                const option = document.createElement('option');
                option.value = program;
                option.textContent = program;
        
                // If the program matches the selectedProgram (used when loading for existing student), mark it as selected
                if (program === selectedProgram) {
                    option.selected = true;
                }
        
                programDropdown.appendChild(option);
            });
        }
    
        document.getElementById('editStudentForm').onsubmit = function (e) {
            e.preventDefault();
        
            const formData = new FormData(this); // Collect the form data
        
            const originalStudentId = this.getAttribute('data-student-id'); // Get the original student ID
        
            fetch(`/students/${originalStudentId}`, {
                method: 'POST',  // Use PUT or PATCH here based on your route definition
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',  // Ensure JSON response
                },
                body: formData,
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(JSON.stringify(data)); // Handle validation errors
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    alert('Student updated successfully!');
                    document.getElementById('editStudentModal').style.display = 'none';
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error.message);
                    alert('Error: ' + error.message);
                });
        };
        
        
        
        // Handle delete student functionality
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.onclick = function () {
                const studentId = button.getAttribute('data-id');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content'); // CSRF token
    
                // Show a confirmation prompt before deleting
                if (confirm('Are you sure you want to delete this student?')) {
                    fetch(`/students/${studentId}`, { // Ensure this points to the correct route
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken // Include CSRF token
                        }
                    })
                    .then(response => response.json()) // Expecting JSON response
                    .then(data => {
                        if (data.success) {
                            alert("Student deleted successfully!"); // Success message
                            location.reload(); // Refresh to update student list
                        } else {
                            alert("Error deleting student: " + data.message); // Show error message
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert("An error occurred while deleting the student. Please try again.");
                    });
                }
            };
        });
    
        function searchStudents(query) {
            // Fetch students based on the search query
            fetch(`/students?search=${query}`)
                .then(response => response.text()) // Expect HTML as the response
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTableBody = doc.querySelector('#student-body').innerHTML;
        
                    // Replace the table body with the new filtered data
                    document.getElementById('student-body').innerHTML = newTableBody;
                })
                .catch(error => {
                    console.error('Error fetching students:', error);
                });
        }
        
        // Attach search function to the input field
        document.getElementById('search').addEventListener('input', function() {
            searchStudents(this.value); // Call search function as user types
        });
});