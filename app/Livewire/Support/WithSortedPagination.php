<?php

namespace App\Livewire\Support;

use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination;

trait WithSortedPagination
{
    use WithPagination;

    public array $sorts = [];

    public function isSorted(string $column, string $pageName): bool
    {
        return $this->sorts[$pageName]['column'] === $column;
    }

    public function sortDirection(string $pageName): string
    {
        return $this->sorts[$pageName]['dir'];
    }

    public function sortBy(string $column, string $pageName, ?string $defaultDir = 'asc'): void
    {
        if ($this->isSorted($column, $pageName)) {
            // Toggle the direction
            $this->sorts[$pageName]['dir'] = $this->sortDirection($pageName) === 'asc' ? 'desc' : 'asc';
        } else {
            // Set to the new column and default direction
            $this->sorts[$pageName]['column'] = $column;
            $this->sorts[$pageName]['dir'] = $defaultDir;
        }
        $this->resetPage($pageName);
    }

    protected function getSortColumn(string $pageName): mixed
    {
        return $this->sorts[$pageName]['column'];
    }

    private function getSortDirection(string $pageName): mixed
    {
        return $this->sorts[$pageName]['dir'];
    }

    protected function setSortDefaults(string $pageName, ?string $column = 'created_at', ?string $dir = 'asc'): void
    {
        if (!isset($this->sorts[$pageName])) {
            $this->sorts[$pageName] = ['column' => $column, 'dir' => $dir];
        }
    }

    protected function sortQuery(Builder $query, string $pageName): Builder
    {
        $this->setSortDefaults($pageName);

        return $query->orderBy($this->getSortColumn($pageName), $this->getSortDirection($pageName));
    }
}
