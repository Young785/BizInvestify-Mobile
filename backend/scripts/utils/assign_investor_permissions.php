<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

// Find the user
$user = User::where('email', 'ayomikunariyo@gmail.com')->first();

if (!$user) {
    echo "User not found!\n";
    exit(1);
}

echo "User: {$user->email}\n";
echo "Current role (column): {$user->role}\n";

// Get or create the investor role
$investorRole = Role::firstOrCreate(['name' => 'investor']);

// Assign the role to the user
$user->assignRole('investor');

echo "Assigned 'investor' role to user\n";

// Show permissions
$permissions = $user->getAllPermissions()->pluck('name');
echo "\nUser now has " . $permissions->count() . " permissions:\n";
echo $permissions->implode(', ') . "\n";

echo "\n✅ User permissions updated successfully!\n";
