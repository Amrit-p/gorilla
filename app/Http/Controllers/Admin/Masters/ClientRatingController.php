<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\Masters\Concerns\ManagesMasterCatalog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Masters\StoreClientRatingRequest;
use App\Http\Requests\Admin\Masters\ToggleMasterStatusRequest;
use App\Http\Requests\Admin\Masters\UpdateClientRatingRequest;
use App\Models\ClientRating;
use App\Services\MasterCatalogService;
use App\Support\MasterCatalog;
use Illuminate\Http\Request;

class ClientRatingController extends Controller
{
    use ManagesMasterCatalog;

    public function __construct(MasterCatalogService $masterCatalogService)
    {
        $this->masterCatalogService = $masterCatalogService;
    }

    protected function catalogKey(): string
    {
        return MasterCatalog::CLIENT_RATINGS;
    }

    protected function modelClass(): string
    {
        return ClientRating::class;
    }

    protected function title(): string
    {
        return 'Client Ratings';
    }

    protected function singularLabel(): string
    {
        return 'client rating';
    }

    protected function indexRoute(): string
    {
        return 'admin.masters.client-ratings.index';
    }

    protected function storeRoute(): string
    {
        return 'admin.masters.client-ratings.store';
    }

    protected function showRouteName(): string
    {
        return 'admin.masters.client-ratings.show';
    }

    protected function updateRouteName(): string
    {
        return 'admin.masters.client-ratings.update';
    }

    protected function statusRouteName(): string
    {
        return 'admin.masters.client-ratings.status.update';
    }

    protected function destroyRouteName(): string
    {
        return 'admin.masters.client-ratings.destroy';
    }

    protected function logPrefix(): string
    {
        return 'master.client_rating';
    }

    protected function tablePartial(): string
    {
        return 'admin.masters.client-ratings.partials.table';
    }

    protected function formFieldsPartial(): string
    {
        return 'admin.masters.client-ratings.partials.form-fields';
    }

    public function store(StoreClientRatingRequest $request)
    {
        return $this->storeRecord($request);
    }

    public function update(UpdateClientRatingRequest $request, ClientRating $clientRating)
    {
        return $this->updateRecord($request, $clientRating);
    }

    public function show(ClientRating $clientRating)
    {
        $this->authorizeMaster('view');

        return response()->json($clientRating->only([
            'id',
            'name',
            'description',
            'sort_order',
            'is_active',
        ]));
    }

    public function updateStatus(ToggleMasterStatusRequest $request, ClientRating $clientRating)
    {
        return $this->toggleRecordStatus($request, $clientRating);
    }

    public function destroy(Request $request, ClientRating $clientRating)
    {
        return $this->destroyRecord($request, $clientRating);
    }
}
