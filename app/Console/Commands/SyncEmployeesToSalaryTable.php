<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\SalaryEmployee;
use Illuminate\Console\Command;

class SyncEmployeesToSalaryTable extends Command
{
    protected $signature = 'sync:employees-to-salary';
    protected $description = 'Sync existing employees to salary_employees table';

    public function handle()
    {
        $employees = Employee::all();
        
        foreach ($employees as $employee) {
            SalaryEmployee::updateOrCreate(
                ['phone' => $employee->phone],
                [
                    'name' => $employee->full_name,
                    'phone' => $employee->phone,
                    'position' => $employee->position,
                    'station' => $employee->station?->name ?? 'Main Office',
                    'status' => $employee->status,
                    'base_salary' => $employee->salary,
                    'bank_account' => $employee->bank_account ?? null,
                    'mpesa_number' => $employee->phone,
                ]
            );
        }
        
        $this->info('Successfully synced ' . $employees->count() . ' employees to salary_employees table');
    }
}