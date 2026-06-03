<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EmployeeBonusesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEmployeeBonusRequest;
use App\Http\Requests\Admin\UpdateEmployeeBonusRequest;
use App\Models\EmployeeBonus;
use App\Services\EmployeeBonusService;
use App\Support\CrmRoles;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeBonusController extends Controller
{
    public function __construct(
        private readonly EmployeeBonusService $employeeBonusService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', EmployeeBonus::class);

        $filters = $this->extractFilters($request);

        $bonuses = $this->employeeBonusService->paginatedBonuses(
            $filters,
            (int) config('mowing.default_pagination', 15)
        );

        if (crm_wants_partial($request)) {
            return crm_ajax_html('admin.employee-bonuses.partials.table', compact('bonuses', 'filters'));
        }

        return view('admin.employee-bonuses.index', array_merge(
            ['bonuses' => $bonuses, 'filters' => $filters],
            $this->employeeBonusService->formOptions()
        ));
    }

    public function create(): View
    {
        $this->authorize('create', EmployeeBonus::class);

        return view('admin.employee-bonuses.create', $this->employeeBonusService->formOptions());
    }

    public function store(StoreEmployeeBonusRequest $request): RedirectResponse
    {
        $this->authorize('create', EmployeeBonus::class);

        $this->employeeBonusService->createBonus($request->user(), $request->validated());

        return redirect()
            ->route('admin.employee-bonuses.index')
            ->with('success', 'Employee bonus added successfully.');
    }

    public function edit(EmployeeBonus $employeeBonus): View
    {
        $this->authorize('update', $employeeBonus);

        return view('admin.employee-bonuses.edit', array_merge(
            ['bonus' => $employeeBonus->load('employee')],
            $this->employeeBonusService->formOptions()
        ));
    }

    public function update(UpdateEmployeeBonusRequest $request, EmployeeBonus $employeeBonus): RedirectResponse
    {
        $this->authorize('update', $employeeBonus);

        $this->employeeBonusService->updateBonus($employeeBonus, $request->validated());

        return redirect()
            ->route('admin.employee-bonuses.index')
            ->with('success', 'Employee bonus updated successfully.');
    }

    public function destroy(EmployeeBonus $employeeBonus): JsonResponse
    {
        $this->authorize('delete', $employeeBonus);

        $this->employeeBonusService->deleteBonus($employeeBonus);

        return response()->json(['message' => 'Bonus deleted successfully.']);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', EmployeeBonus::class);

        $bonuses = $this->employeeBonusService->exportBonuses($this->extractFilters($request));

        return (new EmployeeBonusesExport($bonuses, $request->user()->hasRole(CrmRoles::MOWER)))
            ->download('employee-bonuses-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportPdf(Request $request): Response
    {
        $this->authorize('viewAny', EmployeeBonus::class);

        $bonuses = $this->employeeBonusService->exportBonuses($this->extractFilters($request));

        return Pdf::loadView('admin.employee-bonuses.partials.export-pdf', compact('bonuses'))
            ->setPaper('a4', 'landscape')
            ->download('employee-bonuses-' . now()->format('Y-m-d') . '.pdf');
    }

    private function extractFilters(Request $request): array
    {
        $user = $request->user();

        $userId = $user->hasRole(CrmRoles::MOWER)
            ? (string) $user->id
            : $request->input('user_id', '');

        return [
            'search'           => $request->string('search')->toString(),
            'user_id'          => $userId,
            'status'           => $request->input('status', ''),
            'date_range_start' => $request->input('date_range.start', ''),
            'date_range_end'   => $request->input('date_range.end', ''),
        ];
    }
}
