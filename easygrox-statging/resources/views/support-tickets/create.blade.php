@extends('layouts.app')
@section('title', 'New support ticket')
@section('page-title', 'New support ticket')
@section('content')

@php
    $oldBody = old('body', '');
    $categoryLabels = collect(\App\Models\SupportTicket::CATEGORIES)->mapWithKeys(fn ($c) => [$c => \App\Models\SupportTicket::categoryLabel($c)]);
@endphp

<div class="max-w-6xl mx-auto" x-data="supportTicketForm(@js($errors->any()), @js(old('category', '')), @js(old('priority', 'normal')), @js(old('subject', '')))">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('support-tickets.index') }}" class="text-sm font-medium text-velour-600 dark:text-velour-400 hover:underline shrink-0">← All tickets</a>
            <h1 class="text-base sm:text-lg font-bold text-heading leading-tight truncate">Report a store issue</h1>
        </div>
        <p class="text-[11px] text-muted sm:text-right">Expected response · <span class="font-semibold text-heading">Within 24 hours</span></p>
    </div>

    <div class="grid lg:grid-cols-[minmax(0,68%)_minmax(17.5rem,32%)] gap-5 items-start">
        <form method="POST" action="{{ \App\Support\AppUrl::path('support-tickets.store', ['store' => \App\Support\SalonUrl::key($salon)]) }}" enctype="multipart/form-data"
              class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/80 p-4 shadow-sm"
              @submit="onFormSubmit($event)"
              @paste="onFormPaste($event)">
            @csrf

            {{-- Step 1 --}}
            <div x-show="step === 1" class="space-y-3">
                <div class="flex items-center gap-2 text-sm font-semibold text-heading">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-velour-600 text-white text-[11px] font-bold">1</span>
                    Tell us what’s wrong
                </div>

                <div>
                    <label class="form-label mb-0.5">Subject <span class="required-asterisk">*</span></label>
                    <input type="text" name="subject" x-model="subject" maxlength="255"
                           class="st-field w-full rounded-xl border bg-white dark:bg-gray-800 text-sm text-heading px-3 placeholder-gray-400"
                           :class="showErr('subject') ? 'st-err' : 'border-gray-300 dark:border-gray-700'"
                           placeholder="e.g. Online booking slots not showing">
                    <p class="st-err-msg" x-show="showErr('subject')" x-cloak>
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        This field is required.
                    </p>
                    @error('subject')<p class="st-err-msg"><svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>{{ $message }}</p>@enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="form-label mb-0.5">Category <span class="required-asterisk">*</span></label>
                        <select name="category" x-model="category"
                                class="st-field w-full rounded-xl border bg-white dark:bg-gray-800 text-sm text-heading px-3"
                                :class="showErr('category') ? 'st-err' : 'border-gray-300 dark:border-gray-700'">
                            <option value="">Select category</option>
                            @foreach(\App\Models\SupportTicket::CATEGORIES as $c)
                                <option value="{{ $c }}">{{ \App\Models\SupportTicket::categoryLabel($c) }}</option>
                            @endforeach
                        </select>
                        <p class="st-err-msg" x-show="showErr('category')" x-cloak>
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            This field is required.
                        </p>
                    </div>
                    <div>
                        <label class="form-label mb-0.5">Priority <span class="required-asterisk">*</span></label>
                        <input type="hidden" name="priority" :value="priority">
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="opt in priorities" :key="opt.id">
                                <button type="button" @click="priority = opt.id"
                                        class="h-11 px-2.5 rounded-xl border text-[11px] font-semibold inline-flex items-center gap-1"
                                        :class="priority === opt.id ? opt.on : opt.off">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="opt.dot"></span>
                                    <span x-text="opt.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="form-label mb-0.5">What happened? <span class="required-asterisk">*</span></label>
                    <div class="rounded-xl border overflow-hidden bg-white dark:bg-gray-950/50"
                         :class="showErr('body') ? 'st-err' : 'border-gray-300 dark:border-gray-700'">
                        <div class="flex flex-wrap gap-0.5 px-1.5 py-1 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/80">
                            <button type="button" class="st-tb" @click="cmd('bold')" title="Bold"><strong>B</strong></button>
                            <button type="button" class="st-tb" @click="cmd('italic')" title="Italic"><em>I</em></button>
                            <button type="button" class="st-tb" @click="cmd('insertUnorderedList')" title="List">•</button>
                            <button type="button" class="st-tb" @click="insertLink()" title="Link">🔗</button>
                        </div>
                        <div x-ref="editor" contenteditable="true"
                             class="st-editor px-3 py-2 text-[13px] text-heading outline-none overflow-y-auto"
                             data-placeholder="Describe what happened, where it happened, and what you expected to happen."
                             @input="syncBody()"
                             @keyup="syncBody()"
                             @paste="onEditorPaste($event)">{!! \App\Support\SupportTicketHtml::sanitize($oldBody) !!}</div>
                        <textarea name="body" x-ref="body" class="hidden">{{ \App\Support\SupportTicketHtml::sanitize($oldBody) }}</textarea>
                        <div class="flex justify-end px-2 py-0.5 border-t border-gray-100 dark:border-gray-800 text-[10px] text-muted">
                            <span x-text="chars"></span> / 3000
                        </div>
                    </div>
                    <p class="mt-1 text-[11px] text-muted">Example: Store → Online Booking → Monday slots are not appearing for customers.</p>
                    <p class="st-err-msg" x-show="showErr('body')" x-cloak>
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        Please describe what happened (at least 10 characters).
                    </p>
                    @error('body')
                    <p class="st-err-msg" x-show="!showErr('body')">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>{{ $message }}
                    </p>
                    @enderror
                </div>

                <div>
                    <p class="form-label mb-0.5">Attach screenshots</p>
                    <div class="st-drop rounded-xl border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50/70 dark:bg-gray-800/40 px-3 py-2.5 text-center"
                         tabindex="0"
                         :class="dragOver && 'border-velour-400 bg-velour-50/50 dark:bg-velour-950/20'"
                         @dragover.prevent="dragOver = true"
                         @dragleave.prevent="dragOver = false"
                         @drop.prevent="dragOver = false; addFiles($event.dataTransfer.files)"
                         @paste.prevent="onAttachPaste($event)">
                        <input x-ref="files" type="file" name="attachments[]" multiple
                               accept=".png,.jpg,.jpeg,.gif,.pdf,image/png,image/jpeg,image/gif,application/pdf"
                               class="hidden" @change="addFiles($event.target.files)">
                        <p class="text-xs text-heading">Drag &amp; drop screenshots here, paste (Ctrl+V), or
                            <button type="button" class="text-velour-600 dark:text-velour-400 font-semibold hover:underline" @click="$refs.files.click()">Browse</button>
                        </p>
                        <p class="text-[10px] text-muted mt-0.5">PNG, JPG, PDF · Max 10 MB per file · You can paste a copied screenshot</p>
                    </div>
                    @error('attachments')<p class="st-err-msg">{{ $message }}</p>@enderror
                    @error('attachments.*')<p class="st-err-msg">{{ $message }}</p>@enderror
                    <ul class="mt-2 space-y-1.5" x-show="previews.length" x-cloak>
                        <template x-for="(item, idx) in previews" :key="item.id">
                            <li class="flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 px-2 py-1">
                                <img x-show="item.url" :src="item.url" alt="" class="w-9 h-9 rounded object-cover bg-gray-100">
                                <span x-show="!item.url" class="w-9 h-9 rounded bg-gray-100 dark:bg-gray-800 text-[10px] font-bold text-muted inline-flex items-center justify-center">PDF</span>
                                <span class="flex-1 min-w-0 text-xs text-heading truncate" x-text="item.name"></span>
                                <button type="button" class="text-[11px] text-red-500 hover:underline shrink-0" @click="removeFile(idx)">Remove</button>
                            </li>
                        </template>
                    </ul>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 pt-1">
                    <a href="{{ route('support-tickets.index') }}" class="btn-outline btn-sm">Cancel</a>
                    <button type="button" class="btn-primary btn-sm inline-flex items-center gap-1" @click="goReview()">Continue →</button>
                </div>
            </div>

            {{-- Step 2 review --}}
            <div x-show="step === 2" x-cloak class="space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 text-sm font-semibold text-heading">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-velour-600 text-white text-[11px] font-bold">2</span>
                        Review your ticket
                    </div>
                </div>
                <div class="grid sm:grid-cols-3 gap-2">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 px-3 py-2 min-w-0">
                        <p class="text-[10px] uppercase tracking-wide text-muted">Subject</p>
                        <p class="text-sm font-medium text-heading break-words" x-text="subject"></p>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 px-3 py-2 min-w-0">
                        <p class="text-[10px] uppercase tracking-wide text-muted">Category</p>
                        <p class="text-sm font-medium text-heading" x-text="categoryLabel(category)"></p>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 px-3 py-2 min-w-0">
                        <p class="text-[10px] uppercase tracking-wide text-muted">Priority</p>
                        <p class="text-sm font-semibold capitalize" :class="priorityTextClass()" x-text="priority"></p>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-wide text-muted mb-1">Description</p>
                    <div class="rounded-xl bg-gray-50 dark:bg-gray-800/60 px-3 py-2 text-[13px] leading-relaxed text-heading st-ticket-body max-h-28 overflow-y-auto" x-html="bodyHtml"></div>
                </div>
                <div>
                    <p class="text-[10px] uppercase tracking-wide text-muted mb-1.5">Attachments <span class="normal-case tracking-normal font-normal" x-text="previews.length ? '(' + previews.length + ')' : ''"></span></p>
                    <div class="flex flex-wrap gap-2" x-show="previews.length">
                        <template x-for="item in previews" :key="item.id">
                            <div class="w-16 text-center">
                                <img x-show="item.url" :src="item.url" alt="" class="w-16 h-16 rounded-lg object-cover border border-gray-200 dark:border-gray-700 bg-gray-100">
                                <div x-show="!item.url" class="w-16 h-16 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-[10px] font-bold text-muted flex items-center justify-center">PDF</div>
                            </div>
                        </template>
                    </div>
                    <p class="text-xs text-muted" x-show="!previews.length">No files attached.</p>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2 pt-1 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" class="btn-outline btn-sm" @click="step = 1">← Back</button>
                    <button type="submit" class="btn-primary btn-sm inline-flex items-center gap-1">Submit ticket →</button>
                </div>
            </div>
        </form>

        <aside class="space-y-3 lg:sticky lg:top-24">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/80 p-4">
                <h2 class="text-sm font-bold text-heading mb-3">We’re here to help</h2>
                <ul class="space-y-2.5 text-xs">
                    <li class="flex gap-2">
                        <span class="w-7 text-center shrink-0">🕐</span>
                        <div><p class="font-semibold text-heading">Response time</p><p class="text-muted">Usually within 24 hours</p></div>
                    </li>
                    <li class="flex gap-2">
                        <span class="w-7 text-center shrink-0">🔔</span>
                        <div><p class="font-semibold text-heading">Notifications</p><p class="text-muted">Email when support replies</p></div>
                    </li>
                    <li class="flex gap-2">
                        <span class="w-7 text-center shrink-0">🎫</span>
                        <div><p class="font-semibold text-heading">Track ticket</p><p class="text-muted">Available in My Tickets</p></div>
                    </li>
                </ul>
            </div>
            <div class="rounded-2xl border border-amber-200/80 dark:border-amber-800/40 bg-amber-50/80 dark:bg-amber-950/25 px-4 py-3 text-xs text-body" x-show="step === 1">
                <p class="font-semibold text-heading mb-0.5">💡 Quick tip</p>
                Add screenshots of the page and the time you tried it — we can fix store issues faster.
            </div>
            <div class="rounded-2xl border border-velour-200/70 dark:border-velour-800 bg-velour-50/60 dark:bg-velour-950/20 px-4 py-3 text-xs text-body" x-show="step === 2" x-cloak>
                <p class="font-semibold text-heading mb-0.5">Ready to send</p>
                Check the details, then submit. You can go back to edit if something looks off.
            </div>
        </aside>
    </div>
</div>

<style>
    .st-field { height: 44px; }
    .st-editor { min-height: 180px; max-height: 200px; }
    .st-drop { min-height: 100px; display: flex; flex-direction: column; justify-content: center; }
    .st-tb { @apply inline-flex items-center justify-center min-w-[1.6rem] h-7 px-1 rounded-md text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700; }
    .st-err { border-color: rgb(248 113 113 / 0.85) !important; }
    .st-err-msg { @apply mt-1 text-[11px] text-red-500 dark:text-red-400 flex items-center gap-1; }
    .st-editor:empty::before { content: attr(data-placeholder); color: rgb(148 163 184); pointer-events: none; }
    .st-editor ul, .st-ticket-body ul { list-style: disc; padding-left: 1.1rem; }
    .st-editor ol, .st-ticket-body ol { list-style: decimal; padding-left: 1.1rem; }
</style>
<script>
function supportTicketForm(hadServerErrors, category, priority, subject) {
    const labels = @json($categoryLabels);
    return {
        step: 1,
        attempted: !!hadServerErrors,
        subject: subject || '',
        category: category || '',
        priority: priority || 'normal',
        chars: 0,
        bodyHtml: '',
        dragOver: false,
        previews: [],
        files: [],
        priorities: [
            { id: 'low', label: 'Low', on: 'border-gray-300 bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300', off: 'border-gray-200 dark:border-gray-700 text-muted', dot: 'bg-gray-400' },
            { id: 'normal', label: 'Normal', on: 'border-blue-200 bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300', off: 'border-gray-200 dark:border-gray-700 text-muted', dot: 'bg-blue-500' },
            { id: 'high', label: 'High', on: 'border-orange-200 bg-orange-50 text-orange-700 dark:bg-orange-950/40 dark:text-orange-300', off: 'border-gray-200 dark:border-gray-700 text-muted', dot: 'bg-orange-400' },
            { id: 'urgent', label: 'Urgent', on: 'border-red-200 bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300', off: 'border-gray-200 dark:border-gray-700 text-muted', dot: 'bg-red-400' },
        ],
        showErr(field) {
            if (!this.attempted) return false;
            if (field === 'subject') return !this.subject.trim();
            if (field === 'category') return !this.category;
            if (field === 'body') return this.chars < 10;
            return false;
        },
        categoryLabel(id) { return labels[id] || id; },
        priorityTextClass() {
            return {
                low: 'text-gray-500',
                normal: 'text-blue-600 dark:text-blue-400',
                high: 'text-orange-600 dark:text-orange-400',
                urgent: 'text-red-600 dark:text-red-400',
            }[this.priority] || 'text-heading';
        },
        editorEl() {
            return this.$refs.editor || this.$root.querySelector('.st-editor');
        },
        syncBody() {
            const ed = this.editorEl();
            const ta = this.$refs.body;
            if (!ed) return;
            ed.querySelectorAll('img').forEach((img) => img.remove());
            const html = ed.innerHTML.trim();
            if (ta) ta.value = html;
            this.bodyHtml = html;
            const text = (ed.innerText || ed.textContent || '').replace(/\s+/g, ' ').trim();
            this.chars = text.length;
        },
        clipboardImages(e) {
            const files = [];
            const seen = new Set();
            const cd = e.clipboardData;
            if (!cd) return files;
            const push = (file) => {
                if (!file || !file.type || file.type.indexOf('image/') !== 0) return;
                const key = file.size + ':' + file.type + ':' + (file.lastModified || 0);
                if (seen.has(key)) return;
                seen.add(key);
                files.push(file);
            };
            const items = Array.from(cd.items || []).filter((item) => item.kind === 'file' && item.type && item.type.indexOf('image/') === 0);
            if (items.length) {
                items.forEach((item) => push(item.getAsFile()));
            } else {
                Array.from(cd.files || []).forEach(push);
            }
            return files;
        },
        onEditorPaste(e) {
            const images = this.clipboardImages(e);
            if (images.length) {
                e.preventDefault();
                e.stopPropagation();
                this.addFiles(images);
                const text = (e.clipboardData && e.clipboardData.getData('text/plain')) || '';
                if (text) document.execCommand('insertText', false, text);
                this.$nextTick(() => this.syncBody());
                return;
            }
            this.$nextTick(() => this.syncBody());
        },
        onAttachPaste(e) {
            const images = this.clipboardImages(e);
            if (images.length) {
                e.stopPropagation();
                this.addFiles(images);
            }
        },
        onFormPaste(e) {
            const ed = this.editorEl();
            if (ed && ed.contains(e.target)) return;
            const images = this.clipboardImages(e);
            if (!images.length) return;
            if (e.target && e.target.matches && e.target.matches('input[type="text"]')) return;
            e.preventDefault();
            this.addFiles(images);
        },
        cmd(name, value) {
            const ed = this.editorEl();
            if (ed) ed.focus();
            document.execCommand(name, false, value || null);
            this.syncBody();
        },
        insertLink() {
            const url = window.prompt('Link URL (https://…)');
            if (url) this.cmd('createLink', url);
        },
        addFiles(list) {
            const incoming = Array.from(list || []);
            incoming.forEach((raw) => {
                if (this.files.length >= 5) return;
                let file = raw;
                if (!file || (file.type && file.type.indexOf('image/') !== 0 && file.type !== 'application/pdf')) {
                    if (!file || !/\.(png|jpe?g|gif|pdf)$/i.test(file.name || '')) return;
                }
                if (file.size > 10 * 1024 * 1024) return;
                const dup = this.files.some((f) => f.file && f.file.size === file.size && f.file.type === file.type);
                if (dup) return;
                const ext = (file.type && file.type.split('/')[1]) ? file.type.split('/')[1].replace('jpeg', 'jpg') : 'png';
                const name = (file.name && file.name !== 'image.png' && file.name !== 'blob') ? file.name : ('screenshot-' + Date.now() + '.' + ext);
                if (!file.name || file.name === 'image.png') {
                    file = new File([file], name, { type: file.type || 'image/png' });
                }
                const id = name + '-' + file.size + '-' + (file.lastModified || Date.now()) + '-' + Math.random();
                const item = { id, file, name, url: null };
                if (file.type && file.type.indexOf('image/') === 0) item.url = URL.createObjectURL(file);
                this.files.push(item);
            });
            this.syncInput();
            this.previews = this.files.slice();
        },
        removeFile(idx) {
            const gone = this.files.splice(idx, 1)[0];
            if (gone && gone.url) URL.revokeObjectURL(gone.url);
            this.syncInput();
            this.previews = this.files;
        },
        syncInput() {
            const input = this.$refs.files;
            if (!input || !window.DataTransfer) return;
            const dt = new DataTransfer();
            this.files.forEach((item) => dt.items.add(item.file));
            input.files = dt.files;
        },
        goReview() {
            this.syncBody();
            this.attempted = true;
            if (!this.subject.trim() || this.chars < 10 || !this.category) return;
            this.step = 2;
        },
        onFormSubmit(e) {
            this.syncBody();
            if (this.step !== 2) {
                e.preventDefault();
                this.goReview();
            }
        },
        init() { this.$nextTick(() => this.syncBody()); },
    };
}
</script>
@endsection
