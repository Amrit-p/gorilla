<?php

namespace App\Services;

use App\Models\EmployeeBonus;
use App\Models\User;
use App\Repositories\EmployeeBonusRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EmployeeBonusService
{
    public function __construct(
        private readonly EmployeeBonusRepository $repository
    ) {}

    public function paginatedBonuses(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->repository->paginatedList($filters, $perPage);
    }

    /** @return Collection<int, EmployeeBonus> */
    public function exportBonuses(array $filters): Collection
    {
        return $this->repository->exportList($filters);
    }

    /** @return array<string, mixed> */
    public function formOptions(): array
    {
        return [
            'employees' => User::query()
                ->select(['id', 'name', 'user_unique_id'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ];
    }

    public function createBonus(User $actor, array $data): EmployeeBonus
    {
        $bonus = new EmployeeBonus(array_merge($data, ['created_by' => $actor->id]));
        $bonus->save();

        return $bonus;
    }

    public function updateBonus(EmployeeBonus $bonus, array $data): void
    {
        $bonus->fill($data)->save();
    }

    public function deleteBonus(EmployeeBonus $bonus): void
    {
        EmployeeBonus::destroy($bonus->getKey());
    }
}
