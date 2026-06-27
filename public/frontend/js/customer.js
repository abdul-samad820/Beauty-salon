/* ==========================================================================
   LUMIÈRE — CUSTOMER INTERACTIVE TERMINAL (customer.js)
   ========================================================================== */

const Booking = {
    selectedService: null,
    selectedServiceName: null,
    selectedServicePrice: null,
    selectedServiceDur: null,
    selectedDate: null,
    selectedStaff: '',
    selectedSlot: null,
    selectedStaffId: null,

    // ── Service Selection ──────────────────────────────────────────────────
    selectService(element, subdomain) {
        // Toggle selected class on article cards
        document.querySelectorAll('#svcGrid article').forEach(c => c.style.outline = '');
        element.style.outline = '2px solid var(--gold)';

        this.selectedService = element.dataset.id;
        this.selectedServiceName = element.dataset.name;
        this.selectedServicePrice = element.dataset.price;
        this.selectedServiceDur = element.dataset.dur;

        // Update info panel
        const infoBox = document.getElementById('svcInfo');
        document.getElementById('infoName').textContent = this.selectedServiceName;
        document.getElementById('infoPrice').textContent = '₹' + Number(this.selectedServicePrice).toLocaleString('en-IN');
        document.getElementById('infoDur').textContent = this.selectedServiceDur + ' min';
        infoBox.classList.remove('hidden');

        // Set hidden form field
        document.getElementById('f_svc_id').value = this.selectedService;

        // Show notes section
        document.getElementById('notesSection').style.display = '';

        // If date already selected, refresh slots
        if (this.selectedDate) {
            this.fetchSlots(this.selectedDate, subdomain);
        }

        // Mobile scroll to booking panel
        const bookingPanel = document.getElementById('bookingPanel');
        if (window.innerWidth <= 992 && bookingPanel) {
            bookingPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        this.refreshSubmitState();
    },

    // ── Category Filter ────────────────────────────────────────────────────
    filterCategory(btn, cat) {
        document.querySelectorAll('.cat-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        document.querySelectorAll('.service-card-wrapper').forEach(card => {
            if (cat === 'all' || card.dataset.cat === cat) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    },

    // ── Date Change ────────────────────────────────────────────────────────
    onDateChange(date, subdomain) {
        this.selectedDate = date;
        this.selectedSlot = null;
        document.getElementById('f_date_val').value = date;

        if (this.selectedService) {
            this.fetchSlots(date, subdomain);
        } else {
            // Show slots section with prompt
            const section = document.getElementById('slotsSection');
            section.classList.remove('hidden');
            document.getElementById('slotsContainer').innerHTML =
                '<p style="font-size:0.75rem; color:var(--text-3); grid-column:1/-1;">Please select a service first.</p>';
        }

        this.refreshSubmitState();
    },

    // ── Staff Selection ────────────────────────────────────────────────────
    selectStaff(btn, staffId, subdomain) {
        document.querySelectorAll('.staff-chip').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');

        this.selectedStaff = staffId;
        this.selectedStaffId = staffId;
        this.selectedSlot = null;
        document.getElementById('f_staff_id').value = staffId;

        if (this.selectedDate && this.selectedService) {
            this.fetchSlots(this.selectedDate, subdomain);
        }

        this.refreshSubmitState();
    },

    // ── Fetch Slots from API ───────────────────────────────────────────────
    fetchSlots(date, subdomain) {
        if (!this.selectedService || !date) return;

        const section = document.getElementById('slotsSection');
        const spinner = document.getElementById('slotSpinner');
        const container = document.getElementById('slotsContainer');

        section.classList.remove('hidden');
        spinner.style.display = 'block';
        container.innerHTML = '';

        const params = new URLSearchParams({
            date: date,
            service_id: this.selectedService,
        });
        if (this.selectedStaff) params.append('staff_id', this.selectedStaff);

        const url = `/${subdomain}/slots?${params.toString()}`;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(json => {
            spinner.style.display = 'none';
            this.renderSlots(json.data || []);
        })
        .catch(() => {
            spinner.style.display = 'none';
            container.innerHTML = '<p style="font-size:0.75rem;color:var(--rose);grid-column:1/-1;">Could not load slots. Please try again.</p>';
        });
    },

    // ── Render Slot Buttons ────────────────────────────────────────────────
    renderSlots(staffSlots) {
        const container = document.getElementById('slotsContainer');
        container.innerHTML = '';

        // Merge all available slots across staff
        const allSlots = [];
        staffSlots.forEach(staffData => {
            staffData.slots.forEach(slot => {
                // Avoid time duplicates — keep first available
                const existing = allSlots.find(s => s.start === slot.start);
                if (!existing) {
                    allSlots.push({ ...slot, staff_id: staffData.staff_id });
                } else if (!existing.available && slot.available) {
                    Object.assign(existing, { ...slot, staff_id: staffData.staff_id });
                }
            });
        });

        if (allSlots.length === 0) {
            container.innerHTML = '<p style="font-size:0.75rem;color:var(--text-3);grid-column:1/-1;">No slots available for selected parameters.</p>';
            return;
        }

        allSlots.forEach(slot => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'slot-btn' + (slot.available ? '' : ' disabled');
            btn.textContent = slot.display;
            btn.disabled = !slot.available;

            if (slot.available) {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
                    btn.classList.add('selected');
                    this.selectedSlot = slot.start;
                    document.getElementById('f_time_val').value = slot.start;

                    // Auto-assign staff if "Anyone" selected
                    if (!this.selectedStaff) {
                        document.getElementById('f_staff_id').value = slot.staff_id;
                    }

                    this.refreshSubmitState();
                });
            }

            container.appendChild(btn);
        });
    },

    // ── Submit Button State ────────────────────────────────────────────────
    refreshSubmitState() {
        const btn = document.getElementById('bookBtn');
        if (!btn) return;

        const ready = this.selectedService && this.selectedDate && this.selectedSlot;
        btn.disabled = !ready;
        btn.style.opacity = ready ? '1' : '0.5';
        btn.style.cursor = ready ? 'pointer' : 'not-allowed';
    },

    // Legacy alias kept for backward compat
    renderTimeSlots(containerId, slotsArray) {
        this.renderSlots([{ staff_id: null, slots: slotsArray.map(s => ({
            start: s.time_value, display: s.display_time, available: s.available
        }))}]);
    }
};

window.Booking = Booking;

// ── Slot button styles ─────────────────────────────────────────────────────
(function injectSlotStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .slot-btn {
            border: 1px solid var(--border);
            background: var(--bg-input);
            color: var(--text-2);
            padding: 0.4rem 0.3rem;
            border-radius: var(--r-sm);
            font-size: 0.7rem;
            transition: 0.2s;
            cursor: pointer;
        }
        .slot-btn:hover:not(:disabled) { border-color: var(--gold); color: var(--gold); }
        .slot-btn.selected { border-color: var(--gold); background: var(--gold-dim); color: var(--gold); font-weight: 600; }
        .slot-btn.disabled, .slot-btn:disabled { opacity: 0.3; cursor: not-allowed; }
    `;
    document.head.appendChild(style);
})();