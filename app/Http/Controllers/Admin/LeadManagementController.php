<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LeadsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportLeadsRequest;
use App\Http\Requests\Admin\StoreLeadRequest;
use App\Http\Requests\Admin\UpdateLeadRequest;
use App\Http\Requests\Admin\UpdateLeadStatusRequest;
use App\Models\Lead;
use App\Services\LeadManagementService;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $lead = $this->leadManagementService->createLead($request->user(), $request->validated());

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
        $lead = $this->leadManagementService->updateLead($request->user(), $lead, $request->validated());

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
        $count = $this->leadManagementService->importFromCsv($request->user(), $request->file('csv_file'));

        return response()->json(['message' => "Imported {$count} leads successfully."]);
    }

    public function downloadImportSample(): StreamedResponse
    {
        $this->authorize('create', Lead::class);

        return $this->leadManagementService->downloadImportSampleCsv();
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
