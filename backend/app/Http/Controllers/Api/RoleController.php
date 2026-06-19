<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Get all roles with permissions
     */
    public function index(): JsonResponse
    {
        try {
            $roles = Role::with('permissions')
                ->orderBy('name')
                ->get()
                ->map(function ($role) {
                    $role->display_name = $role->display_name
                        ?: ucwords(str_replace('_', ' ', $role->name));
                    $role->users_count = $role->users()->count();
                    return $role;
                });

            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch roles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch roles'
            ], 500);
        }
    }

    /**
     * Create a new role
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'nullable|integer|min:1|max:100',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'level' => $request->level ?? 1,
            ]);

            if ($request->has('permissions')) {
                // Convert permission IDs to permission names
                $permissionNames = Permission::whereIn('id', $request->permissions)->pluck('name')->toArray();
                $role->givePermissionTo($permissionNames);
            }

            // Log activity
            ActivityLog::log(
                $request->user()->id,
                'role_created',
                "Created role: {$role->name}",
                ['role_id' => $role->id, 'permissions' => $request->permissions ?? []],
                'medium',
                true
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'data' => $role->load('permissions')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create role: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role'
            ], 500);
        }
    }

    /**
     * Update role
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'nullable|integer|min:1|max:100',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        try {
            DB::beginTransaction();

            $oldData = $role->toArray();
            
            $role->update([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'level' => $request->level ?? 1,
            ]);

            if ($request->has('permissions')) {
                // Convert permission IDs to permission names
                $permissionNames = Permission::whereIn('id', $request->permissions)->pluck('name')->toArray();
                $role->syncPermissions($permissionNames);
            }

            // Log activity
            ActivityLog::log(
                $request->user()->id,
                'role_updated',
                "Updated role: {$role->name}",
                ['role_id' => $role->id, 'old_data' => $oldData, 'new_permissions' => $request->permissions ?? []],
                'medium',
                true
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'data' => $role->load('permissions')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update role: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role'
            ], 500);
        }
    }

    /**
     * Delete role
     */
    public function destroy(Role $role): JsonResponse
    {
        try {
            // Check if role is in use
            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role that is assigned to users'
                ], 400);
            }

            $roleName = $role->name;
            $role->delete();

            // Log activity
            ActivityLog::log(
                request()->user()->id,
                'role_deleted',
                "Deleted role: {$roleName}",
                ['role_name' => $roleName],
                'medium',
                true
            );

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete role: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role'
            ], 500);
        }
    }

    /**
     * Get all permissions grouped by category
     */
    public function getPermissions(): JsonResponse
    {
        try {
            $permissionDescriptions = [
                // User Management
                'users.view' => 'View user profiles and information',
                'users.create' => 'Create new user accounts',
                'users.edit' => 'Edit user information and settings',
                'users.delete' => 'Delete user accounts',
                'users.impersonate' => 'Login as other users for support',
                'users.export' => 'Export user data to CSV/Excel',
                
                // Product Management
                'products.view' => 'View product listings and details',
                'products.create' => 'Create new product listings',
                'products.edit' => 'Edit existing product information',
                'products.delete' => 'Delete product listings',
                'products.approve' => 'Approve product listings for publication',
                
                // Business Management
                'businesses.view' => 'View business listings and details',
                'businesses.create' => 'Create new business listings',
                'businesses.edit' => 'Edit existing business information',
                'businesses.delete' => 'Delete business listings',
                'businesses.approve' => 'Approve business listings for publication',
                
                // Investment Management
                'investments.view' => 'View investment opportunities and details',
                'investments.create' => 'Create new investment opportunities',
                'investments.approve' => 'Approve investment proposals',
                'investments.manage' => 'Manage all investment activities',
                
                // Transaction Management
                'transactions.view' => 'View transaction history and details',
                'transactions.create' => 'Create new transactions',
                'transactions.cancel' => 'Cancel pending transactions',
                'admin.transactions' => 'Access admin transaction management',
                'transactions.manage' => 'Manage all transaction activities',
                'transactions.refund' => 'Process transaction refunds',
                
                // KYC Management
                'kyc.view' => 'View KYC applications and documents',
                'kyc.approve' => 'Approve KYC applications',
                'kyc.reject' => 'Reject KYC applications with reasons',
                'kyc.documents' => 'Access KYC document files',
                
                // Analytics
                'analytics.view' => 'View basic analytics and reports',
                'admin.analytics' => 'Access comprehensive admin analytics',
                
                // Reports
                'reports.view' => 'View system reports and statistics',
                
                // Content Moderation
                'moderation.manage' => 'Manage content moderation and disputes',
                
                // System Settings
                'system.settings' => 'Access and modify system settings',
                
                // Admin Access
                'admin.access' => 'Access admin dashboard and features',
                'admin.dashboard' => 'View admin dashboard statistics',
                
                // Super Admin Access
                'super_admin.access' => 'Full system access and control',
                
                // Communication
                'messages.view' => 'View messages and communications',
                'messages.send' => 'Send messages to users',
                
                // Activity Logs
                'activity_logs.view' => 'View system activity logs',
                
                // Notifications
                'notifications.view' => 'View and manage notifications',
                
                // Payment Management
                'payments.create' => 'Create payment transactions',
                'payments.refund' => 'Process payment refunds',
                'payments.view' => 'View payment information',
                
                // Roles and Permissions
                'roles.manage' => 'Manage user roles and assignments',
                'permissions.manage' => 'Manage system permissions',
            ];

            $permissions = Permission::all()->map(function ($permission) use ($permissionDescriptions) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'display_name' => ucwords(str_replace('_', ' ', $permission->name)),
                    'description' => $permissionDescriptions[$permission->name] ?? 'No description available',
                    'category' => explode('.', $permission->name)[0],
                    'is_active' => true
                ];
            })->groupBy('category');

            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch permissions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch permissions'
            ], 500);
        }
    }
}