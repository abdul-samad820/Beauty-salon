<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Queue Monitor Controller
 * Handles monitoring of pending, failed, and processed jobs in the Laravel database queue.
 */
class QueueMonitorController extends Controller
{
    public function index()
    {
        // ── Pending Jobs ───────────────────────────────────────────
        $pendingJobs = DB::table('jobs')
            ->select('id', 'queue', 'payload', 'attempts', 'reserved_at', 'available_at', 'created_at')
            ->orderByDesc('created_at')
            ->take(50)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);

                return [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'job_class' => class_basename($payload['displayName'] ?? 'Unknown'),
                    'attempts' => $job->attempts,
                    'reserved_at' => $job->reserved_at ? date('d M Y H:i', $job->reserved_at) : null,
                    'available_at' => date('d M Y H:i', $job->available_at),
                    'created_at' => date('d M Y H:i', $job->created_at),
                    'status' => $job->reserved_at ? 'processing' : 'pending',
                ];
            });

        // ── Failed Jobs ────────────────────────────────────────────
        $failedJobs = DB::table('failed_jobs')
            ->select('id', 'uuid', 'queue', 'payload', 'exception', 'failed_at')
            ->orderByDesc('failed_at')
            ->take(50)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                // Extract only the first line of the exception
                $exceptionLine = explode("\n", $job->exception)[0] ?? $job->exception;

                return [
                    'id' => $job->id,
                    'uuid' => $job->uuid,
                    'queue' => $job->queue,
                    'job_class' => class_basename($payload['displayName'] ?? 'Unknown'),
                    'exception' => mb_substr($exceptionLine, 0, 200),
                    'failed_at' => $job->failed_at,
                    'failed_at_fmt' => Carbon::parse($job->failed_at)->format('d M Y H:i'),
                ];
            });

        // ── Batch statistics ───────────────────────────────────────────
        $stats = [
            'pending_count' => DB::table('jobs')->count(),
            'processing_count' => DB::table('jobs')->whereNotNull('reserved_at')->count(),
            'failed_count' => DB::table('failed_jobs')->count(),
            'queues' => DB::table('jobs')->select('queue')->distinct()->pluck('queue')->toArray(),
        ];

        return view('superadmin.queue.index', compact('pendingJobs', 'failedJobs', 'stats'));
    }

    /**
     * Retry a failed job.
     */
    public function retry($uuid)
    {
        try {
            \Artisan::call('queue:retry', ['id' => [$uuid]]);

            return back()->with('success', 'The job has been added back to the queue.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retry the job: '.$e->getMessage());
        }
    }

    /**
     * Delete a specific failed job.
     */
    public function deleteFailedJob($uuid)
    {
        DB::table('failed_jobs')->where('uuid', $uuid)->delete();

        return back()->with('success', 'The failed job has been deleted.');
    }

    /**
     * Flush all failed jobs.
     */
    public function flushFailed()
    {
        \Artisan::call('queue:flush');

        return back()->with('success', 'All failed jobs have been deleted.');
    }
}
