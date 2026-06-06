<?php

/**
 * Authenticated CRM routes — Gorilla CRM permission middleware.
 */

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\Masters\EquipmentTypeController;
use App\Http\Controllers\Admin\Masters\SafetyTypeController;
use App\Http\Controllers\Admin\Masters\ServiceTypeController;
use App\Http\Controllers\Admin\Masters\RecurrenceController;
use App\Http\Controllers\Admin\Masters\AccountingLevelController;
use App\Http\Controllers\Admin\Masters\JobLevelController;
use App\Http\Controllers\Admin\Masters\ChecklistController;
use App\Http\Controllers\Admin\Masters\ZoneController;
use App\Http\Controllers\Mower\ChecklistController as MowerChecklistController;
use App\Http\Controllers\Admin\ClientManagementController;
use App\Http\Controllers\Admin\JobManagementController;
use App\Http\Controllers\Admin\LeadManagementController;
use App\Http\Controllers\Admin\MapRoutingController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\EmployeeBonusController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Mower\MowerDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Support\CrmPermissions;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active_user'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('crm.permission:'.CrmPermissions::VIEW_DASHBOARD)
        ->name('dashboard.index');
    Route::patch('/dashboard/preferences', [DashboardController::class, 'updatePreferences'])
        ->middleware('crm.permission:'.CrmPermissions::VIEW_DASHBOARD)
        ->name('dashboard.preferences.update');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::middleware('crm.permission:'.CrmPermissions::MANAGE_USERS)->group(function (): void {
        Route::get('/admin/rbac', [RolePermissionController::class, 'index'])->name('admin.rbac.index');
        Route::patch('/admin/rbac/users/{user}/role', [RolePermissionController::class, 'updateUserRole'])
            ->name('admin.rbac.users.role.update');

        Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [UserManagementController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
        Route::patch('/admin/users/{user}/status', [UserManagementController::class, 'updateStatus'])->name('admin.users.status.update');
        Route::get('/admin/users/{user}', [UserManagementController::class, 'show'])->name('admin.users.show');
        Route::patch('/admin/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
        Route::get('/admin/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
        Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
        Route::patch('/admin/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    });

    // Mowers can view and export their own bonuses; policy + extractFilters enforce the user_id scope.
    Route::get('/admin/employee-bonuses', [EmployeeBonusController::class, 'index'])->name('admin.employee-bonuses.index');
    Route::get('/admin/employee-bonuses/export/excel', [EmployeeBonusController::class, 'exportExcel'])->name('admin.employee-bonuses.export.excel');
    Route::get('/admin/employee-bonuses/export/pdf', [EmployeeBonusController::class, 'exportPdf'])->name('admin.employee-bonuses.export.pdf');

    Route::middleware('crm.permission:'.CrmPermissions::MANAGE_EMPLOYEE_BONUSES)->group(function (): void {
        Route::get('/admin/employee-bonuses/create', [EmployeeBonusController::class, 'create'])->name('admin.employee-bonuses.create');
        Route::post('/admin/employee-bonuses', [EmployeeBonusController::class, 'store'])->name('admin.employee-bonuses.store');
        Route::get('/admin/employee-bonuses/{employeeBonus}/edit', [EmployeeBonusController::class, 'edit'])->name('admin.employee-bonuses.edit');
        Route::patch('/admin/employee-bonuses/{employeeBonus}', [EmployeeBonusController::class, 'update'])->name('admin.employee-bonuses.update');
        Route::delete('/admin/employee-bonuses/{employeeBonus}', [EmployeeBonusController::class, 'destroy'])->name('admin.employee-bonuses.destroy');
    });

    Route::middleware('crm.permission:'.CrmPermissions::MANAGE_MASTERS)->prefix('admin/masters')->name('admin.masters.')->group(function (): void {
        Route::get('checklists', [ChecklistController::class, 'index'])->name('checklists.index');
        Route::post('checklists', [ChecklistController::class, 'store'])->name('checklists.store');
        Route::get('checklists/{checklist}', [ChecklistController::class, 'show'])->name('checklists.show');
        Route::patch('checklists/{checklist}', [ChecklistController::class, 'update'])->name('checklists.update');
        Route::get('service-types', [ServiceTypeController::class, 'index'])->name('service-types.index');
        Route::post('service-types', [ServiceTypeController::class, 'store'])->name('service-types.store');
        Route::get('service-types/{serviceType}', [ServiceTypeController::class, 'show'])->name('service-types.show');
        Route::patch('service-types/{serviceType}', [ServiceTypeController::class, 'update'])->name('service-types.update');
        Route::patch('service-types/{serviceType}/status', [ServiceTypeController::class, 'updateStatus'])->name('service-types.status.update');
        Route::delete('service-types/{serviceType}', [ServiceTypeController::class, 'destroy'])->name('service-types.destroy');

        Route::get('equipment-types', [EquipmentTypeController::class, 'index'])->name('equipment-types.index');
        Route::post('equipment-types', [EquipmentTypeController::class, 'store'])->name('equipment-types.store');
        Route::get('equipment-types/{equipmentType}', [EquipmentTypeController::class, 'show'])->name('equipment-types.show');
        Route::patch('equipment-types/{equipmentType}', [EquipmentTypeController::class, 'update'])->name('equipment-types.update');
        Route::patch('equipment-types/{equipmentType}/status', [EquipmentTypeController::class, 'updateStatus'])->name('equipment-types.status.update');
        Route::delete('equipment-types/{equipmentType}', [EquipmentTypeController::class, 'destroy'])->name('equipment-types.destroy');

        Route::get('safety-types', [SafetyTypeController::class, 'index'])->name('safety-types.index');
        Route::post('safety-types', [SafetyTypeController::class, 'store'])->name('safety-types.store');
        Route::get('safety-types/{safetyType}', [SafetyTypeController::class, 'show'])->name('safety-types.show');
        Route::patch('safety-types/{safetyType}', [SafetyTypeController::class, 'update'])->name('safety-types.update');
        Route::patch('safety-types/{safetyType}/status', [SafetyTypeController::class, 'updateStatus'])->name('safety-types.status.update');
        Route::delete('safety-types/{safetyType}', [SafetyTypeController::class, 'destroy'])->name('safety-types.destroy');

        Route::get('recurrences', [RecurrenceController::class, 'index'])->name('recurrences.index');
        Route::post('recurrences', [RecurrenceController::class, 'store'])->name('recurrences.store');
        Route::get('recurrences/{recurrence}', [RecurrenceController::class, 'show'])->name('recurrences.show');
        Route::patch('recurrences/{recurrence}', [RecurrenceController::class, 'update'])->name('recurrences.update');
        Route::patch('recurrences/{recurrence}/status', [RecurrenceController::class, 'updateStatus'])->name('recurrences.status.update');
        Route::delete('recurrences/{recurrence}', [RecurrenceController::class, 'destroy'])->name('recurrences.destroy');

        Route::get('zones', [ZoneController::class, 'index'])->name('zones.index');
        Route::post('zones', [ZoneController::class, 'store'])->name('zones.store');
        Route::get('zones/{zone}', [ZoneController::class, 'show'])->name('zones.show');
        Route::patch('zones/{zone}', [ZoneController::class, 'update'])->name('zones.update');
        Route::patch('zones/{zone}/status', [ZoneController::class, 'updateStatus'])->name('zones.status.update');
        Route::delete('zones/{zone}', [ZoneController::class, 'destroy'])->name('zones.destroy');

        Route::get('accounting-levels', [AccountingLevelController::class, 'index'])->name('accounting-levels.index');
        Route::post('accounting-levels', [AccountingLevelController::class, 'store'])->name('accounting-levels.store');
        Route::get('accounting-levels/{accountingLevel}', [AccountingLevelController::class, 'show'])->name('accounting-levels.show');
        Route::patch('accounting-levels/{accountingLevel}', [AccountingLevelController::class, 'update'])->name('accounting-levels.update');
        Route::patch('accounting-levels/{accountingLevel}/status', [AccountingLevelController::class, 'updateStatus'])->name('accounting-levels.status.update');
        Route::delete('accounting-levels/{accountingLevel}', [AccountingLevelController::class, 'destroy'])->name('accounting-levels.destroy');

        Route::get('job-levels', [JobLevelController::class, 'index'])->name('job-levels.index');
        Route::post('job-levels', [JobLevelController::class, 'store'])->name('job-levels.store');
        Route::get('job-levels/{jobLevel}', [JobLevelController::class, 'show'])->name('job-levels.show');
        Route::patch('job-levels/{jobLevel}', [JobLevelController::class, 'update'])->name('job-levels.update');
        Route::patch('job-levels/{jobLevel}/status', [JobLevelController::class, 'updateStatus'])->name('job-levels.status.update');
        Route::delete('job-levels/{jobLevel}', [JobLevelController::class, 'destroy'])->name('job-levels.destroy');
    });

    Route::middleware('crm.permission:'.CrmPermissions::MANAGE_LEADS)->group(function (): void {
        Route::get('/admin/leads', [LeadManagementController::class, 'index'])->name('admin.leads.index');
        Route::get('/admin/leads/create', [LeadManagementController::class, 'create'])->name('admin.leads.create');
        Route::post('/admin/leads', [LeadManagementController::class, 'store'])->name('admin.leads.store');
        Route::get('/admin/leads/export/excel', [LeadManagementController::class, 'exportExcel'])->name('admin.leads.export.excel');
        Route::get('/admin/leads/export/pdf', [LeadManagementController::class, 'exportPdf'])->name('admin.leads.export.pdf');
        Route::get('/admin/leads/import/sample', [LeadManagementController::class, 'downloadImportSample'])->name('admin.leads.import.sample');
        Route::post('/admin/leads/import', [LeadManagementController::class, 'import'])->name('admin.leads.import');
        Route::get('/admin/leads/{lead}/edit', [LeadManagementController::class, 'edit'])->name('admin.leads.edit');
        Route::patch('/admin/leads/{lead}', [LeadManagementController::class, 'update'])->name('admin.leads.update');
        Route::patch('/admin/leads/{lead}/status', [LeadManagementController::class, 'updateStatus'])->name('admin.leads.status.update');
        Route::post('/admin/leads/{lead}/notes', [LeadManagementController::class, 'addNote'])->name('admin.leads.notes.store');
        Route::get('/admin/leads/{lead}', [LeadManagementController::class, 'show'])->name('admin.leads.show');
        Route::delete('/admin/leads/{lead}', [LeadManagementController::class, 'destroy'])->name('admin.leads.destroy');
    });

    Route::middleware('crm.permission:'.CrmPermissions::MANAGE_CUSTOMERS)->group(function (): void {
        Route::get('/admin/clients', [ClientManagementController::class, 'index'])->name('admin.clients.index');
        Route::get('/admin/clients/export/excel', [ClientManagementController::class, 'exportExcel'])->name('admin.clients.export.excel');
        Route::get('/admin/clients/export/pdf', [ClientManagementController::class, 'exportPdf'])->name('admin.clients.export.pdf');
        Route::get('/admin/clients/create', [ClientManagementController::class, 'create'])->name('admin.clients.create');
        Route::post('/admin/clients', [ClientManagementController::class, 'store'])->name('admin.clients.store');
        Route::get('/admin/clients/{client}/edit', [ClientManagementController::class, 'edit'])->name('admin.clients.edit');
        Route::get('/admin/clients/{client}/jobs', [ClientManagementController::class, 'jobsTab'])->name('admin.clients.jobs');
        Route::patch('/admin/clients/{client}', [ClientManagementController::class, 'update'])->name('admin.clients.update');
        Route::delete('/admin/clients/{client}', [ClientManagementController::class, 'destroy'])->name('admin.clients.destroy');
        Route::get('/admin/clients/{client}', [ClientManagementController::class, 'show'])->name('admin.clients.show');
    });

    Route::middleware('crm.permission:'.CrmPermissions::ASSIGN_JOBS)->group(function (): void {
        Route::get('/admin/jobs/create', [JobManagementController::class, 'create'])->name('admin.jobs.create');
        Route::get('/admin/jobs/mower-suggestions', [JobManagementController::class, 'mowerSuggestions'])->name('admin.jobs.mower-suggestions');
        Route::get('/admin/jobs/mower-workloads', [JobManagementController::class, 'mowerWorkloads'])->name('admin.jobs.mower-workloads');
        Route::get('/admin/jobs/client-remarks', [JobManagementController::class, 'clientRemarks'])->name('admin.jobs.client-remarks');
        Route::post('/admin/jobs', [JobManagementController::class, 'store'])->name('admin.jobs.store');
        Route::get('/admin/jobs/{job}/edit', [JobManagementController::class, 'edit'])->name('admin.jobs.edit');
        Route::patch('/admin/jobs/{job}', [JobManagementController::class, 'update'])->name('admin.jobs.update');
        Route::post('/admin/jobs/bulk/assign', [JobManagementController::class, 'bulkAssignEmployees'])->name('admin.jobs.bulk.assign');
        Route::post('/admin/jobs/bulk/status', [JobManagementController::class, 'bulkUpdateStatus'])->name('admin.jobs.bulk.status.update');
        Route::post('/admin/jobs/bulk/schedule', [JobManagementController::class, 'bulkScheduleJobs'])->name('admin.jobs.bulk.schedule');
        Route::delete('/admin/jobs/bulk', [JobManagementController::class, 'bulkDestroy'])->name('admin.jobs.bulk.destroy');
        Route::post('/admin/jobs/reorder', [JobManagementController::class, 'reorder'])->name('admin.jobs.reorder');
        Route::post('/admin/jobs/{job}/images', [JobManagementController::class, 'uploadImages'])
            ->middleware('crm.permission:'.CrmPermissions::UPLOAD_JOB_IMAGES)
            ->name('admin.jobs.images.store');
        Route::post('/admin/maps/optimize', [MapRoutingController::class, 'optimize'])->name('admin.maps.optimize');
        Route::post('/admin/maps/assign-nearest', [MapRoutingController::class, 'assignNearest'])->name('admin.maps.assign-nearest');
    });

    Route::middleware('crm.any_permission:'.CrmPermissions::MANAGE_JOBS.','.CrmPermissions::ASSIGN_JOBS)->group(function (): void {
        Route::get('/admin/jobs', [JobManagementController::class, 'index'])->name('admin.jobs.index');
        Route::get('/admin/jobs/export/excel', [JobManagementController::class, 'exportExcel'])->name('admin.jobs.export.excel');
        Route::get('/admin/jobs/export/pdf', [JobManagementController::class, 'exportPdf'])->name('admin.jobs.export.pdf');
        Route::get('/admin/jobs/{job}', [JobManagementController::class, 'show'])->name('admin.jobs.show');
        Route::get('/admin/maps', [MapRoutingController::class, 'index'])->name('admin.maps.index');
        Route::get('/admin/maps/jobs', [MapRoutingController::class, 'jobs'])->name('admin.maps.jobs');
        Route::redirect('/employee/mobile', '/mower')->name('employee.mobile.index');

        Route::prefix('mower')->name('mower.')->group(function (): void {
            Route::get('/checklist', [MowerChecklistController::class, 'index'])->name('checklist.index');
            Route::post('/checklist', [MowerChecklistController::class, 'submit'])->name('checklist.submit');

            Route::middleware('mower.checklist')->group(function (): void {
                Route::get('/', [MowerDashboardController::class, 'index'])->name('index');
                Route::get('/notifications', [NotificationController::class, 'mowerIndex'])->name('notifications.index');
                Route::get('/jobs/{job}', [MowerDashboardController::class, 'show'])->name('jobs.show');
                Route::patch('/jobs/{job}/status', [MowerDashboardController::class, 'updateStatus'])->name('jobs.status.update');
                Route::patch('/jobs/{job}/payment', [MowerDashboardController::class, 'updatePayment'])->name('jobs.payment.update');
                Route::patch('/jobs/{job}/consumed-time', [MowerDashboardController::class, 'updateConsumedTime'])->name('jobs.consumed-time.update');
                Route::post('/jobs/{job}/images/before', [MowerDashboardController::class, 'uploadBefore'])->name('jobs.images.before');
                Route::post('/jobs/{job}/images/after', [MowerDashboardController::class, 'uploadAfter'])->name('jobs.images.after');
                Route::delete('/jobs/{job}/images/before/{imageId}', [MowerDashboardController::class, 'deleteBefore'])->name('jobs.images.before.destroy');
                Route::delete('/jobs/{job}/images/after/{imageId}', [MowerDashboardController::class, 'deleteAfter'])->name('jobs.images.after.destroy');
                Route::post('/jobs/{job}/remark', [MowerDashboardController::class, 'storeRemark'])->name('jobs.remark.store');
            });
        });
    });

    Route::middleware('crm.any_permission:'.implode(',', [
        CrmPermissions::VIEW_MOWER_REPORT,
    ]))->group(function (): void {
        Route::prefix('reports')->name('reports.')->group(function (): void {
            Route::prefix('mower')->name('mower.')->group(function (): void {
                Route::get('/', [\App\Http\Controllers\Report\MowerReportController::class, 'index'])
                    ->name('index');
                Route::get('/report', [\App\Http\Controllers\Report\MowerReportController::class, 'report'])
                    ->name('report');
                Route::get('/jobs', [\App\Http\Controllers\Report\MowerReportController::class, 'jobs'])
                    ->name('jobs');
                Route::get('/export', [\App\Http\Controllers\Report\MowerReportController::class, 'export'])
                    ->name('export');
                Route::get('/export-pdf', [\App\Http\Controllers\Report\MowerReportController::class, 'exportPdf'])
                    ->name('export-pdf');
            });
        });
    });
});
