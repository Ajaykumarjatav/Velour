<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\MarketingCampaign;
use App\Models\PosTransaction;
use App\Models\Review;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\SupportTicket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatbotService
{
    private ?Salon $salon = null;
    private bool $isAdmin = false;

    public function __construct()
    {
        $user = Auth::user();
        if ($user) {
            $this->isAdmin = $user->isSuperAdmin();
            if (!$this->isAdmin) {
                $this->salon = $user->salons()->first();
            }
        }
    }

    public function respond(string $message): array
    {
        $msg = mb_strtolower(trim($message));
        return $this->isAdmin
            ? $this->handleAdmin($msg)
            : $this->handleTenant($msg);
    }

    private function has(string $msg, array $kw): bool
    {
        foreach ($kw as $k) { if (str_contains($msg, $k)) return true; }
        return false;
    }

    private function reply(string $text, string $type = 'info', ?string $link = null): array
    {
        return ['text' => $text, 'type' => $type, 'link' => $link];
    }

    private function money(float $amount): string
    {
        $cur = $this->salon?->currency ?? 'GBP';
        $sym = match(strtoupper($cur)) { 'USD'=>'$','EUR'=>'€','GBP'=>'£', default=>strtoupper($cur).' ' };
        return $sym . number_format($amount, 2);
    }

    private function adminMoney(float $amount): string
    {
        return '£' . number_format($amount, 2);
    }

    // ── TENANT HANDLER ────────────────────────────────────────────────────────

    private function handleTenant(string $msg): array
    {
        if (!$this->salon) return $this->reply('No salon found for your account.', 'error');
        $id = $this->salon->id;

        // Appointments
        if ($this->has($msg, ['appointment','booking','schedule','booked'])) {
            if ($this->has($msg, ['today'])) {
                $c = Appointment::where('salon_id',$id)->whereDate('starts_at',today())->whereNotIn('status',['cancelled','no_show'])->count();
                return $this->reply("You have **{$c}** appointment(s) today.", 'appointments', route('calendar'));
            }
            if ($this->has($msg, ['tomorrow'])) {
                $c = Appointment::where('salon_id',$id)->whereDate('starts_at',today()->addDay())->whereNotIn('status',['cancelled','no_show'])->count();
                return $this->reply("You have **{$c}** appointment(s) tomorrow.", 'appointments', route('calendar'));
            }
            if ($this->has($msg, ['week','this week'])) {
                $c = Appointment::where('salon_id',$id)->whereBetween('starts_at',[now()->startOfWeek(),now()->endOfWeek()])->whereNotIn('status',['cancelled','no_show'])->count();
                return $this->reply("You have **{$c}** appointment(s) this week.", 'appointments', route('calendar'));
            }
            if ($this->has($msg, ['pending','unconfirmed'])) {
                $c = Appointment::where('salon_id',$id)->where('status','pending')->count();
                return $this->reply("**{$c}** pending appointment(s) awaiting confirmation.", 'appointments', route('appointments.index'));
            }
            if ($this->has($msg, ['cancel','cancelled','no show','no-show'])) {
                $c = Appointment::where('salon_id',$id)->whereMonth('created_at',now()->month)->whereIn('status',['cancelled','no_show'])->count();
                return $this->reply("**{$c}** cancelled/no-show appointment(s) this month.", 'appointments', route('appointments.index'));
            }
            if ($this->has($msg, ['next','upcoming'])) {
                $next = Appointment::where('salon_id',$id)->where('starts_at','>',now())->whereNotIn('status',['cancelled','no_show'])->orderBy('starts_at')->with('client')->first();
                if ($next) {
                    $name = $next->client?->first_name ?? 'Unknown';
                    $time = Carbon::parse($next->starts_at)->format('D d M, g:ia');
                    return $this->reply("Next: **{$name}** on **{$time}**.", 'appointments', route('calendar'));
                }
                return $this->reply('No upcoming appointments.', 'appointments', route('calendar'));
            }
            $c = Appointment::where('salon_id',$id)->whereMonth('starts_at',now()->month)->whereNotIn('status',['cancelled','no_show'])->count();
            return $this->reply("**{$c}** appointment(s) this month.", 'appointments', route('appointments.index'));
        }

        // Revenue
        if ($this->has($msg, ['revenue','sales','income','earned','money','takings'])) {
            if ($this->has($msg, ['today'])) {
                $a = PosTransaction::where('salon_id',$id)->whereDate('created_at',today())->where('status','completed')->sum('total');
                return $this->reply("Today's revenue: **{$this->money($a)}**.", 'revenue', route('reports.index'));
            }
            if ($this->has($msg, ['week','this week'])) {
                $a = PosTransaction::where('salon_id',$id)->whereBetween('created_at',[now()->startOfWeek(),now()->endOfWeek()])->where('status','completed')->sum('total');
                return $this->reply("This week's revenue: **{$this->money($a)}**.", 'revenue', route('reports.index'));
            }
            if ($this->has($msg, ['last month','previous month'])) {
                $a = PosTransaction::where('salon_id',$id)->whereMonth('created_at',now()->subMonth()->month)->whereYear('created_at',now()->subMonth()->year)->where('status','completed')->sum('total');
                return $this->reply("Last month's revenue: **{$this->money($a)}**.", 'revenue', route('reports.index'));
            }
            $a = PosTransaction::where('salon_id',$id)->whereMonth('created_at',now()->month)->where('status','completed')->sum('total');
            return $this->reply("This month's revenue: **{$this->money($a)}**.", 'revenue', route('reports.index'));
        }

        // Clients
        if ($this->has($msg, ['client','customer','clients','customers'])) {
            if ($this->has($msg, ['new','added','this month'])) {
                $c = Client::where('salon_id',$id)->whereMonth('created_at',now()->month)->count();
                return $this->reply("**{$c}** new client(s) this month.", 'clients', route('clients.index'));
            }
            if ($this->has($msg, ['vip'])) {
                $c = Client::where('salon_id',$id)->where('is_vip',true)->count();
                return $this->reply("**{$c}** VIP client(s).", 'clients', route('clients.index'));
            }
            if ($this->has($msg, ['lapsed','inactive','lost'])) {
                $c = Client::where('salon_id',$id)->where('last_visit_at','<',now()->subDays(90))->count();
                return $this->reply("**{$c}** client(s) haven't visited in 90+ days.", 'clients', route('clients.index'));
            }
            if ($this->has($msg, ['birthday','birthdays'])) {
                $c = Client::where('salon_id',$id)->whereMonth('date_of_birth',now()->month)->count();
                return $this->reply("**{$c}** client(s) have birthdays this month.", 'clients', route('clients.index'));
            }
            $c = Client::where('salon_id',$id)->count();
            return $this->reply("**{$c}** total client(s).", 'clients', route('clients.index'));
        }

        // Staff
        if ($this->has($msg, ['staff','team','employee','stylist'])) {
            if ($this->has($msg, ['busiest','top','best'])) {
                $top = Staff::where('salon_id',$id)->withCount(['appointments'=>fn($q)=>$q->whereMonth('starts_at',now()->month)])->orderByDesc('appointments_count')->first();
                if ($top) return $this->reply("Busiest this month: **{$top->first_name} {$top->last_name}** ({$top->appointments_count} appts).", 'staff', route('staff.index'));
            }
            $c = Staff::where('salon_id',$id)->where('is_active',true)->count();
            return $this->reply("**{$c}** active staff member(s).", 'staff', route('staff.index'));
        }

        // Inventory
        if ($this->has($msg, ['inventory','stock','product','supply','supplies'])) {
            if ($this->has($msg, ['low','running out','reorder','alert'])) {
                $c = InventoryItem::where('salon_id',$id)->whereColumn('stock_quantity','<=','min_stock_level')->where('is_active',true)->count();
                return $this->reply("**{$c}** item(s) at or below minimum stock.", 'inventory', route('inventory.index'));
            }
            if ($this->has($msg, ['out of stock','zero','empty'])) {
                $c = InventoryItem::where('salon_id',$id)->where('stock_quantity',0)->where('is_active',true)->count();
                return $this->reply("**{$c}** item(s) completely out of stock.", 'inventory', route('inventory.index'));
            }
            $total = InventoryItem::where('salon_id',$id)->where('is_active',true)->count();
            $value = InventoryItem::where('salon_id',$id)->selectRaw('SUM(stock_quantity * cost_price) as v')->value('v') ?? 0;
            return $this->reply("**{$total}** active item(s) worth **{$this->money($value)}**.", 'inventory', route('inventory.index'));
        }

        // Services
        if ($this->has($msg, ['service','services','treatment','menu'])) {
            if ($this->has($msg, ['popular','top','most booked'])) {
                $top = DB::table('appointment_services')->join('appointments','appointments.id','=','appointment_services.appointment_id')->where('appointments.salon_id',$id)->whereMonth('appointments.starts_at',now()->month)->select('appointment_services.service_name',DB::raw('count(*) as cnt'))->groupBy('appointment_services.service_name')->orderByDesc('cnt')->first();
                if ($top) return $this->reply("Most booked: **{$top->service_name}** ({$top->cnt}x this month).", 'services', route('services.index'));
            }
            $c = Service::where('salon_id',$id)->where('status','active')->count();
            return $this->reply("**{$c}** active service(s).", 'services', route('services.index'));
        }

        // Reviews
        if ($this->has($msg, ['review','reviews','rating','feedback'])) {
            if ($this->has($msg, ['unreplied','unanswered','pending'])) {
                $c = Review::where('salon_id',$id)->whereNull('owner_reply')->count();
                return $this->reply("**{$c}** review(s) waiting for a reply.", 'reviews', route('reviews.index'));
            }
            $avg = Review::where('salon_id',$id)->avg('rating');
            $total = Review::where('salon_id',$id)->count();
            $unreplied = Review::where('salon_id',$id)->whereNull('owner_reply')->count();
            return $this->reply("Avg rating: **".number_format($avg??0,1)."/5** from **{$total}** review(s). {$unreplied} unreplied.", 'reviews', route('reviews.index'));
        }

        // Marketing
        if ($this->has($msg, ['marketing','campaign','email','sms','promotion'])) {
            $sent = MarketingCampaign::where('salon_id',$id)->where('status','sent')->count();
            $draft = MarketingCampaign::where('salon_id',$id)->where('status','draft')->count();
            return $this->reply("**{$sent}** sent campaign(s), **{$draft}** draft(s).", 'marketing', route('marketing.index'));
        }

        // Payment
        if ($this->has($msg, ['payment','stripe','gateway','charge','pay'])) {
            $ok = $this->salon->paymentGateway?->isConfigured();
            return $ok
                ? $this->reply('Stripe is **connected**. You can charge clients.', 'payments', route('payments.charge'))
                : $this->reply('Payment gateway **not configured**. Add your Stripe keys.', 'payments', route('payments.gateway'));
        }

        // Billing
        if ($this->has($msg, ['billing','subscription','plan','invoice','upgrade'])) {
            $plan = Auth::user()->plan ?? 'free';
            return $this->reply("You are on the **".ucfirst($plan)."** plan.", 'billing', route('billing.dashboard'));
        }

        // Greeting
        if ($this->has($msg, ['hi','hello','hey','good morning','good afternoon'])) {
            return $this->reply("Hi **".Auth::user()->name."**! Ask me about appointments, revenue, clients, staff, inventory, reviews, and more.", 'greeting');
        }

        return $this->tenantSuggestions();
    }

    // ── ADMIN HANDLER ─────────────────────────────────────────────────────────

    private function handleAdmin(string $msg): array
    {
        // ── Platform overview ─────────────────────────────────────────────────
        if ($this->has($msg, ['overview','summary','platform','dashboard'])) {
            $salons  = Salon::withoutGlobalScopes()->count();
            $active  = Salon::withoutGlobalScopes()->where('is_active',true)->count();
            $users   = User::withoutGlobalScopes()->count();
            $mrr     = PosTransaction::whereMonth('created_at',now()->month)->where('status','completed')->sum('total');
            $tickets = SupportTicket::whereIn('status',['open','in_progress'])->count();
            return $this->reply(
                "**Platform overview:**\n- Salons: **{$salons}** ({$active} active)\n- Users: **{$users}**\n- Revenue this month: **{$this->adminMoney($mrr)}**\n- Open tickets: **{$tickets}**",
                'admin', route('admin.dashboard')
            );
        }

        // ── Tenants ───────────────────────────────────────────────────────────
        if ($this->has($msg, ['tenant','salon','salons','tenants'])) {

            // Specific tenant lookup
            if ($this->has($msg, ['find','search','lookup','show','detail','report','info'])) {
                preg_match('/(?:find|search|lookup|show|detail|report|info|for|about)\s+(.+)/', $msg, $m);
                $term = trim($m[1] ?? '');
                if ($term) {
                    $salon = Salon::withoutGlobalScopes()
                        ->where('name','like',"%{$term}%")
                        ->orWhere('slug','like',"%{$term}%")
                        ->orWhere('email','like',"%{$term}%")
                        ->first();
                    if ($salon) {
                        $appts   = Appointment::where('salon_id',$salon->id)->whereMonth('starts_at',now()->month)->count();
                        $revenue = PosTransaction::where('salon_id',$salon->id)->whereMonth('created_at',now()->month)->where('status','completed')->sum('total');
                        $clients = Client::where('salon_id',$salon->id)->count();
                        $staff   = Staff::where('salon_id',$salon->id)->where('is_active',true)->count();
                        $status  = $salon->is_active ? 'Active' : 'Suspended';
                        return $this->reply(
                            "**{$salon->name}** ({$status})\n- Plan: **".ucfirst($salon->owner?->plan ?? 'free')."**\n- Clients: **{$clients}** | Staff: **{$staff}**\n- Appts this month: **{$appts}**\n- Revenue this month: **{$this->adminMoney($revenue)}**",
                            'admin', route('admin.tenants.show', $salon->id)
                        );
                    }
                    return $this->reply("No salon found matching \"**{$term}**\".", 'admin', route('admin.tenants'));
                }
            }

            if ($this->has($msg, ['top','highest revenue','best performing'])) {
                $top = Salon::withoutGlobalScopes()
                    ->select('salons.*', DB::raw('COALESCE(SUM(pos_transactions.total),0) as rev'))
                    ->leftJoin('pos_transactions', function($j) {
                        $j->on('pos_transactions.salon_id','=','salons.id')
                          ->where('pos_transactions.status','completed')
                          ->whereRaw('MONTH(pos_transactions.created_at) = ?',[now()->month]);
                    })
                    ->groupBy('salons.id')
                    ->orderByDesc('rev')
                    ->limit(5)
                    ->get();
                $lines = $top->map(fn($s,$i) => ($i+1).". **{$s->name}** — {$this->adminMoney($s->rev)}")->implode("\n");
                return $this->reply("Top 5 salons by revenue this month:\n{$lines}", 'admin', route('admin.tenants'));
            }

            if ($this->has($msg, ['suspended','inactive','blocked'])) {
                $c = Salon::withoutGlobalScopes()->where('is_active',false)->count();
                $list = Salon::withoutGlobalScopes()->where('is_active',false)->latest()->limit(5)->pluck('name')->implode(', ');
                return $this->reply("**{$c}** suspended salon(s). Recent: {$list}", 'admin', route('admin.tenants'));
            }

            if ($this->has($msg, ['new','this month','joined','registered'])) {
                $c = Salon::withoutGlobalScopes()->whereMonth('created_at',now()->month)->count();
                return $this->reply("**{$c}** new salon(s) joined this month.", 'admin', route('admin.tenants'));
            }

            if ($this->has($msg, ['no appointment','no activity','inactive'])) {
                $c = Salon::withoutGlobalScopes()
                    ->whereDoesntHave('appointments', fn($q) => $q->where('starts_at','>=',now()->subDays(30)))
                    ->where('is_active',true)->count();
                return $this->reply("**{$c}** active salon(s) with no appointments in 30 days.", 'admin', route('admin.tenants'));
            }

            $total  = Salon::withoutGlobalScopes()->count();
            $active = Salon::withoutGlobalScopes()->where('is_active',true)->count();
            return $this->reply("**{$total}** total salons (**{$active}** active).", 'admin', route('admin.tenants'));
        }

        // ── Users ─────────────────────────────────────────────────────────────
        if ($this->has($msg, ['user','users','account','accounts'])) {

            if ($this->has($msg, ['find','search','lookup'])) {
                preg_match('/(?:find|search|lookup|show)\s+(.+)/', $msg, $m);
                $term = trim($m[1] ?? '');
                if ($term) {
                    $user = User::withoutGlobalScopes()->where('name','like',"%{$term}%")->orWhere('email','like',"%{$term}%")->first();
                    if ($user) {
                        $salon = $user->salons()->first();
                        return $this->reply(
                            "**{$user->name}** ({$user->email})\n- Plan: **".ucfirst($user->plan ?? 'free')."**\n- Status: **".($user->is_active?'Active':'Inactive')."**\n- Salon: **".($salon?->name ?? 'None')."**\n- Joined: **{$user->created_at->format('d M Y')}**",
                            'admin', route('admin.users.show', $user->id)
                        );
                    }
                    return $this->reply("No user found matching \"**{$term}**\".", 'admin', route('admin.users'));
                }
            }

            if ($this->has($msg, ['new','this month','registered'])) {
                $c = User::withoutGlobalScopes()->whereMonth('created_at',now()->month)->count();
                return $this->reply("**{$c}** new user(s) registered this month.", 'admin', route('admin.users'));
            }

            if ($this->has($msg, ['inactive','disabled','blocked'])) {
                $c = User::withoutGlobalScopes()->where('is_active',false)->count();
                return $this->reply("**{$c}** inactive/disabled user(s).", 'admin', route('admin.users'));
            }

            $total = User::withoutGlobalScopes()->count();
            $active = User::withoutGlobalScopes()->where('is_active',true)->count();
            return $this->reply("**{$total}** total users (**{$active}** active).", 'admin', route('admin.users'));
        }

        // ── Revenue ───────────────────────────────────────────────────────────
        if ($this->has($msg, ['revenue','mrr','income','money','sales','earning'])) {

            if ($this->has($msg, ['today'])) {
                $a = PosTransaction::whereDate('created_at',today())->where('status','completed')->sum('total');
                return $this->reply("Platform revenue today: **{$this->adminMoney($a)}**.", 'admin', route('admin.revenue'));
            }
            if ($this->has($msg, ['week','this week'])) {
                $a = PosTransaction::whereBetween('created_at',[now()->startOfWeek(),now()->endOfWeek()])->where('status','completed')->sum('total');
                return $this->reply("Platform revenue this week: **{$this->adminMoney($a)}**.", 'admin', route('admin.revenue'));
            }
            if ($this->has($msg, ['last month','previous month'])) {
                $a = PosTransaction::whereMonth('created_at',now()->subMonth()->month)->whereYear('created_at',now()->subMonth()->year)->where('status','completed')->sum('total');
                return $this->reply("Last month's platform revenue: **{$this->adminMoney($a)}**.", 'admin', route('admin.revenue'));
            }
            if ($this->has($msg, ['year','this year','annual'])) {
                $a = PosTransaction::whereYear('created_at',now()->year)->where('status','completed')->sum('total');
                return $this->reply("Platform revenue this year: **{$this->adminMoney($a)}**.", 'admin', route('admin.revenue'));
            }

            $curr = PosTransaction::whereMonth('created_at',now()->month)->where('status','completed')->sum('total');
            $last = PosTransaction::whereMonth('created_at',now()->subMonth()->month)->whereYear('created_at',now()->subMonth()->year)->where('status','completed')->sum('total');
            $diff = $last > 0 ? round((($curr - $last) / $last) * 100, 1) : 0;
            $arrow = $diff >= 0 ? '↑' : '↓';
            return $this->reply(
                "This month: **{$this->adminMoney($curr)}** {$arrow} {$diff}% vs last month ({$this->adminMoney($last)}).",
                'admin', route('admin.revenue')
            );
        }

        // ── Plans ─────────────────────────────────────────────────────────────
        if ($this->has($msg, ['plan','plans','subscription','tier','pricing'])) {
            $breakdown = User::withoutGlobalScopes()->select('plan',DB::raw('count(*) as cnt'))->groupBy('plan')->pluck('cnt','plan')->toArray();
            $lines = collect($breakdown)->map(fn($c,$p) => "**".ucfirst($p)."**: {$c}")->implode(' | ');
            $free = $breakdown['free'] ?? 0;
            $paid = array_sum($breakdown) - $free;
            return $this->reply("Plan breakdown — {$lines}\nPaid users: **{$paid}**", 'admin', route('admin.plans'));
        }

        // ── Support ───────────────────────────────────────────────────────────
        if ($this->has($msg, ['support','ticket','tickets','issue','help request'])) {
            if ($this->has($msg, ['open','pending','unresolved'])) {
                $open = SupportTicket::whereIn('status',['open','in_progress'])->count();
                $unassigned = SupportTicket::whereIn('status',['open','in_progress'])->whereNull('assigned_to')->count();
                return $this->reply("**{$open}** open ticket(s), **{$unassigned}** unassigned.", 'admin', route('admin.support.index'));
            }
            if ($this->has($msg, ['resolved','closed','done'])) {
                $c = SupportTicket::where('status','resolved')->whereMonth('updated_at',now()->month)->count();
                return $this->reply("**{$c}** ticket(s) resolved this month.", 'admin', route('admin.support.index'));
            }
            $total = SupportTicket::count();
            $open  = SupportTicket::whereIn('status',['open','in_progress'])->count();
            return $this->reply("**{$total}** total tickets, **{$open}** open.", 'admin', route('admin.support.index'));
        }

        // ── Audit ─────────────────────────────────────────────────────────────
        if ($this->has($msg, ['audit','log','activity','security','event'])) {
            $today = DB::table('audit_logs')->whereDate('created_at',today())->count();
            $week  = DB::table('audit_logs')->whereBetween('created_at',[now()->startOfWeek(),now()])->count();
            return $this->reply("Audit log: **{$today}** event(s) today, **{$week}** this week.", 'admin', route('admin.audit.index'));
        }

        // ── Analytics ────────────────────────────────────────────────────────
        if ($this->has($msg, ['analytics','stats','statistics','growth','retention','adoption'])) {
            $newSalons = Salon::withoutGlobalScopes()->whereMonth('created_at',now()->month)->count();
            $newUsers  = User::withoutGlobalScopes()->whereMonth('created_at',now()->month)->count();
            $totalAppts = Appointment::whereMonth('starts_at',now()->month)->count();
            return $this->reply(
                "This month:\n- New salons: **{$newSalons}**\n- New users: **{$newUsers}**\n- Total appointments: **{$totalAppts}**",
                'admin', route('admin.analytics')
            );
        }

        // ── Appointments (platform-wide) ──────────────────────────────────────
        if ($this->has($msg, ['appointment','booking','appointments'])) {
            if ($this->has($msg, ['today'])) {
                $c = Appointment::whereDate('starts_at',today())->whereNotIn('status',['cancelled','no_show'])->count();
                return $this->reply("**{$c}** appointment(s) across all salons today.", 'admin', route('admin.analytics'));
            }
            $c = Appointment::whereMonth('starts_at',now()->month)->whereNotIn('status',['cancelled','no_show'])->count();
            return $this->reply("**{$c}** appointment(s) across all salons this month.", 'admin', route('admin.analytics'));
        }

        // ── Billing / Webhooks ────────────────────────────────────────────────
        if ($this->has($msg, ['billing','webhook','stripe','invoice'])) {
            $failed = DB::table('webhook_calls')->where('status','failed')->whereDate('created_at',today())->count();
            return $this->reply("**{$failed}** failed webhook(s) today.", 'admin', route('admin.billing.webhooks'));
        }

        // ── Greeting ─────────────────────────────────────────────────────────
        if ($this->has($msg, ['hi','hello','hey'])) {
            return $this->reply("Hi **".Auth::user()->name."**! Ask me about tenants, users, revenue, plans, support, audit logs, or find a specific salon/user.", 'greeting');
        }

        return $this->adminSuggestions();
    }

    // ── Suggestion menus ──────────────────────────────────────────────────────

    private function tenantSuggestions(): array
    {
        return $this->reply(
            "I can help with:\n- **Appointments** — today / tomorrow / this week / pending\n- **Revenue** — today / this month / last month\n- **Clients** — total / new / VIP / lapsed / birthdays\n- **Staff** — count / busiest\n- **Inventory** — low stock / out of stock\n- **Services** — count / most popular\n- **Reviews** — rating / unreplied\n- **Marketing** — campaigns sent/draft\n- **Payments** — gateway status\n- **Billing** — current plan\n\nTry: *\"How many appointments today?\"*",
            'suggestions'
        );
    }

    private function adminSuggestions(): array
    {
        return $this->reply(
            "Admin queries I can answer:\n- **Overview** — full platform summary\n- **Tenants** — total / active / suspended / new / top revenue\n- **Find salon** — *\"find salon Maison\"*\n- **Find user** — *\"find user john\"*\n- **Revenue** — today / this month / last month / this year\n- **Plans** — breakdown by tier\n- **Support** — open / unassigned tickets\n- **Audit** — today's event count\n- **Analytics** — growth stats\n- **Appointments** — platform-wide count\n- **Billing** — failed webhooks\n\nTry: *\"Show me top salons by revenue\"*",
            'suggestions'
        );
    }
}
