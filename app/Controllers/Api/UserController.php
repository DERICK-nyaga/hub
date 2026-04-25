<?php

namespace App\Controllers\Api;

use App\Controllers\Api\ApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends ApiController
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        // Only allow admin users to view all users
        if ($request->user()->role !== 'admin') {
            return $this->forbidden('Only administrators can view all users');
        }

        try {
            $query = User::query();

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by role
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            // Order by
            $orderBy = $request->get('order_by', 'name');
            $orderDir = $request->get('order_dir', 'asc');
            $query->orderBy($orderBy, $orderDir);

            $perPage = $request->get('per_page', 20);
            $users = $query->paginate($perPage);

            // Remove sensitive data from response
            $users->getCollection()->transform(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            });

            return $this->paginated($users, 'Users retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error fetching users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverError('Failed to retrieve users');
        }
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        // Only allow admin users to create users
        if ($request->user()->role !== 'admin') {
            return $this->forbidden('Only administrators can create users');
        }

        Log::info('Creating user', ['data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,manager,staff'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors(), 'Validation failed');
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role
            ]);

            Log::info('User created successfully', ['user_id' => $user->id]);

            return $this->created([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ], 'User created successfully');
        } catch (\Exception $e) {
            Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverError('Failed to create user');
        }
    }

    /**
     * Display the specified user
     */
    public function show(Request $request, $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->notFound('User not found');
            }

            // Users can view their own profile, admins can view any profile
            if ($request->user()->id !== $user->id && $request->user()->role !== 'admin') {
                return $this->forbidden('You can only view your own profile');
            }

            return $this->success([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ], 'User retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error fetching user', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->serverError('Failed to retrieve user');
        }
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->notFound('User not found');
        }

        // Users can update their own profile, admins can update any profile
        if ($request->user()->id !== $user->id && $request->user()->role !== 'admin') {
            return $this->forbidden('You can only update your own profile');
        }

        Log::info('Updating user', ['user_id' => $id, 'data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'sometimes|required|in:admin,manager,staff',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors(), 'Validation failed');
        }

        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            // Only admins can change roles
            if ($request->user()->role === 'admin' && $request->filled('role')) {
                $updateData['role'] = $request->role;
            }

            // Update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            Log::info('User updated successfully', ['user_id' => $id]);

            return $this->success([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ], 'User updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverError('Failed to update user');
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(Request $request, $id)
    {
        // Only allow admin users to delete users
        if ($request->user()->role !== 'admin') {
            return $this->forbidden('Only administrators can delete users');
        }

        $user = User::find($id);

        if (!$user) {
            return $this->notFound('User not found');
        }

        // Prevent self-deletion
        if ($request->user()->id === $user->id) {
            return $this->error('You cannot delete your own account', null, 403);
        }

        Log::info('Deleting user', ['user_id' => $id]);

        try {
            $user->delete();

            Log::info('User deleted successfully', ['user_id' => $id]);

            return $this->success(null, 'User deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverError('Failed to delete user');
        }
    }

    /**
     * Get current authenticated user profile
     */
    public function profile(Request $request)
    {
        try {
            $user = $request->user();

            return $this->success([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ], 'User profile retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error fetching user profile', [
                'error' => $e->getMessage()
            ]);
            return $this->serverError('Failed to retrieve user profile');
        }
    }

    /**
     * Update current user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        Log::info('Updating user profile', ['user_id' => $user->id, 'data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'required_with:password|string',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors(), 'Validation failed');
        }

        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            // Update password if provided
            if ($request->filled('password')) {
                // Verify current password
                if (!Hash::check($request->current_password, $user->password)) {
                    return $this->error('Current password is incorrect', null, 422);
                }
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            Log::info('User profile updated successfully', ['user_id' => $user->id]);

            return $this->success([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ], 'Profile updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating user profile', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverError('Failed to update profile');
        }
    }

    /**
     * Get user statistics
     */
    public function stats(Request $request)
    {
        // Only allow admin users to view statistics
        if ($request->user()->role !== 'admin') {
            return $this->forbidden('Only administrators can view user statistics');
        }

        try {
            $stats = [
                'total_users' => User::count(),
                'users_by_role' => User::select('role')
                    ->selectRaw('COUNT(*) as count')
                    ->groupBy('role')
                    ->get()
                    ->pluck('count', 'role'),
                'recent_users' => User::where('created_at', '>=', now()->subDays(30))->count(),
            ];

            return $this->success($stats, 'User statistics retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error fetching user statistics', [
                'error' => $e->getMessage()
            ]);
            return $this->serverError('Failed to retrieve user statistics');
        }
    }

    /**
     * Get all roles
     */
    public function roles()
    {
        try {
            $roles = [
                ['value' => 'admin', 'label' => 'Administrator'],
                ['value' => 'manager', 'label' => 'Manager'],
                ['value' => 'staff', 'label' => 'Staff'],
            ];

            return $this->success($roles, 'User roles retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Error fetching user roles', [
                'error' => $e->getMessage()
            ]);
            return $this->serverError('Failed to retrieve user roles');
        }
    }
}
