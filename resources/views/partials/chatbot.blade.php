@php
    $isAdmin      = $isAdminLayout ?? false;
    $chatRoute    = $isAdmin ? route('admin.chatbot.message') : route('chatbot.message');
    $suggestions  = $isAdmin
        ? ['Platform overview', 'Active tenants', 'Revenue this month', 'Open tickets', 'Find salon']
        : ['Appointments today', 'Revenue this month', 'Low stock items', 'New clients', 'Unreplied reviews'];
    $headerBg     = $isAdmin ? 'bg-velour-700' : 'bg-velour-600';
    $bubbleBg     = $isAdmin ? 'bg-gray-800 text-gray-200' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300';
    $windowBg     = $isAdmin ? 'bg-gray-900 border-gray-700' : 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700';
    $inputBg      = $isAdmin ? 'bg-gray-800 border-gray-700 text-gray-100 placeholder-gray-500' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500';
    $chipBg       = $isAdmin ? 'bg-gray-800 border-gray-700 text-gray-400 hover:bg-velour-900/40 hover:border-velour-700 hover:text-velour-300' : 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-velour-50 dark:hover:bg-velour-900/30 hover:border-velour-300 dark:hover:border-velour-700 hover:text-velour-700 dark:hover:text-velour-300';
@endphp

<div x-data="chatbot()" class="fixed bottom-5 right-5 z-50 flex flex-col items-end gap-3">

    {{-- Chat window --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="w-80 sm:w-96 rounded-2xl shadow-2xl border flex flex-col overflow-hidden {{ $windowBg }}"
         style="max-height:540px;">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 {{ $headerBg }}">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white leading-none">
                        {{ $isAdmin ? 'Admin Assistant' : 'Velour Assistant' }}
                    </p>
                    <p class="text-xs text-velour-200 mt-0.5" x-text="listening ? '🎙 Listening…' : (voiceMode ? '🔊 Voice mode on' : 'Ask me anything')"></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                {{-- Voice mode toggle (auto-speak replies) --}}
                <button @click="toggleVoiceMode()"
                        :title="voiceMode ? 'Disable auto-speak' : 'Enable auto-speak'"
                        :class="voiceMode ? 'text-white' : 'text-white/50 hover:text-white'"
                        class="transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.536 8.464a5 5 0 010 7.072M12 6v12m0 0l-3-3m3 3l3-3M9 9a3 3 0 000 6"/>
                    </svg>
                </button>
                <button @click="open=false" class="text-white/70 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Voice listening overlay --}}
        <div x-show="listening" x-cloak
             class="absolute inset-0 z-10 flex flex-col items-center justify-center rounded-2xl bg-black/80 backdrop-blur-sm">
            <div class="relative flex items-center justify-center mb-5">
                <span class="absolute w-24 h-24 rounded-full bg-velour-500/20 animate-ping"></span>
                <span class="absolute w-16 h-16 rounded-full bg-velour-500/30 animate-ping" style="animation-delay:150ms"></span>
                <button @click="stopListening()"
                        class="relative w-14 h-14 rounded-full bg-velour-600 hover:bg-red-600 text-white flex items-center justify-center transition-colors shadow-lg">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 1a4 4 0 014 4v7a4 4 0 01-8 0V5a4 4 0 014-4zm-1 18.93V22h2v-2.07A8.001 8.001 0 0020 12h-2a6 6 0 01-12 0H4a8.001 8.001 0 007 7.93z"/>
                    </svg>
                </button>
            </div>
            <p class="text-white text-sm font-medium">Listening…</p>
            <p class="text-gray-400 text-xs mt-1" x-text="interimText || 'Speak now'"></p>
            <button @click="stopListening()" class="mt-4 text-xs text-gray-500 hover:text-white transition-colors">Tap mic to send</button>
        </div>

        {{-- Messages --}}
        <div id="chat-messages"
             class="flex-1 overflow-y-auto px-4 py-3 space-y-3 scroll-smooth"
             style="min-height:280px;max-height:340px;">

            {{-- Welcome --}}
            <div class="flex gap-2">
                <div class="w-6 h-6 rounded-full bg-velour-100 dark:bg-velour-900 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5 text-velour-600 dark:text-velour-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <div class="rounded-2xl rounded-tl-sm px-3 py-2 max-w-[85%] {{ $bubbleBg }}">
                    <p class="text-xs">
                        @if($isAdmin)
                            Hi Super Admin! Ask me about tenants, revenue, users, plans, support tickets, or find a specific salon.
                        @else
                            Hi! Ask me about appointments, revenue, clients, staff, inventory, reviews, and more.
                        @endif
                    </p>
                </div>
            </div>

            {{-- Dynamic messages --}}
            <template x-for="(msg, i) in messages" :key="i">
                <div>
                    {{-- User bubble --}}
                    <div x-show="msg.role==='user'" class="flex justify-end items-end gap-1.5">
                        <div class="bg-velour-600 text-white rounded-2xl rounded-tr-sm px-3 py-2 max-w-[80%]">
                            <p class="text-xs" x-text="msg.text"></p>
                        </div>
                    </div>
                    {{-- Bot bubble --}}
                    <div x-show="msg.role==='bot'" class="flex gap-2 items-end">
                        <div class="w-6 h-6 rounded-full bg-velour-100 dark:bg-velour-900 flex items-center justify-center flex-shrink-0 mb-0.5">
                            <svg class="w-3.5 h-3.5 text-velour-600 dark:text-velour-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                        <div class="rounded-2xl rounded-tl-sm px-3 py-2 max-w-[80%] space-y-1.5 {{ $bubbleBg }}">
                            <p class="text-xs whitespace-pre-line" x-html="fmt(msg.text)"></p>
                            <div class="flex items-center gap-2">
                                <a x-show="msg.link" :href="msg.link"
                                   class="inline-flex items-center gap-1 text-[11px] font-medium text-velour-400 hover:underline">
                                    View →
                                </a>
                                {{-- Speak this message --}}
                                <button @click="speak(msg.text, i)"
                                        :title="speakingIdx===i ? 'Stop' : 'Read aloud'"
                                        :class="speakingIdx===i ? 'text-velour-400 animate-pulse' : 'text-gray-400 hover:text-velour-400'"
                                        class="transition-colors ml-auto">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15.536 8.464a5 5 0 010 7.072M18.364 5.636a9 9 0 010 12.728M12 6v12m0 0l-3-3m3 3l3-3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="typing" class="flex gap-2">
                <div class="w-6 h-6 rounded-full bg-velour-100 dark:bg-velour-900 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5 text-velour-600 dark:text-velour-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <div class="rounded-2xl rounded-tl-sm px-3 py-2.5 flex items-center gap-1 {{ $bubbleBg }}">
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                </div>
            </div>
        </div>

        {{-- Quick chips --}}
        <div class="px-3 pb-2 flex flex-wrap gap-1.5">
            @foreach($suggestions as $s)
            <button @click="sendSuggestion('{{ $s }}')"
                    class="px-2.5 py-1 text-[11px] font-medium rounded-full border transition-colors {{ $chipBg }}">
                {{ $s }}
            </button>
            @endforeach
        </div>

        {{-- Input row --}}
        <div class="px-3 pb-3">
            <form @submit.prevent="send()" class="flex items-center gap-2">
                {{-- Mic button --}}
                <button type="button" @click="toggleListening()"
                        :disabled="typing"
                        :title="listening ? 'Stop listening' : (voiceSupported ? 'Voice input' : 'Voice not supported in this browser')"
                        :class="[
                            'w-8 h-8 rounded-xl flex items-center justify-center transition-colors flex-shrink-0 disabled:opacity-40',
                            listening
                                ? 'bg-red-500 hover:bg-red-600 text-white animate-pulse'
                                : (voiceSupported ? 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-velour-100 dark:hover:bg-velour-900/40 hover:text-velour-600 dark:hover:text-velour-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed')
                        ]">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 1a4 4 0 014 4v7a4 4 0 01-8 0V5a4 4 0 014-4zm-1 18.93V22h2v-2.07A8.001 8.001 0 0020 12h-2a6 6 0 01-12 0H4a8.001 8.001 0 007 7.93z"/>
                    </svg>
                </button>

                <input x-model="input" type="text"
                       :placeholder="listening ? 'Listening…' : 'Ask something…'"
                       :disabled="typing"
                       class="flex-1 rounded-xl border px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent disabled:opacity-50 {{ $inputBg }}" />

                {{-- Send button --}}
                <button type="submit" :disabled="!input.trim()||typing"
                        class="w-8 h-8 rounded-xl bg-velour-600 hover:bg-velour-700 text-white flex items-center justify-center transition-colors disabled:opacity-40 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>

            {{-- No-support notice --}}
            <p x-show="!voiceSupported" class="text-[10px] text-gray-400 mt-1 text-center">
                Voice input requires Chrome or Edge
            </p>
        </div>
    </div>

    {{-- FAB --}}
    <button @click="open=!open"
            class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-all bg-velour-600 hover:bg-velour-700 text-white relative">
        <svg x-show="!open" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
        </svg>
        <svg x-show="open" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span x-show="unread&&!open"
              class="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full border-2 border-white dark:border-gray-950"></span>
        {{-- Mic active indicator on FAB --}}
        <span x-show="listening"
              class="absolute inset-0 rounded-full border-2 border-red-400 animate-ping pointer-events-none"></span>
    </button>
</div>

@push('scripts')
<script>
function chatbot() {
    return {
        // ── state ──────────────────────────────────────────────────────────────
        open: false,
        input: '',
        typing: false,
        unread: false,
        messages: [],

        // voice
        voiceSupported: false,
        listening: false,
        voiceMode: false,
        interimText: '',
        speakingIdx: null,
        recognition: null,
        synth: window.speechSynthesis || null,

        // ── lifecycle ──────────────────────────────────────────────────────────
        init() {
            this.voiceSupported = !!( window.SpeechRecognition || window.webkitSpeechRecognition );
            this.$watch('open', v => {
                if (v) { this.unread = false; this.$nextTick(() => this.scrollBottom()); }
                else   { this.stopListening(); this.stopSpeaking(); }
            });
        },

        // ── text send ──────────────────────────────────────────────────────────
        send() {
            const text = this.input.trim();
            if (!text) return;
            this.input = '';
            this.stopSpeaking();
            this.messages.push({ role: 'user', text });
            this.typing = true;
            this.$nextTick(() => this.scrollBottom());

            fetch('{{ $chatRoute }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message: text }),
            })
            .then(r => r.json())
            .then(data => {
                this.typing = false;
                const idx = this.messages.length;
                this.messages.push({ role: 'bot', text: data.text, link: data.link || null });
                if (!this.open) this.unread = true;
                this.$nextTick(() => this.scrollBottom());
                if (this.voiceMode) this.speak(data.text, idx);
            })
            .catch(() => {
                this.typing = false;
                this.messages.push({ role: 'bot', text: 'Something went wrong. Please try again.', link: null });
                this.$nextTick(() => this.scrollBottom());
            });
        },

        sendSuggestion(t) { this.input = t; this.send(); },

        // ── voice input ────────────────────────────────────────────────────────
        toggleListening() {
            if (!this.voiceSupported) return;
            this.listening ? this.stopListening() : this.startListening();
        },

        startListening() {
            if (this.listening) return;
            this.stopSpeaking();

            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.recognition = new SR();
            this.recognition.lang = 'en-US';
            this.recognition.interimResults = true;
            this.recognition.maxAlternatives = 1;
            this.recognition.continuous = false;

            this.recognition.onstart = () => {
                this.listening = true;
                this.interimText = '';
            };

            this.recognition.onresult = (e) => {
                let interim = '', final = '';
                for (let i = e.resultIndex; i < e.results.length; i++) {
                    const t = e.results[i][0].transcript;
                    e.results[i].isFinal ? (final += t) : (interim += t);
                }
                this.interimText = interim;
                if (final) {
                    this.input = final.trim();
                    this.interimText = '';
                }
            };

            this.recognition.onend = () => {
                this.listening = false;
                this.interimText = '';
                if (this.input.trim()) this.send();
            };

            this.recognition.onerror = (e) => {
                this.listening = false;
                this.interimText = '';
                if (e.error === 'not-allowed') {
                    alert('Microphone access denied. Please allow mic access in your browser settings.');
                }
            };

            this.recognition.start();
        },

        stopListening() {
            if (this.recognition) {
                try { this.recognition.stop(); } catch(e) {}
                this.recognition = null;
            }
            this.listening = false;
            this.interimText = '';
        },

        // ── voice output ───────────────────────────────────────────────────────
        speak(text, idx) {
            if (!this.synth) return;
            if (this.speakingIdx === idx) { this.stopSpeaking(); return; }
            this.stopSpeaking();

            const clean = text.replace(/\*\*(.*?)\*\*/g, '$1').replace(/\n/g, '. ');
            const utt = new SpeechSynthesisUtterance(clean);
            utt.lang  = 'en-US';
            utt.rate  = 1.0;
            utt.pitch = 1.0;

            const voices = this.synth.getVoices();
            const preferred = voices.find(v =>
                v.lang.startsWith('en') && (v.name.includes('Google') || v.name.includes('Natural') || v.name.includes('Samantha'))
            ) || voices.find(v => v.lang.startsWith('en'));
            if (preferred) utt.voice = preferred;

            utt.onstart = () => { this.speakingIdx = idx; };
            utt.onend   = () => { this.speakingIdx = null; };
            utt.onerror = () => { this.speakingIdx = null; };

            this.synth.speak(utt);
        },

        stopSpeaking() {
            if (this.synth && this.synth.speaking) this.synth.cancel();
            this.speakingIdx = null;
        },

        toggleVoiceMode() {
            this.voiceMode = !this.voiceMode;
            if (!this.voiceMode) this.stopSpeaking();
        },

        // ── helpers ────────────────────────────────────────────────────────────
        scrollBottom() {
            const el = document.getElementById('chat-messages');
            if (el) el.scrollTop = el.scrollHeight;
        },

        fmt(t) {
            return t
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>');
        },
    }
}
</script>
@endpush
