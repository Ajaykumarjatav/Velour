@php
    $icon = $icon ?? 'default';
@endphp
<span class="nav-icon-wrap" aria-hidden="true">
@switch($icon)
@case('dashboard')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="5" y="18" width="5" height="9" rx="1.5" fill="#F472B6"/>
        <rect x="13.5" y="12" width="5" height="15" rx="1.5" fill="#60A5FA"/>
        <rect x="22" y="7" width="5" height="20" rx="1.5" fill="#34D399"/>
        <rect x="4" y="5" width="24" height="24" rx="4" fill="#1F2937" fill-opacity="0.08"/>
    </svg>
    @break
@case('tasks')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="7" y="5" width="18" height="22" rx="3" fill="#A78BFA"/>
        <rect x="10" y="9" width="12" height="2.5" rx="1.25" fill="#EDE9FE"/>
        <rect x="10" y="14" width="9" height="2.5" rx="1.25" fill="#EDE9FE"/>
        <circle cx="22" cy="22" r="6" fill="#22C55E"/>
        <path d="M20 22l1.5 1.5L24 20" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    @break
@case('calendar')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="5" y="7" width="22" height="20" rx="4" fill="#3B82F6"/>
        <rect x="5" y="7" width="22" height="7" rx="4" fill="#2563EB"/>
        <rect x="9" y="4" width="3" height="6" rx="1.5" fill="#93C5FD"/>
        <rect x="20" y="4" width="3" height="6" rx="1.5" fill="#93C5FD"/>
        <rect x="9" y="17" width="4" height="3" rx="1" fill="#EFF6FF"/>
        <rect x="14" y="17" width="4" height="3" rx="1" fill="#EFF6FF"/>
        <rect x="19" y="17" width="4" height="3" rx="1" fill="#FDE68A"/>
    </svg>
    @break
@case('appointments')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="6" y="6" width="20" height="22" rx="3" fill="#818CF8"/>
        <rect x="9" y="11" width="14" height="3" rx="1.5" fill="#E0E7FF"/>
        <rect x="9" y="17" width="10" height="3" rx="1.5" fill="#E0E7FF"/>
        <circle cx="23" cy="23" r="5" fill="#F59E0B"/>
        <path d="M23 21v4M21 23h4" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/>
    </svg>
    @break
@case('clients')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="16" cy="11" r="6" fill="#A855F7"/>
        <path d="M6 27c0-5.523 4.477-10 10-10s10 4.477 10 10" fill="#C084FC"/>
        <circle cx="24" cy="12" r="4" fill="#7C3AED" fill-opacity="0.55"/>
    </svg>
    @break
@case('staff')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="11" cy="12" r="4.5" fill="#A855F7"/>
        <circle cx="21" cy="12" r="4.5" fill="#C084FC"/>
        <path d="M4 26c0-4.5 3.5-7 7-7s7 2.5 7 7" fill="#9333EA"/>
        <path d="M16 26c0-4.5 3.5-7 7-7s7 2.5 7 7" fill="#7C3AED"/>
        <path d="M16 4l1.2 3.6H21l-3 2.2 1.2 3.6L16 11.2 12.8 13.4 14 9.8 11 7.6h3.8L16 4z" fill="#FBBF24"/>
    </svg>
    @break
@case('services')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="11" cy="21" r="5" fill="#F472B6" stroke="#EC4899" stroke-width="1.5"/>
        <circle cx="21" cy="21" r="5" fill="#F472B6" stroke="#EC4899" stroke-width="1.5"/>
        <path d="M11 16c2-6 4-9 5-9s3 3 5 9" stroke="#FB7185" stroke-width="2.5" stroke-linecap="round"/>
        <path d="M21 16c2-6 4-9 5-9s3 3 5 9" stroke="#FB7185" stroke-width="2.5" stroke-linecap="round"/>
    </svg>
    @break
@case('packages')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M16 6L6 11v10l10 5 10-5V11L16 6z" fill="#F59E0B"/>
        <path d="M16 6v20M6 11l10 5 10-5" stroke="#FDE68A" stroke-width="1.5" stroke-linejoin="round"/>
        <rect x="12" y="14" width="8" height="6" rx="1.5" fill="#FEF3C7"/>
    </svg>
    @break
@case('location')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="5" y="12" width="9" height="15" rx="2" fill="#60A5FA"/>
        <rect x="18" y="8" width="9" height="19" rx="2" fill="#3B82F6"/>
        <rect x="8" y="16" width="3" height="3" rx="0.75" fill="#DBEAFE"/>
        <rect x="21" y="12" width="3" height="3" rx="0.75" fill="#DBEAFE"/>
        <path d="M9 8h5v4H9z" fill="#93C5FD"/>
    </svg>
    @break
@case('availability')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M6 14l10-8 10 8v12H6V14z" fill="#34D399"/>
        <rect x="13" y="18" width="6" height="8" rx="1" fill="#A7F3D0"/>
        <circle cx="24" cy="10" r="5" fill="#FBBF24"/>
        <path d="M24 8v4M22 10h4" stroke="#fff" stroke-width="1.5" stroke-linecap="round"/>
    </svg>
    @break
@case('inventory')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M6 10l10-5 10 5v14l-10 5-10-5V10z" fill="#D97706"/>
        <path d="M6 10l10 5 10-5" stroke="#FCD34D" stroke-width="1.5"/>
        <rect x="12" y="15" width="8" height="7" rx="1.5" fill="#FEF3C7"/>
    </svg>
    @break
@case('pos')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="4" y="9" width="24" height="16" rx="4" fill="#3B82F6"/>
        <rect x="4" y="13" width="24" height="4" fill="#2563EB"/>
        <rect x="7" y="20" width="8" height="2" rx="1" fill="#BFDBFE"/>
        <rect x="17" y="19" width="8" height="4" rx="2" fill="#FBBF24"/>
    </svg>
    @break
@case('go_live')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M17 4L6 18h8l-1 10 11-14h-8l1-10z" fill="#F59E0B"/>
        <path d="M17 4L6 18h8l-1 10 11-14h-8l1-10z" stroke="#FDE68A" stroke-width="1" stroke-linejoin="round"/>
    </svg>
    @break
@case('website')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="16" cy="16" r="11" fill="#38BDF8"/>
        <ellipse cx="16" cy="16" rx="11" ry="4.5" stroke="#E0F2FE" stroke-width="1.5"/>
        <path d="M16 5c3 4 4.5 7 4.5 11S19 23 16 27M16 5c-3 4-4.5 7-4.5 11S13 23 16 27" stroke="#E0F2FE" stroke-width="1.5"/>
    </svg>
    @break
@case('customization')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="16" cy="16" r="10" fill="#F472B6"/>
        <circle cx="11" cy="13" r="2.5" fill="#FDE68A"/>
        <circle cx="20" cy="11" r="2.5" fill="#60A5FA"/>
        <circle cx="21" cy="20" r="2.5" fill="#34D399"/>
        <circle cx="12" cy="21" r="2.5" fill="#A78BFA"/>
    </svg>
    @break
@case('marketing')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M6 14h12l8-6v16l-8-6H6V14z" fill="#F97316"/>
        <rect x="4" y="12" width="4" height="8" rx="2" fill="#FB923C"/>
        <path d="M24 6l3 2-3 2" stroke="#FDE68A" stroke-width="2" stroke-linecap="round"/>
        <path d="M26 18l3 2-3 2" stroke="#FDE68A" stroke-width="2" stroke-linecap="round"/>
    </svg>
    @break
@case('reviews')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M16 5l3.2 6.5 7.2 1-5.2 5.1 1.2 7.2L16 22.4l-6.4 3.4 1.2-7.2-5.2-5.1 7.2-1L16 5z" fill="#FBBF24"/>
        <path d="M16 5l3.2 6.5 7.2 1-5.2 5.1 1.2 7.2L16 22.4l-6.4 3.4 1.2-7.2-5.2-5.1 7.2-1L16 5z" stroke="#F59E0B" stroke-width="1" stroke-linejoin="round"/>
    </svg>
    @break
@case('analytics')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="5" y="6" width="22" height="20" rx="3" fill="#38BDF8"/>
        <path d="M9 20l5-6 4 4 6-9" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="24" cy="9" r="2" fill="#FDE68A"/>
    </svg>
    @break
@case('reports')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="6" y="5" width="20" height="22" rx="3" fill="#60A5FA"/>
        <rect x="10" y="14" width="3.5" height="9" rx="1" fill="#F472B6"/>
        <rect x="14.5" y="11" width="3.5" height="12" rx="1" fill="#34D399"/>
        <rect x="19" y="16" width="3.5" height="7" rx="1" fill="#FBBF24"/>
    </svg>
    @break
@case('billing')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="4" y="9" width="24" height="16" rx="4" fill="#6366F1"/>
        <rect x="4" y="13" width="24" height="4" fill="#4F46E5"/>
        <rect x="7" y="20" width="10" height="2" rx="1" fill="#C7D2FE"/>
        <circle cx="23" cy="21" r="3" fill="#FBBF24"/>
    </svg>
    @break
@case('settings')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="16" cy="16" r="5" fill="#9CA3AF"/>
        <path d="M16 4v4M16 24v4M4 16h4M24 16h4M7.05 7.05l2.83 2.83M22.12 22.12l2.83 2.83M7.05 24.95l2.83-2.83M22.12 9.88l2.83-2.83" stroke="#D1D5DB" stroke-width="2.5" stroke-linecap="round"/>
        <circle cx="16" cy="16" r="9" stroke="#6B7280" stroke-width="2"/>
    </svg>
    @break
@case('security')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M16 5l10 4v7c0 6.5-4.5 10.5-10 12-5.5-1.5-10-5.5-10-12V9l10-4z" fill="#22C55E"/>
        <rect x="13" y="14" width="6" height="7" rx="2" fill="#DCFCE7"/>
        <path d="M16 17v3" stroke="#16A34A" stroke-width="2" stroke-linecap="round"/>
    </svg>
    @break
@case('notifications')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M10 24h12l-2-3V14c0-3.5 1.5-5.5 2.5-6.5C23 6.5 24 5 24 5l-2 2c-1 1-2 2.5-2 5v9l-2 3z" fill="#FBBF24"/>
        <circle cx="16" cy="27" r="3" fill="#F59E0B"/>
        <circle cx="22" cy="8" r="4" fill="#EF4444"/>
    </svg>
    @break
@case('trash')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M10 9V7a3 3 0 013-3h6a3 3 0 013 3v2" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round"/>
        <rect x="7" y="9" width="18" height="18" rx="3" fill="#6B7280"/>
        <rect x="12" y="14" width="2" height="8" rx="1" fill="#D1D5DB"/>
        <rect x="18" y="14" width="2" height="8" rx="1" fill="#D1D5DB"/>
    </svg>
    @break
@case('growth')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M6 24l7-9 5 5 8-12" stroke="#34D399" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M22 8h4v4" stroke="#34D399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="6" cy="24" r="2.5" fill="#10B981"/>
        <circle cx="26" cy="8" r="2.5" fill="#6EE7B7"/>
    </svg>
    @break
@case('support')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M6 10a4 4 0 014-4h12a4 4 0 014 4v7a4 4 0 01-4 4h-2l-4 4v-4H10a4 4 0 01-4-4v-7z" fill="#8B5CF6"/>
        <circle cx="12" cy="14" r="1.5" fill="#EDE9FE"/>
        <circle cx="16" cy="14" r="1.5" fill="#EDE9FE"/>
        <circle cx="20" cy="14" r="1.5" fill="#EDE9FE"/>
    </svg>
    @break
@case('team')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="12" r="4.5" fill="#A855F7"/>
        <circle cx="22" cy="12" r="4" fill="#C084FC"/>
        <path d="M4 26c0-4.5 3.5-7 8-7s8 2.5 8 7" fill="#9333EA"/>
        <path d="M18 26c0-3.5 2.5-5.5 6-5.5s6 2 6 5.5" fill="#7C3AED"/>
    </svg>
    @break
@case('subscription')
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="16" cy="16" r="11" fill="#F59E0B"/>
        <path d="M11 16l3.5 3.5L22 12" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    @break
@default
    <svg class="nav-icon-illustration" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="16" cy="16" r="10" fill="#9CA3AF"/>
    </svg>
@endswitch
</span>
