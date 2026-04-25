{{-- <!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card h3 { margin: 0 0 10px 0; color: #333; font-size: 14px; text-transform: uppercase; }
        .stat-value { font-size: 28px; font-weight: bold; color: #007bff; }
        .stat-value.danger { color: #dc3545; }
        .stat-value.success { color: #28a745; }
        .stat-value.warning { color: #ffc107; }
        .section { margin: 30px 0; }
        .section h2 { border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: black; }
        .badge-success { background: #28a745; color: white; }
        .badge-info { background: #17a2b8; color: white; }
    </style>
</head>
<body>
    <div id="app">
        <h1>Dashboard</h1>

        <!-- Stats Grid -->
        <div class="dashboard">
            <div class="stat-card">
                <h3>Total Stations</h3>
                <div class="stat-value">{{ stats.total_stations }}</div>
            </div>
            <div class="stat-card">
                <h3>Total Internet Payments</h3>
                <div class="stat-value">KSh {{ formatMoney(stats.total_internet_payments) }}</div>
            </div>
            <div class="stat-card">
                <h3>Total Airtime Payments</h3>
                <div class="stat-value">KSh {{ formatMoney(stats.total_airtime_payments) }}</div>
            </div>
            <div class="stat-card">
                <h3>Internet Overdue</h3>
                <div class="stat-value danger">{{ stats.internet_overdue }}</div>
            </div>
            <div class="stat-card">
                <h3>Airtime Expiring Soon</h3>
                <div class="stat-value warning">{{ stats.airtime_expiring_soon }}</div>
            </div>
            <div class="stat-card">
                <h3>Active Airtime</h3>
                <div class="stat-value success">{{ stats.airtime_active }}</div>
            </div>
            <div class="stat-card">
                <h3>Internet Due Soon</h3>
                <div class="stat-value warning">{{ stats.internet_due_soon }}</div>
            </div>
            <div class="stat-card">
                <h3>Top Station</h3>
                <div class="stat-value">{{ stats.top_station }}</div>
                <small>KSh {{ formatMoney(stats.top_station_total) }}</small>
            </div>
        </div>

        <!-- Stations -->
        <div class="section">
            <h2>Stations ({{ stations.total }})</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Contact</th>
                        <th>Monthly Loss</th>
                        <th>Airtime</th>
                        <th>Internet</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="station in stations.data" :key="station.station_id">
                        <td>{{ station.name }}</td>
                        <td>{{ station.location }}</td>
                        <td>{{ station.mobile_number }}</td>
                        <td>KSh {{ formatMoney(station.monthly_loss) }}</td>
                        <td>
                            <span v-if="station.airtime_payments.length > 0" class="badge badge-info">
                                {{ station.airtime_payments.length }} active
                            </span>
                            <span v-else class="badge">None</span>
                        </td>
                        <td>
                            <span v-if="station.internet_payments.length > 0" class="badge badge-info">
                                {{ station.internet_payments.length }} pending
                            </span>
                            <span v-else class="badge">None</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Overdue Internet Bills -->
        <div class="section" v-if="overdue_internet.length > 0">
            <h2>Overdue Internet Bills ({{ overdue_internet.length }})</h2>
            <table>
                <thead>
                    <tr>
                        <th>Station</th>
                        <th>Provider</th>
                        <th>Account</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Days Overdue</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="payment in overdue_internet" :key="payment.id">
                        <td>{{ payment.station.name }}</td>
                        <td>{{ payment.provider.name }}</td>
                        <td>{{ payment.account_number }}</td>
                        <td>KSh {{ formatMoney(payment.total_due) }}</td>
                        <td>{{ formatDate(payment.due_date) }}</td>
                        <td>
                            <span class="badge badge-danger">
                                {{ Math.abs(Math.round(payment.days_overdue)) }} days
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-danger">Overdue</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Expiring Airtime -->
        <div class="section" v-if="Object.keys(expiring_airtime).length > 0">
            <h2>Expiring Airtime</h2>
            <div v-for="(items, timeframe) in expiring_airtime" :key="timeframe">
                <h3>{{ timeframe }}</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Station</th>
                            <th>Mobile Number</th>
                            <th>Amount</th>
                            <th>Provider</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items" :key="item.id">
                            <td>{{ item.station.name }}</td>
                            <td>{{ item.mobile_number }}</td>
                            <td>KSh {{ formatMoney(item.amount) }}</td>
                            <td>{{ item.network_provider }}</td>
                            <td>{{ formatDate(item.expected_expiry) }}</td>
                            <td>
                                <span class="badge badge-warning">Expiring</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="section">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <h2>Recent Internet Payments</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Station</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="payment in recent_internet" :key="payment.id">
                                <td>{{ payment.station.name }}</td>
                                <td>KSh {{ formatMoney(payment.total_due) }}</td>
                                <td>{{ formatDate(payment.due_date) }}</td>
                                <td>
                                    <span v-if="payment.is_overdue" class="badge badge-danger">Overdue</span>
                                    <span v-else class="badge badge-warning">Pending</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <h2>Recent Airtime Payments</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Station</th>
                                <th>Mobile</th>
                                <th>Amount</th>
                                <th>Expiry</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="payment in recent_airtime" :key="payment.id">
                                <td>{{ payment.station.name }}</td>
                                <td>{{ payment.mobile_number }}</td>
                                <td>KSh {{ formatMoney(payment.amount) }}</td>
                                <td>{{ formatDate(payment.expected_expiry) }}</td>
                                <td>
                                    <span class="badge badge-success">Active</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        new Vue({
            el: '#app',
            data: {
                stats: {},
                stations: {},
                expiring_airtime: {},
                due_internet: [],
                overdue_internet: [],
                upcoming_internet: [],
                upcoming_airtime: [],
                recent_internet: [],
                recent_airtime: [],
                loading: true,
                error: null
            },
            mounted() {
                this.fetchDashboardData();
            },
            methods: {
                async fetchDashboardData() {
                    try {
                        const response = await axios.get('/api/dashboard');
                        const data = response.data.data;

                        this.stats = data.stats;
                        this.stations = data.stations;
                        this.expiring_airtime = data.expiring_airtime;
                        this.due_internet = data.due_internet;
                        this.overdue_internet = data.overdue_internet;
                        this.upcoming_internet = data.upcoming_internet;
                        this.upcoming_airtime = data.upcoming_airtime;
                        this.recent_internet = data.recent_internet;
                        this.recent_airtime = data.recent_airtime;

                        this.loading = false;
                    } catch (error) {
                        console.error('Error fetching dashboard:', error);
                        this.error = 'Failed to load dashboard data';
                        this.loading = false;
                    }
                },
                formatMoney(amount) {
                    if (!amount) return '0.00';
                    return parseFloat(amount).toLocaleString('en-KE', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },
                formatDate(dateString) {
                    if (!dateString) return 'N/A';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-KE', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                }
            }
        });
    </script>
</body>
</html> --}}
