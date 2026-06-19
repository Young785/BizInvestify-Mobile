<?php

namespace Tests\Unit;

use App\Models\FeaturedListing;
use App\Models\Product;
use App\Models\User;
use App\Services\FeaturedListingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class FeaturedListingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_notifies_listing_owner(): void
    {
        Event::fake([MessageSending::class]);

        $seller = User::factory()->create(['role' => 'seller']);
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['seller_id' => $seller->id]);

        $listing = FeaturedListing::create([
            'user_id' => $seller->id,
            'listable_type' => 'product',
            'listable_id' => $product->id,
            'title' => 'Summer Promo',
            'promotion_type' => 'featured',
            'daily_budget' => 10,
            'total_budget' => 100,
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(10),
            'status' => 'pending',
        ]);

        app(FeaturedListingService::class)->approve($listing, $admin);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $seller->id,
            'type' => 'listing_approved',
            'title' => 'Featured Listing Approved',
        ]);

        Event::assertDispatched(MessageSending::class);
    }
}
