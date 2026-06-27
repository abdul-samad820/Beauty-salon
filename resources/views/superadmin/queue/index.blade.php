@extends('layouts.superadmin')

@section('title', 'Queue Monitor')
@section('page-title', 'Queue Monitor')
@section('page-sub', 'Laravel job queue status — pending, processing, failed')

@section('content')

{{-- Stats --}}
<div class="row g-3 mb-4">
    @php
    $cards = [
    ['label'=>'Pending Jobs', 'val'=>$stats['pending_count'], 'color'=>'var(--amber)', 'bg'=>'var(--amber-dim)', 'icon'=>'bi-hourglass-split', 'desc'=>'Waiting in queue'],
    ['label'=>'Processing', 'val'=>$stats['processing_count'],'color'=>'var(--purple)', 'bg'=>'var(--purple-dim)', 'icon'=>'bi-arrow-repeat', 'desc'=>'Currently running'],
    ['label'=>'Failed Jobs', 'val'=>$stats['failed_count'], 'color'=>'var(--rose)', 'bg'=>'var(--rose-dim)', 'icon'=>'bi-x-circle-fill', 'desc'=>'Need attention'],
    ['label'=>'Active Queues', 'val'=>count($stats['queues']), 'color'=>'var(--emerald)','bg'=>'var(--emerald-dim)','icon'=>'bi-activity', 'desc'=>implode(', ', $stats['queues']) ?: 'none'],
    ];
    @endphp
    @foreach($cards as $i => $c)
    <div class="col-md-3 col-6 fade-up" style="animation-delay:{{ $i*.05 }}s">
        <div class="card-lux kpi-pad" style="height:100%;">
            <div class="kpi-icon-abs" style="background:{{ $c['bg'] }};color:{{ $c['color'] }};"><i class="bi {{ $c['icon'] }}"></i></div>
            <div class="kpi-label">{{ $c['label'] }}</div>
            <div class="kpi-value" style="font-size:2rem;color:{{ $c['color'] }};">{{ $c['val'] }}</div>
            <div style="font-size:.62rem;color:var(--text-3);margin-top:.3rem;">{{ $c['desc'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Notice if queue:work not running --}}
@if($stats['pending_count'] > 0 && $stats['processing_count'] === 0)
<div class="flash-alert flash-warning fade-up s2" style="margin-bottom:1.5rem;">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <strong>Queue worker is not running!</strong> There are jobs pending, but they are not being processed.
    Run the following command on your server:
    <code style="background:rgba(255,255,255,0.08);padding:.1rem .4rem;border-radius:4px;font-size:.8rem;">php artisan queue:work</code>
</div>
@endif

{{-- Pending Jobs --}}
<div class="card-lux p-0 mb-4 fade-up s2">
    <div class="p-4 pb-2 sec-hdr">
        <div>
            <h3 class="sec-title">Pending Jobs</h3>
            <p class="sec-sub">Jobs currently waiting to be processed</p>
        </div>
        <span class="lux-badge {{ $stats['pending_count'] > 0 ? 'lb-amber' : 'lb-green' }}">
            {{ $stats['pending_count'] }} jobs
        </span>
    </div>
    <div style="overflow-x:auto;">
        <table class="lux-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Queue</th>
                    <th>Job Class</th>
                    <th>Attempts</th>
                    <th>Status</th>
                    <th>Available At</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingJobs as $job)
                <tr>
                    <td style="color:var(--text-3);font-size:.7rem;">{{ $job['id'] }}</td>
                    <td><span class="lux-badge lb-purple">{{ $job['queue'] }}</span></td>
                    <td style="font-size:.8rem;color:var(--text);">{{ $job['job_class'] }}</td>
                    <td style="font-size:.8rem;">{{ $job['attempts'] }}</td>
                    <td>
                        <span class="lux-badge {{ $job['status'] === 'processing' ? 'lb-amber' : 'lb-muted' }}">
                            {{ ucfirst($job['status']) }}
                        </span>
                    </td>
                    <td style="font-size:.75rem;">{{ $job['available_at'] }}</td>
                    <td style="font-size:.75rem;color:var(--text-3);">{{ $job['created_at'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <x-empty-state icon="bi-list-task" title="Queue Empty" text="No pending jobs were found." />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Failed Jobs --}}
<div class="card-lux p-0 fade-up s3">
    <div class="p-4 pb-2 sec-hdr">
        <div>
            <h3 class="sec-title">Failed Jobs</h3>
            <p class="sec-sub">Jobs that have encountered errors</p>
        </div>
        <div style="display:flex;gap:.5rem;align-items:center;">
            <span class="lux-badge {{ $stats['failed_count'] > 0 ? 'lb-red' : 'lb-green' }}">
                {{ $stats['failed_count'] }} failed
            </span>
            @if($stats['failed_count'] > 0)
            <form method="POST" action="{{ route('superadmin.queue.flush') }}">
                @csrf
                <button type="submit" class="btn-lux-danger" style="font-size:.65rem;padding:.35rem .75rem;">
                    <i class="bi bi-trash3"></i> Flush All
                </button>
            </form>
            @endif
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="lux-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Queue</th>
                    <th>Job Class</th>
                    <th>Exception</th>
                    <th>Failed At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($failedJobs as $job)
                <tr>
                    <td style="color:var(--text-3);font-size:.7rem;">{{ $job['id'] }}</td>
                    <td><span class="lux-badge lb-purple">{{ $job['queue'] }}</span></td>
                    <td style="font-size:.8rem;color:var(--text);">{{ $job['job_class'] }}</td>
                    <td style="font-size:.72rem;color:var(--rose);max-width:300px;word-break:break-all;">
                        {{ $job['exception'] }}
                    </td>
                    <td style="font-size:.75rem;color:var(--text-3);">{{ $job['failed_at_fmt'] }}</td>
                    <td>
                        <div style="display:flex;gap:.4rem;">
                            {{-- Retry --}}
                            <form method="POST" action="{{ route('superadmin.queue.retry', $job['uuid']) }}">
                                @csrf
                                <button type="submit" class="btn-icon-action btn-action-success" title="Retry Job">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </form>
                            {{-- Delete --}}
                            <form method="POST" action="{{ route('superadmin.queue.failed.delete', $job['uuid']) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon-action btn-action-danger" title="Delete Job">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <x-empty-state icon="bi-check2-circle" title="No Failed Jobs" text="The queue is healthy, no failed jobs were found." />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Queue Commands Help --}}
<div class="card-lux p-4 mt-4 fade-up s4">
    <h3 class="sec-title" style="margin-bottom:1rem;">
        <i class="bi bi-terminal" style="color:var(--gold);margin-right:.5rem;"></i> Queue Management Commands
    </h3>
    <div class="row g-3">
        @php
        $cmds = [
        ['cmd'=>'php artisan queue:work', 'desc'=>'Start the queue worker (main command)'],
        ['cmd'=>'php artisan queue:work --queue=default,emails', 'desc'=>'Process specific queues'],
        ['cmd'=>'php artisan queue:restart', 'desc'=>'Gracefully restart the workers'],
        ['cmd'=>'php artisan queue:retry all', 'desc'=>'Retry all failed jobs'],
        ['cmd'=>'php artisan queue:flush', 'desc'=>'Clear all failed jobs'],
        ['cmd'=>'php artisan queue:monitor default:50', 'desc'=>'Monitor queue size'],
        ];
        @endphp
        @foreach($cmds as $c)
        <div class="col-lg-6">
            <div style="background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:.8rem 1rem;">
                <code style="font-size:.75rem;color:var(--gold);">{{ $c['cmd'] }}</code>
                <div style="font-size:.65rem;color:var(--text-3);margin-top:.3rem;">{{ $c['desc'] }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
