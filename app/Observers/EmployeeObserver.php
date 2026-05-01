<?php

namespace App\Observers; 

use App\Models\Employee;
use App\Models\SalaryEmployee;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        $this->syncToSalaryTable($employee);
    }

    /**
     * Handle the Employee "updated" event.
     */
    public function updated(Employee $employee): void
    {
        $this->syncToSalaryTable($employee);
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        SalaryEmployee::where('phone', $employee->phone)->delete();
    }
    
    /**
     * Sync employee data to salary_employees table
     */
    private function syncToSalaryTable(Employee $employee)
    {
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
}