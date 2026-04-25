<?php

namespace App\Controllers;

use App\Models\Employee;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('station')
            ->latest()
            ->paginate(10);

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $stations = Station::all();
        $statuses = ['active', 'on_leave', 'terminated'];
        return view('employees.create', compact('stations', 'statuses'));
    }

    public function store(Request $request)
    {
        $formFields = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees',
            'phone' => 'required|string|unique:employees|max:20',
            'station_id' => 'required|exists:stations,station_id',
            'employee_id' => 'required|string|unique:employees|min:7|max:12',
            'position' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'status' => ['required', Rule::in(['active', 'on_leave', 'terminated'])]
        ]);

        Employee::create($formFields);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully!');
    }

    public function edit(Employee $employee)
    {
        $stations = Station::all();
        $statuses = ['active', 'on_leave', 'terminated'];
        return view('employees.edit', compact('employee', 'stations', 'statuses'));
    }

    public function update(Request $request, Employee $employee)
    {
        $formFields = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('employees')->ignore($employee->id)],
            'phone' => 'required|string|max:20',
            'station_id' => 'required|exists:stations,station_id',
            'position' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'status' => ['required', Rule::in(['active', 'on_leave', 'terminated'])],
            'deduction_balance' => 'required|numeric|min:0'
        ]);
        logger('Update Data:', $formFields);
        logger('Employee Before Update:', $employee->toArray());

        $employee->update($formFields);

            logger('Employee After Update:', $employee->fresh()->toArray());

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully!');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully!');
    }

    public function show(Employee $employee)
    {
        return view('employees.show', compact('employee'));
    }

    public function deductions(Employee $employee)
    {
        $employee->load('deductions');
        return view('employees.deductions', compact('employee'));
    }
}
