<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransactionExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_download_another_users_export(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $filename = 'transactions_u'.$owner->id.'_2025-01-01_12-00-00.csv';

        Storage::disk('local')->put('exports/'.$filename, 'id,type,amount');

        $this->actingAs($intruder, 'sanctum')
            ->withoutMiddleware()
            ->getJson('/api/transactions/download/'.$filename)
            ->assertStatus(403);
    }

    public function test_user_can_download_own_export_file(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $filename = 'transactions_u'.$owner->id.'_2025-01-01_12-00-00.csv';

        Storage::disk('local')->put('exports/'.$filename, "id,type,amount\n1,purchase,10");

        $this->actingAs($owner, 'sanctum')
            ->withoutMiddleware()
            ->get('/api/transactions/download/'.$filename)
            ->assertOk();
    }
}
