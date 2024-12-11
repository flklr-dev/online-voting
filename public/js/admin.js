function validatePassword(password) {
    const minLength = 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /\d/.test(password);
    const hasSpecialChar = /[@$!%*#?&]/.test(password);

    if (password.length < minLength) return "Password must be at least 8 characters long";
    if (!hasUpperCase) return "Password must contain at least one uppercase letter";
    if (!hasLowerCase) return "Password must contain at least one lowercase letter";
    if (!hasNumbers) return "Password must contain at least one number";
    if (!hasSpecialChar) return "Password must contain at least one special character (@$!%*#?&)";
    
    return null;
}

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

        const password = document.getElementById('password').value;
        const passwordError = validatePassword(password);

        if (passwordError) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Password',
                text: passwordError
            });
            return;
        }

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
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Admin added successfully!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    addAdminModal.style.display = "none";
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Error adding admin'
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
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Admin updated successfully!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    document.getElementById('editAdminModal').style.display = 'none';
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Error updating admin'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while updating the admin.'
            });
        });
    };
    

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.onclick = function () {
            const adminId = button.getAttribute('data-admin-id');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this admin?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
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
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Admin has been deleted.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'Error deleting admin'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while deleting the admin.'
                        });
                    });
                }
            });
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
