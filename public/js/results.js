// Select the chart canvas
const ctx = document.getElementById('resultsChart').getContext('2d');

// Initialize the chart
const resultsChart = new Chart(ctx, {
    type: 'bar', // or 'pie'
    data: {
        labels: ['Candidate 1', 'Candidate 2', 'Candidate 3'], // Replace with dynamic data
        datasets: [{
            label: 'Votes Cast',
            data: [12, 19, 3], // Replace with dynamic data
            backgroundColor: [
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true, // Ensure the chart is responsive
        maintainAspectRatio: false, // Disable aspect ratio to make it fill container
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Function to resize chart
function resizeChart() {
    resultsChart.resize(); // Resize chart dynamically
}

// Listen for the sidebar toggle and resize the chart
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    sidebar.classList.toggle('minimized');
    mainContent.classList.toggle('minimized');

    // Add a slight delay before resizing the chart to ensure layout changes are done
    setTimeout(resizeChart, 100);
}
