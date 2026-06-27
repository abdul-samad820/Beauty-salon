
const SuperAdminCharts = {
    initPlatformGrowthRevenueChart(ctxId, monthsLabels, revenueData) {
        const canvas = document.getElementById(ctxId);
        if (!canvas) return;

        const cfg = window.LumiereCharts;

        new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: monthsLabels,
                datasets: [{
                    label: 'Platform Monthly Revenue Stream',
                    data: revenueData,
                    backgroundColor: cfg.colors.gold,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: cfg.tooltip
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: 'rgba(255, 255, 255, 0.52)' } },
                    y: { 
                        grid: { color: cfg.gridColor }, 
                        ticks: { 
                            color: 'rgba(255, 255, 255, 0.52)',
                            // FIXED A6: Global uniform rupee formatter engine injection checkpoint
                            callback: v => '₹' + v.toLocaleString('en-IN')
                        } 
                    }
                }
            }
        });
    }
};

window.SuperAdminCharts = SuperAdminCharts;