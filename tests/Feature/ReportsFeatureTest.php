<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportsFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function createAccountId(): int
    {
        $planId = DB::table('plans')->insertGetId([
            'name' => 'Starter',
            'slug' => 'starter-' . uniqid(),
            'emails_per_day' => 5000,
            'campaigns_limit' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table('accounts')->insertGetId([
            'name' => 'Acme Account',
            'plan_id' => $planId,
            'owner_user_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function actingAsAccountUser(?int $accountId = null): User
    {
        $accountId = $accountId ?? $this->createAccountId();

        $user = User::factory()->create([
            'account_id' => $accountId,
        ]);

        DB::table('accounts')
            ->where('id', $accountId)
            ->update(['owner_user_id' => $user->id]);

        DB::table('account_user')->insert([
            'account_id' => $accountId,
            'user_id' => $user->id,
            'role' => 'owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user);

        return $user;
    }

    public function test_reports_page_requires_authentication(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_reports_page_loads_for_authenticated_user_with_empty_state(): void
    {
        $this->actingAsAccountUser();

        $response = $this->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Total Emails Sent');
        $response->assertSee('No data available for the selected filters.');
    }

    public function test_reports_page_renders_metrics_with_seeded_data(): void
    {
        $user = $this->actingAsAccountUser();
        $accountId = (int) $user->account_id;

        $campaignId = DB::table('campaigns')->insertGetId([
            'account_id' => $accountId,
            'name' => 'Q2 Promo',
            'subject' => 'Promo',
            'body' => '<p>Hello</p>',
            'status' => 'draft',
            'scheduled_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $queueId = DB::table('email_queue')->insertGetId([
            'account_id' => $accountId,
            'campaign_id' => $campaignId,
            'contact_id' => null,
            'email' => 'john@example.com',
            'subject' => 'Promo',
            'body' => '<p>Hello</p>',
            'status' => 'sent',
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
            'utm_source' => 'newsletter',
            'utm_medium' => 'email',
            'utm_campaign' => 'q2-promo',
        ]);

        DB::table('email_opens')->insert([
            'email_queue_id' => $queueId,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'opened_at' => now(),
            'created_at' => now(),
        ]);

        DB::table('email_clicks')->insert([
            'email_queue_id' => $queueId,
            'url' => 'https://example.com',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'clicked_at' => now(),
            'created_at' => now(),
        ]);

        DB::table('unsubscribes')->insert([
            'contact_id' => null,
            'email' => 'john@example.com',
            'unsubscribed_at' => now(),
            'created_at' => now(),
        ]);

        $response = $this->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Total Emails Sent');
        $response->assertSee('Campaign Performance');
        $response->assertSee('Top UTM Sources');
        $response->assertSee('Top UTM Campaigns');
    }

    public function test_reports_filters_are_accepted(): void
    {
        $this->actingAsAccountUser();

        $response7d = $this->get(route('reports.index', ['date_range' => '7d']));
        $response7d->assertOk();

        $response30d = $this->get(route('reports.index', ['date_range' => '30d']));
        $response30d->assertOk();

        $responseCustom = $this->get(route('reports.index', [
            'date_range' => 'custom',
            'from' => now()->subDays(10)->toDateString(),
            'to' => now()->toDateString(),
        ]));
        $responseCustom->assertOk();
    }
}
