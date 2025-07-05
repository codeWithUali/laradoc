<?php

namespace Laradoc\Laradoc\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use MeiliSearch\Client;

class SearchService
{
    protected $config;
    protected $client;
    protected $index;

    public function __construct()
    {
        $this->config = config('laradoc');
        $this->initializeSearch();
    }

    protected function initializeSearch()
    {
        if ($this->config['search']['driver'] === 'meilisearch') {
            $this->initializeMeilisearch();
        }
    }

    protected function initializeMeilisearch()
    {
        try {
            $searchConfig = $this->config['search']['meilisearch'];
            $this->client = new Client($searchConfig['host'], $searchConfig['key']);
            $this->index = $this->client->index($searchConfig['index']);
        } catch (\Exception $e) {
            // Fallback to database search if Meilisearch is not available
            $this->config['search']['driver'] = 'database';
        }
    }

    public function indexDocumentation($documentation)
    {
        if ($this->config['search']['driver'] === 'meilisearch') {
            return $this->indexMeilisearch($documentation);
        } else {
            return $this->indexDatabase($documentation);
        }
    }

    protected function indexMeilisearch($documentation)
    {
        try {
            $documents = [];
            
            foreach ($documentation as $module => $doc) {
                $documents[] = [
                    'id' => $module,
                    'title' => $doc['title'],
                    'content' => $doc['content'],
                    'description' => $doc['description'] ?? '',
                    'module' => $module,
                    'type' => 'documentation',
                ];

                // Index individual sections
                $sections = $this->extractSections($doc['content']);
                foreach ($sections as $sectionId => $section) {
                    $documents[] = [
                        'id' => $module . '_' . $sectionId,
                        'title' => $section['title'],
                        'content' => $section['content'],
                        'module' => $module,
                        'section' => $sectionId,
                        'type' => 'section',
                    ];
                }
            }

            $this->index->addDocuments($documents);
            
            return [
                'success' => true,
                'indexed' => count($documents),
                'driver' => 'meilisearch',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'driver' => 'meilisearch',
            ];
        }
    }

    protected function indexDatabase($documentation)
    {
        try {
            // Create search table if it doesn't exist
            $this->createSearchTable();

            // Clear existing data
            DB::table('laradoc_search')->truncate();

            $documents = [];
            
            foreach ($documentation as $module => $doc) {
                $documents[] = [
                    'module' => $module,
                    'title' => $doc['title'],
                    'content' => $doc['content'],
                    'description' => $doc['description'] ?? '',
                    'type' => 'documentation',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Index individual sections
                $sections = $this->extractSections($doc['content']);
                foreach ($sections as $sectionId => $section) {
                    $documents[] = [
                        'module' => $module,
                        'title' => $section['title'],
                        'content' => $section['content'],
                        'description' => '',
                        'type' => 'section',
                        'section' => $sectionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            DB::table('laradoc_search')->insert($documents);
            
            return [
                'success' => true,
                'indexed' => count($documents),
                'driver' => 'database',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'driver' => 'database',
            ];
        }
    }

    protected function createSearchTable()
    {
        if (!DB::getSchemaBuilder()->hasTable('laradoc_search')) {
            DB::statement('
                CREATE TABLE laradoc_search (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    module VARCHAR(255) NOT NULL,
                    title VARCHAR(500) NOT NULL,
                    content LONGTEXT NOT NULL,
                    description TEXT,
                    type VARCHAR(50) DEFAULT "documentation",
                    section VARCHAR(255) NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    FULLTEXT(title, content, description)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
        }
    }

    public function search($query, $filters = [])
    {
        if ($this->config['search']['driver'] === 'meilisearch') {
            return $this->searchMeilisearch($query, $filters);
        } else {
            return $this->searchDatabase($query, $filters);
        }
    }

    protected function searchMeilisearch($query, $filters)
    {
        try {
            $searchParams = [
                'query' => $query,
                'limit' => 20,
            ];

            if (!empty($filters['module'])) {
                $searchParams['filter'] = 'module = ' . $filters['module'];
            }

            if (!empty($filters['type'])) {
                $searchParams['filter'] = 'type = ' . $filters['type'];
            }

            $results = $this->index->search($query, $searchParams);
            
            return [
                'success' => true,
                'results' => $results['hits'],
                'total' => $results['estimatedTotalHits'],
                'driver' => 'meilisearch',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'driver' => 'meilisearch',
            ];
        }
    }

    protected function searchDatabase($query, $filters)
    {
        try {
            $searchQuery = DB::table('laradoc_search')
                ->whereRaw('MATCH(title, content, description) AGAINST(? IN BOOLEAN MODE)', [$query]);

            if (!empty($filters['module'])) {
                $searchQuery->where('module', $filters['module']);
            }

            if (!empty($filters['type'])) {
                $searchQuery->where('type', $filters['type']);
            }

            $results = $searchQuery->get();
            
            return [
                'success' => true,
                'results' => $results,
                'total' => $results->count(),
                'driver' => 'database',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'driver' => 'database',
            ];
        }
    }

    protected function extractSections($content)
    {
        $sections = [];
        $lines = explode("\n", $content);
        $currentSection = null;
        $currentContent = '';

        foreach ($lines as $line) {
            if (preg_match('/^#{1,6}\s+(.+)$/', $line, $matches)) {
                // Save previous section
                if ($currentSection) {
                    $sections[$currentSection] = [
                        'title' => $currentSection,
                        'content' => trim($currentContent),
                    ];
                }

                // Start new section
                $currentSection = $matches[1];
                $currentContent = $line . "\n";
            } else {
                if ($currentSection) {
                    $currentContent .= $line . "\n";
                }
            }
        }

        // Save last section
        if ($currentSection) {
            $sections[$currentSection] = [
                'title' => $currentSection,
                'content' => trim($currentContent),
            ];
        }

        return $sections;
    }

    public function getSearchableModules()
    {
        if ($this->config['search']['driver'] === 'meilisearch') {
            return $this->getMeilisearchModules();
        } else {
            return $this->getDatabaseModules();
        }
    }

    protected function getMeilisearchModules()
    {
        try {
            $results = $this->index->search('', ['limit' => 1000]);
            $modules = [];
            
            foreach ($results['hits'] as $hit) {
                if ($hit['type'] === 'documentation') {
                    $modules[$hit['module']] = $hit['title'];
                }
            }
            
            return $modules;
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getDatabaseModules()
    {
        try {
            $modules = DB::table('laradoc_search')
                ->where('type', 'documentation')
                ->select('module', 'title')
                ->get()
                ->pluck('title', 'module')
                ->toArray();
            
            return $modules;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function clearIndex()
    {
        if ($this->config['search']['driver'] === 'meilisearch') {
            try {
                $this->index->deleteAllDocuments();
                return ['success' => true, 'driver' => 'meilisearch'];
            } catch (\Exception $e) {
                return ['success' => false, 'error' => $e->getMessage()];
            }
        } else {
            try {
                DB::table('laradoc_search')->truncate();
                return ['success' => true, 'driver' => 'database'];
            } catch (\Exception $e) {
                return ['success' => false, 'error' => $e->getMessage()];
            }
        }
    }

    public function getSearchStats()
    {
        if ($this->config['search']['driver'] === 'meilisearch') {
            try {
                $stats = $this->index->getStats();
                return [
                    'driver' => 'meilisearch',
                    'total_documents' => $stats['numberOfDocuments'],
                    'index_size' => $stats['databaseSize'],
                ];
            } catch (\Exception $e) {
                return ['driver' => 'meilisearch', 'error' => $e->getMessage()];
            }
        } else {
            try {
                $total = DB::table('laradoc_search')->count();
                return [
                    'driver' => 'database',
                    'total_documents' => $total,
                ];
            } catch (\Exception $e) {
                return ['driver' => 'database', 'error' => $e->getMessage()];
            }
        }
    }
} 