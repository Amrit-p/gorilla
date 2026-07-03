<?php

namespace App\Repositories;

use App\Models\Lead;
use App\Support\QueryFilters\LeadListFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LeadRepository
{
    public function __construct(
        private readonly LeadListFilter $leadListFilter
    ) {}

    public function paginatedList(array $filters, int $perPage = 15, bool $convertedOnly = false): LengthAwarePaginator
    {
        $query = Lead::query()
            ->select([
                'id',
                'zone_id',
                'client_name',
                'email',
                'mobile_number',
                'address',
                'service_types',
                'equipment_type_id',
                'recurrence_id',
                'job_type',
                'charges',
                'payment_mode',
                'payment_status',
                'status',
                'is_locked',
                'assigned_sales_user_id',
                'converted_at',
                'created_at',
            ])
            ->with([
                'assignedSalesUser:id,name',
                'equipmentType:id,name,color_code',
                'client:id,lead_id',
                'zone:id,name',
                'recurrence:id,name',
            ])
            ->latest();

        if ($convertedOnly) {
            $query->whereNotNull('converted_at');
        } else {
            $query->whereNull('converted_at');
        }

        $this->leadListFilter->apply($query, $filters);

        return $query->paginate($perPage)->withQueryString();
    }

    public function exportList(array $filters): Collection
    {
        $query = Lead::query()
            ->with([
                'assignedSalesUser:id,name',
                'equipmentType:id,name,color_code',
                'client:id,lead_id',
                'zone:id,name',
                'recurrence:id,name',
            ])
            ->latest();

        $this->leadListFilter->apply($query, $filters);

        return $query->get();
    }
}
