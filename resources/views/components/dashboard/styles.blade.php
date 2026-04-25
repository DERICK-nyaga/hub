<style>
    .dashboard-container {
        padding: 20px;
        background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        min-height: 100vh;
    }

    .dashboard-header {
        margin-bottom: 30px;
        color: white;
    }

    .dashboard-header h2 {
        color: white;
    }

    .dashboard-header .text-muted {
        color: rgba(255, 255, 255, 0.8) !important;
    }

    .dashboard-header .btn-outline-secondary {
        border-color: rgba(255, 255, 255, 0.5);
        color: white;
    }

    .dashboard-header .btn-outline-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: white;
    }

    .stat-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
        background: white;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.2);
    }

    .stat-card .card-body {
        padding: 20px;
    }

    .stat-card .card-subtitle {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        color: #666;
    }

    .stat-card h2 {
        font-weight: 700;
        margin-bottom: 5px;
        color: #333;
    }

    .stat-card .card-text small {
        font-size: 12px;
        color: #888;
    }

    .icon-container {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .icon-container i {
        font-size: 24px;
    }

    .progress {
        height: 6px;
        border-radius: 3px;
        background: #f0f0f0;
        margin-top: 15px;
    }

    .progress-bar {
        border-radius: 3px;
    }

    /* Color scheme classes */
    .card-primary .icon-container { background: rgba(66, 133, 244, 0.1); }
    .card-primary .icon-container i { color: #4285f4; }
    .card-primary .progress-bar { background: #4285f4; }

    .card-success .icon-container { background: rgba(52, 168, 83, 0.1); }
    .card-success .icon-container i { color: #34a853; }
    .card-success .progress-bar { background: #34a853; }

    .card-info .icon-container { background: rgba(66, 133, 244, 0.1); }
    .card-info .icon-container i { color: #4285f4; }
    .card-info .progress-bar { background: #4285f4; }

    .card-warning .icon-container { background: rgba(251, 188, 5, 0.1); }
    .card-warning .icon-container i { color: #fbbc05; }
    .card-warning .progress-bar { background: #fbbc05; }

    .card-danger .icon-container { background: rgba(234, 67, 53, 0.1); }
    .card-danger .icon-container i { color: #ea4335; }
    .card-danger .progress-bar { background: #ea4335; }

    .card-purple .icon-container { background: rgba(123, 31, 162, 0.1); }
    .card-purple .icon-container i { color: #7b1fa2; }
    .card-purple .progress-bar { background: #7b1fa2; }

    .card-teal .icon-container { background: rgba(0, 150, 136, 0.1); }
    .card-teal .icon-container i { color: #009688; }
    .card-teal .progress-bar { background: #009688; }

    .card-indigo .icon-container { background: rgba(57, 73, 171, 0.1); }
    .card-indigo .icon-container i { color: #3949ab; }
    .card-indigo .progress-bar { background: #3949ab; }

    .card-cyan .icon-container { background: rgba(0, 188, 212, 0.1); }
    .card-cyan .icon-container i { color: #00bcd4; }
    .card-cyan .progress-bar { background: #00bcd4; }

    .card-orange .icon-container { background: rgba(255, 152, 0, 0.1); }
    .card-orange .icon-container i { color: #ff9800; }
    .card-orange .progress-bar { background: #ff9800; }

    .card-gray .icon-container { background: rgba(117, 117, 117, 0.1); }
    .card-gray .icon-container i { color: #757575; }
    .card-gray .progress-bar { background: #757575; }

    .quick-actions-card, .table-container, .card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 12px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        backdrop-filter: blur(10px);
        border: none;
    }

    .card-header {
        background: transparent;
        border-bottom: 1px solid rgba(0,0,0,0.1);
        padding: 15px 20px;
    }

    .card-header h5 {
        margin: 0;
        color: #333;
    }

    .card-body {
        padding: 20px;
    }

    .search-box {
        position: relative;
        width: 300px;
    }

    .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
        z-index: 10;
    }

    .search-box .form-control {
        padding-left: 40px;
        border-radius: 20px;
        border: 1px solid rgba(0,0,0,0.1);
        background: rgba(255, 255, 255, 0.9);
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #333;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
        background: rgba(0,0,0,0.02);
    }

    .table td {
        vertical-align: middle;
        padding: 12px 8px;
        border-color: rgba(0,0,0,0.05);
    }

    .badge {
        font-weight: 500;
        padding: 5px 10px;
        border-radius: 20px;
    }

    .badge.bg-success { background: #34a853 !important; }
    .badge.bg-danger { background: #ea4335 !important; }
    .badge.bg-warning { background: #fbbc05 !important; color: #212529; }
    .badge.bg-info { background: #4285f4 !important; }
    .badge.bg-primary { background: #4285f4 !important; }
    .badge.bg-secondary { background: #757575 !important; }

    .list-group-item {
        border-left: none;
        border-right: none;
        padding: 12px 15px;
        background: transparent;
        border-color: rgba(0,0,0,0.05);
    }

    .list-group-item:first-child { border-top: none; }
    .list-group-item:last-child { border-bottom: none; }

    .list-group-item-danger {
        background-color: rgba(234, 67, 53, 0.05);
        border-color: rgba(234, 67, 53, 0.2);
    }

    .list-group-item-warning {
        background-color: rgba(251, 188, 5, 0.05);
        border-color: rgba(251, 188, 5, 0.2);
    }

    .notification-stats .p-3 {
        background: white;
        border-radius: 8px;
        transition: transform 0.3s ease;
    }

    .notification-stats .p-3:hover {
        transform: translateY(-2px);
    }

    .notification-stats .bg-danger {
        background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%) !important;
        color: white;
    }

    @media (max-width: 768px) {
        .dashboard-container { padding: 10px; }
        .search-box { width: 100%; margin-top: 10px; }
        .stat-card { margin-bottom: 15px; }
    }
</style>
