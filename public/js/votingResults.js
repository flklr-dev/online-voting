document.addEventListener("DOMContentLoaded", function () {
    const resultsChartCtx = document.getElementById("resultsChart").getContext("2d");
    let resultsChart;

    // Initialize default chart
    function initializeDefaultChart() {
        if (resultsChart) {
            resultsChart.destroy();
        }

        resultsChart = new Chart(resultsChartCtx, {
            type: 'bar',
            data: {
                labels: ['No data'],
                datasets: [{
                    label: 'Total Votes',
                    data: [0],
                    backgroundColor: '#d3d3d3',
                    borderColor: '#b3b3b3',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Select an election and position to view results'
                    }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Fetch positions for a specific election
    function fetchPositions(electionId) {
        fetch(`/elections/${electionId}/positions`)
            .then(response => response.json())
            .then(data => {
                const positionSelect = document.getElementById("positionSelect");
                positionSelect.innerHTML = '<option value="">--Select a Position--</option>'; // Clear existing options

                // Populate positions in dropdown
                data.forEach(position => {
                    const option = document.createElement("option");
                    option.value = position.position_id;
                    option.textContent = position.position_name;
                    positionSelect.appendChild(option);
                });
            })
            .catch(error => console.error("Error fetching positions:", error));
    }

    // Fetch voting results based on selected election and position
    function fetchResults(electionId, positionId) {
        fetch(`/voting-results?election_id=${electionId}&position_id=${positionId}`)
            .then(response => response.json())
            .then(data => {
                const candidateNames = data.map(item => item.candidate_name);
                const votes = data.map(item => item.total_votes);

                updateChart(candidateNames, votes);
            })
            .catch(error => {
                console.error("Error fetching voting results:", error);
                initializeDefaultChart();
            });
    }

    function updateChart(labels, data) {
        if (resultsChart) {
            resultsChart.destroy();
        }

        resultsChart = new Chart(resultsChartCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Votes',
                    data: data,
                    backgroundColor: '#4CAF50',
                    borderColor: '#388E3C',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Event listeners for election and position select
    document.getElementById("electionSelect").addEventListener("change", function () {
        const electionId = this.value;
        if (electionId) {
            fetchPositions(electionId);
            initializeDefaultChart(); // Reset chart if only election is selected
        }
    });

    document.getElementById("positionSelect").addEventListener("change", function () {
        const positionId = this.value;
        const electionId = document.getElementById("electionSelect").value;
        if (electionId && positionId) {
            fetchResults(electionId, positionId);
        } else {
            initializeDefaultChart();
        }
    });

    initializeDefaultChart();
});
