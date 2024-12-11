document.addEventListener('DOMContentLoaded', function() {
    let inactivityTimeout = 5 * 60; // 5 minutes in seconds
    let warningTime = 30; // Show warning 30 seconds before timeout
    let timer;

    function resetTimer() {
        clearTimeout(timer);
        timer = setTimeout(showWarning, (inactivityTimeout - warningTime) * 1000);
    }

    function showWarning() {
        Swal.fire({
            title: 'Session Expiring!',
            html: 'Your session will expire in <b>30</b> seconds.<br>Do you want to stay logged in?',
            icon: 'warning',
            timer: warningTime * 1000,
            timerProgressBar: true,
            showCancelButton: true,
            confirmButtonText: 'Yes, keep me logged in',
            cancelButtonText: 'Logout now',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Refresh the session
                fetch('/refresh-session')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            resetTimer();
                            Swal.fire('Session Extended', 'Your session has been extended.', 'success');
                        }
                    });
            } else {
                window.location.href = '/logout';
            }
        });
    }

    // Reset timer on user activity
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(evt => 
        document.addEventListener(evt, resetTimer)
    );

    // Start the initial timer
    resetTimer();
}); 