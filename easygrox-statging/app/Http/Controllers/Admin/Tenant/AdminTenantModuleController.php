<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Http\Controllers\Admin\Concerns\AdminTenantContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\MarketingCampaign;
use App\Models\PosTransaction;
use App\Models\Review;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffLeaveRequest;
use App\Models\StaffAttendanceRecord;
use App\Support\AdminTenantModuleRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class AdminTenantModuleController extends Controller
{
    use AdminTenantContext;

    abstract protected function module(): string;

    public function index(Request $request, int $salon): View
    {
        $salonModel = $this->resolveSalon($salon);
        $this->logTenantView($salonModel, $this->module().'.index');

        $search = $request->get('search');
        $query = $this->indexQuery($salonModel->id, $search);
        $records = $query->paginate(25)->withQueryString();

        return view('admin.tenants.modules.index', [
            'salon' => $salonModel,
            'module' => $this->module(),
            'moduleLabel' => AdminTenantModuleRegistry::label($this->module()),
            'records' => $records,
            'search' => $search,
            'columns' => $this->columns(),
        ]);
    }

    public function show(int $salon, int $record): View
    {
        $salonModel = $this->resolveSalon($salon);
        $item = $this->findRecord($salonModel, $record);
        $this->logTenantView($salonModel, $this->module().'.show', $item);

        return view('admin.tenants.modules.show', [
            'salon' => $salonModel,
            'module' => $this->module(),
            'moduleLabel' => AdminTenantModuleRegistry::label($this->module()),
            'record' => $item,
            'fields' => $this->detailFields($item),
            'related' => $this->relatedData($salonModel, $item),
        ]);
    }

    /** @return list<array{key: string, label: string}> */
    protected function columns(): array
    {
        return match ($this->module()) {
            'clients' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'phone', 'label' => 'Phone'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'visit_count', 'label' => 'Visits'],
                ['key' => 'total_spent', 'label' => 'Spent'],
            ],
            'appointments' => [
                ['key' => 'starts_at', 'label' => 'When'],
                ['key' => 'client', 'label' => 'Client'],
                ['key' => 'staff', 'label' => 'Staff'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'total_price', 'label' => 'Total'],
            ],
            'staff' => [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'role', 'label' => 'Role'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'is_active', 'label' => 'Active'],
            ],
            'pos' => [
                ['key' => 'created_at', 'label' => 'Date'],
                ['key' => 'reference', 'label' => 'Ref'],
                ['key' => 'client', 'label' => 'Client'],
                ['key' => 'payment_method', 'label' => 'Payment'],
                ['key' => 'total', 'label' => 'Total'],
                ['key' => 'status', 'label' => 'Status'],
            ],
            'services' => [
                ['key' => 'name', 'label' => 'Service'],
                ['key' => 'category', 'label' => 'Category'],
                ['key' => 'price', 'label' => 'Price'],
                ['key' => 'is_active', 'label' => 'Active'],
            ],
            'inventory' => [
                ['key' => 'name', 'label' => 'Item'],
                ['key' => 'sku', 'label' => 'SKU'],
                ['key' => 'stock_quantity', 'label' => 'Stock'],
                ['key' => 'retail_price', 'label' => 'Price'],
            ],
            'expenses' => [
                ['key' => 'expense_date', 'label' => 'Date'],
                ['key' => 'title', 'label' => 'Title'],
                ['key' => 'category', 'label' => 'Category'],
                ['key' => 'amount', 'label' => 'Amount'],
            ],
            'reviews' => [
                ['key' => 'created_at', 'label' => 'Date'],
                ['key' => 'rating', 'label' => 'Rating'],
                ['key' => 'client', 'label' => 'Client'],
                ['key' => 'comment', 'label' => 'Comment'],
            ],
            'marketing' => [
                ['key' => 'name', 'label' => 'Campaign'],
                ['key' => 'type', 'label' => 'Type'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'created_at', 'label' => 'Created'],
            ],
            'leave' => [
                ['key' => 'staff', 'label' => 'Staff'],
                ['key' => 'leave_type', 'label' => 'Type'],
                ['key' => 'dates', 'label' => 'Dates'],
                ['key' => 'status', 'label' => 'Status'],
            ],
            'attendance' => [
                ['key' => 'staff', 'label' => 'Staff'],
                ['key' => 'attendance_date', 'label' => 'Date'],
                ['key' => 'status', 'label' => 'Status'],
                ['key' => 'clock_in_at', 'label' => 'Clock in'],
            ],
            default => [],
        };
    }

    protected function indexQuery(int $salonId, ?string $search): Builder
    {
        $q = match ($this->module()) {
            'clients' => Client::withoutGlobalScopes()->where('salon_id', $salonId)->orderByDesc('id'),
            'appointments' => Appointment::withoutGlobalScopes()->where('salon_id', $salonId)
                ->with(['client:id,first_name,last_name', 'staff:id,first_name,last_name'])
                ->orderByDesc('starts_at'),
            'staff' => Staff::withoutGlobalScopes()->where('salon_id', $salonId)->orderBy('first_name'),
            'pos' => PosTransaction::withoutGlobalScopes()->where('salon_id', $salonId)
                ->with('client:id,first_name,last_name')->orderByDesc('created_at'),
            'services' => Service::withoutGlobalScopes()->where('salon_id', $salonId)
                ->with('category:id,name')->orderBy('name'),
            'inventory' => InventoryItem::withoutGlobalScopes()->where('salon_id', $salonId)
                ->orderBy('name'),
            'expenses' => Expense::withoutGlobalScopes()->where('salon_id', $salonId)
                ->where('status', 'recorded')->with('category:id,name')->orderByDesc('expense_date'),
            'reviews' => Review::withoutGlobalScopes()->where('salon_id', $salonId)
                ->with('client:id,first_name,last_name')->orderByDesc('created_at'),
            'marketing' => MarketingCampaign::withoutGlobalScopes()->where('salon_id', $salonId)
                ->orderByDesc('created_at'),
            'leave' => StaffLeaveRequest::withoutGlobalScopes()->where('salon_id', $salonId)
                ->with('staff:id,first_name,last_name')->orderByDesc('start_date'),
            'attendance' => StaffAttendanceRecord::withoutGlobalScopes()->where('salon_id', $salonId)
                ->with('staff:id,first_name,last_name')->orderByDesc('attendance_date'),
            default => abort(404),
        };

        if ($search) {
            $this->applySearch($q, $search);
        }

        return $q;
    }

    protected function applySearch(Builder $q, string $search): void
    {
        match ($this->module()) {
            'clients' => $q->where(function ($w) use ($search) {
                $w->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }),
            'appointments' => $q->where(function ($w) use ($search) {
                $w->where('reference', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            }),
            'staff' => $q->where(function ($w) use ($search) {
                $w->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }),
            'pos' => $q->where('reference', 'like', "%{$search}%"),
            'services' => $q->where('name', 'like', "%{$search}%"),
            'inventory' => $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
            }),
            'expenses' => $q->where(function ($w) use ($search) {
                $w->where('title', 'like', "%{$search}%")->orWhere('vendor', 'like', "%{$search}%");
            }),
            'reviews' => $q->where('comment', 'like', "%{$search}%"),
            'marketing' => $q->where('name', 'like', "%{$search}%"),
            'leave' => $q->where('leave_type', 'like', "%{$search}%"),
            'attendance' => $q->where('status', 'like', "%{$search}%"),
            default => null,
        };
    }

    protected function findRecord(Salon $salon, int $id): Model
    {
        $model = AdminTenantModuleRegistry::modelFor($this->module());
        abort_unless($model, 404);

        $record = $model::withoutGlobalScopes()->findOrFail($id);
        $this->authorizeSalonRecord($salon, $record);

        return $this->loadRecordRelations($record);
    }

    protected function loadRecordRelations(Model $record): Model
    {
        return match ($this->module()) {
            'clients' => $record->load(['appointments' => fn ($q) => $q->limit(10), 'transactions' => fn ($q) => $q->limit(10)]),
            'appointments' => $record->load(['client', 'staff', 'services.service']),
            'staff' => $record->loadCount('appointments'),
            'pos' => $record->load(['client', 'staff', 'items', 'appointment']),
            'services' => $record->load('category'),
            'inventory' => $record->load('category'),
            'expenses' => $record->load(['category', 'staff']),
            'reviews' => $record->load(['client', 'appointment']),
            'marketing' => $record,
            'leave' => $record->load('staff'),
            'attendance' => $record->load('staff'),
            default => $record,
        };
    }

    /** @return list<array{label: string, value: string}> */
    protected function detailFields(Model $record): array
    {
        return match ($this->module()) {
            'clients' => [
                ['label' => 'Name', 'value' => $record->full_name],
                ['label' => 'Phone', 'value' => $record->phone ?? '—'],
                ['label' => 'Email', 'value' => $record->email ?? '—'],
                ['label' => 'Visits', 'value' => (string) $record->visit_count],
                ['label' => 'Total spent', 'value' => number_format((float) $record->total_spent, 2)],
                ['label' => 'Status', 'value' => $record->status ?? '—'],
            ],
            'appointments' => [
                ['label' => 'Reference', 'value' => $record->reference],
                ['label' => 'When', 'value' => $record->starts_at?->format('j M Y H:i') ?? '—'],
                ['label' => 'Client', 'value' => $record->client?->full_name ?? '—'],
                ['label' => 'Staff', 'value' => $record->staff?->name ?? '—'],
                ['label' => 'Status', 'value' => $record->status],
                ['label' => 'Total', 'value' => number_format((float) $record->total_price, 2)],
                ['label' => 'Payment', 'value' => $record->payment_status ?? '—'],
            ],
            'staff' => [
                ['label' => 'Name', 'value' => $record->name],
                ['label' => 'Email', 'value' => $record->email ?? '—'],
                ['label' => 'Phone', 'value' => $record->phone ?? '—'],
                ['label' => 'Role', 'value' => $record->role ?? '—'],
                ['label' => 'Active', 'value' => $record->is_active ? 'Yes' : 'No'],
            ],
            'pos' => [
                ['label' => 'Reference', 'value' => $record->reference ?? '—'],
                ['label' => 'Date', 'value' => $record->created_at?->format('j M Y H:i') ?? '—'],
                ['label' => 'Client', 'value' => $record->client?->full_name ?? 'Walk-in'],
                ['label' => 'Payment', 'value' => $record->payment_method ?? '—'],
                ['label' => 'Status', 'value' => $record->status],
                ['label' => 'Total', 'value' => number_format((float) $record->total, 2)],
            ],
            'services' => [
                ['label' => 'Name', 'value' => $record->name],
                ['label' => 'Category', 'value' => $record->category?->name ?? '—'],
                ['label' => 'Price', 'value' => number_format((float) $record->price, 2)],
                ['label' => 'Duration', 'value' => ($record->duration_minutes ?? 0).' min'],
                ['label' => 'Active', 'value' => $record->is_active ? 'Yes' : 'No'],
            ],
            'inventory' => [
                ['label' => 'Name', 'value' => $record->name],
                ['label' => 'SKU', 'value' => $record->sku ?? '—'],
                ['label' => 'Stock', 'value' => (string) $record->stock_quantity],
                ['label' => 'Retail', 'value' => number_format((float) $record->retail_price, 2)],
                ['label' => 'Cost', 'value' => number_format((float) $record->cost_price, 2)],
            ],
            'expenses' => [
                ['label' => 'Title', 'value' => $record->title],
                ['label' => 'Date', 'value' => $record->expense_date?->format('j M Y') ?? '—'],
                ['label' => 'Category', 'value' => $record->category?->name ?? '—'],
                ['label' => 'Amount', 'value' => number_format((float) $record->amount, 2)],
                ['label' => 'Vendor', 'value' => $record->vendor ?? '—'],
            ],
            'reviews' => [
                ['label' => 'Rating', 'value' => (string) $record->rating],
                ['label' => 'Client', 'value' => $record->client?->full_name ?? '—'],
                ['label' => 'Date', 'value' => $record->created_at?->format('j M Y') ?? '—'],
                ['label' => 'Comment', 'value' => $record->comment ?? '—'],
            ],
            'marketing' => [
                ['label' => 'Name', 'value' => $record->name],
                ['label' => 'Type', 'value' => $record->type ?? '—'],
                ['label' => 'Status', 'value' => $record->status ?? '—'],
                ['label' => 'Created', 'value' => $record->created_at?->format('j M Y') ?? '—'],
            ],
            'leave' => [
                ['label' => 'Staff', 'value' => $record->staff?->name ?? '—'],
                ['label' => 'Type', 'value' => $record->leave_type],
                ['label' => 'From', 'value' => $record->start_date?->format('j M Y') ?? '—'],
                ['label' => 'To', 'value' => $record->end_date?->format('j M Y') ?? '—'],
                ['label' => 'Status', 'value' => $record->status],
            ],
            'attendance' => [
                ['label' => 'Staff', 'value' => $record->staff?->name ?? '—'],
                ['label' => 'Date', 'value' => $record->attendance_date?->format('j M Y') ?? '—'],
                ['label' => 'Status', 'value' => $record->status],
                ['label' => 'Clock in', 'value' => $record->clock_in_at?->format('H:i') ?? '—'],
                ['label' => 'Clock out', 'value' => $record->clock_out_at?->format('H:i') ?? '—'],
                ['label' => 'Notes', 'value' => $record->notes ?? '—'],
            ],
            default => [],
        };
    }

    /** @return array<string, mixed> */
    protected function relatedData(Salon $salon, Model $record): array
    {
        return match ($this->module()) {
            'clients' => ['appointments' => $record->appointments ?? collect(), 'transactions' => $record->transactions ?? collect()],
            'appointments' => ['services' => $record->services ?? collect()],
            'pos' => ['items' => $record->items ?? collect()],
            default => [],
        };
    }
}
