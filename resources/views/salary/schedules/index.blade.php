@extends('layouts.salary-app')

@section('title', 'Payment Schedules')

@section('content')
<div x-data="schedulesManager()" x-init="init()" class="space-y-6">
    
    <!-- Page Header with Role-based Buttons -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-indigo-600"></i> 
                Payment Schedules
            </h2>
            <p class="text-slate-500 text-sm mt-1">Schedule future salary payments, advances, and recurring deductions</p>
        </div>
        
        <!-- Role-based Action Buttons -->
        <div x-data="{ role: localStorage.getItem('user_role') || 'Admin' }"
             x-on:roleChanged.window="role = $event.detail.role"
             class="flex gap-3">
            <!-- Admin: Can create new schedules -->
            <div x-show="role === 'Admin'" x-cloak>
                <button @click="openCreateModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i> New Schedule
                </button>
            </div>
            <!-- Director: Can bulk approve schedules -->
            <div x-show="role === 'Director'" x-cloak>
                <button onclick="bulkApprove()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fas fa-check-double"></i> Bulk Approve
                </button>
            </div>
            <!-- Export button - visible to both -->
            <button onclick="exportSchedules()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl shadow-md transition flex items-center gap-2">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Role-based Info Banner -->
    <div x-data="{ role: localStorage.getItem('user_role') || 'Admin' }"
         x-on:roleChanged.window="role = $event.detail.role">
        
        <!-- Admin Banner -->
        <div x-show="role === 'Admin'" x-cloak class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-lg mb-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-crown text-indigo-600 text-xl"></i>
                <div>
                    <p class="font-semibold text-indigo-800">Administrator Access - Schedules</p>
                    <p class="text-sm text-indigo-600">You can create, edit, delete, and approve any schedule.</p>
                </div>
            </div>
        </div>
        
        <!-- Director Banner -->
        <div x-show="role === 'Director'" x-cloak class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-lg mb-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-star-of-life text-amber-600 text-xl"></i>
                <div>
                    <p class="font-semibold text-amber-800">Director Access - Schedules</p>
                    <p class="text-sm text-amber-600">You can review, approve, and process scheduled payments.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-gradient-to-br from-indigo-50 to-white rounded-xl p-5 border border-indigo-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-indigo-600 text-sm font-medium">Total Schedules</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalSchedules ?? 0) }}</p>
                </div>
                <div class="bg-indigo-100 p-2 rounded-lg"><i class="fas fa-list text-indigo-600 text-xl"></i></div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-amber-50 to-white rounded-xl p-5 border border-amber-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-amber-600 text-sm font-medium">Pending Approval</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($pendingApprovals ?? 0) }}</p>
                </div>
                <div class="bg-amber-100 p-2 rounded-lg"><i class="fas fa-clock text-amber-600 text-xl"></i></div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-emerald-50 to-white rounded-xl p-5 border border-emerald-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-emerald-600 text-sm font-medium">Scheduled Amount</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">KES {{ number_format($totalScheduledAmount ?? 0, 2) }}</p>
                </div>
                <div class="bg-emerald-100 p-2 rounded-lg"><i class="fas fa-money-bill-wave text-emerald-600 text-xl"></i></div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-white rounded-xl p-5 border border-purple-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-purple-600 text-sm font-medium">Upcoming (7 days)</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($upcomingCount ?? 0) }}</p>
                </div>
                <div class="bg-purple-100 p-2 rounded-lg"><i class="fas fa-hourglass-half text-purple-600 text-xl"></i></div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form method="GET" action="{{ route('salary.schedules.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Employee name..." 
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                    <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Type</label>
                    <select name="type" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All</option>
                        <option value="regular" {{ request('type') == 'regular' ? 'selected' : '' }}>Regular Salary</option>
                        <option value="advance" {{ request('type') == 'advance' ? 'selected' : '' }}>Advance</option>
                        <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Employee</label>
                    <select name="employee_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">All Employees</option>
                        @foreach($employees ?? [] as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" 
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" 
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('salary.schedules.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm hover:bg-slate-50 transition">Reset</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">
                    <i class="fas fa-search mr-1"></i> Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Schedules Table with Role-based Actions -->
    <div x-data="{ role: localStorage.getItem('user_role') || 'Admin' }"
         x-on:roleChanged.window="role = $event.detail.role"
         class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">
                            <input type="checkbox" id="selectAll" onclick="toggleSelectAll()" class="rounded">
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">ID</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Employee</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Amount</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Type</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Scheduled Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Approved By</th>
                        <!-- Admin-only column header -->
                        <th x-show="role === 'Admin'" x-cloak class="px-5 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Admin Actions</th>
                        <!-- Director-only column header -->
                        <th x-show="role === 'Director'" x-cloak class="px-5 py-3 text-center text-xs font-semibold text-slate-600 uppercase">Director Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schedules as $schedule)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3">
                            <input type="checkbox" class="schedule-checkbox" value="{{ $schedule->id }}" 
                                   {{ $schedule->status !== 'pending' ? 'disabled' : '' }}>
                        </td>
                        <td class="px-5 py-3 text-sm font-mono text-slate-500">#{{ $schedule->id }}</td>
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-800">{{ $schedule->employee->name ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-500">{{ $schedule->employee->position ?? '' }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="font-bold text-emerald-600">KES {{ number_format($schedule->amount, 2) }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $typeColors = [
                                    'regular' => 'bg-blue-100 text-blue-700',
                                    'advance' => 'bg-purple-100 text-purple-700',
                                    'adjustment' => 'bg-orange-100 text-orange-700'
                                ];
                                $typeIcons = [
                                    'regular' => 'calendar-check',
                                    'advance' => 'bolt',
                                    'adjustment' => 'sliders-h'
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium flex items-center gap-1 w-fit {{ $typeColors[$schedule->type] ?? 'bg-gray-100' }}">
                                <i class="fas fa-{{ $typeIcons[$schedule->type] ?? 'tag' }}"></i>
                                {{ ucfirst($schedule->type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="text-sm font-medium">{{ \Carbon\Carbon::parse($schedule->scheduled_date)->format('M d, Y') }}</div>
                            <div class="text-xs text-slate-400">
                                {{ \Carbon\Carbon::parse($schedule->scheduled_date)->diffForHumans() }}
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'approved' => 'bg-blue-100 text-blue-700',
                                    'processed' => 'bg-emerald-100 text-emerald-700',
                                    'failed' => 'bg-red-100 text-red-700'
                                ];
                                $statusIcons = [
                                    'pending' => 'clock',
                                    'approved' => 'check-circle',
                                    'processed' => 'check-double',
                                    'failed' => 'times-circle'
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium flex items-center gap-1 w-fit {{ $statusColors[$schedule->status] ?? 'bg-gray-100' }}">
                                <i class="fas fa-{{ $statusIcons[$schedule->status] ?? 'info-circle' }}"></i>
                                {{ ucfirst($schedule->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-sm">
                            @if($schedule->approved_by)
                                <div class="text-slate-600">{{ $schedule->approver->name ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-400">{{ $schedule->approved_at ? $schedule->approved_at->format('M d, Y') : '' }}</div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <!-- Admin Actions Column -->
                        <td x-show="role === 'Admin'" x-cloak class="px-5 py-3">
                            <div class="flex justify-center gap-2">
                                <button onclick="viewSchedule({{ $schedule->id }})" class="text-blue-600 hover:bg-blue-50 p-1.5 rounded-lg transition" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($schedule->status === 'pending')
                                <button onclick="approveSchedule({{ $schedule->id }})" class="text-emerald-600 hover:bg-emerald-50 p-1.5 rounded-lg transition" title="Approve">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                <button onclick="rejectSchedule({{ $schedule->id }})" class="text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition" title="Reject">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                                @endif
                                <button onclick="editSchedule({{ $schedule->id }})" class="text-amber-600 hover:bg-amber-50 p-1.5 rounded-lg transition" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteSchedule({{ $schedule->id }})" class="text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                        <!-- Director Actions Column -->
                        <td x-show="role === 'Director'" x-cloak class="px-5 py-3">
                            <div class="flex justify-center gap-2">
                                <button onclick="viewSchedule({{ $schedule->id }})" class="text-blue-600 hover:bg-blue-50 p-1.5 rounded-lg transition" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($schedule->status === 'pending')
                                <button onclick="approveSchedule({{ $schedule->id }})" class="text-emerald-600 hover:bg-emerald-50 p-1.5 rounded-lg transition" title="Approve">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                <button onclick="rejectSchedule({{ $schedule->id }})" class="text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition" title="Reject">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                                @endif
                                @if($schedule->status === 'approved')
                                <button onclick="processNow({{ $schedule->id }})" class="text-indigo-600 hover:bg-indigo-50 p-1.5 rounded-lg transition" title="Process Now">
                                    <i class="fas fa-play-circle"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-5 py-12 text-center text-slate-400">
                            <i class="fas fa-calendar-times text-4xl mb-3 block"></i>
                            <p>No payment schedules found</p>
                            <div x-data="{ role: localStorage.getItem('user_role') || 'Admin' }">
                                <div x-show="role === 'Admin'">
                                    <button @click="openCreateModal()" class="text-indigo-600 hover:underline text-sm mt-2 inline-block">Create first schedule</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Bulk Actions Bar - Shows when items are selected -->
        <div id="bulkActionsBar" class="hidden bg-indigo-50 px-5 py-3 border-t border-indigo-200 flex justify-between items-center">
            <div>
                <span id="selectedCount" class="font-semibold text-indigo-700">0</span> schedules selected
            </div>
            <div class="flex gap-2">
                <button onclick="bulkApprove()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-check-double mr-1"></i> Approve Selected
                </button>
                <button onclick="bulkDelete()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-trash mr-1"></i> Delete Selected
                </button>
                <button onclick="clearSelection()" class="border border-slate-300 px-4 py-2 rounded-lg text-sm hover:bg-slate-50">
                    Cancel
                </button>
            </div>
        </div>
        
        <!-- Pagination -->
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
            {{ $schedules->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- Create/Edit Schedule Modal -->
<div id="scheduleModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
            <h3 id="modalTitle" class="text-xl font-bold">New Payment Schedule</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="scheduleForm" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="schedule_id" name="schedule_id">
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Employee <span class="text-red-500">*</span></label>
                <select id="employee_id" name="employee_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2">
                    <option value="">Select Employee</option>
                    @foreach($employees ?? [] as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->name }} - {{ $employee->position }} (Base: KES {{ number_format($employee->base_salary, 2) }})</option>
                    @endforeach
                </select>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Schedule Type <span class="text-red-500">*</span></label>
                    <select id="type" name="type" required class="w-full border border-slate-300 rounded-lg px-3 py-2">
                        <option value="regular">Regular Monthly Salary</option>
                        <option value="advance">Advance Payment</option>
                        <option value="adjustment">Adjustment / Bonus</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Amount (KES) <span class="text-red-500">*</span></label>
                    <input type="number" id="amount" name="amount" step="0.01" required placeholder="0.00"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Scheduled Date <span class="text-red-500">*</span></label>
                <input type="date" id="scheduled_date" name="scheduled_date" required 
                       min="{{ date('Y-m-d') }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2">
                <p class="text-xs text-slate-400 mt-1">Must be today or a future date</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Notes (Optional)</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Additional notes about this scheduled payment..."
                          class="w-full border border-slate-300 rounded-lg px-3 py-2"></textarea>
            </div>
            
            <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded text-sm">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                This schedule will require approval. Once approved, payment will be automatically processed on the scheduled date.
            </div>
            
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-save mr-1"></i> Save Schedule
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Schedule Modal -->
<div id="viewScheduleModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold">Schedule Details</h3>
            <button onclick="closeViewModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="scheduleDetailsContent" class="p-6">
            <!-- Dynamic content loads here -->
        </div>
    </div>
</div>

<script>
let selectedSchedules = [];

function schedulesManager() {
    return {
        init() {
            setTimeout(() => {
                const alerts = document.querySelectorAll('.flash-message');
                alerts.forEach(alert => alert.style.display = 'none');
            }, 5000);
        },
        openCreateModal() {
            document.getElementById('modalTitle').innerText = 'New Payment Schedule';
            document.getElementById('scheduleForm').reset();
            document.getElementById('schedule_id').value = '';
            document.getElementById('scheduled_date').value = '{{ date("Y-m-d") }}';
            document.getElementById('scheduleModal').classList.remove('hidden');
        }
    }
}

// Toggle select all checkboxes
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.schedule-checkbox:not([disabled])');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
    updateBulkActionsBar();
}

// Update bulk actions bar
function updateBulkActionsBar() {
    const checkboxes = document.querySelectorAll('.schedule-checkbox:checked');
    selectedSchedules = Array.from(checkboxes).map(cb => parseInt(cb.value));
    const count = selectedSchedules.length;
    
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');
    
    if (count > 0) {
        bulkBar.classList.remove('hidden');
        selectedCount.innerText = count;
    } else {
        bulkBar.classList.add('hidden');
    }
}

// Clear selection
function clearSelection() {
    const checkboxes = document.querySelectorAll('.schedule-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActionsBar();
}

// Bulk approve schedules
function bulkApprove() {
    if (selectedSchedules.length === 0) {
        alert('Please select at least one schedule to approve');
        return;
    }
    
    if(confirm(`Approve ${selectedSchedules.length} schedule(s)?`)) {
        fetch('/salary/schedules/bulk-approve', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: selectedSchedules })
        }).then(response => response.json())
          .then(data => {
              if(data.success) {
                  location.reload();
              } else {
                  alert('Error: ' + data.message);
              }
          });
    }
}

// Bulk delete schedules
function bulkDelete() {
    if (selectedSchedules.length === 0) {
        alert('Please select at least one schedule to delete');
        return;
    }
    
    if(confirm(`Delete ${selectedSchedules.length} schedule(s)? This action cannot be undone.`)) {
        fetch('/salary/schedules/bulk-delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: selectedSchedules })
        }).then(response => response.json())
          .then(data => {
              if(data.success) {
                  location.reload();
              } else {
                  alert('Error: ' + data.message);
              }
          });
    }
}

// Export schedules
function exportSchedules() {
    window.location.href = '{{ route("salary.schedules.index") }}?export=true&' + new URLSearchParams(window.location.search).toString();
}

// Form submission
document.getElementById('scheduleForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const id = document.getElementById('schedule_id').value;
    const url = id ? `/salary/schedules/${id}` : '{{ route("salary.schedules.store") }}';
    const method = id ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to save schedule'));
        }
    } catch (error) {
        alert('Error saving schedule');
    }
});

// View schedule details
function viewSchedule(id) {
    fetch(`/salary/schedules/${id}`)
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="text-xs text-slate-500">Employee</label><p class="font-semibold text-lg">${data.employee?.name}</p></div>
                        <div><label class="text-xs text-slate-500">Position</label><p>${data.employee?.position || 'N/A'}</p></div>
                        <div><label class="text-xs text-slate-500">Schedule Type</label><p><span class="px-2 py-1 rounded-full text-xs ${data.type === 'regular' ? 'bg-blue-100' : data.type === 'advance' ? 'bg-purple-100' : 'bg-orange-100'}">${data.type.toUpperCase()}</span></p></div>
                        <div><label class="text-xs text-slate-500">Amount</label><p class="text-2xl font-bold text-emerald-600">KES ${Number(data.amount).toLocaleString()}</p></div>
                        <div><label class="text-xs text-slate-500">Scheduled Date</label><p class="font-medium">${new Date(data.scheduled_date).toLocaleDateString()}</p></div>
                        <div><label class="text-xs text-slate-500">Status</label><p><span class="px-2 py-1 rounded-full text-xs ${data.status === 'approved' ? 'bg-emerald-100 text-emerald-700' : data.status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100'}">${data.status.toUpperCase()}</span></p></div>
                        ${data.approved_by ? `<div><label class="text-xs text-slate-500">Approved By</label><p>${data.approver?.name || 'N/A'}</p></div>` : ''}
                        ${data.approved_at ? `<div><label class="text-xs text-slate-500">Approved At</label><p>${new Date(data.approved_at).toLocaleString()}</p></div>` : ''}
                        ${data.notes ? `<div class="col-span-2"><label class="text-xs text-slate-500">Notes</label><p class="text-sm bg-slate-50 p-2 rounded">${data.notes}</p></div>` : ''}
                    </div>
                    ${data.status === 'approved' ? `
                        <div class="bg-emerald-50 p-3 rounded-lg text-center">
                            <i class="fas fa-check-circle text-emerald-600"></i> 
                            Schedule approved! Payment will be processed on ${new Date(data.scheduled_date).toLocaleDateString()}
                            <br>
                            <button onclick="processNow(${data.id})" class="mt-2 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Process Now</button>
                        </div>
                    ` : ''}
                </div>
            `;
            document.getElementById('scheduleDetailsContent').innerHTML = content;
            document.getElementById('viewScheduleModal').classList.remove('hidden');
        })
        .catch(error => {
            alert('Error loading schedule details');
        });
}

function approveSchedule(id) {
    if(confirm('Approve this schedule? Payment will be processed on the scheduled date.')) {
        fetch(`/salary/schedules/${id}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        }).then(response => response.json())
          .then(data => {
              if(data.success) {
                  location.reload();
              } else {
                  alert('Error: ' + data.message);
              }
          });
    }
}

function rejectSchedule(id) {
    if(confirm('Reject this schedule?')) {
        fetch(`/salary/schedules/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        }).then(response => response.json())
          .then(data => {
              if(data.success) {
                  location.reload();
              } else {
                  alert('Error: ' + data.message);
              }
          });
    }
}

function processNow(id) {
    if(confirm('Process this payment immediately? This will create a payment record.')) {
        fetch(`/salary/schedules/${id}/process`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        }).then(response => response.json())
          .then(data => {
              if(data.success) {
                  location.reload();
              } else {
                  alert('Error: ' + data.message);
              }
          });
    }
}

function editSchedule(id) {
    fetch(`/salary/schedules/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalTitle').innerText = 'Edit Schedule';
            document.getElementById('schedule_id').value = data.id;
            document.getElementById('employee_id').value = data.employee_id;
            document.getElementById('type').value = data.type;
            document.getElementById('amount').value = data.amount;
            document.getElementById('scheduled_date').value = data.scheduled_date;
            document.getElementById('notes').value = data.notes || '';
            document.getElementById('scheduleModal').classList.remove('hidden');
        });
}

function deleteSchedule(id) {
    if(confirm('Delete this schedule permanently? This action cannot be undone.')) {
        fetch(`/salary/schedules/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        }).then(response => response.json())
          .then(data => {
              if(data.success) {
                  location.reload();
              } else {
                  alert('Error: ' + data.message);
              }
          });
    }
}

function closeModal() {
    document.getElementById('scheduleModal').classList.add('hidden');
}

function closeViewModal() {
    document.getElementById('viewScheduleModal').classList.add('hidden');
}

// Add event listeners to checkboxes
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.schedule-checkbox');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActionsBar);
    });
});
</script>

@push('styles')
<style>
    table { min-width: 1100px; }
    .pagination { display: flex; justify-content: center; gap: 0.5rem; }
    .pagination .page-item { list-style: none; }
    .pagination .page-link { padding: 0.5rem 0.75rem; border-radius: 0.5rem; background: white; border: 1px solid #e2e8f0; }
    .pagination .active .page-link { background: #4f46e5; color: white; border-color: #4f46e5; }
    [x-cloak] { display: none !important; }
</style>
@endpush

@endsection