<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Book at {{ $salon->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; min-height: 100vh; }
        [x-cloak] { display: none !important; }

        /* ── Gradient background ── */
        .page-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            padding: 0 0 60px;
        }

        /* ── Header ── */
        .booking-header {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.2);
            position: sticky; top: 0; z-index: 50;
        }

        /* ── Main card ── */
        .booking-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.15), 0 8px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        /* ── Step progress ── */
        .step-pill {
            display: flex; align-items: center; gap: 6px;
            padding: 6px 14px; border-radius: 100px;
            font-size: 12px; font-weight: 600;
            transition: all 0.3s ease;
        }
        .step-pill.done { background: rgba(16,185,129,0.15); color: #059669; }
        .step-pill.active { background: white; color: #7c3aed; box-shadow: 0 4px 12px rgba(124,58,237,0.25); }
        .step-pill.idle { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.6); }
        .step-num {
            width: 20px; height: 20px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 10px; font-weight: 800;
        }
        .step-pill.done .step-num { background: #10b981; color: white; }
        .step-pill.active .step-num { background: #7c3aed; color: white; }
        .step-pill.idle .step-num { background: rgba(255,255,255,0.2); color: rgba(255,255,255,0.7); }

        /* ── Service cards ── */
        .svc-card {
            border: 2px solid #f1f5f9; border-radius: 16px;
            padding: 16px 18px; cursor: pointer;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            background: white; width: 100%; text-align: left;
            transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
        }
        .svc-card:hover { border-color: #c4b5fd; background: #faf5ff; transform: translateY(-1px); box-shadow: 0 8px 20px rgba(124,58,237,0.1); }
        .svc-card.selected { border-color: #7c3aed; background: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%); box-shadow: 0 0 0 4px rgba(124,58,237,0.1), 0 8px 20px rgba(124,58,237,0.15); }

        /* ── Staff cards ── */
        .staff-card {
            border: 2px solid #f1f5f9; border-radius: 16px;
            padding: 14px 18px; cursor: pointer;
            display: flex; align-items: center; gap: 14px;
            background: white; width: 100%; text-align: left;
            transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
        }
        .staff-card:hover { border-color: #c4b5fd; background: #faf5ff; transform: translateY(-1px); box-shadow: 0 8px 20px rgba(124,58,237,0.1); }
        .staff-card.selected { border-color: #7c3aed; background: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%); box-shadow: 0 0 0 4px rgba(124,58,237,0.1); }

        /* ── Time slots ── */
        .time-slot {
            border: 2px solid #e8ecf0; border-radius: 12px;
            padding: 11px 6px; text-align: center;
            font-size: 13px; font-weight: 600; color: #374151;
            cursor: pointer; background: white;
            transition: all 0.18s cubic-bezier(0.4,0,0.2,1);
            position: relative; overflow: hidden;
        }
        .time-slot:hover { border-color: #7c3aed; color: #7c3aed; background: #faf5ff; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(124,58,237,0.15); }
        .time-slot.selected { background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); color: white; border-color: #7c3aed; box-shadow: 0 8px 20px rgba(124,58,237,0.35); transform: translateY(-2px); }

        /* ── Inputs ── */
        .input-field {
            width: 100%; border: 2px solid #e8ecf0; border-radius: 12px;
            padding: 12px 16px; font-size: 14px; font-family: inherit;
            outline: none; transition: all 0.2s ease; background: #fafbfc;
            color: #111827;
        }
        .input-field::placeholder { color: #9ca3af; }
        .input-field:focus { border-color: #7c3aed; background: white; box-shadow: 0 0 0 4px rgba(124,58,237,0.08); }
        .input-field:hover:not(:focus) { border-color: #c4b5fd; }

        /* ── Primary button ── */
        .btn-primary {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            color: white; border-radius: 14px; font-weight: 700;
            padding: 15px 28px; border: none; cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
            box-shadow: 0 6px 20px rgba(124,58,237,0.35);
            font-size: 15px; letter-spacing: 0.01em;
        }
        .btn-primary:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(124,58,237,0.45); background: linear-gradient(135deg, #6d28d9 0%, #5b21b6 100%); }
        .btn-primary:active:not(:disabled) { transform: translateY(0); }
        .btn-primary:disabled { opacity: 0.45; cursor: not-allowed; transform: none; box-shadow: none; }

        /* ── Summary rows ── */
        .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .summary-row:last-child { border-bottom: none; }

        /* ── Date input ── */
        input[type="date"] { color-scheme: light; }

        /* ── Animations ── */
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes scaleIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .animate-in { animation: fadeIn 0.3s ease forwards; }
        .scale-in { animation: scaleIn 0.25s ease forwards; }

        /* ── Skeleton loader ── */
        .skeleton { background: linear-gradient(90deg, #f1f5f9 25%, #e8ecf0 50%, #f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 10px; }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

        /* ── Category label ── */
        .cat-label { font-size: 10px; font-weight: 800; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 10px; padding-left: 2px; }

        /* ── Price badge ── */
        .price-badge { background: linear-gradient(135deg, #7c3aed15, #6d28d915); color: #7c3aed; font-weight: 800; font-size: 15px; padding: 6px 12px; border-radius: 10px; white-space: nowrap; }

        /* ── Avatar ── */
        .staff-avatar { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 18px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

        /* ── Date nav ── */
        .date-nav-btn { width: 36px; height: 36px; border-radius: 10px; border: 2px solid #e8ecf0; background: white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; transition: all 0.15s; color: #374151; }
        .date-nav-btn:hover { border-color: #7c3aed; color: #7c3aed; background: #faf5ff; }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
    </style>
</head>
<body>
<div class="page-bg">

<div x-data="bookingApp()" x-init="init()">

    {{-- ══ HEADER ══ --}}
    <header class="booking-header">
        <div style="max-width:700px; margin:0 auto; padding:14px 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">

                {{-- Salon identity --}}
                <div style="display:flex; align-items:center; gap:12px;">
                    @if($salon->logo)
                    <img src="{{ asset('storage/' . $salon->logo) }}" alt="{{ $salon->name }}"
                         style="width:42px;height:42px;border-radius:12px;object-fit:cover;box-shadow:0 4px 12px rgba(0,0,0,0.2);">
                    @else
                    <div style="width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,0.25);backdrop-filter:blur(10px);display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:18px;box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                        {{ strtoupper(substr($salon->name, 0, 1)) }}
                    </div>
                    @endif
                    <div>
                        <div style="font-weight:700;font-size:16px;color:white;text-shadow:0 1px 3px rgba(0,0,0,0.2);">{{ $salon->name }}</div>
                        @if($salon->address_line1)
                        <div style="font-size:11px;color:rgba(255,255,255,0.75);">📍 {{ $salon->address_line1 }}{{ $salon->city ? ', '.$salon->city : '' }}</div>
                        @endif
                    </div>
                </div>

                {{-- Step pills --}}
                <div style="display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                    @php $stepLabels = ['Services','Staff','Date & Time','Details','Confirm']; @endphp
                    @foreach($stepLabels as $i => $label)
                    <div class="step-pill"
                         :class="step > {{ $i }} ? 'done' : (step === {{ $i }} ? 'active' : 'idle')">
                        <div class="step-num">
                            <span x-text="step > {{ $i }} ? '✓' : '{{ $i+1 }}'"></span>
                        </div>
                        <span style="display:none;" class="sm:inline">{{ $label }}</span>
                    </div>
                    @if($i < count($stepLabels)-1)
                    <div style="width:16px;height:2px;border-radius:2px;opacity:0.3;background:white;"></div>
                    @endif
                    @endforeach
                </div>

            </div>
        </div>
    </header>

    {{-- ══ MAIN CONTENT ══ --}}
    <main style="max-width:700px;margin:0 auto;padding:28px 16px 80px;">

        {{-- Loading skeleton --}}
        <div x-show="loading" class="booking-card animate-in" style="padding:32px;">
            <div class="skeleton" style="height:24px;width:40%;margin-bottom:8px;"></div>
            <div class="skeleton" style="height:14px;width:60%;margin-bottom:28px;"></div>
            <div class="skeleton" style="height:80px;margin-bottom:10px;"></div>
            <div class="skeleton" style="height:80px;margin-bottom:10px;"></div>
            <div class="skeleton" style="height:80px;"></div>
        </div>

        {{-- Global error --}}
        <div x-show="globalError && !loading" x-cloak
             style="background:#fef2f2;border:2px solid #fecaca;border-radius:16px;padding:16px 18px;margin-bottom:16px;color:#dc2626;font-size:14px;display:flex;align-items:center;gap:10px;"
             class="animate-in">
            <span style="font-size:20px;">⚠️</span>
            <span x-text="globalError"></span>
        </div>


        {{-- ══ STEP 0: Services ══ --}}
        <div x-show="step === 0 && !loading" x-cloak class="animate-in">
            <div class="booking-card" style="padding:28px 24px;">
                <div style="margin-bottom:24px;">
                    <h2 style="font-size:22px;font-weight:800;color:#111827;margin-bottom:4px;">Choose your services</h2>
                    <p style="font-size:13px;color:#9ca3af;">Select one or more services — duration and price combine for your visit</p>
                </div>

                <div x-show="allServices.length === 0"
                     style="text-align:center;padding:48px 20px;color:#9ca3af;">
                    <div style="font-size:48px;margin-bottom:12px;">✂️</div>
                    <p style="font-size:15px;font-weight:600;color:#374151;margin-bottom:4px;">No services available</p>
                    <p style="font-size:13px;">Online booking is not currently available.</p>
                </div>

                <template x-for="cat in allServices" :key="cat.name">
                    <div style="margin-bottom:20px;">
                        <div class="cat-label" x-show="cat.name !== 'Uncategorised'" x-text="cat.name"></div>
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <template x-for="svc in cat.services" :key="svc.id">
                                <button type="button"
                                        @click="toggleService(svc)"
                                        class="svc-card"
                                        :class="isServiceSelected(svc) ? 'selected' : ''">
                                    <div style="flex:1;min-width:0;">
                                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                            <span style="font-weight:700;font-size:15px;color:#111827;" x-text="svc.name"></span>
                                            <span x-show="isServiceSelected(svc)"
                                                  style="width:18px;height:18px;background:#7c3aed;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:10px;flex-shrink:0;">✓</span>
                                        </div>
                                        <div style="font-size:12px;color:#9ca3af;display:flex;align-items:center;gap:8px;">
                                            <span>⏱ <span x-text="(svc.duration_minutes ?? 0) + ' min'"></span></span>
                                            <span x-show="svc.description" x-text="'· ' + (svc.description || '').substring(0,55)"></span>
                                        </div>
                                    </div>
                                    <div class="price-badge" x-text="CURRENCY + parseFloat(svc.price || 0).toFixed(2)"></div>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                <div x-show="allServices.length > 0" style="margin-top:20px;padding-top:20px;border-top:2px solid #f1f5f9;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:13px;color:#6b7280;">
                        <span>Selected</span>
                        <span style="font-weight:700;color:#111827;" x-text="selected.services.length + ' service(s)'"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;font-size:13px;color:#6b7280;">
                        <span>Total time (services)</span>
                        <span style="font-weight:700;color:#7c3aed;" x-text="totalServiceMinutes() + ' min'"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;font-size:15px;">
                        <span style="font-weight:700;color:#111827;">Estimated total</span>
                        <span style="font-weight:800;color:#7c3aed;font-size:18px;" x-text="CURRENCY + totalPrice().toFixed(2)"></span>
                    </div>
                    <button type="button" @click="continueToStaff()"
                            class="btn-primary"
                            style="width:100%;"
                            :disabled="selected.services.length === 0">
                        Continue to team →
                    </button>
                </div>
            </div>
        </div>


        {{-- ══ STEP 1: Staff ══ --}}
        <div x-show="step === 1 && !loading" x-cloak class="animate-in">
            <div class="booking-card" style="padding:28px 24px;">
                <div style="margin-bottom:24px;">
                    <h2 style="font-size:22px;font-weight:800;color:#111827;margin-bottom:4px;">Choose a team member</h2>
                    <p style="font-size:13px;color:#9ca3af;">Pick who you'd like to be seen by, or let us choose</p>
                </div>

                <div style="display:flex;flex-direction:column;gap:8px;">
                    {{-- Any available --}}
                    <button @click="selectStaff(null)"
                            class="staff-card"
                            :class="selected.staff === null && step > 1 ? 'selected' : ''">
                        <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#f1f5f9,#e8ecf0);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;box-shadow:0 4px 12px rgba(0,0,0,0.08);">🎲</div>
                        <div style="flex:1;">
                            <div style="font-weight:700;font-size:15px;color:#111827;">Any available</div>
                            <div style="font-size:12px;color:#9ca3af;margin-top:2px;">We'll assign the best available team member</div>
                        </div>
                        <div style="width:20px;height:20px;border-radius:50%;border:2px solid #e8ecf0;display:flex;align-items:center;justify-content:center;flex-shrink:0;"
                             :style="selected.staff === null && step > 1 ? 'background:#7c3aed;border-color:#7c3aed;' : ''">
                            <span x-show="selected.staff === null && step > 1" style="color:white;font-size:10px;">✓</span>
                        </div>
                    </button>

                    {{-- Staff list --}}
                    <template x-for="member in staffList" :key="member.id">
                        <button @click="selectStaff(member)"
                                class="staff-card"
                                :class="selected.staff?.id === member.id ? 'selected' : ''">
                            <div class="staff-avatar"
                                 :style="`background: linear-gradient(135deg, ${member.color || '#7c3aed'}, ${member.color || '#6d28d9'})`"
                                 x-text="(member.first_name || '?').charAt(0).toUpperCase()"></div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:700;font-size:15px;color:#111827;"
                                     x-text="(member.first_name || '') + ' ' + (member.last_name || '')"></div>
                                <div style="font-size:12px;color:#9ca3af;margin-top:2px;text-transform:capitalize;"
                                     x-text="(member.role || '').replace(/_/g,' ')"></div>
                            </div>
                            <div style="width:20px;height:20px;border-radius:50%;border:2px solid #e8ecf0;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:all 0.15s;"
                                 :style="selected.staff?.id === member.id ? 'background:#7c3aed;border-color:#7c3aed;' : ''">
                                <span x-show="selected.staff?.id === member.id" style="color:white;font-size:10px;">✓</span>
                            </div>
                        </button>
                    </template>

                    {{-- Loading staff --}}
                    <div x-show="staffLoading" style="display:flex;flex-direction:column;gap:8px;">
                        <div class="skeleton" style="height:76px;border-radius:16px;"></div>
                        <div class="skeleton" style="height:76px;border-radius:16px;"></div>
                    </div>
                </div>

                <button @click="step = 0"
                        style="margin-top:20px;color:#9ca3af;font-size:13px;font-weight:500;background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;">
                    ← Back to services
                </button>
            </div>
        </div>


        {{-- ══ STEP 2: Date & Time ══ --}}
        <div x-show="step === 2 && !loading" x-cloak class="animate-in">
            <div class="booking-card" style="padding:28px 24px;">
                <div style="margin-bottom:24px;">
                    <h2 style="font-size:22px;font-weight:800;color:#111827;margin-bottom:4px;">Pick a date &amp; time</h2>
                    <p style="font-size:13px;color:#9ca3af;">Choose when you'd like to come in</p>
                </div>

                {{-- Selected services summary --}}
                <div style="background:linear-gradient(135deg,#faf5ff,#f5f3ff);border:2px solid #e9d5ff;border-radius:14px;padding:12px 16px;margin-bottom:20px;">
                    <div style="display:flex;align-items:flex-start;gap:10px;">
                        <div style="width:36px;height:36px;background:linear-gradient(135deg,#7c3aed,#6d28d9);border-radius:10px;display:flex;align-items:center;justify-content:center;color:white;font-size:16px;flex-shrink:0;">✂️</div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:14px;color:#111827;line-height:1.35;" x-text="serviceNamesSummary()"></div>
                            <div style="font-size:12px;color:#7c3aed;margin-top:4px;">
                                <span x-text="(combinedInfo?.duration_minutes ?? totalServiceMinutes()) + ' min services'"></span>
                                <span x-show="combinedInfo"> · </span>
                                <span x-show="combinedInfo" x-text="(combinedInfo?.appointment_minutes ?? '') + ' min with buffers'"></span>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;justify-content:flex-end;margin-top:10px;font-weight:800;font-size:16px;color:#7c3aed;" x-text="CURRENCY + totalPrice().toFixed(2)"></div>
                </div>

                {{-- Date picker --}}
                <div style="background:#fafbfc;border:2px solid #e8ecf0;border-radius:14px;padding:16px 18px;margin-bottom:20px;">
                    <label style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;display:block;margin-bottom:10px;">📅 Select date</label>
                    <input type="date" class="input-field"
                           :min="today" :max="maxDate"
                           x-model="selected.date"
                           @change="loadSlots()"
                           style="background:white;">
                </div>

                {{-- Slots area --}}
                <div x-show="selected.date">

                    {{-- Loading --}}
                    <div x-show="slotsLoading" style="padding:20px 0;">
                        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;">
                            <template x-for="i in 12" :key="i">
                                <div class="skeleton" style="height:44px;border-radius:12px;"></div>
                            </template>
                        </div>
                    </div>

                    {{-- Slots grid --}}
                    <div x-show="!slotsLoading && slots.length > 0" class="scale-in">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                            <div style="font-size:12px;font-weight:700;color:#374151;">
                                <span style="display:inline-flex;align-items:center;gap:6px;background:#ecfdf5;color:#059669;padding:4px 10px;border-radius:20px;font-size:11px;">
                                    ✓ <span x-text="slots.length + ' slots available'"></span>
                                </span>
                            </div>
                            <div style="font-size:12px;color:#9ca3af;" x-text="formatDate(selected.date)"></div>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;">
                            <template x-for="slot in slots" :key="slot.time">
                                <button @click="selectSlot(slot)"
                                        class="time-slot"
                                        :class="selected.slot?.time === slot.time ? 'selected' : ''"
                                        x-text="slot.time"></button>
                            </template>
                        </div>
                    </div>

                    {{-- No availability --}}
                    <div x-show="!slotsLoading && slots.length === 0 && selected.date"
                         style="text-align:center;padding:40px 20px;background:#fafbfc;border-radius:16px;border:2px dashed #e8ecf0;">
                        <div style="font-size:40px;margin-bottom:12px;">📅</div>
                        <div style="font-weight:700;color:#374151;font-size:15px;margin-bottom:4px;">No availability on this date</div>
                        <div style="font-size:13px;color:#9ca3af;">Try selecting a different date — we're open Mon–Sat</div>
                    </div>
                </div>

                <button @click="step = 1"
                        style="margin-top:20px;color:#9ca3af;font-size:13px;font-weight:500;background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;">
                    ← Back
                </button>
            </div>
        </div>


        {{-- ══ STEP 3: Details ══ --}}
        <div x-show="step === 3 && !loading" x-cloak class="animate-in">
            <div class="booking-card" style="padding:28px 24px;">
                <div style="margin-bottom:24px;">
                    <h2 style="font-size:22px;font-weight:800;color:#111827;margin-bottom:4px;">Your details</h2>
                    <p style="font-size:13px;color:#9ca3af;">We'll send your confirmation to these details</p>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.06em;display:block;margin-bottom:6px;">First name *</label>
                        <input type="text" class="input-field" x-model="client.first_name" placeholder="Jane">
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.06em;display:block;margin-bottom:6px;">Last name *</label>
                        <input type="text" class="input-field" x-model="client.last_name" placeholder="Smith">
                    </div>
                </div>
                <div style="margin-bottom:14px;">
                    <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.06em;display:block;margin-bottom:6px;">Email address *</label>
                    <input type="email" class="input-field" x-model="client.email" placeholder="jane@example.com">
                </div>
                <div style="margin-bottom:14px;">
                    <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.06em;display:block;margin-bottom:6px;">Phone number *</label>
                    <input type="tel" class="input-field" x-model="client.phone" placeholder="+44 7700 000000">
                </div>
                <div style="margin-bottom:18px;">
                    <label style="font-size:11px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.06em;display:block;margin-bottom:6px;">Notes (optional)</label>
                    <textarea class="input-field" x-model="client.notes" rows="2"
                              placeholder="Any special requests or things we should know…"
                              style="resize:none;"></textarea>
                </div>
                <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;padding:12px;background:#fafbfc;border-radius:12px;border:2px solid #e8ecf0;">
                    <input type="checkbox" x-model="client.marketing_consent" style="margin-top:2px;accent-color:#7c3aed;width:16px;height:16px;flex-shrink:0;">
                    <span style="font-size:12px;color:#6b7280;line-height:1.5;">I agree to receive marketing messages and special offers from <strong style="color:#374151;">{{ $salon->name }}</strong></span>
                </label>

                <div x-show="detailsError" x-cloak
                     style="background:#fef2f2;border:2px solid #fecaca;border-radius:12px;padding:12px 14px;margin-top:14px;color:#dc2626;font-size:13px;display:flex;align-items:center;gap:8px;">
                    <span>⚠️</span><span x-text="detailsError"></span>
                </div>

                <button @click="goToConfirm()"
                        class="btn-primary"
                        style="width:100%;margin-top:20px;display:flex;align-items:center;justify-content:center;gap:8px;"
                        :disabled="!client.first_name || !client.last_name || !client.email || !client.phone">
                    Review booking →
                </button>
                <button @click="step = 2"
                        style="margin-top:12px;color:#9ca3af;font-size:13px;font-weight:500;background:none;border:none;cursor:pointer;width:100%;text-align:center;">
                    ← Back
                </button>
            </div>
        </div>


        {{-- ══ STEP 4: Confirm ══ --}}
        <div x-show="step === 4 && !loading" x-cloak class="animate-in">
            <div class="booking-card" style="padding:28px 24px;">
                <div style="margin-bottom:24px;">
                    <h2 style="font-size:22px;font-weight:800;color:#111827;margin-bottom:4px;">Confirm your booking</h2>
                    <p style="font-size:13px;color:#9ca3af;">Please review everything before confirming</p>
                </div>

                {{-- Booking summary --}}
                <div style="background:linear-gradient(135deg,#faf5ff,#f5f3ff);border:2px solid #e9d5ff;border-radius:16px;padding:20px;margin-bottom:16px;">
                    <div style="font-size:11px;font-weight:800;color:#7c3aed;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:14px;">Booking Summary</div>
                    <div class="summary-row">
                        <span style="color:#6b7280;font-size:13px;">Services</span>
                        <span style="font-weight:700;color:#111827;text-align:right;max-width:62%;" x-text="serviceNamesSummary()"></span>
                    </div>
                    <div class="summary-row">
                        <span style="color:#6b7280;font-size:13px;">With</span>
                        <span style="font-weight:700;color:#111827;" x-text="staffDisplayName()"></span>
                    </div>
                    <div class="summary-row">
                        <span style="color:#6b7280;font-size:13px;">Date</span>
                        <span style="font-weight:700;color:#111827;" x-text="formatDate(selected.date)"></span>
                    </div>
                    <div class="summary-row">
                        <span style="color:#6b7280;font-size:13px;">Time</span>
                        <span style="font-weight:700;color:#111827;" x-text="selected.slot?.time"></span>
                    </div>
                    <div class="summary-row">
                        <span style="color:#6b7280;font-size:13px;">Duration</span>
                        <span style="font-weight:700;color:#111827;text-align:right;" x-text="(combinedInfo?.appointment_minutes ?? totalBufferedMinutes()) + ' minutes (incl. buffers)'"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding-top:14px;margin-top:4px;border-top:2px solid #e9d5ff;">
                        <span style="font-weight:800;color:#111827;font-size:16px;">Total</span>
                        <span style="font-weight:800;color:#7c3aed;font-size:22px;" x-text="CURRENCY + totalPrice().toFixed(2)"></span>
                    </div>
                </div>

                {{-- Client summary --}}
                <div style="background:#fafbfc;border:2px solid #e8ecf0;border-radius:16px;padding:16px 18px;margin-bottom:16px;">
                    <div style="font-size:11px;font-weight:800;color:#9ca3af;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:12px;">Your Details</div>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#6d28d9);display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:16px;flex-shrink:0;"
                             x-text="(client.first_name || '?').charAt(0).toUpperCase()"></div>
                        <div>
                            <div style="font-weight:700;color:#111827;font-size:14px;" x-text="client.first_name + ' ' + client.last_name"></div>
                            <div style="font-size:12px;color:#6b7280;" x-text="client.email"></div>
                            <div style="font-size:12px;color:#6b7280;" x-text="client.phone"></div>
                        </div>
                    </div>
                </div>

                <div x-show="bookingError" x-cloak
                     style="background:#fef2f2;border:2px solid #fecaca;border-radius:12px;padding:12px 14px;margin-bottom:14px;color:#dc2626;font-size:13px;display:flex;align-items:center;gap:8px;">
                    <span>⚠️</span><span x-text="bookingError"></span>
                </div>

                <button @click="confirmBooking()"
                        class="btn-primary"
                        style="width:100%;display:flex;align-items:center;justify-content:center;gap:10px;"
                        :disabled="confirming">
                    <svg x-show="confirming" style="width:18px;height:18px;animation:spin 1s linear infinite;flex-shrink:0;" fill="none" viewBox="0 0 24 24">
                        <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span x-text="confirming ? 'Confirming your booking…' : '🎉 Confirm Booking'"></span>
                </button>
                <button @click="step = 3"
                        style="margin-top:12px;color:#9ca3af;font-size:13px;font-weight:500;background:none;border:none;cursor:pointer;width:100%;text-align:center;">
                    ← Edit details
                </button>
            </div>
        </div>


        {{-- ══ STEP 5: Success ══ --}}
        <div x-show="step === 5" x-cloak class="animate-in">
            <div class="booking-card" style="padding:48px 28px;text-align:center;">
                {{-- Confetti icon --}}
                <div style="width:80px;height:80px;background:linear-gradient(135deg,#7c3aed,#6d28d9);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 16px 40px rgba(124,58,237,0.4);">
                    <span style="font-size:36px;">🎉</span>
                </div>
                <h2 style="font-size:26px;font-weight:800;color:#111827;margin-bottom:8px;">You're all booked!</h2>
                <p style="font-size:14px;color:#6b7280;margin-bottom:4px;">
                    A confirmation has been sent to <strong x-text="client.email" style="color:#374151;"></strong>
                </p>
                <div style="display:inline-flex;align-items:center;gap:6px;background:#f3f4f6;border-radius:20px;padding:6px 14px;margin-bottom:28px;">
                    <span style="font-size:12px;color:#6b7280;">Booking ref:</span>
                    <span style="font-size:12px;font-weight:800;color:#111827;font-family:monospace;" x-text="bookingRef"></span>
                </div>

                {{-- Summary card --}}
                <div style="background:linear-gradient(135deg,#faf5ff,#f5f3ff);border:2px solid #e9d5ff;border-radius:16px;padding:20px;text-align:left;max-width:380px;margin:0 auto 28px;">
                    <div class="summary-row">
                        <span style="color:#6b7280;font-size:13px;">Services</span>
                        <span style="font-weight:700;text-align:right;max-width:62%;" x-text="serviceNamesSummary()"></span>
                    </div>
                    <div class="summary-row">
                        <span style="color:#6b7280;font-size:13px;">Date</span>
                        <span style="font-weight:700;" x-text="confirmDisplay?.date_long ?? formatDate(selected.date)"></span>
                    </div>
                    <div class="summary-row">
                        <span style="color:#6b7280;font-size:13px;">Time</span>
                        <span style="font-weight:700;" x-text="confirmDisplay?.time ?? selected.slot?.time"></span>
                    </div>
                    <div class="summary-row">
                        <span style="color:#6b7280;font-size:13px;">With</span>
                        <span style="font-weight:700;" x-text="staffDisplayName()"></span>
                    </div>
                </div>

                <a href="{{ url('/book/' . $salon->slug) }}"
                   class="btn-primary"
                   style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                    ✂️ Book another appointment
                </a>
            </div>
        </div>

    </main>

    {{-- Footer --}}
    <footer style="text-align:center;padding:20px;font-size:12px;color:rgba(255,255,255,0.5);">
        Powered by <span style="font-weight:800;color:rgba(255,255,255,0.8);">velour.</span>
    </footer>

</div>


<script>
const SLUG     = '{{ $salon->slug }}';
const BASE_URL = '{{ rtrim(config("app.url"), "/") }}';
const API      = BASE_URL + '/api/v1/book/' + SLUG;
const CSRF     = document.querySelector('meta[name="csrf-token"]').content;
const CURRENCY = '{{ \App\Helpers\CurrencyHelper::symbol($salon->currency ?? "GBP") }}';

function bookingApp() {
    return {
        step:         0,
        loading:      true,
        slotsLoading: false,
        staffLoading: false,
        confirming:   false,
        globalError:  '',
        detailsError: '',
        bookingError: '',
        bookingRef:   '',
        holdToken:    '',
        confirmedStaff: null,
        confirmDisplay: null,

        allServices: [],
        staffList:   [],
        slots:         [],
        combinedInfo:  null,

        today:   new Date().toISOString().split('T')[0],
        maxDate: (() => {
            const d = new Date();
            d.setDate(d.getDate() + {{ $salon->booking_advance_days ?? 90 }});
            return d.toISOString().split('T')[0];
        })(),

        selected: { services: [], staff: undefined, date: '', slot: null },
        client:   { first_name: '', last_name: '', email: '', phone: '', notes: '', marketing_consent: false },

        async init() {
            try {
                const res  = await fetch(API + '/services');
                if (!res.ok) throw new Error('Failed to load services');
                const data = await res.json();
                const raw  = data.services ?? {};
                const cats = [];
                for (const [catId, svcs] of Object.entries(raw)) {
                    if (!Array.isArray(svcs) || svcs.length === 0) continue;
                    const catName = svcs[0]?.category?.name ?? 'Uncategorised';
                    cats.push({ name: catName, services: svcs });
                }
                this.allServices = cats;
            } catch(e) {
                this.globalError = 'Failed to load services. Please refresh the page.';
            }
            this.loading = false;
        },

        isServiceSelected(svc) {
            return this.selected.services.some((s) => s.id === svc.id);
        },

        totalServiceMinutes() {
            return this.selected.services.reduce((a, s) => a + (parseInt(s.duration_minutes, 10) || 0), 0);
        },

        totalBufferedMinutes() {
            return this.selected.services.reduce((a, s) => {
                const buf = s.buffer_minutes != null ? parseInt(s.buffer_minutes, 10) : 15;
                return a + (parseInt(s.duration_minutes, 10) || 0) + buf;
            }, 0);
        },

        totalPrice() {
            return this.selected.services.reduce((a, s) => a + parseFloat(s.price || 0), 0);
        },

        serviceNamesSummary() {
            if (!this.selected.services.length) return '';
            return this.selected.services.map((s) => s.name).join(', ');
        },

        toggleService(svc) {
            const idx = this.selected.services.findIndex((s) => s.id === svc.id);
            if (idx >= 0) {
                if (this.selected.services.length <= 1) return;
                this.selected.services.splice(idx, 1);
            } else {
                this.selected.services.push(svc);
            }
            this.onServicesChanged();
        },

        onServicesChanged() {
            this.selected.staff = undefined;
            this.selected.date   = '';
            this.selected.slot   = null;
            this.slots           = [];
            this.combinedInfo    = null;
            if (this.step >= 1) {
                this.step = 1;
                if (this.selected.services.length) this.loadStaff();
            }
        },

        async loadStaff() {
            if (!this.selected.services.length) {
                this.staffList = [];
                return;
            }
            this.staffLoading = true;
            try {
                const params = new URLSearchParams();
                this.selected.services.forEach((s) => params.append('service_ids[]', s.id));
                const res  = await fetch(API + '/staff?' + params.toString());
                const data = await res.json();
                this.staffList = data.staff ?? [];
            } catch(e) {
                this.staffList = [];
            }
            this.staffLoading = false;
        },

        continueToStaff() {
            if (this.selected.services.length === 0) return;
            this.loadStaff();
            this.step = 1;
        },

        selectStaff(member) {
            this.selected.staff = member;
            this.step = 2;
            // Auto-load today's slots
            if (!this.selected.date) {
                this.selected.date = this.today;
            }
            this.loadSlots();
        },

        async loadSlots() {
            if (!this.selected.date || !this.selected.services.length) return;
            this.slotsLoading = true;
            this.slots = [];
            this.combinedInfo = null;
            try {
                const params = new URLSearchParams({ date: this.selected.date });
                this.selected.services.forEach((s) => params.append('service_ids[]', s.id));
                if (this.selected.staff) params.set('staff_id', this.selected.staff.id);
                const res  = await fetch(API + '/availability?' + params);
                const data = await res.json();
                this.slots = data.slots ?? [];
                this.combinedInfo = data.combined ?? null;
            } catch(e) {
                this.slots = [];
            }
            this.slotsLoading = false;
        },

        selectSlot(slot) {
            this.selected.slot = slot;
            this.step = 3;
        },

        goToConfirm() {
            this.detailsError = '';
            if (!this.client.first_name || !this.client.last_name) {
                this.detailsError = 'Please enter your full name.'; return;
            }
            if (!this.client.email) {
                this.detailsError = 'Please enter your email address.'; return;
            }
            if (!this.client.phone) {
                this.detailsError = 'Please enter your phone number.'; return;
            }
            this.step = 4;
        },

        /**
         * Explicit staff choice, or — for "Any available" — the first staff listed for this slot
         * (must match BookingService slot order: sort_order, id) so we do not assign a different person at confirm.
         */
        resolveHoldStaffId() {
            if (this.selected.staff && this.selected.staff.id != null) {
                return this.selected.staff.id;
            }
            const first = this.selected.slot?.available_staff?.[0];
            return first && first.id != null ? first.id : null;
        },

        /** Shown on confirm + success: after booking, prefer server staff; else explicit pick, then slot order (matches hold). */
        staffDisplayName() {
            if (this.confirmedStaff) {
                return ((this.confirmedStaff.first_name || '') + ' ' + (this.confirmedStaff.last_name || '')).trim();
            }
            if (this.selected.staff) {
                return ((this.selected.staff.first_name || '') + ' ' + (this.selected.staff.last_name || '')).trim();
            }
            const fb = this.selected.slot?.available_staff?.[0];
            if (fb) {
                return ((fb.first_name || '') + ' ' + (fb.last_name || '')).trim();
            }
            return 'Any available';
        },

        async confirmBooking() {
            this.confirming   = true;
            this.bookingError = '';

            try {
                // Hold the slot
                const holdRes = await fetch(API + '/hold', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body:    JSON.stringify({
                        service_ids: this.selected.services.map((s) => s.id),
                        staff_id:    this.resolveHoldStaffId(),
                        starts_at:   this.selected.date + ' ' + this.selected.slot.time + ':00',
                    }),
                });
                const holdData = await holdRes.json();
                if (!holdRes.ok) {
                    this.bookingError = holdData.message ?? 'That slot is no longer available. Please choose another time.';
                    this.confirming = false;
                    return;
                }
                this.holdToken = holdData.hold_token;

                // Confirm booking
                const confirmRes = await fetch(API + '/confirm', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body:    JSON.stringify({
                        hold_token:        this.holdToken,
                        first_name:        this.client.first_name,
                        last_name:         this.client.last_name,
                        email:             this.client.email,
                        phone:             this.client.phone,
                        notes:             this.client.notes,
                        marketing_consent: this.client.marketing_consent,
                    }),
                });
                const confirmData = await confirmRes.json();
                if (!confirmRes.ok) {
                    this.bookingError = confirmData.message ?? 'Booking failed. Please try again.';
                } else {
                    this.bookingRef = confirmData.reference ?? confirmData.appointment?.reference ?? '';
                    this.confirmedStaff = confirmData.appointment?.staff ?? null;
                    this.confirmDisplay = confirmData.display ?? null;
                    this.step = 5;
                }
            } catch(e) {
                this.bookingError = 'Something went wrong. Please try again.';
            }
            this.confirming = false;
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            try {
                const d = new Date(dateStr + 'T00:00:00');
                return d.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            } catch(e) { return dateStr; }
        },
    };
}
</script>
</body>
</html>
