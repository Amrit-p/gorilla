<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadPaymentStatus;
use App\Enums\LeadStatus;
use App\Enums\LeadWeedSpray;
use App\Exports\LeadsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportLeadsRequest;
use App\Http\Requests\Admin\StoreLeadRequest;
use App\Http\Requests\Admin\UpdateLeadRequest;
use App\Http\Requests\Admin\UpdateLeadStatusRequest;
use App\Models\EquipmentType;
use App\Models\Lead;
use App\Models\Recurrence;
use App\Models\Zone;
use App\Services\LeadManagementService;
use App\Support\CrmRoles;
use App\Support\ServiceTypes;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadManagementController extends Controller
{
    public function __construct(
        private readonly LeadManagementService $leadManagementService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Lead::class);

        $filters = [
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'assigned_sales_user_id' => $request->string('assigned_sales_user_id')->toString(),
            'zone_id' => $request->string('zone_id')->toString(),
            'recurrence_id' => $request->string('recurrence_id')->toString(),
        ];

        $leads = $this->leadManagementService->paginatedLeads(
            $filters,
            (int) config('mowing.default_pagination', 15)
        );

        if (crm_wants_partial($request)) {
            return crm_ajax_html('admin.leads.partials.table', compact('leads'));
        }

        return view('admin.leads.index', array_merge(
            ['leads' => $leads, 'filters' => $filters],
            $this->leadManagementService->formOptions()
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Lead::class);

        return view('admin.leads.create', $this->leadManagementService->formOptions());
    }

    public function store(StoreLeadRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', Lead::class);
        $validated = $request->validated();
        if ($request->user()->hasRole(CrmRoles::SALES_MANAGER)) {
            $validated['assigned_sales_user_id'] = $request->user()->id;
        }
        $lead = $this->leadManagementService->createLead($request->user(), $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Lead created successfully.',
                'lead' => $lead,
                'redirect' => route('admin.leads.index'),
            ], 201);
        }

        return redirect()
            ->route('admin.leads.index')
            ->with('success', 'Lead created successfully.');
    }

    public function show(Request $request, Lead $lead): JsonResponse
    {
        $this->authorize('view', $lead);
        $lead->load([
            'leadNotes.user:id,name',
            'equipmentType:id,name,color_code',
            'client:id,lead_id',
            'assignedSalesUser:id,name',
        ]);

        return response()->json([
            'lead' => $lead,
            'notes' => $lead->leadNotes->map(fn ($note): array => [
                'id' => $note->id,
                'note' => $note->note,
                'user_name' => $note->user?->name ?? 'System',
                'created_at' => $note->created_at?->diffForHumans(),
            ])->values(),
        ]);
    }

    public function edit(Lead $lead): View
    {
        $this->authorize('update', $lead);
        $lead->load('equipmentType:id,name,color_code');

        return view('admin.leads.edit', array_merge(
            ['lead' => $lead],
            $this->leadManagementService->formOptions()
        ));
    }

    public function update(UpdateLeadRequest $request, Lead $lead): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $lead);
        $validated = $request->validated();
        if ($request->user()->hasRole(CrmRoles::SALES_MANAGER)) {
            $validated['assigned_sales_user_id'] = $request->user()->id;
        }
        $lead = $this->leadManagementService->updateLead($request->user(), $lead, $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Lead updated successfully.',
                'lead' => $lead,
                'redirect' => route('admin.leads.index'),
            ]);
        }

        return redirect()
            ->route('admin.leads.index')
            ->with('success', 'Lead updated successfully.');
    }

    public function destroy(Request $request, Lead $lead): JsonResponse
    {
        $this->authorize('delete', $lead);
        $this->leadManagementService->deleteLead($request->user(), $lead);

        return response()->json(['message' => 'Lead deleted successfully.']);
    }

    public function updateStatus(UpdateLeadStatusRequest $request, Lead $lead): JsonResponse
    {
        $this->authorize('update', $lead);
        $updatedLead = $this->leadManagementService->updateStatus(
            $request->user(),
            $lead,
            $request->validated('status')
        );

        if (! empty($request->validated('note'))) {
            $this->leadManagementService->addLeadNote($request->user(), $updatedLead, $request->validated('note'));
        }

        return response()->json([
            'message' => 'Lead status updated successfully.',
            'lead' => $updatedLead,
            'converted' => $updatedLead->client !== null,
        ]);
    }

    public function addNote(Request $request, Lead $lead): JsonResponse
    {
        $this->authorize('update', $lead);
        $validated = $request->validate(['note' => ['required', 'string', 'max:5000']]);
        $this->leadManagementService->addLeadNote($request->user(), $lead, $validated['note']);

        return response()->json(['message' => 'Lead note added successfully.']);
    }

    public function import(ImportLeadsRequest $request): JsonResponse
    {
        $this->authorize('create', Lead::class);

        try {
            $result = $this->leadManagementService->importLeads($request->user(), $request->file('import_file'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $message = "Imported {$result['imported']} lead(s) successfully.";
        if ($result['failed'] > 0) {
            $message .= " {$result['failed']} row(s) failed.";
        }
        if ($result['duplicated'] > 0) {
            $message .= " {$result['duplicated']} row(s) skipped as duplicates.";
        }
        
        return response()->json([
            'message'       => $message,
            'imported'      => $result['imported'],
            'imported_rows' => $result['imported_rows'],
            'failed'        => $result['failed'],
            'failures'      => $result['failures'],
            'duplicated'    => $result['duplicated'],
            'duplicates'    => $result['duplicates'],
        ]);
    }

    public function downloadImportSample(): StreamedResponse
    {
        $this->authorize('create', Lead::class);

        $sample = (object) [
            'client_name'       => 'Sample Property',
            'email'             => 'lead@example.com',
            'mobile_number'     => '555-0100',
            'address'           => '123 Green Street',
            'zone'              => (object) ['name' => Zone::query()->where('is_active', true)->value('name') ?? 'Zone A'],
            'service_types'     => array_slice(ServiceTypes::all(), 0, 2) ?: ['Mulching'],
            'equipmentType'     => (object) ['name' => EquipmentType::query()->where('is_active', true)->value('name') ?? 'Mower'],
            'job_type'          => LeadJobType::REGULAR->value,
            'charges'           => 75.00,
            'payment_mode'      => LeadPaymentMode::CASH->value,
            'payment_status'    => LeadPaymentStatus::PENDING->value,
            'status'            => LeadStatus::NEW->value,
            'assignedSalesUser' => null,
            'lead_date'         => now(),
            'lead_time'         => '09:00',
            'converted_at'      => null,
            'weed_spray'        => LeadWeedSpray::NO->value,
            'recurrence'        => (object) ['name' => Recurrence::query()->where('is_active', true)->value('name') ?? ''],
            'remarks'           => 'Gate code 1234',
            'property_details'  => '',
            'latitude'          => null,
            'longitude'         => null,
            'created_at'        => now(),
        ];

        return (new LeadsExport(EloquentCollection::make([$sample])))->download('leads-import-sample.xlsx');
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Lead::class);

        $leads = $this->leadManagementService->exportLeads($this->exportFilters($request));

        return (new LeadsExport($leads))->download('leads-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportPdf(Request $request): Response
    {
        $this->authorize('viewAny', Lead::class);

        $leads = $this->leadManagementService->exportLeads($this->exportFilters($request));

        return Pdf::loadView('admin.leads.partials.export-pdf', compact('leads'))
            ->setPaper('a3', 'landscape')
            ->download('leads-' . now()->format('Y-m-d') . '.pdf');
    }

    private function exportFilters(Request $request): array
    {
        return [
            'search'                 => $request->string('search')->toString(),
            'status'                 => $request->string('status')->toString(),
            'assigned_sales_user_id' => $request->string('assigned_sales_user_id')->toString(),
            'zone_id'                => $request->string('zone_id')->toString(),
        ];
    }
}
