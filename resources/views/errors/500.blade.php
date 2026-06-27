@extends('layouts.auth')

@section('title', '500 — Core Infrastructure Exception')

@section('content')
<div class="w-full max-w-md mx-auto text-center py-12 px-4 rounded-2xl border border-slate-200 bg-white shadow-xl dark:border-slate-800 dark:bg-slate-900 transition-all duration-200 fade-up s1">

    <div class="font-mono text-7xl font-extrabold tracking-tighter text-rose-600 dark:text-rose-400 select-none">
        500
    </div>

    <h2 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white mt-4">
        Internal Server Core Error
    </h2>

    <p class="text-xs text-slate-400 dark:text-slate-500 mt-2 mb-6 leading-relaxed max-w-xs mx-auto">
        The application lifecycle processing pipeline has encountered an error.
        System operation diagnostic logs have been synchronized with the centralized data warehouse for review.
    </p>

    <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
        <a href="/" class="inline-flex w-full sm:w-auto items-center justify-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 no-underline transition-colors">
            <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Re-Initialize Platform Gateway
        </a>
    </div>
</div>
@endsection
