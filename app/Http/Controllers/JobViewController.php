<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JobViewController extends Controller
{
    public function show(Job $job)
    {
        return $this->respondWithViews($job);
    }

    public function store(Request $request, Job $job)
    {
        $contentType = strtolower($request->header('Content-Type', ''));
        if (strpos($contentType, 'application/json') === false) {
            return $this->respondWithViews($job->fresh() ?? $job);
        }

        $userAgent = $request->userAgent() ?? '';
        if (preg_match('/bot|crawler|spider|preview|fetch/i', $userAgent)) {
            return $this->respondWithViews($job->fresh() ?? $job);
        }

        $viewerId = trim((string) $request->input('viewerId', ''));
        if ($viewerId === '') {
            return $this->respondWithViews($job->fresh() ?? $job);
        }

        $ipBucket = $this->ipBucket($request->ip());
        $uaShort = Str::limit($userAgent, 160, '');
        $fingerprint = hash('sha256', implode('|', [$viewerId, $ipBucket, $uaShort]));
        $day = now()->toDateString();

        $inserted = DB::table('job_view_uniques')->insertOrIgnore([
            'job_id' => $job->id,
            'fingerprint' => $fingerprint,
            'day' => $day,
            'created_at' => now(),
        ]);

        if ($inserted === 1) {
            DB::table('jobs')
                ->where('id', $job->id)
                ->update(['views_count' => DB::raw('views_count + 1')]);
        }

        $job->refresh();

        return $this->respondWithViews($job);
    }

    private function respondWithViews(Job $job)
    {
        return response()
            ->json(['views' => (int) ($job->views_count ?? 0)])
            ->header('Cache-Control', 'no-store');
    }

    private function ipBucket(?string $ip): string
    {
        if (empty($ip)) {
            return '0.0.0.0';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $segments = explode('.', $ip);
            $segments[3] = '0';

            return implode('.', $segments);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $binary = @inet_pton($ip);
            if ($binary === false) {
                return $ip;
            }

            $parts = unpack('n*', $binary);
            $hexParts = array_map(function ($part) {
                return str_pad(dechex($part), 4, '0', STR_PAD_LEFT);
            }, $parts);

            return implode(':', array_slice($hexParts, 0, 4)) . '::';
        }

        return $ip;
    }
}
