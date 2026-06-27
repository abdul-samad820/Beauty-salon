
window.LumiereCharts = {
    colors: {
        gold: '#c9a96e',
        goldDim: 'rgba(201, 169, 110, 0.14)',
        emerald: '#10b981',
        emeraldDim: 'rgba(16, 185, 129, 0.12)',
        rose: '#f43f5e',
        roseDim: 'rgba(244, 63, 94, 0.12)',
        teal: '#2d7d6f',
        purple: '#8b5cf6'
    },
    gridColor: 'rgba(255, 255, 255, 0.04)',
    tooltip: {
        backgroundColor: '#111116', 
        borderColor: 'rgba(255, 255, 255, 0.06)',
        borderWidth: 1,
        titleFont: { family: 'Jost', size: 12, weight: '600' },
        bodyFont: { family: 'Jost', size: 13 },
        padding: 10,
        cornerRadius: 6,
        displayColors: true
    }
};

window.LuxModal = {
    open(id) {
        const el = document.getElementById(id);
        if (!el) return;
        
        const bsModal = bootstrap.Modal.getOrCreateInstance(el, { backdrop: 'static', keyboard: true });
        bsModal.show();
       
        el.addEventListener('shown.bs.modal', () => {
            const first = el.querySelector('input:not([disabled]), select:not([disabled]), textarea:not([disabled])');
            if (first) first.focus();
        }, { once: true });
    },

    close(id) {
        const el = document.getElementById(id);
        if (!el) return;
        const bsModal = bootstrap.Modal.getInstance(el);
        if (bsModal) bsModal.hide();
    }
};