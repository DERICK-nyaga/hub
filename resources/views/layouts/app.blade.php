<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pickup Points - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            {{-- @auth --}}
                @include('partials.sidebar')
             {{-- @endauth --}}

             
            <div class="col-md-10 main-content">
                @yield('content')
            </div>
        </div>
    </div>

    @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/css/employee-create.css', 'resources/css/fixedstyles.css', 'resources/css/modifiedstyles.css', 'resources/css/order-numbers.css', 'resources/js/payments.js', 'resources/js/app.js', 'resources/js/employee-balance.js', 'resources/js/deductions.js', 'resources/js/employee-statuses.js', 'resources/js/airtime.js'])
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
