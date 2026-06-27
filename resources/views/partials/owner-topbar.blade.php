<header class="lm-topbar" role="banner">

    {{-- Mobile Sidebar Toggle --}}
    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle structural workspace sidebar" aria-controls="sidebar" style="display: none; background: transparent; border: none; color: var(--text); font-size: 1.4rem; padding: 0; margin-right: 1rem;">
        <i class="bi bi-list" aria-hidden="true"></i>
    </button>

    {{-- Breadcrumbs & Title --}}
    <div style="flex:1; display:flex; flex-direction:column; justify-content:center;">
        @hasSection('breadcrumb')
        <nav aria-label="Breadcrumb navigation segment">
            <ol style="list-style: none; padding: 0; margin: 0 0 0.1rem 0; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.15em; color: var(--text-3);">
                <li>@yield('breadcrumb')</li>
            </ol>
        </nav>
        @endif

        <div style="display:flex; align-items:baseline; gap:0.5rem;">
            <h1 class="topbar-heading" style="margin: 0; line-height: 1;">
                @yield('page-title', 'Dashboard Gateway')
            </h1>
            @hasSection('page-sub')
            <span class="faint" style="font-size: 0.75rem; border-left: 1px solid var(--border); padding-left: 0.5rem;">
                @yield('page-sub')
            </span>
            @endif
        </div>
    </div>

    {{-- Global Search Bar --}}
    <form class="topbar-search" action="#" method="GET" role="search">
        <i class="bi bi-search" aria-hidden="true"></i>
        <input type="search" placeholder="Search workspace nodes, clients profiles, bills..." aria-label="Global tracking system engine search input" />
    </form>

    {{-- Actions & Notifications --}}
    <div style="display:flex; align-items:center; gap:0.75rem; margin-left: 1rem;" role="toolbar" aria-label="Page controls actions terminal">

        {{-- ADVANCED PREMIUM NOTIFICATION BELL --}}
        <div style="position:relative; display: inline-block;" id="notif-wrapper">

            {{-- Bell Button --}}
            <button type="button" id="notif-btn" aria-label="View system notifications" onclick="toggleNotifications()" style="position: relative; width: 42px; height: 42px; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); color: var(--text-2); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.borderColor='var(--gold)'; this.style.color='var(--gold)'; this.style.background='rgba(201,169,110,0.05)';" onmouseout="this.style.borderColor='rgba(255,255,255,0.08)'; this.style.color='var(--text-2)'; this.style.background='rgba(255,255,255,0.03)';">

                <i class="bi bi-bell" style="font-size: 1.15rem;"></i>

                {{-- Notification Badge (Red Cutout Style) --}}
                <span id="notif-count" class="notif-badge-active" style="display:none; position:absolute; top:-4px; right:-4px; background: var(--rose, #f43f5e); color: white; font-size: 0.6rem; font-family: var(--ff-display); font-weight: 700; width: 20px; height: 20px; border-radius: 50%; border: 2px solid var(--bg-body, #09090b); align-items: center; justify-content: center; line-height: 1;"></span>
            </button>

            {{-- Glassmorphism Dropdown --}}
            <div id="notif-dropdown" style="display:none; position:absolute; right:0; top:calc(100% + 12px); width:360px; background: rgba(15, 15, 20, 0.95); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(201, 169, 110, 0.2); border-radius: 16px; box-shadow: 0 16px 40px rgba(0,0,0,0.5); z-index:9999; overflow:hidden; transform-origin: top right; animation: fadeUp 0.2s ease forwards;">

                {{-- Header --}}
                <div style="display:flex; justify-content:space-between; align-items:center; padding: 1.2rem; border-bottom: 1px solid rgba(255,255,255,0.05); background: linear-gradient(180deg, rgba(201, 169, 110, 0.05) 0%, transparent 100%);">
                    <span class="serif" style="font-size:1.1rem; color:var(--text); font-weight: 600;">Alerts</span>
                    <button onclick="markAllRead()" class="btn-lux-ghost" style="font-size:0.7rem; padding: 0.3rem 0.6rem; height: auto;">Mark all read</button>
                </div>

                {{-- Notification List --}}
                <div id="notif-list" class="lux-notif-scroller" style="max-height:350px; overflow-y:auto; text-align: left;">
                    <div style="padding:3rem 2rem; text-align:center; color:var(--text-3); font-size:0.85rem;">
                        <div class="spinner-border spinner-border-sm text-secondary mb-2" role="status"></div>
                        <div>Fetching logs...</div>
                    </div>
                </div>

                {{-- Footer --}}
                <div style="padding:0.8rem 1rem; border-top:1px solid rgba(255,255,255,0.05); text-align:center; background: rgba(0,0,0,0.2);">
                    <a href="{{ route('owner.appointments.index') }}" style="font-size:0.75rem; color:var(--gold); text-decoration:none; font-weight: 500; letter-spacing:0.05em; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='var(--gold)'">View All Operations <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:0.5rem;">
            @yield('topbar-actions')
        </div>
    </div>

</header>
