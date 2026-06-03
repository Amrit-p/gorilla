<?php

declare(strict_types=1);

namespace App\DTOS\Request\Reports;

use Illuminate\Support\Carbon;

class MowerRequestReportDTO  
{
    public function __construct(
        public string $search = "",
        public string $list_scope = "",
        public string $status = "",
        public string $priority = "",
        public string $user_id = "",
        public Carbon|null $start_date = null,
        public Carbon|null $end_date = null,
        public ?int $equipment_type_id = null,
        public ?string $customer_type = null,
        public ?string $service_type = null,
        public int|string|null $zone_id = null,
        public int|string|null $recurrence_id = null,
        public int|string|null $client_id = null,
        public ?string $assignment = null,
        public ?string $payment_mode = null,
        public ?string $payment_status = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? '',
            list_scope: $data['list_scope'] ?? '',
            status: $data['status'] ?? '',
            priority: $data['priority'] ?? '',
            user_id: $data['user_id'] ?? '',
            start_date: isset($data['start_date']) ? Carbon::parse($data['start_date']) : null,
            end_date: isset($data['end_date']) ? Carbon::parse($data['end_date']) : null,
            equipment_type_id: isset($data['equipment_type_id']) ? (int) $data['equipment_type_id'] : null,
            customer_type: isset($data['customer_type']) ? $data['customer_type'] : null,
            service_type: isset($data['service_type']) ? $data['service_type'] : null,
            zone_id: data_get($data, 'zone_id', null),
            recurrence_id: data_get($data, 'recurrence_id', null),
            client_id: data_get($data, 'client_id', null),
            assignment: isset($data['assignment']) ? $data['assignment'] : null,
            payment_mode: isset($data['payment_mode']) ? $data['payment_mode'] : null,
            payment_status: isset($data['payment_status']) ? $data['payment_status'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'search' => $this->search,
            'list_scope' => $this->list_scope,
            'status' => $this->status,
            'priority' => $this->priority,
            'done_by_user_id' => $this->user_id,
            'date_range_start' => $this->start_date?->toDateString(),
            'date_range_end' => $this->end_date?->toDateString(),
            'equipment_type_id' => $this->equipment_type_id,
            'customer_type' => $this->customer_type,
            'service_type' => $this->service_type,
            'zone_id' => $this->zone_id,
            'recurrence_id' => $this->recurrence_id,
            'client_id' => $this->client_id,
            'assignment' => $this->assignment,
            'payment_mode' => $this->payment_mode,
            'payment_status' => $this->payment_status,
        ];
    }

    public function withSearch(string $search): self
    {
        $this->search = $search;
        return $this;
    }

    public function withListScope(string $listScope): self
    {
        $this->list_scope = $listScope;
        return $this;
    }

    public function withStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function withPriority(string $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function withUserId(string|int $user_id): self
    {
        $this->user_id = (string) $user_id;
        return $this;
    }
    public function withStartDate(Carbon $start_date): self
    {
        $this->start_date = $start_date;
        return $this;
    }

    public function withEndDate(Carbon $end_date): self
    {
        $this->end_date = $end_date;
        return $this;
    }

    public function withEquipmentTypeId(int $equipment_type_id): self
    {
        $this->equipment_type_id = $equipment_type_id;
        return $this;
    }
    
    public function withCustomerType(string $customer_type): self
    {
        $this->customer_type = $customer_type;
        return $this;
    }

    public function withServiceType(string $service_type): self
    {
        $this->service_type = $service_type;
        return $this;
    }

    public function withClientId(int|string $client_id): self
    {
        $this->client_id = $client_id;
        return $this;
    }

    public function withAssignment(string $assignment): self
    {
        $this->assignment = $assignment;
        return $this;
    }

    public function withPaymentMode(string $payment_mode): self
    {
        $this->payment_mode = $payment_mode;
        return $this;
    }

    public function withPaymentStatus(string $payment_status): self
    {
        $this->payment_status = $payment_status;
        return $this;
    }
}
