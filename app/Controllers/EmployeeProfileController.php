<?php

namespace App\Controllers;

use App\Models\EmployeeProfile;
use App\Models\EmployeeDocument;
use App\Models\EmployeeChangeLog;
use App\Services\EmployeeChangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PendingApproval;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\PendingApprovalService;

class EmployeeProfileController extends Controller
{
    protected $changeService;

    public function __construct(EmployeeChangeService $changeService)
    {
        $this->changeService = $changeService;
    }

    public function index(Request $request)
    {
        $departments = EmployeeProfile::distinct()
            ->whereNotNull('department')
            ->orderBy('department')
            ->pluck('department');

        $query = EmployeeProfile::with(['documents', 'qualifications', 'supervisor']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('department') && !empty($request->department)) {
            $query->where('department', $request->department);
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $employees = $query->orderBy('created_at', 'desc')->paginate(20);

        if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
            return response()->json([
                'success' => true,
                'data' => $employees,
                'filters' => [
                    'departments' => $departments,
                    'statuses' => ['active', 'on_leave', 'terminated', 'suspended']
                ]
            ], 200);
        }

        return view('employees_profile.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $supervisors = EmployeeProfile::active()->get();
        return view('employees_profile.create', compact('supervisors'));
    }


    public function store(Request $request)
    {
        try {
            $validated = $this->validateEmployeeStore($request);

            $employeeId = 'EMP' . date('Y') . Str::random(6);

            $nextOfKin = [
                'name' => $validated['next_of_kin_name'],
                'relationship' => $validated['next_of_kin_relationship'],
                'phone' => $validated['next_of_kin_phone'],
                'address' => $validated['next_of_kin_address']
            ];

            $guarantorDetails = [
                'name' => $validated['guarantor_name'],
                'id_number' => $validated['guarantor_id_number'],
                'phone' => $validated['guarantor_phone'],
                'email' => $validated['guarantor_email'] ?? null,
                'address' => $validated['guarantor_address'],
                'relationship' => $validated['guarantor_relationship']
            ];

            $workSchedule = [
                'monday' => ['start' => '08:00', 'end' => '18:00', 'break' => '13:00-13:20'],
                'tuesday' => ['start' => '08:00', 'end' => '18:00', 'break' => '13:00-13:20'],
                'wednesday' => ['start' => '08:00', 'end' => '18:00', 'break' => '13:00-13:20'],
                'thursday' => ['start' => '08:00', 'end' => '18:00', 'break' => '13:00-13:20'],
                'friday' => ['start' => '08:00', 'end' => '18:00', 'break' => '13:00-13:20'],
                'saturday' => ['start' => '09:00', 'end' => '16:00'],
                'sunday' => ['start' => null, 'end' => null]
            ];

            $allowances = $validated['allowances'] ?? [];
            $deductions = $validated['deductions'] ?? [];

            $employee = EmployeeProfile::create([
                'employee_id' => $employeeId,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'national_id' => $validated['national_id'],
                'passport_number' => $validated['passport_number'] ?? null,
                'passport_expiry' => $request->passport_expiry ?? null,
                'kra_pin' => $validated['kra_pin'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'phone_number' => $validated['phone_number'],
                'personal_email' => $validated['personal_email'] ?? null,
                'company_email' => $validated['company_email'] ?? null,
                'marital_status' => $validated['marital_status'] ?? null,
                'nssf_number' => $validated['nssf_number'] ?? null,
                'nhif_number' => $validated['nhif_number'] ?? null,
                'job_title' => $validated['job_title'],
                'department' => $validated['department'],
                'job_grade' => $validated['job_grade'] ?? null,
                'employment_type' => $validated['employment_type'],
                'reporting_to' => $validated['reporting_to'] ?? null,
                'basic_salary' => $validated['basic_salary'],
                'hire_date' => $validated['hire_date'],
                'contract_start_date' => $validated['contract_start_date'] ?? null,
                'contract_end_date' => $validated['contract_end_date'] ?? null,
                'work_location' => $validated['work_location'],
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account' => $validated['bank_account'] ?? null,
                'bank_branch' => $validated['bank_branch'] ?? null,
                'next_of_kin' => $nextOfKin,
                'guarantor_details' => $guarantorDetails,
                'work_schedule' => $workSchedule,
                'allowances' => $allowances,
                'deductions' => $deductions,
                'status' => 'active',
            ]);

            $this->handleDocumentUploads($request, $employee);

            $successMessage = 'Employee created successfully. Employee ID: ' . $employeeId;

            if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'data' => [
                        'employee' => $employee->load('reportingTo'),
                        'employee_id' => $employeeId
                    ]
                ], 201);
            }

            return redirect()
                ->route('employees_profile.index', $employee->id)
                ->with('success', $successMessage);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create employee',
                    'error' => $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }

    public function show(Request $request, EmployeeProfile $employee)
    {
        $employee->load(['documents', 'qualifications', 'supervisor', 'subordinates']);

        if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
            return response()->json([
                'success' => true,
                'data' => $employee
            ], 200);
        }

        return view('employees_profile.show', compact('employee'));
    }

    public function edit(EmployeeProfile $employee)
    {
        $supervisors = EmployeeProfile::where('id', '!=', $employee->id)->active()->get();
        return view('employees_profile.edit', compact('employee', 'supervisors'));
    }

    // public function update(Request $request, EmployeeProfile $employee)
    // {
    //     try {
    //         $validated = $this->validateEmployeeUpdate($request);

    //         if ($request->filled('next_of_kin_name')) {
    //             $nextOfKin = [
    //                 'name' => $validated['next_of_kin_name'],
    //                 'relationship' => $validated['next_of_kin_relationship'],
    //                 'phone' => $validated['next_of_kin_phone'],
    //                 'address' => $validated['next_of_kin_address']
    //             ];
    //             $employee->next_of_kin = $nextOfKin;
    //         }

    //         if ($request->filled('guarantor_name')) {
    //             $guarantorDetails = [
    //                 'name' => $validated['guarantor_name'],
    //                 'id_number' => $validated['guarantor_id_number'],
    //                 'phone' => $validated['guarantor_phone'],
    //                 'email' => $validated['guarantor_email'] ?? null,
    //                 'address' => $validated['guarantor_address'],
    //                 'relationship' => $validated['guarantor_relationship']
    //             ];
    //             $employee->guarantor_details = $guarantorDetails;
    //         }

    //         $employee->update([
    //             'first_name' => $validated['first_name'],
    //             'last_name' => $validated['last_name'],
    //             'middle_name' => $validated['middle_name'] ?? null,
    //             'gender' => $validated['gender'] ?? null,
    //             'date_of_birth' => $validated['date_of_birth'] ?? null,
    //             'phone_number' => $validated['phone_number'],
    //             'personal_email' => $validated['personal_email'] ?? null,
    //             'company_email' => $validated['company_email'] ?? null,
    //             'marital_status' => $validated['marital_status'] ?? null,
    //             'nssf_number' => $validated['nssf_number'] ?? null,
    //             'nhif_number' => $validated['nhif_number'] ?? null,
    //             'job_title' => $validated['job_title'],
    //             'department' => $validated['department'],
    //             'job_grade' => $validated['job_grade'] ?? null,
    //             'employment_type' => $validated['employment_type'],
    //             'reporting_to' => $validated['reporting_to'] ?? null,
    //             'basic_salary' => $validated['basic_salary'],
    //             'hire_date' => $validated['hire_date'],
    //             'confirmation_date' => $validated['confirmation_date'] ?? null,
    //             'contract_start_date' => $validated['contract_start_date'] ?? null,
    //             'contract_end_date' => $validated['contract_end_date'] ?? null,
    //             'work_location' => $validated['work_location'],
    //             'bank_name' => $validated['bank_name'] ?? null,
    //             'bank_account' => $validated['bank_account'] ?? null,
    //             'bank_branch' => $validated['bank_branch'] ?? null,
    //             'status' => $validated['status'],
    //             'termination_date' => $validated['termination_date'] ?? null,
    //             'termination_reason' => $validated['termination_reason'] ?? null,
    //         ]);

    //         $this->handleDocumentUploads($request, $employee);

    //         if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Employee updated successfully',
    //                 'data' => $employee->fresh()
    //             ], 200);
    //         }

    //         return redirect()->route('employee_profile.show', $employee->id)
    //             ->with('success', 'Employee profile updated successfully');

    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Validation failed',
    //                 'errors' => $e->errors()
    //             ], 422);
    //         }
    //         throw $e;
    //     } catch (\Exception $e) {
    //         if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Failed to update employee',
    //                 'error' => $e->getMessage()
    //             ], 500);
    //         }
    //         throw $e;
    //     }
    // }


    public function update(Request $request, EmployeeProfile $employee)
    {
        try {
            \Log::info('Update started for employee: ' . $employee->id);

            $validated = $this->validateEmployeeUpdate($request);

            // Prepare the changes
            $changes = [];
            $currentData = $employee->getOriginal();

            // Collect only changed fields
            $updateFields = [
                'first_name', 'last_name', 'middle_name', 'gender', 'date_of_birth',
                'phone_number', 'personal_email', 'company_email', 'marital_status',
                'nssf_number', 'nhif_number', 'job_title', 'department', 'job_grade',
                'employment_type', 'reporting_to', 'basic_salary', 'hire_date',
                'confirmation_date', 'contract_start_date', 'contract_end_date',
                'work_location', 'bank_name', 'bank_account', 'bank_branch', 'status'
            ];

            foreach ($updateFields as $field) {
                if ($request->has($field) && $request->$field != ($currentData[$field] ?? null)) {
                    $changes[$field] = $validated[$field];
                    \Log::info('Changed field: ' . $field);
                }
            }

            // Handle next_of_kin changes
            if ($request->filled('next_of_kin_name')) {
                $nextOfKin = [
                    'name' => $validated['next_of_kin_name'],
                    'relationship' => $validated['next_of_kin_relationship'],
                    'phone' => $validated['next_of_kin_phone'],
                    'address' => $validated['next_of_kin_address']
                ];
                if ($employee->next_of_kin != $nextOfKin) {
                    $changes['next_of_kin'] = $nextOfKin;
                }
            }

            // Handle guarantor changes
            if ($request->filled('guarantor_name')) {
                $guarantorDetails = [
                    'name' => $validated['guarantor_name'],
                    'id_number' => $validated['guarantor_id_number'],
                    'phone' => $validated['guarantor_phone'],
                    'email' => $validated['guarantor_email'] ?? null,
                    'address' => $validated['guarantor_address'],
                    'relationship' => $validated['guarantor_relationship']
                ];
                if ($employee->guarantor_details != $guarantorDetails) {
                    $changes['guarantor_details'] = $guarantorDetails;
                }
            }

            // If no changes, return early
            if (empty($changes)) {
                \Log::info('No changes detected');
                return $this->jsonResponse($request, true, 'No changes detected', null, 200, $employee->id);
            }

            \Log::info('Changes detected: ' . json_encode(array_keys($changes)));

            // Check if changes require approval - FIX: Instantiate the service correctly
            $pendingApprovalService = new PendingApprovalService();
            $requiresApproval = $pendingApprovalService->requiresApproval($changes);

            if ($requiresApproval) {
                \Log::info('Changes require approval');

                // Create approval request
                $pendingApproval = $pendingApprovalService->createApprovalRequest(
                    $employee,
                    $changes,
                    'profile_update',
                    [
                        'comments' => $request->input('approval_comments', 'Profile update request'),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]
                );

                // Store pending changes
                $employee->update([
                    'pending_changes' => json_encode($changes),
                ]);

                $message = 'Changes submitted for approval. They will be applied once approved by HR/Admin.';

                return $this->jsonResponse($request, true, $message, [
                    'employee' => $employee,
                    'pending_approval' => $pendingApproval,
                    'changes_requested' => $changes,
                    'requires_approval' => true
                ], 200, $employee->id);
            } else {
                \Log::info('Applying changes immediately');

                // Apply changes immediately
                if (isset($changes['next_of_kin'])) {
                    $employee->next_of_kin = $changes['next_of_kin'];
                }

                if (isset($changes['guarantor_details'])) {
                    $employee->guarantor_details = $changes['guarantor_details'];
                }

                $regularUpdates = array_diff_key($changes, ['next_of_kin' => '', 'guarantor_details' => '']);
                $employee->update($regularUpdates);

                $this->handleDocumentUploads($request, $employee);

                // Create change log
                EmployeeChangeLog::create([
                    'employee_profile_id' => $employee->id,
                    'changed_by' => Auth::id(),
                    'change_type' => 'profile_update',
                    'field_name' => 'immediate_update',
                    'old_value' => json_encode($currentData),
                    'new_value' => json_encode($employee->fresh()),
                    'status' => 'approved',
                    'requires_approval' => false
                ]);

                $message = 'Employee updated successfully';

                return $this->jsonResponse($request, true, $message, [
                    'employee' => $employee->fresh(),
                    'requires_approval' => false
                ], 200, $employee->id);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return $this->jsonResponse($request, false, 'Validation failed', ['errors' => $e->errors()], 422, $employee->id ?? null);
        } catch (\Exception $e) {
            \Log::error('Update failed: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return $this->jsonResponse($request, false, 'Failed to update employee: ' . $e->getMessage(), ['error' => $e->getMessage()], 500, $employee->id ?? null);
        }
    }

    private function jsonResponse(Request $request, bool $success, string $message, $data = null, int $status = 200, $employeeId = null)
    {
        if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
            $response = [
                'success' => $success,
                'message' => $message
            ];

            if ($data) {
                $response['data'] = $data;
            }

            return response()->json($response, $status);
        }

        if (!$success) {
            throw new \Exception($message);
        }

        return redirect()->route('employees_profile.show', $employeeId)
            ->with('success', $message);
    }
    public function addQualification(Request $request, EmployeeProfile $employee)
    {
        try {
            $validated = $request->validate([
                'qualification_type' => 'required|in:academic,professional,skill',
                'title' => 'required|string|max:255',
                'institution' => 'required|string|max:255',
                'grade' => 'nullable|string|max:50',
                'year_obtained' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1),
                'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
            ]);

            $certificatePath = null;
            if ($request->hasFile('certificate')) {
                $file = $request->file('certificate');
                $filename = 'qualification_' . Str::slug($validated['title']) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $certificatePath = $file->storeAs('documents/qualifications/' . $employee->id, $filename, 'public');
            }

            $qualification = $employee->qualifications()->create([
                'qualification_type' => $validated['qualification_type'],
                'title' => $validated['title'],
                'institution' => $validated['institution'],
                'grade' => $validated['grade'],
                'year_obtained' => $validated['year_obtained'],
                'certificate_path' => $certificatePath
            ]);

            if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Qualification added successfully',
                    'data' => $qualification
                ], 201);
            }

            return back()->with('success', 'Qualification added successfully');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add qualification',
                    'error' => $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }

        public function terminate(Request $request, EmployeeProfile $employee)
        {
            try {
                $validated = $request->validate([
                    'termination_date' => 'required|date',
                    'termination_reason' => 'required|string|max:1000'
                ]);

                $changeLog = EmployeeChangeLog::create([
                    'employee_profile_id' => $employee->id,
                    'changed_by' => Auth::id(),
                    'change_type' => 'termination',
                    'field_name' => 'status',
                    'old_value' => $employee->status,
                    'new_value' => 'terminated',
                    'additional_data' => [
                        'termination_date' => $validated['termination_date'],
                        'termination_reason' => $validated['termination_reason'],
                        'requested_at' => now()
                    ],
                    'status' => 'pending',
                    'requires_approval' => true
                ]);

                //pending approval record
                PendingApproval::create([
                    'approvable_type' => EmployeeChangeLog::class,
                    'approvable_id' => $changeLog->id,
                    'requester_id' => Auth::id(),
                    'approver_id' => $this->getAdminApprover(),
                    'type' => 'termination',
                    'status' => 'pending',
                    'deadline' => now()->addDays(3),
                    'priority' => 'medium',
                    'comments' => "Termination request for employee {$employee->first_name} {$employee->last_name}"
                ]);

                $employee->update([
                    'pending_termination_date' => $validated['termination_date'],
                    'pending_termination_reason' => $validated['termination_reason'],
                    'status' => 'pending_termination'
                ]);

                $message = 'Termination request submitted for approval. Employee status will be updated once approved by admin.';

                if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'data' => [
                            'employee' => $employee,
                            'change_log' => $changeLog,
                            'approval_pending' => true
                        ]
                    ], 200);
                }

                return back()->with('success', $message);

            } catch (\Illuminate\Validation\ValidationException $e) {
                if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $e->errors()
                    ], 422);
                }
                throw $e;
            } catch (\Exception $e) {
                if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to submit termination request',
                        'error' => $e->getMessage()
                    ], 500);
                }
                throw $e;
            }
        }

        // private function getAdminApprover()
        // {
        //     $admin = \App\Models\User::whereHas('roles', function($query) {
        //         $query->whereIn('name', ['admin', 'super_admin', 'CEO']);
        //     })->first();

        //     return $admin ? $admin->id : null;
        // }

    private function getAdminApprover()
    {
        if (class_exists('\Spatie\Permission\Models\Role')) {
            $admin = \App\Models\User::role('admin')->first();
            if ($admin) {
                return $admin->id;
            }
        }

        $admin = \App\Models\User::where('role', 'admin')->first();
        if ($admin) {
            return $admin->id;
        }

        $admin = \App\Models\User::where('is_admin', true)->first();
        if ($admin) {
            return $admin->id;
        }

        $admin = \App\Models\User::where('id', 1)->first();
        if ($admin) {
            return $admin->id;
        }

        return null;
    }

    public function downloadDocument(Request $request, EmployeeDocument $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }
            abort(404);
        }

        $path = Storage::disk('public')->path($document->file_path);
        $filename = basename($document->file_path);

        return response()->download($path, $filename);
    }

    public function viewDocument(Request $request, EmployeeDocument $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }
            abort(404);
        }

        $path = Storage::disk('public')->path($document->file_path);

        if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
            return response()->json([
                'success' => true,
                'data' => [
                    'file_url' => asset('storage/' . $document->file_path),
                    'file_name' => $document->document_name,
                    'file_type' => pathinfo($document->file_path, PATHINFO_EXTENSION)
                ]
            ], 200);
        }

        return response()->file($path);
    }

    public function changeHistory(Request $request, EmployeeProfile $employee)
    {
        $changeLogs = $this->changeService->getEmployeeChangeHistory($employee, $request->all());

        if ($request->wantsJson() || $request->ajax() || $request->get('format') == 'json') {
            return response()->json([
                'success' => true,
                'data' => [
                    'employee' => $employee,
                    'change_logs' => $changeLogs
                ]
            ], 200);
        }

        return view('change-history.reports-approval', compact('employee', 'changeLogs'));
    }


    private function validateEmployeeStore(Request $request): array
    {
        return $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'national_id' => 'required|string|unique:employee_profiles,national_id|max:20',
            'passport_number' => 'nullable|string|max:50',
            'kra_pin' => 'required|string|unique:employee_profiles,kra_pin|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'phone_number' => 'required|string|max:15',
            'personal_email' => 'nullable|email',
            'company_email' => 'nullable|email',
            'marital_status' => 'nullable|string|in:single,married,divorced,widowed',
            'nssf_number' => 'nullable|string|max:50',
            'nhif_number' => 'nullable|string|max:50',
            'job_title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'job_grade' => 'nullable|string|max:50',
            'employment_type' => 'required|string|in:permanent,contract,probation,casual,intern',
            'reporting_to' => 'nullable|exists:employee_profiles,id',
            'basic_salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'work_location' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:255',
            'next_of_kin_name' => 'required|string|max:255',
            'next_of_kin_relationship' => 'required|string|max:100',
            'next_of_kin_phone' => 'required|string|max:15',
            'next_of_kin_address' => 'required|string|max:500',
            'guarantor_name' => 'required|string|max:255',
            'guarantor_id_number' => 'required|string|max:20',
            'guarantor_phone' => 'required|string|max:15',
            'guarantor_email' => 'nullable|email',
            'guarantor_address' => 'required|string|max:500',
            'guarantor_relationship' => 'required|string|max:100',
        ]);
    }

    private function validateEmployeeUpdate(Request $request): array
    {
        return $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'phone_number' => 'required|string|max:15',
            'personal_email' => 'nullable|email',
            'company_email' => 'nullable|email',
            'marital_status' => 'nullable|string|in:single,married,divorced,widowed',
            'nssf_number' => 'nullable|string|max:50',
            'nhif_number' => 'nullable|string|max:50',
            'job_title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'job_grade' => 'nullable|string|max:50',
            'employment_type' => 'required|string|in:permanent,contract,probation,casual,intern',
            'reporting_to' => 'nullable|exists:employee_profiles,id',
            'basic_salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'confirmation_date' => 'nullable|date',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'work_location' => 'required|string|max:255',
            'status' => 'required|in:active,on_leave,terminated,suspended,pending_termination',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:255',
            'next_of_kin_name' => 'nullable|string|max:255',
            'next_of_kin_relationship' => 'nullable|string|max:100',
            'next_of_kin_phone' => 'nullable|string|max:15',
            'next_of_kin_address' => 'nullable|string|max:500',
            'guarantor_name' => 'nullable|string|max:255',
            'guarantor_id_number' => 'nullable|string|max:20',
            'guarantor_phone' => 'nullable|string|max:15',
            'guarantor_email' => 'nullable|email',
            'guarantor_address' => 'nullable|string|max:500',
            'guarantor_relationship' => 'nullable|string|max:100',
            'termination_date' => 'nullable|date',
            'termination_reason' => 'nullable|string|max:1000',
        ]);
    }

    private function handleDocumentUploads($request, $employee)
    {
        $documentFields = [
            'passport_copy' => 'passport',
            'id_card_copy' => 'id_card',
            'kra_pin_copy' => 'kra_pin',
            'application_letter' => 'application',
            'reference_letter' => 'reference',
            'acceptance_letter' => 'acceptance',
            'signed_rules' => 'rules',
            'guarantor_form' => 'guarantor'
        ];

        foreach ($documentFields as $fieldName => $documentType) {
            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                $fileName = $documentType . '_' . $employee->employee_id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs(
                    "documents/employees/{$employee->id}",
                    $fileName,
                    'public'
                );

                $pathField = str_replace('_copy', '_copy_path', $fieldName);
                $employee->update([$pathField => $filePath]);

                EmployeeDocument::create([
                    'employee_profile_id' => $employee->id,
                    'document_type' => $documentType,
                    'document_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'is_verified' => false
                ]);
            }
        }
    }
}
