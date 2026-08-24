@php
    $gapClass = $gapClass ?? 'gap-6';
    $ariaLabel = $ariaLabel ?? 'Scrollable row';
    $extraClass = $class ?? '';
    $trackClass = $trackClass ?? '';
@endphp
<div class="w-full min-w-0 {{ $extraClass }}" x-data="horizontalDragScroll()">
    <div
        x-ref="track"
        role="region"
        aria-label="{{ $ariaLabel }}"
        @mousedown="onMouseDown($event)"
        @mouseleave="endDrag()"
        @mouseup="endDrag()"
        @mousemove="onMouseMove($event)"
        :class="grabbing ? 'cursor-grabbing snap-none' : 'cursor-grab'"
        class="flex items-stretch {{ $gapClass }} {{ $trackClass }} overflow-x-auto scrollbar-none scroll-smooth snap-x snap-mandatory py-2 w-full min-w-0 touch-pan-x select-none"
    >
        {{ $slot }}
    </div>
</div>

@once
@push('scripts')
<script>
function horizontalDragScroll() {
    return {
        grabbing: false,
        isDragging: false,
        startX: 0,
        scrollStart: 0,
        init() {
            const el = this.$refs.track;
            if (!el) return;
            this._onWheel = (e) => this.onWheel(e);
            el.addEventListener('wheel', this._onWheel, { passive: false });
        },
        destroy() {
            if (this.$refs.track && this._onWheel) {
                this.$refs.track.removeEventListener('wheel', this._onWheel);
            }
        },
        onMouseDown(e) {
            if (e.button !== 0 || !this.$refs.track) return;
            this.isDragging = true;
            this.startX = e.pageX;
            this.scrollStart = this.$refs.track.scrollLeft;
            this.grabbing = true;
            document.addEventListener('mouseup', this.endDragBound = () => this.endDrag());
        },
        onMouseMove(e) {
            if (!this.isDragging || !this.$refs.track) return;
            this.$refs.track.scrollLeft = this.scrollStart - (e.pageX - this.startX);
        },
        onWheel(e) {
            const el = this.$refs.track;
            if (!el) return;
            const overflowX = el.scrollWidth > el.clientWidth + 1;
            if (!overflowX) return;
            if (Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return;
            const atStart = el.scrollLeft <= 0;
            const atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 1;
            if ((e.deltaY < 0 && atStart) || (e.deltaY > 0 && atEnd)) return;
            e.preventDefault();
            el.scrollLeft += e.deltaY;
        },
        endDrag() {
            this.isDragging = false;
            this.grabbing = false;
            if (this.endDragBound) {
                document.removeEventListener('mouseup', this.endDragBound);
            }
        },
    };
}
</script>
@endpush
@endonce
