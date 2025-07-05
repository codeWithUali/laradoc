<?php

namespace Laradoc\Laradoc\Livewire;

use Livewire\Component;
use Laradoc\Laradoc\Services\SearchService;

class SearchComponent extends Component
{
    public $query = '';
    public $results = [];
    public $filters = [
        'module' => '',
        'type' => '',
    ];
    public $isLoading = false;
    public $totalResults = 0;
    public $modules = [];

    protected $listeners = ['search', 'clearSearch'];

    public function mount()
    {
        $this->modules = app(SearchService::class)->getSearchableModules();
    }

    public function search($query = null)
    {
        if ($query !== null) {
            $this->query = $query;
        }

        if (empty(trim($this->query))) {
            $this->results = [];
            $this->totalResults = 0;
            return;
        }

        $this->isLoading = true;

        try {
            $searchService = app(SearchService::class);
            $searchResult = $searchService->search($this->query, $this->filters);
            
            if ($searchResult['success']) {
                $this->results = $searchResult['results'];
                $this->totalResults = $searchResult['total'];
            } else {
                $this->results = [];
                $this->totalResults = 0;
                $this->dispatch('search-error', ['message' => $searchResult['error']]);
            }
        } catch (\Exception $e) {
            $this->results = [];
            $this->totalResults = 0;
            $this->dispatch('search-error', ['message' => $e->getMessage()]);
        }

        $this->isLoading = false;
    }

    public function clearSearch()
    {
        $this->query = '';
        $this->results = [];
        $this->totalResults = 0;
        $this->filters = [
            'module' => '',
            'type' => '',
        ];
    }

    public function updatedFilters()
    {
        if (!empty($this->query)) {
            $this->search();
        }
    }

    public function updatedQuery()
    {
        if (strlen($this->query) >= 2) {
            $this->search();
        } else {
            $this->results = [];
            $this->totalResults = 0;
        }
    }

    public function highlightText($text, $query)
    {
        if (empty($query)) {
            return $text;
        }

        $words = explode(' ', $query);
        $highlighted = $text;

        foreach ($words as $word) {
            $word = trim($word);
            if (!empty($word)) {
                $highlighted = preg_replace(
                    '/(' . preg_quote($word, '/') . ')/i',
                    '<mark>$1</mark>',
                    $highlighted
                );
            }
        }

        return $highlighted;
    }

    public function getResultExcerpt($content, $query, $length = 200)
    {
        $content = strip_tags($content);
        
        if (strlen($content) <= $length) {
            return $this->highlightText($content, $query);
        }

        // Find the position of the query in the content
        $pos = stripos($content, $query);
        
        if ($pos !== false) {
            // Start from the query position, but ensure we don't start too early
            $start = max(0, $pos - ($length / 2));
            $excerpt = substr($content, $start, $length);
            
            // Add ellipsis if we're not at the beginning
            if ($start > 0) {
                $excerpt = '...' . $excerpt;
            }
            
            // Add ellipsis if we're not at the end
            if ($start + $length < strlen($content)) {
                $excerpt .= '...';
            }
        } else {
            // If query not found, just take the first part
            $excerpt = substr($content, 0, $length) . '...';
        }

        return $this->highlightText($excerpt, $query);
    }

    public function getResultUrl($result)
    {
        if (isset($result['module'])) {
            return route('laradoc.module', $result['module']);
        }
        
        return route('laradoc.index');
    }

    public function render()
    {
        return view('laradoc::livewire.search', [
            'query' => $this->query,
            'results' => $this->results,
            'filters' => $this->filters,
            'modules' => $this->modules,
            'isLoading' => $this->isLoading,
            'totalResults' => $this->totalResults,
        ]);
    }
} 