@once
@push('scripts')
<script>
function appointmentSchedulerMixin() {
    return {
        viewMonth: '',
        monthOptions: [],
        weekDays: [],
        timeSlots: @json(\App\Support\AppointmentSlotGrid::allTimes()),

        formatSlotLabel(hhmm) {
            if (!hhmm) return '';
            const [h, m] = hhmm.split(':').map(Number);
            const d = new Date(2000, 0, 1, h, m);
            return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }).toLowerCase();
        },

        buildMonthOptions() {
            const opts = [];
            const base = new Date(this.today + 'T12:00:00');
            for (let i = 0; i < 6; i++) {
                const d = new Date(base.getFullYear(), base.getMonth() + i, 1);
                const value = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
                const label = d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                opts.push({ value, label });
            }
            this.monthOptions = opts;
        },

        syncViewMonthFromDate() {
            if (!this.selectedDate) {
                this.viewMonth = this.today.substring(0, 7);
                return;
            }
            this.viewMonth = this.selectedDate.substring(0, 7);
        },

        buildWeekDays() {
            const anchor = this.selectedDate && this.selectedDate >= this.today
                ? this.selectedDate
                : this.today;
            const d = new Date(anchor + 'T12:00:00');
            const dow = d.getDay();
            const mondayOffset = dow === 0 ? -6 : 1 - dow;
            const monday = new Date(d);
            monday.setDate(d.getDate() + mondayOffset);

            const days = [];
            const labels = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
            for (let i = 0; i < 7; i++) {
                const cur = new Date(monday);
                cur.setDate(monday.getDate() + i);
                const y = cur.getFullYear();
                const mo = String(cur.getMonth() + 1).padStart(2, '0');
                const da = String(cur.getDate()).padStart(2, '0');
                const ymd = `${y}-${mo}-${da}`;
                days.push({
                    ymd,
                    dayNum: cur.getDate(),
                    dow: labels[i],
                    isPast: ymd < this.today,
                    isSelected: ymd === this.selectedDate,
                });
            }
            this.weekDays = days;
        },

        selectWeekDay(ymd) {
            if (ymd < this.today) return;
            this.selectedDate = ymd;
            this.syncViewMonthFromDate();
            this.selectedTime = '';
            this.buildWeekDays();
            this.fetchBlocked();
        },

        onMonthChange() {
            if (!this.viewMonth) return;
            const [y, m] = this.viewMonth.split('-').map(Number);
            const currentDay = this.selectedDate
                ? parseInt(this.selectedDate.substring(8, 10), 10)
                : parseInt(this.today.substring(8, 10), 10);
            const lastDay = new Date(y, m, 0).getDate();
            const day = Math.min(currentDay, lastDay);
            let ymd = `${y}-${String(m).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            if (ymd < this.today) {
                ymd = this.today;
            }
            this.selectedDate = ymd;
            this.selectedTime = '';
            this.buildWeekDays();
            this.fetchBlocked();
        },

        shiftWeek(delta) {
            if (!this.selectedDate) return;
            const d = new Date(this.selectedDate + 'T12:00:00');
            d.setDate(d.getDate() + (delta * 7));
            const y = d.getFullYear();
            const mo = String(d.getMonth() + 1).padStart(2, '0');
            const da = String(d.getDate()).padStart(2, '0');
            let ymd = `${y}-${mo}-${da}`;
            if (ymd < this.today) ymd = this.today;
            this.selectWeekDay(ymd);
        },

        initScheduler() {
            this.buildMonthOptions();
            if (!this.selectedDate || this.selectedDate < this.today) {
                this.selectedDate = this.today;
            }
            this.syncViewMonthFromDate();
            this.buildWeekDays();
        },
    };
}
</script>
@endpush
@endonce
