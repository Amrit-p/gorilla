<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClientCustomerType;
use App\Enums\ClientPaymentStatus;
use App\Enums\JobWorkflowStatus;
use App\Enums\LeadJobType;
use App\Enums\LeadPaymentMode;
use App\Enums\LeadWeedSpray;
use App\Exports\ClientsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportClientsRequest;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\EquipmentType;
use App\Models\Recurrence;
use App\Models\User;
use App\Models\Zone;
use App\Services\ClientManagementService;
use App\Services\JobManagementService;
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

class ClientManagementController extends Controller
{
    public function __construct(
        private readonly ClientManagementService $clientManagementService,
        private readonly JobManagementService $jobManagementService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        $filters = [
            'search' => $request->string('search')->toString(),
            'zone_id' => $request->string('zone_id')->toString(),
            'accounting_level_id' => $request->string('accounting_level_id')->toString(),
            'job_level_id' => $request->string('job_level_id')->toString(),
            'recurrence_id' => $request->string('recurrence_id')->toString(),
            'job_type' => $request->string('job_type')->toString(),
            'customer_type' => $request->string('customer_type')->toString(),
            'parking_status' => $request->string('parking_status')->toString(),
            'payment_status' => $request->string('payment_status')->toString(),
            'client_type' => $request->string('client_type')->toString(),
            'from_lead' => $request->string('from_lead')->toString(),
            'sort' => $request->string('sort')->toString(),
            'direction' => $request->string('direction')->toString(),
        ];

        $clients = $this->clientManagementService->paginatedClients(
            $filters,
            (int) config('mowing.default_pagination', 15)
        );

        $sort = $filters['sort'];
        $direction = $filters['direction'] ?: 'asc';

        if (crm_wants_partial($request)) {
            return crm_ajax_html('admin.clients.partials.table', compact('clients', 'sort', 'direction'));
        }

        return view('admin.clients.index', array_merge(
            ['clients' => $clients, 'filters' => $filters, 'sort' => $sort, 'direction' => $direction],
            $this->clientManagementService->formOptions()
        ));
    }

    public function import(ImportClientsRequest $request): JsonResponse
    {
        $this->authorize('create', Client::class);

        try {
            $result = $this->clientManagementService->importClients(
                $request->user(),
                $request->file('import_file'),
                (bool) $request->boolean('create_jobs'),
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $message = "Imported {$result['imported']} customer(s) successfully.";
        if ($result['failed'] > 0) {
            $message .= " {$result['failed']} row(s) failed.";
        }
        if ($result['duplicated'] > 0) {
            $message .= " {$result['duplicated']} row(s) skipped as duplicates.";
        }

        return response()->json([
            'message' => $message,
            'imported' => $result['imported'],
            'imported_rows' => $result['imported_rows'],
            'failed' => $result['failed'],
            'failures' => $result['failures'],
            'duplicated' => $result['duplicated'],
            'duplicates' => $result['duplicates'],
        ]);
    }

    public function downloadImportSample(): StreamedResponse
    {
        $this->authorize('create', Client::class);

        $sample = (object) [
            'customer_unique_id' => null,
            'name' => 'Sample Customer',
            'email' => 'customer@example.com',
            'phone' => '555-0100',
            'address' => '123 Green Street',
            'zone' => (object) ['name' => Zone::query()->where('is_active', true)->value('name') ?? 'Zone A'],
            'accountingLevel' => null,
            'jobLevel' => null,
            'service_types' => array_slice(ServiceTypes::all(), 0, 2) ?: ['Mulching'],
            'equipmentType' => (object) ['name' => EquipmentType::query()->where('is_active', true)->value('name') ?? 'Mower'],
            'job_type' => LeadJobType::REGULAR->value,
            'total_charges' => 75.00,
            'payment_mode' => LeadPaymentMode::CASH->value,
            'payment_status' => ClientPaymentStatus::PENDING->value,
            'customer_type' => ClientCustomerType::DONT_KNOW->value,
            'client_type' => 'Regular',
            'weed_spray' => LeadWeedSpray::NO->value,
            'recurrence' => (object) ['name' => Recurrence::query()->where('is_active', true)->value('name') ?? ''],
            'property_details' => '',
            'special_remarks' => 'Gate code 1234',
            'notes' => '',
            'latitude' => null,
            'longitude' => null,
            'created_at' => now(),
        ];

        return (new ClientsExport(EloquentCollection::make([$sample])))->download('customers-import-sample.xlsx');
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Client::class);

        $clients = $this->clientManagementService->exportClients($this->exportFilters($request));

        return (new ClientsExport($clients))->download('customers-'.now()->format('Y-m-d').'.xlsx');
    }

    public function exportPdf(Request $request): Response
    {
        $this->authorize('viewAny', Client::class);

        $clients = $this->clientManagementService->exportClients($this->exportFilters($request));

        return Pdf::loadView('admin.clients.partials.export-pdf', compact('clients'))
            ->setPaper('a3', 'landscape')
            ->download('customers-'.now()->format('Y-m-d').'.pdf');
    }

    private function exportFilters(Request $request): array
    {
        return [
            'search' => $request->string('search')->toString(),
            'zone_id' => $request->string('zone_id')->toString(),
            'accounting_level_id' => $request->string('accounting_level_id')->toString(),
            'job_level_id' => $request->string('job_level_id')->toString(),
            'job_type' => $request->string('job_type')->toString(),
            'customer_type' => $request->string('customer_type')->toString(),
            'parking_status' => $request->string('parking_status')->toString(),
            'payment_status' => $request->string('payment_status')->toString(),
            'client_type' => $request->string('client_type')->toString(),
            'from_lead' => $request->string('from_lead')->toString(),
            'recurrence_id' => $request->string('recurrence_id')->toString(),
        ];
    }

    public function create(): View
    {
        $this->authorize('create', Client::class);

        return view('admin.clients.create', $this->clientManagementService->formOptions());
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $this->authorize('create', Client::class);
        $client = $this->clientManagementService->createClient($request->user(), $request->validated());

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Customer created successfully.');
    }

    public function show(Request $request, Client $client): View
    {
        $this->authorize('view', $client);

        $client = $this->clientManagementService->findForShow($client->id) ?? $client;
        $statistics = $this->clientManagementService->customerStatistics($client);
        $activeTab = $request->string('tab')->toString() ?: 'details';

        $employees = User::query()
            ->role(CrmRoles::MOWER)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'efficiency']);

        $jobs_filters = [
            'search' => $request->string('search')->toString(),
            'list_scope' => $request->string('list_scope')->toString(),
            'status' => $request->string('status')->toString(),
            'priority' => $request->string('priority')->toString(),
            'zone_id' => $request->string('zone_id')->toString(),
            'client_id' => $request->string('client_id')->toString(),
        ];

        return view('admin.clients.show', array_merge(
            [
                'filters' => $jobs_filters,
                'client' => $client,
                'statistics' => $statistics,
                'activeTab' => in_array($activeTab, ['details', 'jobs'], true) ? $activeTab : 'details',
                'employees' => $employees,
                'workflowStatuses' => JobWorkflowStatus::values(),
            ],
            $this->clientManagementService->formOptions(),
            $this->jobManagementService->formOptions($client->id)
        ));
    }

    public function jobsTab(Request $request, Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        $filters = [
            'job_status' => $request->string('job_status')->toString(),
            'job_search' => $request->string('job_search')->toString(),
        ];

        $jobs = $this->clientManagementService->paginatedCustomerJobs(
            $client,
            $filters,
            (int) config('mowing.default_pagination', 10)
        );

        $jobStats = $this->clientManagementService->jobTabStatistics($client, $filters['job_status']);

        return response()->json([
            'html' => view('admin.jobs.partials.table', compact('jobs'))->render(),
            'stats' => $jobStats,
        ]);
    }

    public function edit(Client $client): View
    {
        $this->authorize('update', $client);

        return view('admin.clients.edit', array_merge(
            ['client' => $client],
            $this->clientManagementService->formOptions()
        ));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);
        $this->clientManagementService->updateClient($request->user(), $client, $request->validated());

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Customer updated successfully.');
    }

    public function downloadDocument(Client $client, ClientDocument $document): StreamedResponse
    {
        $this->authorize('view', $client);
        abort_unless($document->client_id === $client->id, 404);
        abort_unless(ClientDocument::disk()->exists($document->file_path), 404);

        return ClientDocument::disk()->response(
            $document->file_path,
            $document->original_name,
            ['Content-Disposition' => 'attachment; filename="'.$document->original_name.'"']
        );
    }

    public function destroy(Request $request, Client $client): JsonResponse
    {
        $this->authorize('delete', $client);
        $this->clientManagementService->deleteClient($request->user(), $client);

        return response()->json(['message' => 'Customer deleted successfully.']);
    }
}
