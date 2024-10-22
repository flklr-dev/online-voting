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
    
    // Function to open the Edit Admin modal
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.onclick = function () {
            const adminId = button.getAttribute('data-id');
            
            // Fetch the admin data
            fetch(`/admins/${adminId}/edit`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate the edit form with existing data
                        document.getElementById('edit_username').value = data.admin.username;
                        // Display the decrypted password (assuming it's returned by the backend)
                        document.getElementById('edit_password').value = data.admin.password;

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
        const adminId = document.getElementById('edit_admin_id').value;

        fetch(`/admins/${adminId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Admin updated successfully!");
                editAdminModal.style.display = 'none';
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
            const adminId = button.getAttribute('data-id');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
            if (confirm('Are you sure you want to delete this admin?')) {
                fetch(`/admins/${adminId}`, {
                    method: 'DELETE',
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
        fetch(`/admins?search=${query}`)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('#admin-body').innerHTML;
                document.getElementById('admin-body').innerHTML = newTableBody;
            })
            .catch(error => {
                console.error('Error fetching admins:', error);
            });
    }
    

    document.getElementById('search').addEventListener('input', function() {
        searchAdmins(this.value);
    });
});
