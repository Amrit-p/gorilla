<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportCrmBackupRequest;
use App\Imports\GorillaCrmBackupImport;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Hidden Office Manager tool: seeds a Gorilla CRM JSON backup into the CRM.
 * Deliberately absent from the sidebar — reachable by URL only.
 */
class CrmBackupImportController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {}

    public function index(): View
    {
        return view('admin.tools.crm-import.index', [
            'result' => session('import_result'),
        ]);
    }

    public function store(ImportCrmBackupRequest $request): RedirectResponse
    {
        $file = $request->file('backup_file');
        $dryRun = $request->boolean('dry_run');

        $import = new GorillaCrmBackupImport(
            $request->user(),
            strtolower($request->string('mower_email_domain')->trim()->toString()),
        );

        try {
            $result = $import->import((string) $file->getRealPath(), $dryRun);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['backup_file' => $e->getMessage()]);
        }

        $result['filename'] = $file->getClientOriginalName();

        $this->activityLogService->log(
            $request->user(),
            $dryRun ? 'crm_backup.import_previewed' : 'crm_backup.imported',
            $dryRun ? 'Gorilla CRM backup preview (dry run).' : 'Gorilla CRM backup imported.',
            [
                'filename' => $result['filename'],
                'sha256' => $result['sha256'],
                'counts' => $result['counts'],
            ]
        );

        return back()->with('import_result', $result);
    }
}
