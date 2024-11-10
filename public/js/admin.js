document.addEventListener("DOMContentLoaded", function () {
    // Handle Add Admin Modal Opening and Closing
    const addAdminModal = document.getElementById('addAdminModal');
    const openAddAdminModal = document.getElementById('openAddAdminModal');
    const closeAddAdminModal = document.getElementById('closeAddAdminModal');

    openAddAdminModal.onclick = () => {
        addAdminModal.style.display = 'block';
    };

    closeAddAdminModal.onclick = () => {
        addAdminModal.style.display = 'none';
    };

    window.onclick = function (event) {
        if (event.target === addAdminModal) {
            addAdminModal.style.display = 'none';
        }
    };

    // Add Admin Form Submission
    document.getElementById("addForm").onsubmit = function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('/admins', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Admin added successfully!");
                addAdminModal.style.display = "none";
                location.reload();
            } else {
                alert("Error adding admin: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred. Please try again.");
        });
    };

    // Handle Edit Admin Modal
    const editAdminModal = document.getElementById('editAdminModal');
    
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.onclick = function () {
            const adminId = button.getAttribute('data-admin-id');
            
            // Fetch the admin data (except the password)
            fetch(`/admins/${adminId}/edit`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit_username').value = data.admin.username;
    
                        // Do not try to fill the password field, let the user set it if needed
                        document.getElementById('edit_password').value = '';
    
                        // Set the form action to point to the correct admin
                        document.getElementById('editForm').action = `/admins/${adminId}`;
    
                        // Show the Edit Admin modal
                        document.getElementById('editAdminModal').style.display = 'block';
                    } else {
                        alert("Error fetching admin data: " + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching admin:', error);
                    alert("An error occurred while fetching admin data.");
                });
        };
    });    
    
    const closeEditAdminModal = document.getElementById('closeEditAdminModal');
    closeEditAdminModal.onclick = () => {
        editAdminModal.style.display = 'none';
    };
    
    window.onclick = (event) => {
        if (event.target === editAdminModal) {
            editAdminModal.style.display = 'none';
        }
    };

    document.getElementById("editForm").onsubmit = function (e) {
        e.preventDefault();
    
        const formData = new FormData(this);
        const adminId = document.getElementById('editForm').action.split('/').pop();
    
        fetch(`/admins/${adminId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Admin updated successfully!");
                document.getElementById('editAdminModal').style.display = 'none';
                location.reload();
            } else {
                alert("Error updating admin: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred. Please try again.");
        });
    };
    

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.onclick = function () {
            const adminId = button.getAttribute('data-admin-id');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
            if (confirm('Are you sure you want to delete this admin?')) {
                fetch(`/admins/${adminId}`, {
                    method: 'DELETE', // Ensure DELETE method is used
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Admin deleted successfully!");
                        location.reload();
                    } else {
                        alert("Error deleting admin: " + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("An error occurred while deleting the admin.");
                });
            }
        };
    });
    
    function searchAdmins(query) {
        // Fetch admins based on the search query
        fetch(`/admins?search=${query}`)
            .then(response => response.text()) // Expect HTML as the response
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('#admin-body').innerHTML;
    
                // Replace the table body with the new filtered data
                document.getElementById('admin-body').innerHTML = newTableBody;
            })
            .catch(error => {
                console.error('Error fetching admins:', error);
            });
    }

    // Attach the search function to the input field
    document.getElementById('search').addEventListener('input', function() {
        searchAdmins(this.value);
    });

});
