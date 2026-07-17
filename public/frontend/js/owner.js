/* ==========================================================================
   LUMIÈRE — OWNER DASHBOARD TELEMETRY TERMINAL (owner.js)
   ========================================================================== */

const OwnerAnalytics = {
    initSparkline(ctxId, dataPointsArray, accentColorName) {
        const canvas = document.getElementById(ctxId);
        if (!canvas) return;

        const chartConfig = window.LumiereCharts;
        const color = chartConfig.colors[accentColorName] || chartConfig.colors.gold;

        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: new Array(dataPointsArray.length).fill(''),
                datasets: [{
                    data: dataPointsArray,
                    borderColor: color,
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });
    },

    // FIXED A6: Standardized doughnut layout rendering logic with beautiful visible right legends
    initDoughnutChart(ctxId, labelsArray, dataValuesArray) {
        const canvas = document.getElementById(ctxId);
        if (!canvas) return;

        const cfg = window.LumiereCharts;

        new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: labelsArray,
                datasets: [{
                    data: dataValuesArray,
                    backgroundColor: [cfg.colors.gold, cfg.colors.emerald, cfg.colors.purple, cfg.colors.teal],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right',
                        labels: {
                            color: 'rgba(255, 255, 255, 0.65)',
                            font: { family: 'Jost', size: 12 }
                        }
                    },
                    tooltip: cfg.tooltip
                },
                cutout: '75%'
            }
        });
    }
};

// --- SIDEBAR NAVIGATION MOBILE OVERLAY TRANSITIONS ACTIONS HOOKS ---
document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const lmSidebar = document.querySelector('.lm-sidebar');
    
    if (sidebarToggle && lmSidebar) {
        let backdrop = document.querySelector('.sidebar-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'sidebar-backdrop';
            document.body.appendChild(backdrop);
        }

        function toggleSidebar() {
            lmSidebar.classList.toggle('open');
            backdrop.classList.toggle('show');
        }

        sidebarToggle.addEventListener('click', toggleSidebar);
        backdrop.addEventListener('click', toggleSidebar);

        // Close the drawer automatically after navigating to a link on mobile
        lmSidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 992) {
                    lmSidebar.classList.remove('open');
                    backdrop.classList.remove('show');
                }
            });
        });
    }
});

window.OwnerAnalytics = OwnerAnalytics;