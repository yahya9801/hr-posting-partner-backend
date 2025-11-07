<?php

namespace Tests\Feature;

use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class JobViewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_views_and_disables_cache(): void
    {
        $job = $this->createJob();
        $job->views_count = 5;
        $job->save();

        $response = $this->getJson("/api/jobs/{$job->slug}/views");

        $response->assertOk()
            ->assertHeader('Cache-Control', 'no-store')
            ->assertJson(['views' => 5]);
    }

    public function test_store_counts_once_per_viewer_per_day(): void
    {
        $job = $this->createJob();

        $response = $this->postJson("/api/jobs/{$job->slug}/view", ['viewerId' => 'viewer-123'], [
            'User-Agent' => 'Mozilla/5.0',
            'REMOTE_ADDR' => '10.0.0.123',
        ]);

        $response->assertOk()
            ->assertHeader('Cache-Control', 'no-store')
            ->assertJson(['views' => 1]);

        $this->postJson("/api/jobs/{$job->slug}/view", ['viewerId' => 'viewer-123'], [
            'User-Agent' => 'Mozilla/5.0',
            'REMOTE_ADDR' => '10.0.0.123',
        ])->assertJson(['views' => 1]);

        $this->assertEquals(1, $job->fresh()->views_count);
    }

    public function test_different_jobs_count_independently(): void
    {
        $firstJob = $this->createJob();
        $secondJob = $this->createJob(['job_title' => 'Second Job']);

        $this->postJson("/api/jobs/{$firstJob->slug}/view", ['viewerId' => 'viewer-abc'], [
            'User-Agent' => 'Mozilla/5.0',
            'REMOTE_ADDR' => '10.0.0.5',
        ])->assertJson(['views' => 1]);

        $this->postJson("/api/jobs/{$secondJob->slug}/view", ['viewerId' => 'viewer-abc'], [
            'User-Agent' => 'Mozilla/5.0',
            'REMOTE_ADDR' => '10.0.0.5',
        ])->assertJson(['views' => 1]);

        $this->assertEquals(1, $firstJob->fresh()->views_count);
        $this->assertEquals(1, $secondJob->fresh()->views_count);
    }

    public function test_new_day_allows_same_viewer_again(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 8));
        $job = $this->createJob();

        $this->postJson("/api/jobs/{$job->slug}/view", ['viewerId' => 'viewer-xyz'], [
            'User-Agent' => 'Mozilla/5.0',
            'REMOTE_ADDR' => '172.16.0.10',
        ])->assertJson(['views' => 1]);

        Carbon::setTestNow(Carbon::create(2025, 1, 2, 8));

        $this->postJson("/api/jobs/{$job->slug}/view", ['viewerId' => 'viewer-xyz'], [
            'User-Agent' => 'Mozilla/5.0',
            'REMOTE_ADDR' => '172.16.0.10',
        ])->assertJson(['views' => 2]);

        $this->assertEquals(2, $job->fresh()->views_count);
        Carbon::setTestNow();
    }

    public function test_bot_user_agent_is_ignored(): void
    {
        $job = $this->createJob();

        $this->postJson("/api/jobs/{$job->slug}/view", ['viewerId' => 'viewer-999'], [
            'User-Agent' => 'Googlebot/2.1',
            'REMOTE_ADDR' => '192.168.1.50',
        ])->assertJson(['views' => 0]);

        $this->assertEquals(0, $job->fresh()->views_count);
    }

    public function test_missing_viewer_id_is_ignored(): void
    {
        $job = $this->createJob();

        $this->postJson("/api/jobs/{$job->slug}/view", ['viewerId' => ''], [
            'User-Agent' => 'Mozilla/5.0',
            'REMOTE_ADDR' => '192.168.1.60',
        ])->assertJson(['views' => 0]);

        $this->assertEquals(0, $job->fresh()->views_count);
    }

    public function test_non_json_content_type_is_ignored(): void
    {
        $job = $this->createJob();

        $this->post("/api/jobs/{$job->slug}/view", ['viewerId' => 'viewer-plain'], [
            'CONTENT_TYPE' => 'text/plain',
            'User-Agent' => 'Mozilla/5.0',
            'REMOTE_ADDR' => '10.10.10.10',
        ])->assertJson(['views' => 0]);

        $this->assertEquals(0, $job->fresh()->views_count);
    }

    private function createJob(array $overrides = []): Job
    {
        return Job::create(array_merge([
            'job_title' => 'Job ' . Str::random(8),
            'description' => 'Example description',
            'short_description' => 'Example short description',
            'posted_at' => now()->toDateString(),
        ], $overrides));
    }
}
