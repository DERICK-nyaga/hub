<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <h1>Dashboard</h1>
    <div id="dashboard">Loading...</div>

    <script>
        axios.get('/api/dashboard')
            .then(response => {
                const data = response.data.data;
                let html = `
                    <h2>Stats</h2>
                    <p>Total Stations: ${data.stats.total_stations}</p>
                    <p>Overdue Internet: ${data.stats.internet_overdue}</p>
                    <p>Active Airtime: ${data.stats.airtime_active}</p>
                    <p>Expiring Airtime: ${data.stats.airtime_expiring_soon}</p>

                    <h2>Top Station</h2>
                    <p>${data.stats.top_station} - KSh ${data.stats.top_station_total}</p>
                `;
                document.getElementById('dashboard').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('dashboard').innerHTML = 'Error: ' + error.message;
            });
    </script>
</body>
</html>
