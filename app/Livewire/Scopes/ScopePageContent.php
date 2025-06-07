<?php

namespace App\Livewire\Scopes;

use App\Models\Page;
use App\Models\Scope;
use App\Services\AccessibilityAnalyzer\AccessibilityAnalyzerService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ScopePageContent extends Component
{
    public Scope $scope;

    #[Computed(persist: true)]
    public function pages(): Collection
    {
        return $this->scope->pages()
            ->select(['id', 'scope_id', 'retrieved_at'])
            ->orderBy('retrieved_at', 'desc')
            ->get();
    }

    public function retrieveContent(): void
    {
        $this->authorize('update', $this->scope);

        $parser = new AccessibilityAnalyzerService();
        $pageContent = $parser->getPageContent($this->scope->url, true);
        $this->scope->setPageContent($this->scope->url, $pageContent);

        unset($this->pages);
    }

    public function makeCurrentPage(Page $page): void
    {
        $this->authorize('update', $this->scope);

        $this->scope->setCurrentPage($page);
        unset($this->pages);
    }

    public function deletePage(Page $page): void
    {
        $this->authorize('update', $this->scope);

        if ($page->id === $this->scope->current_page_id) {
            $this->scope->setCurrentPage(null);
            $page->delete();
            $this->scope->setCurrentPage($this->scope->latestPage);
        } else {
            $page->delete();
        }
        unset($this->pages);
    }
}
