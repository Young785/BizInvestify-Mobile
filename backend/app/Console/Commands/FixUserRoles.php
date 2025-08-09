<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class FixUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:fix-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix users who have role field set but missing the actual Spatie role assignment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking all users for role assignment issues...');
        
        $users = User::all();
        $fixed = 0;
        $errors = 0;
        
        foreach ($users as $user) {
            if ($user->role && !$user->hasRole($user->role)) {
                try {
                    // Check if the role exists
                    $role = Role::where('name', $user->role)->first();
                    if (!$role) {
                        $this->error("Role '{$user->role}' does not exist for user {$user->email}");
                        $errors++;
                        continue;
                    }
                    
                    $user->assignRole($user->role);
                    $this->line("✅ Fixed user: {$user->email} (assigned role: {$user->role})");
                    $fixed++;
                } catch (\Exception $e) {
                    $this->error("❌ Failed to fix user {$user->email}: " . $e->getMessage());
                    $errors++;
                }
            }
        }
        
        $this->info("Completed! Fixed {$fixed} users with missing role assignments.");
        if ($errors > 0) {
            $this->warn("Encountered {$errors} errors during the process.");
        }
        
        return 0;
    }
}
