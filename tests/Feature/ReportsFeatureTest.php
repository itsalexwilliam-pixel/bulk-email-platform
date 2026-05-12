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
        $response->assertSee('Emails Sent');
        $response->assertSee('No sent emails found for the selected filters.');
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
        $response->assertSee('Emails Sent');
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

    public function test_single_email_report_page_shows_single_email_activity_log_rows(): void
    {
        $user = $this->actingAsAccountUser();
        $accountId = (int) $user->account_id;

        $queueId = DB::table('email_queue')->insertGetId([
            'account_id' => $accountId,
            'campaign_id' => null,
            'contact_id' => null,
            'email' => 'activity@example.com',
            'type' => 'single',
            'subject' => 'Activity Subject',
            'body' => '<p>Activity Body</p>',
            'body_snapshot' => '<p>Activity Body Snapshot</p>',
            'from_email' => 'from@example.com',
            'from_name' => 'Report Sender',
            'status' => 'sent',
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('reports.single-email'));

        $response->assertOk();
        $response->assertSee('Single Email Activity Log');
        $response->assertSee('activity@example.com');
        $response->assertSee('Report Sender');
        $response->assertSee('Activity Subject');
        $response->assertSee(route('reports.email.show', ['id' => $queueId]));
    }

    public function test_single_email_report_excludes_campaign_rows(): void
    {
        $user = $this->actingAsAccountUser();
        $accountId = (int) $user->account_id;

        $campaignId = DB::table('campaigns')->insertGetId([
            'account_id' => $accountId,
            'name' => 'Campaign For Single Filter Test',
            'subject' => 'Campaign Subject',
            'body' => '<p>Campaign Body</p>',
            'status' => 'draft',
            'scheduled_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('email_queue')->insert([
            'account_id' => $accountId,
            'campaign_id' => $campaignId,
            'contact_id' => null,
            'email' => 'campaign@example.com',
            'type' => 'campaign',
            'subject' => 'Campaign Subject',
            'body' => '<p>Campaign Body</p>',
            'body_snapshot' => '<p>Campaign Body Snapshot</p>',
            'from_email' => 'from@example.com',
            'from_name' => 'Campaign Sender',
            'status' => 'sent',
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('reports.single-email'));

        $response->assertOk();
        $response->assertDontSee('campaign@example.com');
        $response->assertSee('No single email activity found for selected filters.');
    }

    public function test_reports_email_preview_returns_snapshot_for_same_account(): void
    {
        $user = $this->actingAsAccountUser();
        $accountId = (int) $user->account_id;

        $queueId = DB::table('email_queue')->insertGetId([
            'account_id' => $accountId,
            'campaign_id' => null,
            'contact_id' => null,
            'email' => 'preview@example.com',
            'type' => 'single',
            'subject' => 'Preview Subject',
            'body' => '<p>Original Body</p>',
            'body_snapshot' => '<p>Snapshot Body</p>',
            'from_email' => 'from@example.com',
            'from_name' => 'Preview Sender',
            'status' => 'sent',
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson(route('reports.email.show', ['id' => $queueId]));

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $queueId,
            'to' => 'preview@example.com',
            'from_name' => 'Preview Sender',
            'from_email' => 'from@example.com',
            'subject' => 'Preview Subject',
            'body_snapshot' => '<p>Snapshot Body</p>',
        ]);
    }

    public function test_reports_email_preview_falls_back_to_body_when_snapshot_missing(): void
    {
        $user = $this->actingAsAccountUser();
        $accountId = (int) $user->account_id;

        $queueId = DB::table('email_queue')->insertGetId([
            'account_id' => $accountId,
            'campaign_id' => null,
            'contact_id' => null,
            'email' => 'fallback@example.com',
            'type' => 'single',
            'subject' => 'Fallback Subject',
            'body' => '<p>Fallback Body</p>',
            'body_snapshot' => null,
            'from_email' => 'from@example.com',
            'from_name' => 'Fallback Sender',
            'status' => 'sent',
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson(route('reports.email.show', ['id' => $queueId]));

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $queueId,
            'body_snapshot' => '<p>Fallback Body</p>',
        ]);
    }

    public function test_reports_email_preview_is_scoped_to_account(): void
    {
        $accountA = $this->createAccountId();
        $accountB = $this->createAccountId();

        $userA = User::factory()->create(['account_id' => $accountA]);
        DB::table('accounts')->where('id', $accountA)->update(['owner_user_id' => $userA->id]);
        DB::table('account_user')->insert([
            'account_id' => $accountA,
            'user_id' => $userA->id,
            'role' => 'owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $queueIdForB = DB::table('email_queue')->insertGetId([
            'account_id' => $accountB,
            'campaign_id' => null,
            'contact_id' => null,
            'email' => 'other-account@example.com',
            'type' => 'single',
            'subject' => 'Other Account',
            'body' => '<p>Other</p>',
            'body_snapshot' => '<p>Other Snapshot</p>',
            'from_email' => 'from@other.com',
            'from_name' => 'Other Sender',
            'status' => 'sent',
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($userA)->getJson(route('reports.email.show', ['id' => $queueIdForB]));
        $response->assertNotFound();
    }

    public function test_warmup_report_page_loads_and_shows_warmup_details(): void
    {
        $user = $this->actingAsAccountUser();
        $accountId = (int) $user->account_id;

        $campaignId = DB::table('campaigns')->insertGetId([
            'account_id' => $accountId,
            'name' => 'Warmup Alpha',
            'subject' => 'Warmup Subject',
            'body' => '<p>Warmup Body</p>',
            'status' => 'sending',
            'warmup_enabled' => 1,
            'warmup_day' => 3,
            'warmup_started_at' => now()->subDays(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('email_queue')->insert([
            [
                'account_id' => $accountId,
                'campaign_id' => $campaignId,
                'contact_id' => null,
                'email' => 'w1@example.com',
                'subject' => 'Warmup Subject',
                'body' => '<p>Body</p>',
                'status' => 'sent',
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_id' => $accountId,
                'campaign_id' => $campaignId,
                'contact_id' => null,
                'email' => 'w2@example.com',
                'subject' => 'Warmup Subject',
                'body' => '<p>Body</p>',
                'status' => 'queued',
                'sent_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->get(route('reports.warmup'));

        $response->assertOk();
        $response->assertSee('Warmup Report');
        $response->assertSee('Warmup Campaign Details');
        $response->assertSee('Warmup Alpha');
        $response->assertSee('Today Sent');
        $response->assertSee('Daily Cap');
    }

    public function test_warmup_report_is_scoped_to_account(): void
    {
        $accountA = $this->createAccountId();
        $accountB = $this->createAccountId();

        $userA = User::factory()->create(['account_id' => $accountA]);
        DB::table('accounts')->where('id', $accountA)->update(['owner_user_id' => $userA->id]);
        DB::table('account_user')->insert([
            'account_id' => $accountA,
            'user_id' => $userA->id,
            'role' => 'owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('campaigns')->insert([
            'account_id' => $accountB,
            'name' => 'Other Account Warmup',
            'subject' => 'Subject',
            'body' => '<p>Body</p>',
            'status' => 'sending',
            'warmup_enabled' => 1,
            'warmup_day' => 2,
            'warmup_started_at' => now()->subDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($userA)->get(route('reports.warmup'));

        $response->assertOk();
        $response->assertDontSee('Other Account Warmup');
        $response->assertSee('No warmup-enabled campaigns found for this account.');
    }
}
