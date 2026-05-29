<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Exports\ClientsExport;
use App\Models\Client;
use App\Models\User;
use App\Enums\JobWorkflowStatus;
use App\Services\{
    ClientManagementService,
    JobManagementService,
};
use App\Support\CrmRoles;
use Barryvdh\DomPDF\Facade\Pdf;
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
            'recurrence_id' => $request->string('recurrence_id')->toString(),
            'job_type' => $request->string('job_type')->toString(),
            'customer_type' => $request->string('customer_type')->toString(),
            'parking_status' => $request->string('parking_status')->toString(),
            'payment_status' => $request->string('payment_status')->toString(),
            'client_type' => $request->string('client_type')->toString(),
            'from_lead' => $request->string('from_lead')->toString(),
        ];

        $clients = $this->clientManagementService->paginatedClients(
            $filters,
            (int) config('mowing.default_pagination', 15)
        );

        if (crm_wants_partial($request)) {
            return crm_ajax_html('admin.clients.partials.table', compact('clients'));
        }

        return view('admin.clients.index', array_merge(
            ['clients' => $clients, 'filters' => $filters],
            $this->clientManagementService->formOptions()
        ));
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Client::class);

        $clients = $this->clientManagementService->exportClients($this->exportFilters($request));

        return (new ClientsExport($clients))->download('customers-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportPdf(Request $request): Response
    {
        $this->authorize('viewAny', Client::class);

        $clients = $this->clientManagementService->exportClients($this->exportFilters($request));

        return Pdf::loadView('admin.clients.partials.export-pdf', compact('clients'))
            ->setPaper('a3', 'landscape')
            ->download('customers-' . now()->format('Y-m-d') . '.pdf');
    }

    private function exportFilters(Request $request): array
    {
        return [
            'search'         => $request->string('search')->toString(),
            'zone_id'        => $request->string('zone_id')->toString(),
            'job_type'       => $request->string('job_type')->toString(),
            'customer_type'  => $request->string('customer_type')->toString(),
            'parking_status' => $request->string('parking_status')->toString(),
            'payment_status' => $request->string('payment_status')->toString(),
            'client_type'    => $request->string('client_type')->toString(),
            'from_lead'      => $request->string('from_lead')->toString(),
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

        $jobs_filters =  [
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

    public function destroy(Request $request, Client $client): JsonResponse
    {
        $this->authorize('delete', $client);
        $this->clientManagementService->deleteClient($request->user(), $client);

        return response()->json(['message' => 'Customer deleted successfully.']);
    }
}
