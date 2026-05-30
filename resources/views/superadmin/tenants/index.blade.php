@extends('superadmin.layouts.app')

@section('title', 'Tenant Management')
@section('page-title', 'Tenant Management')
@section('page-sub')
  {{ $stats['total'] }} parlours &nbsp;·&nbsp;
  {{ $stats['active'] }} active &nbsp;·&nbsp;
  {{ $stats['trial'] }} trials &nbsp;·&nbsp;
  {{ $stats['suspended'] }} suspended
@endsection

@section('topbar-actions')
  <a href="{{ route('superadmin.tenants.create') }}" class="btn-gold">
    <i class="bi bi-plus-lg"></i> Add Tenant
  </a>
@endsection

@push('styles')
<style>
  .mini-stat { background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:1rem 1.2rem; display:flex; align-items:center; gap:0.9rem; }
  .mini-stat-icon { width:38px; height:38px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
  .mini-stat-val { font-family:var(--ff-display); font-size:1.5rem; font-weight:400; line-height:1; }
  .mini-stat-label { font-size:0.65rem; color:var(--text-3); letter-spacing:0.1em; text-transform:uppercase; margin-top:0.2rem; }
  .filter-bar { display:flex; flex-wrap:wrap; gap:0.75rem; align-items:center; margin-bottom:1.5rem; }
  .filter-tabs { display:flex; gap:0.4rem; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:8px; padding:0.3rem; }
  .filter-tab { padding:0.35rem 0.9rem; border-radius:6px; font-size:0.72rem; font-weight:500; color:var(--text-2); cursor:pointer; transition:all 0.25s; border:none; background:transparent; }
  .filter-tab.active { background:var(--gold); color:#1a1400; }
  .filter-tab:hover:not(.active) { background:rgba(255,255,255,0.05); color:var(--text); }
  .filter-input { background:rgba(255,255,255,0.04); border:1px solid var(--border-2); border-radius:8px; color:var(--text); font-family:var(--ff-body); font-size:0.8rem; padding:0.5rem 1rem; outline:none; transition:border-color 0.3s; }
  .filter-input:focus { border-color:var(--gold); }
  select.filter-input option { background:var(--bg-card); color:var(--text); }
  .tenant-table { width:100%; border-collapse:collapse; }
  .tenant-table th { font-size:0.6rem; font-weight:600; letter-spacing:0.2em; text-transform:uppercase; color:var(--text-3); padding:0.7rem 1rem; border-bottom:1px solid var(--border); text-align:left; white-space:nowrap; }
  .tenant-table td { padding:0.85rem 1rem; font-size:0.82rem; color:var(--text-2); border-bottom:1px solid rgba(255,255,255,0.03); white-space:nowrap; }
  .tenant-table tr:hover td { background:rgba(255,255,255,0.02); color:var(--text); }
  .tenant-table tr:last-child td { border-bottom:none; }
  .action-btn { width:30px; height:30px; border-radius:6px; border:1px solid var(--border); background:transparent; display:inline-flex; align-items:center; justify-content:center; color:var(--text-3); cursor:pointer; transition:all 0.2s; font-size:0.8rem; text-decoration:none; }
  .action-btn:hover         { border-color:var(--gold);    color:var(--gold);    background:var(--gold-dim); }
  .action-btn.danger:hover  { border-color:var(--rose);    color:var(--rose);    background:var(--rose-dim); }
  .action-btn.success:hover { border-color:var(--emerald); color:var(--emerald); background:var(--emerald-dim); }
  .pagination-custom { display:flex; gap:0.3rem; align-items:center; }
  .page-btn { width:32px; height:32px; border-radius:6px; border:1px solid var(--border); background:transparent; display:flex; align-items:center; justify-content:center; color:var(--text-2); cursor:pointer; transition:all 0.25s; font-size:0.78rem; text-decoration:none; }
  .page-btn:hover         { border-color:var(--gold); color:var(--gold); background:var(--gold-dim); }
  .page-btn.active        { background:var(--gold); color:#1a1400; border-color:var(--gold); font-weight:600; }
  .page-btn.disabled      { opacity:0.3; pointer-events:none; }
  .tenant-dot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:0.4rem; }
  .dot-green { background:var(--emerald); box-shadow:0 0 6px var(--emerald); }
  .dot-gold  { background:var(--gold);    box-shadow:0 0 6px var(--gold); }
  .dot-red   { background:var(--rose);    box-shadow:0 0 6px var(--rose); }
</style>
@endpush

@section('content')

  {{-- ── MINI STATS ── --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3 fade-in-up stagger-1">
      <div class="mini-stat" style="border-top:2px solid var(--gold)">
        <div class="mini-stat-icon" style="background:var(--gold-dim);color:var(--gold)"><i class="bi bi-buildings-fill"></i></div>
        <div><div class="mini-stat-val" style="color:var(--gold)">{{ $stats['total'] }}</div><div class="mini-stat-label">Total Tenants</div></div>
      </div>
    </div>
    <div class="col-6 col-md-3 fade-in-up stagger-2">
      <div class="mini-stat" style="border-top:2px solid var(--emerald)">
        <div class="mini-stat-icon" style="background:var(--emerald-dim);color:var(--emerald)"><i class="bi bi-circle-fill"></i></div>
        <div><div class="mini-stat-val" style="color:var(--emerald)">{{ $stats['active'] }}</div><div class="mini-stat-label">Active</div></div>
      </div>
    </div>
    <div class="col-6 col-md-3 fade-in-up stagger-3">
      <div class="mini-stat" style="border-top:2px solid var(--gold)">
        <div class="mini-stat-icon" style="background:var(--gold-dim);color:var(--gold)"><i class="bi bi-hourglass-split"></i></div>
        <div><div class="mini-stat-val" style="color:var(--gold)">{{ $stats['trial'] }}</div><div class="mini-stat-label">Trial Period</div></div>
      </div>
    </div>
    <div class="col-6 col-md-3 fade-in-up stagger-4">
      <div class="mini-stat" style="border-top:2px solid var(--rose)">
        <div class="mini-stat-icon" style="background:var(--rose-dim);color:var(--rose)"><i class="bi bi-pause-circle-fill"></i></div>
        <div><div class="mini-stat-val" style="color:var(--rose)">{{ $stats['suspended'] }}</div><div class="mini-stat-label">Suspended</div></div>
      </div>
    </div>
  </div>

  {{-- ── FILTERS ── --}}
  <form method="GET" action="{{ route('superadmin.tenants.index') }}" id="filterForm">
    <div class="filter-bar fade-in-up stagger-2">
      <div class="filter-tabs">
        @foreach(['all' => 'All', 'active' => 'Active', 'trial' => 'Trial', 'suspended' => 'Suspended'] as $val => $label)
          <button type="submit" name="status" value="{{ $val }}"
            class="filter-tab {{ request('status', 'all') === $val ? 'active' : '' }}">
            {{ $label }}
          </button>
        @endforeach
      </div>

      <select name="plan" class="filter-input" style="min-width:130px" onchange="document.getElementById('filterForm').submit()">
        <option value="">All Plans</option>
        @foreach(['free' => 'Free', 'pro' => 'Pro', 'enterprise' => 'Enterprise'] as $val => $label)
          <option value="{{ $val }}" {{ request('plan') === $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>

      <select name="sort" class="filter-input" style="min-width:160px" onchange="document.getElementById('filterForm').submit()">
        <option value="created_at" {{ request('sort','created_at') === 'created_at' ? 'selected' : '' }}>Sort: Newest First</option>
        <option value="name"       {{ request('sort') === 'name' ? 'selected' : '' }}>Sort: Name A–Z</option>
      </select>

      <div style="position:relative;margin-left:auto;">
        <i class="bi bi-search" style="position:absolute;left:0.85rem;top:50%;transform:translateY(-50%);color:var(--text-3);font-size:0.85rem;"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tenants…"
          class="filter-input" style="padding-left:2.4rem;min-width:200px;" />
      </div>

      <a href="{{ route('superadmin.tenants.index') }}" class="btn-ghost">
        <i class="bi bi-x-lg"></i> Clear
      </a>
    </div>
  </form>

  {{-- ── TABLE ── --}}
  <div class="card-glass fade-in-up stagger-3">
    <div style="overflow-x:auto">
      <table class="tenant-table">
        <thead>
          <tr>
            <th>Tenant</th>
            <th>Subdomain</th>
            <th>Plan</th>
            <th>Status</th>
            <th>Staff</th>
            <th>Services</th>
            <th>Appointments</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($tenants as $tenant)
          <tr>
            <td>
              <span class="tenant-dot {{ $tenant->status === 'active' ? 'dot-green' : ($tenant->status === 'suspended' ? 'dot-red' : 'dot-gold') }}"></span>
              <strong style="font-weight:500;color:var(--text);">{{ $tenant->name }}</strong>
            </td>
            <td style="font-family:monospace;font-size:0.75rem;color:var(--teal-light);">
              {{ $tenant->subdomain }}.lumiere.app
            </td>
            <td>
              <span class="plan-badge plan-{{ $tenant->plan }}">{{ ucfirst($tenant->plan) }}</span>
            </td>
            <td>
              <span class="status-badge {{ $tenant->status_badge_class }}">
                <i class="bi bi-circle-fill" style="font-size:0.35rem;"></i>
                {{ ucfirst($tenant->status) }}
              </span>
            </td>
            <td>{{ $tenant->staff_count ?? 0 }}</td>
            <td>{{ $tenant->services_count ?? 0 }}</td>
            <td>{{ number_format($tenant->appointments_count ?? 0) }}</td>
            <td>{{ $tenant->created_at->format('d M Y') }}</td>
            <td>
              <div style="display:flex;gap:0.3rem;">
                <a href="{{ route('superadmin.tenants.show', $tenant) }}" class="action-btn" title="View"><i class="bi bi-eye"></i></a>
                <a href="{{ route('superadmin.tenants.edit', $tenant) }}" class="action-btn" title="Edit"><i class="bi bi-pencil"></i></a>

                @if($tenant->status !== 'active')
                  <form method="POST" action="{{ route('superadmin.tenants.status', $tenant) }}" style="display:inline;">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="active">
                    <button type="submit" class="action-btn success" title="Activate"><i class="bi bi-play-fill"></i></button>
                  </form>
                @else
                  <form method="POST" action="{{ route('superadmin.tenants.status', $tenant) }}" style="display:inline;">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="suspended">
                    <button type="submit" class="action-btn danger" title="Suspend" onclick="return confirm('Is tenant ko suspend karna chahte hain?')">
                      <i class="bi bi-pause-fill"></i>
                    </button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="9" style="text-align:center;padding:3rem;color:var(--text-3);">
              <i class="bi bi-buildings" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
              Koi tenant nahi mila
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div style="padding:1rem 1.2rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.8rem;">
      <div style="font-size:0.75rem;color:var(--text-3);">
        Showing <strong style="color:var(--text-2);">{{ $tenants->firstItem() }}–{{ $tenants->lastItem() }}</strong>
        of <strong style="color:var(--text-2);">{{ $tenants->total() }}</strong> tenants
      </div>
      <div class="pagination-custom">
        @if($tenants->onFirstPage())
          <span class="page-btn disabled"><i class="bi bi-chevron-left"></i></span>
        @else
          <a href="{{ $tenants->previousPageUrl() }}" class="page-btn"><i class="bi bi-chevron-left"></i></a>
        @endif

        @foreach($tenants->getUrlRange(max(1, $tenants->currentPage()-2), min($tenants->lastPage(), $tenants->currentPage()+2)) as $page => $url)
          <a href="{{ $url }}" class="page-btn {{ $page == $tenants->currentPage() ? 'active' : '' }}">{{ $page }}</a>
        @endforeach

        @if($tenants->hasMorePages())
          <a href="{{ $tenants->nextPageUrl() }}" class="page-btn"><i class="bi bi-chevron-right"></i></a>
        @else
          <span class="page-btn disabled"><i class="bi bi-chevron-right"></i></span>
        @endif
      </div>
    </div>
  </div>

@endsection
