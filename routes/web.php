<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Controllers\DashboardController;
use App\Controllers\StationController;
use App\Controllers\EmployeeController;
use App\Controllers\LossController;
// use App\Controllers\InternetPaymentController;
use App\Controllers\PaymentController;
use App\Controllers\VendorController;
use App\Controllers\VendorCategoryController;
use App\Controllers\UserController;
use App\Controllers\Auth\LoginController;
use App\Controllers\Auth\RegisterController;
use App\Controllers\Auth\ForgotPasswordController;
use App\Controllers\Auth\ResetPasswordController;
use App\Controllers\ReportController;
use App\Controllers\DeductionTransactionController;
use App\Controllers\OrderNumberController;
use App\Controllers\InternetProviderController;
use App\Controllers\NotificationController;
use App\Controllers\PendingApprovalController;
use App\Controllers\EmployeeProfileController;


Route::resource('order-numbers', OrderNumberController::class);

// Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/sidebar', [DashboardController::class, 'sidebar']);

    Route::get('/employee-profile', [EmployeeProfileController::class, 'index'])->name('employees_profile.index');
    Route::get('/employee-profile/create', [EmployeeProfileController::class, 'create'])->name('employees_profile.create');
    Route::post('/employee-profile', [EmployeeProfileController::class, 'store'])->name('employees_profile.store');
    Route::get('/employee-profile/{employee}', [EmployeeProfileController::class, 'show'])->name('employees_profile.show');
    Route::get('/employee-profile/{employee}/edit', [EmployeeProfileController::class, 'edit'])->name('employee_profile.edit');
    Route::put('/employee-profile/{employee}', [EmployeeProfileController::class, 'update'])->name('employees_profile.update');
    Route::get('/employee-profile/{employee}', [EmployeeProfileController::class, 'show'])->name('employee_profile.show');
    Route::delete('/employee-profile/{employee}', [EmployeeProfileController::class, 'destroy'])->name('employees_profile.destroy');

    Route::post('/employees-profile/{employee}/qualifications', [EmployeeProfileController::class, 'addQualification'])
        ->name('employees.add-qualification');

    Route::patch('/employees-profile/{employee}/terminate', [EmployeeProfileController::class, 'terminate'])
        ->name('employees.terminate');

    Route::get('/documents/{document}/download', [EmployeeProfileController::class, 'downloadDocument'])
        ->name('employees.download');

    Route::get('/documents/{document}/view', [EmployeeProfileController::class, 'viewDocument'])
        ->name('employees.view');

    Route::get('/employees/{employee}/change-history', [EmployeeProfileController::class, 'changeHistory'])
        ->name('employee.change_history');

    Route::post('/employee-change-logs/{changeLog}/approve', [EmployeeProfileController::class, 'approveChange'])
        ->name('employee_profile.approve_change')
        ->middleware('can:approve,changeLog');

    Route::post('/employee-change-logs/{changeLog}/reject', [EmployeeProfileController::class, 'rejectChange'])
        ->name('employee_profile.reject_change')
        ->middleware('can:approve,changeLog');

    // Route::get('/approvals/pending', [ApprovalController::class, 'pending'])
    //     ->name('approvals.pending');

    // Route::prefix('approvals')->name('approvals.')->group(function () {
    // Route::get('/dashboard', [ApprovalController::class, 'dashboard'])->name('dashboard');
    // Route::get('/pending', [ApprovalController::class, 'pending'])->name('pending');
    // Route::get('/bulk-actions', [ApprovalController::class, 'bulkActions'])->name('bulk_actions');
    // Route::post('/bulk-approve', [ApprovalController::class, 'bulkApprove'])->name('bulk_approve');
    // Route::post('/bulk-reject', [ApprovalController::class, 'bulkReject'])->name('bulk_reject');
    // Route::get('/reports', [ApprovalController::class, 'reports'])->name('reports');
    // Route::get('/export-report', [ApprovalController::class, 'exportReport'])->name('export_report');
    // Route::get('/timeline-analytics', [ApprovalController::class, 'timelineAnalytics'])->name('timeline_analytics');
    // });

// Route::middleware('auth')->group(function () {
//     require __DIR__.'/web/approvals.php';
// });

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,1');

    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

    // Route::middleware(['auth'])->group(function () {
        Route::get('/logout', function () {
            Auth::logout();
            return redirect('/login')->with('status', 'You have been logged out.');
        })->name('logout');

    Route::controller(ReportController::class)->group(function () {
        // User-facing
        Route::get('/reports/index', 'index')->name('reports.index');
        Route::get('/reports/create', 'create')->name('reports.create');
        Route::post('/reports', 'store')->name('reports.store');

        // Admin management
        Route::get('/handlereports', 'handleReports')->name('reports.handle');

        Route::get('/reports/{report}', 'show')->name('reports.show');
        Route::get('/reports/{report}/edit', 'edit')->name('reports.edit');
        Route::put('/reports/{report}', 'update')->name('reports.update');
        Route::delete('/reports/{report}', 'destroy')->name('reports.destroy');
        Route::get('/reports/{report}/download', 'download')->name('reports.download');

        Route::get('/approve', 'approve')->name('approve');
        Route::get('/masters', 'masters')->name('ViewAll');
        Route::get('/clear-reports', 'clearReports')->name('clear.reports');
    });

    //bulk actions
    Route::post('/reports/bulk-actions', [ReportController::class, 'bulkActions'])->name('reports.bulk');
    Route::post('/reports/{report}/approve', [ReportController::class, 'approve'])->name('reports.approve');
    Route::post('/reports/{report}/reject', [ReportController::class, 'reject'])->name('reports.reject');

    Route::resource('/deductions', DeductionTransactionController::class)->names([
        'index' => 'deductions.index',
        'create' => 'deductions.create',
        'show' => 'deductions.show',
        'store' => 'deductions.store',
        'edit' => 'deductions.edit',
        'update' => 'deductions.update',
        'destroy' => 'deductions.destroy',
    ]);

    Route::get('/deductions/employee/{employeeId}', [DeductionTransactionController::class, 'employeeTransactions'])->name('deductions.employee-transactions');
    Route::get('/deductions/balance/{employeeId}', [DeductionTransactionController::class, 'getCurrentBalance'])->name('deductions.balance');
    Route::get('/employees/{employeeId}/deduction-history', [DeductionTransactionController::class, 'employeeHistory'])->name('employees.deduction-history');
    Route::get('/deductions/get-balance/{employeeId}', [DeductionTransactionController::class, 'getCurrentBalance'])->name('deductions.get-balance');

    Route::get('/employees/search', [EmployeeController::class, 'search'])->name('employees.search');

    Route::resource('internet-providers', InternetProviderController::class);

    Route::prefix('payments')->group(function () {

    Route::get('/index', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/internet/create', [PaymentController::class, 'createInternetPayment'])
        ->name('payments.internet.create');
    Route::post('/internet', [PaymentController::class, 'storeInternetPayment'])
        ->name('payments.internet.store');

    Route::get('/internet/create', [PaymentController::class, 'createInternetPayment'])
            ->name('payments.internet.create');
        Route::post('/internet', [PaymentController::class, 'storeInternetPayment'])
            ->name('payments.internet.store');
        Route::get('/internet', [PaymentController::class, 'indexInternetPayments'])
            ->name('payments.internet.index');
        Route::get('/internet/{id}/edit', [PaymentController::class, 'editInternetPayment'])
            ->name('payments.internet.edit');
        Route::put('/internet/{id}', [PaymentController::class, 'updateInternetPayment'])
            ->name('payments.internet.update');
        Route::get('/internet/{id}', [PaymentController::class, 'showInternetPayment'])
            ->name('payments.internet.show');
        Route::delete('/internet/{id}', [PaymentController::class, 'destroyInternetPayment'])
            ->name('payments.internet.destroy');
                Route::get('/airtime/{id}/renew', [PaymentController::class, 'renewAirtimePayment'])
            ->name('payments.airtime.renew');

        Route::delete('/airtime/{id}', [PaymentController::class, 'destroyAirtimePayment'])
            ->name('payments.airtime.delete');

        Route::get('/airtime/create', [PaymentController::class, 'createAirtimePayment'])
            ->name('payments.airtime.create');
        Route::post('/airtime', [PaymentController::class, 'storeAirtimePayment'])
            ->name('payments.airtime.store');
        Route::get('/airtime', [PaymentController::class, 'indexAirtimePayments'])
            ->name('payments.airtime.index');
                Route::get('/airtime/{id}/details', [PaymentController::class, 'showAirtimeDetails'])
            ->name('payments.airtime.details');
        Route::delete('/payments/airtime/{id}', [PaymentController::class, 'destroyAirtimePayment'])->name('payments.airtime.delete');
        Route::get('/upcoming', [PaymentController::class, 'upcomingPayments'])
            ->name('payments.upcoming');
        Route::get('/overdue', [PaymentController::class, 'overduePayments'])
            ->name('payments.overdue');
        Route::get('/station/{stationId}', [PaymentController::class, 'stationPayments'])
            ->name('payments.station');
    });

    // API Routes for badge counts
    Route::prefix('api')->group(function () {
        Route::get('/payments/upcoming-count', [PaymentController::class, 'getUpcomingCount']);
        Route::get('/payments/overdue-count', [PaymentController::class, 'getOverdueCount']);
    });

    Route::resource('payments', PaymentController::class)->except(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('/payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{payment}/mark-as-paid', [PaymentController::class, 'markAsPaid'])->name('payments.mark-as-paid');

    // Report Routes
    Route::prefix('reports')->group(function () {
        Route::get('/monthly', [ReportController::class, 'monthlyReport'])
            ->name('reports.monthly');
        Route::get('/station/{stationId}', [ReportController::class, 'stationReport'])
            ->name('reports.station');
        Route::get('/export', [ReportController::class, 'exportReport'])
            ->name('reports.export');
    });

    Route::resource('/stations', StationController::class)->names([
        'index' => 'stations.index',
        'create' => 'stations.create',
        'edit' => 'stations.edit',
        'show' => 'stations.show',
    ]);

    Route::resource('/employees', EmployeeController::class)->names([
        'index' => 'employees.index',
        'create' => 'employees.create',
        'edit' => 'employees.edit',
        'show' => 'employees.show',
    ]);

    Route::get('/employees/{id}', function($id) {
    $employee = \App\Models\Employee::findOrFail($id);
    return response()->json(['success' => true, 'data' => $employee]);
})->name('employee.show');

    Route::resource('/losses', LossController::class)->names([
        'index' => 'losses.index',
        'create' => 'losses.create',
        'stationLosses' => 'losses.station',
        'show' => 'losses.show',
        'edit' => 'loss.edit'
    ]);

    Route::resource('vendors', VendorController::class);
    Route::get('vendors/{vendor}/download-contract', [VendorController::class, 'downloadContract'])->name('vendors.contract.download');

    Route::resource('vendor-categories', VendorCategoryController::class);
    Route::resource('users', UserController::class);
// });

    Route::middleware(['auth', 'role:manager'])->group(function () {
        Route::get('reports', [ReportController::class, 'index']);
    });

    Route::get('/test-deductions/{station_id}', function($station_id) {
        $station = \App\Models\Station::find($station_id);

        if (!$station) {
            return "Station not found";
        }

        echo "<h1>Station: {$station->name}</h1>";
        echo "<h2>Employees: {$station->employees->count()}</h2>";

        foreach ($station->employees as $employee) {
            echo "<h3>Employee: {$employee->full_name} (ID: {$employee->employee_id})</h3>";

            // Check deductions count
            $deductionsCount = \App\Models\DeductionTransaction::where('employee_id', $employee->employee_id)->count();
            echo "Deductions count: " . $deductionsCount . "<br>";

            // actual deductions
            $deductions = \App\Models\DeductionTransaction::where('employee_id', $employee->employee_id)->get();
            if ($deductions->count() > 0) {
                echo "<table border='1'><tr><th>Date</th><th>Reason</th><th>Amount</th></tr>";
                foreach ($deductions as $deduction) {
                    echo "<tr>";
                    echo "<td>{$deduction->transaction_date}</td>";
                    echo "<td>{$deduction->reason}</td>";
                    echo "<td>{$deduction->amount}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "No deductions found<br>";
            }
            echo "<hr>";
        }

        return "";
    });
// Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/check-expiries', [NotificationController::class, 'checkExpiries'])->name('notifications.check-expiries');
    Route::post('/notifications/cleanup', [NotificationController::class, 'cleanupOld'])->name('notifications.cleanup');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
// });

    Route::post('/payments/internet/{id}/mpesa-payment', [PaymentController::class, 'initiateMpesaPayment'])
        ->name('payments.internet.mpesa-payment');
    Route::get('/payments/internet/check-transaction/{transactionId}', [PaymentController::class, 'checkMpesaTransaction'])
        ->name('payments.internet.check-transaction');
    Route::post('/mpesa/callback', [PaymentController::class, 'mpesaCallback'])
        ->name('mpesa.callback');

        Route::prefix('pending-approvals')->middleware(['auth'])->group(function () {
    Route::get('/', [PendingApprovalController::class, 'index'])->name('pending-approvals.index');
    Route::post('/{id}/approve', [PendingApprovalController::class, 'approve'])->name('pending-approvals.approve');
    Route::post('/{id}/reject', [PendingApprovalController::class, 'reject'])->name('pending-approvals.reject');
});


Route::get('/test-approval-service', function() {
    try {
        $service = new \App\Services\PendingApprovalService();
        return response()->json([
            'success' => true,
            'requires_approval' => $service->requiresApproval(['basic_salary' => 50000]),
            'message' => 'Service is working'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

Route::get('/', function () {
    return view('welcome');
});

// Payment Schedules Routes
Route::prefix('payments/schedules')->name('payments.schedules.')->group(function () {
    Route::get('/', [PaymentController::class, 'indexSchedules'])->name('index');
    Route::get('/create', [PaymentController::class, 'createSchedule'])->name('create');
    Route::post('/', [PaymentController::class, 'storeSchedule'])->name('store');
    Route::get('/{schedule}', [PaymentController::class, 'showSchedule'])->name('show');
    Route::get('/{schedule}/edit', [PaymentController::class, 'editSchedule'])->name('edit');
    Route::put('/{schedule}', [PaymentController::class, 'updateSchedule'])->name('update');
    Route::delete('/{schedule}', [PaymentController::class, 'destroySchedule'])->name('destroy');

    // Route::get('/station/{stationId}', [PaymentController::class, 'showStationDetails'])->name('station.details');

});

    Route::get('/payments/station-details/{stationId}', [PaymentController::class, 'showStationDetails'])
    ->name('payments.station.details');
    Route::get('/station/{stationId}', [PaymentController::class, 'stationPayments'])->name('station');