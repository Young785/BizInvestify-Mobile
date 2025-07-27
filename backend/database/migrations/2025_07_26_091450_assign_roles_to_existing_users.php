<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get roles
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $sellerRole = Role::where('name', 'seller')->first();
        $buyerRole = Role::where('name', 'buyer')->first();

        // Assign roles to existing users based on their current role field
        User::chunk(100, function ($users) use ($superAdminRole, $adminRole, $sellerRole, $buyerRole) {
            foreach ($users as $user) {
                $role = match ($user->role) {
                    'super_admin' => $superAdminRole,
                    'admin' => $adminRole,
                    'seller' => $sellerRole,
                    'buyer' => $buyerRole,
                    default => $buyerRole, // Default to buyer role
                };

                if ($role && !$user->hasRole($role)) {
                    $user->assignRole($role);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove all roles from users
        User::chunk(100, function ($users) {
            foreach ($users as $user) {
                $user->syncRoles([]);
            }
        });
    }
};
