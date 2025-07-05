<?php

namespace Laradoc\Laradoc\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentationGenerator
{
    protected $projectAnalyzer;
    protected $aiService;
    protected $config;

    public function __construct(ProjectAnalyzer $projectAnalyzer, AIService $aiService)
    {
        $this->projectAnalyzer = $projectAnalyzer;
        $this->aiService = $aiService;
        $this->config = config('laradoc');
    }

    public function generateCompleteDocumentation()
    {
        $analysis = $this->projectAnalyzer->analyzeProject();
        $documentation = [];

        // Generate overview documentation
        $documentation['overview'] = $this->generateOverviewDocumentation($analysis);

        // Generate module-specific documentation
        foreach ($this->config['documentation']['modules'] as $moduleKey => $moduleConfig) {
            $documentation[$moduleKey] = $this->generateModuleDocumentation($analysis, $moduleKey, $moduleConfig);
        }

        // Generate API documentation
        $documentation['api'] = $this->generateApiDocumentation($analysis);

        // Generate database documentation
        $documentation['database'] = $this->generateDatabaseDocumentation($analysis);

        // Save documentation
        $this->saveDocumentation($documentation);

        return $documentation;
    }

    public function generateModuleDocumentation($analysis, $moduleKey, $moduleConfig)
    {
        $content = "# {$moduleConfig['title']}\n\n";
        $content .= "{$moduleConfig['description']}\n\n";

        // Get module-specific data
        $moduleData = $this->extractModuleData($analysis, $moduleKey);

        // Generate AI-powered documentation for this module
        $aiContent = $this->aiService->generateDocumentation($analysis, $moduleKey);
        
        if ($aiContent) {
            $content .= $aiContent . "\n\n";
        }

        // Add structured data
        $content .= $this->generateStructuredModuleContent($moduleData, $moduleKey);

        return [
            'title' => $moduleConfig['title'],
            'description' => $moduleConfig['description'],
            'content' => $content,
            'data' => $moduleData,
        ];
    }

    protected function generateOverviewDocumentation($analysis)
    {
        $content = "# Project Overview\n\n";
        
        $projectInfo = $analysis['project_info'];
        $content .= "## Project Information\n\n";
        $content .= "- **Name:** {$projectInfo['name']}\n";
        $content .= "- **Description:** {$projectInfo['description']}\n";
        $content .= "- **Version:** {$projectInfo['version']}\n";
        $content .= "- **Laravel Version:** {$projectInfo['laravel_version']}\n";
        $content .= "- **PHP Version:** {$projectInfo['php_version']}\n";
        $content .= "- **Environment:** {$projectInfo['environment']}\n\n";

        // Generate AI-powered overview
        $aiContent = $this->aiService->generateDocumentation($analysis);
        
        if ($aiContent) {
            $content .= $aiContent . "\n\n";
        }

        // Add project statistics
        $content .= $this->generateProjectStatistics($analysis);

        return [
            'title' => 'Project Overview',
            'content' => $content,
            'data' => $projectInfo,
        ];
    }

    protected function generateApiDocumentation($analysis)
    {
        $content = "# API Documentation\n\n";

        if (!empty($analysis['api_endpoints'])) {
            $content .= "## Endpoints\n\n";
            
            foreach ($analysis['api_endpoints'] as $endpoint) {
                $content .= "### {$endpoint['method']} {$endpoint['uri']}\n\n";
                $content .= "**Handler:** {$endpoint['handler']}\n\n";
                
                if (!empty($endpoint['middleware'])) {
                    $content .= "**Middleware:** " . implode(', ', $endpoint['middleware']) . "\n\n";
                }
                
                $content .= "---\n\n";
            }
        }

        // Generate AI-powered API documentation
        $apiAnalysis = [
            'project_info' => $analysis['project_info'],
            'api_endpoints' => $analysis['api_endpoints'],
            'controllers' => $this->filterApiControllers($analysis['controllers']),
        ];

        $aiContent = $this->aiService->generateDocumentation($apiAnalysis, 'api');
        
        if ($aiContent) {
            $content .= $aiContent . "\n\n";
        }

        return [
            'title' => 'API Documentation',
            'content' => $content,
            'data' => $analysis['api_endpoints'],
        ];
    }

    protected function generateDatabaseDocumentation($analysis)
    {
        $content = "# Database Documentation\n\n";

        // Models
        if (!empty($analysis['models'])) {
            $content .= "## Models\n\n";
            
            foreach ($analysis['models'] as $model) {
                $content .= "### {$model['short_name']}\n\n";
                $content .= "**Table:** {$model['table']}\n\n";
                
                if (!empty($model['fillable'])) {
                    $content .= "**Fillable Fields:** " . implode(', ', $model['fillable']) . "\n\n";
                }
                
                if (!empty($model['casts'])) {
                    $content .= "**Casts:**\n";
                    foreach ($model['casts'] as $field => $cast) {
                        $content .= "- `{$field}`: {$cast}\n";
                    }
                    $content .= "\n";
                }
                
                if (!empty($model['relationships'])) {
                    $content .= "**Relationships:**\n";
                    foreach ($model['relationships'] as $relationship) {
                        $content .= "- {$relationship['type']}: {$relationship['method']}\n";
                    }
                    $content .= "\n";
                }
                
                $content .= "---\n\n";
            }
        }

        // Database Structure
        if (!empty($analysis['database_structure'])) {
            $content .= "## Database Structure\n\n";
            
            foreach ($analysis['database_structure'] as $tableName => $table) {
                $content .= "### Table: {$tableName}\n\n";
                
                if (!empty($table['columns'])) {
                    $content .= "**Columns:**\n";
                    foreach ($table['columns'] as $column) {
                        $column = (array) $column;
                        $content .= "- `{$column['Field']}`: {$column['Type']} " . 
                                  ($column['Null'] === 'NO' ? 'NOT NULL' : 'NULL') .
                                  ($column['Default'] ? " DEFAULT {$column['Default']}" : '') . "\n";
                    }
                    $content .= "\n";
                }
                
                if (!empty($table['foreign_keys'])) {
                    $content .= "**Foreign Keys:**\n";
                    foreach ($table['foreign_keys'] as $fk) {
                        $fk = (array) $fk;
                        $content .= "- `{$fk['COLUMN_NAME']}` → `{$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}`\n";
                    }
                    $content .= "\n";
                }
                
                $content .= "---\n\n";
            }
        }

        // Generate AI-powered database documentation
        $dbAnalysis = [
            'project_info' => $analysis['project_info'],
            'models' => $analysis['models'],
            'database_structure' => $analysis['database_structure'],
            'migrations' => $analysis['migrations'],
        ];

        $aiContent = $this->aiService->generateDocumentation($dbAnalysis, 'database');
        
        if ($aiContent) {
            $content .= $aiContent . "\n\n";
        }

        return [
            'title' => 'Database Documentation',
            'content' => $content,
            'data' => [
                'models' => $analysis['models'],
                'structure' => $analysis['database_structure'],
                'migrations' => $analysis['migrations'],
            ],
        ];
    }

    protected function extractModuleData($analysis, $moduleKey)
    {
        $data = [];

        switch ($moduleKey) {
            case 'authentication':
                $data = [
                    'authentication' => $analysis['authentication'],
                    'policies' => $analysis['policies'],
                    'gates' => $analysis['gates'],
                    'middleware' => $this->filterAuthMiddleware($analysis['middleware']),
                ];
                break;

            case 'api':
                $data = [
                    'api_endpoints' => $analysis['api_endpoints'],
                    'controllers' => $this->filterApiControllers($analysis['controllers']),
                ];
                break;

            case 'database':
                $data = [
                    'models' => $analysis['models'],
                    'database_structure' => $analysis['database_structure'],
                    'migrations' => $analysis['migrations'],
                ];
                break;

            case 'frontend':
                $data = [
                    'views' => $analysis['views'],
                    'routes' => $this->filterWebRoutes($analysis['routes']),
                ];
                break;

            case 'business_logic':
                $data = [
                    'controllers' => $analysis['controllers'],
                    'providers' => $analysis['providers'],
                    'validation_rules' => $analysis['validation_rules'],
                ];
                break;
        }

        return $data;
    }

    protected function generateStructuredModuleContent($moduleData, $moduleKey)
    {
        $content = "## Structured Data\n\n";

        switch ($moduleKey) {
            case 'authentication':
                $content .= $this->generateAuthStructuredContent($moduleData);
                break;
            case 'api':
                $content .= $this->generateApiStructuredContent($moduleData);
                break;
            case 'database':
                $content .= $this->generateDatabaseStructuredContent($moduleData);
                break;
            case 'frontend':
                $content .= $this->generateFrontendStructuredContent($moduleData);
                break;
            case 'business_logic':
                $content .= $this->generateBusinessLogicStructuredContent($moduleData);
                break;
        }

        return $content;
    }

    protected function generateProjectStatistics($analysis)
    {
        $content = "## Project Statistics\n\n";
        
        $content .= "- **Controllers:** " . count($analysis['controllers']) . "\n";
        $content .= "- **Models:** " . count($analysis['models']) . "\n";
        $content .= "- **Views:** " . count($analysis['views']) . "\n";
        $content .= "- **API Endpoints:** " . count($analysis['api_endpoints']) . "\n";
        $content .= "- **Database Tables:** " . count($analysis['database_structure']) . "\n";
        $content .= "- **Migrations:** " . count($analysis['migrations']) . "\n";
        $content .= "- **Policies:** " . count($analysis['policies']) . "\n";
        $content .= "- **Gates:** " . count($analysis['gates']) . "\n";
        $content .= "- **Validation Rules:** " . count($analysis['validation_rules']) . "\n\n";

        return $content;
    }

    protected function filterApiControllers($controllers)
    {
        return array_filter($controllers, function ($controller) {
            return Str::contains(strtolower($controller['name']), 'api') ||
                   Str::contains(strtolower($controller['namespace']), 'api');
        });
    }

    protected function filterAuthMiddleware($middleware)
    {
        return array_filter($middleware, function ($mw) {
            return Str::contains(strtolower($mw['name']), 'auth') ||
                   Str::contains(strtolower($mw['short_name']), 'auth');
        });
    }

    protected function filterWebRoutes($routes)
    {
        return isset($routes['web.php']) ? $routes['web.php'] : [];
    }

    protected function generateAuthStructuredContent($data)
    {
        $content = "### Authentication Configuration\n\n";
        
        if (!empty($data['authentication'])) {
            $auth = $data['authentication'];
            $content .= "**Guards:** " . implode(', ', array_keys($auth['guards'] ?? [])) . "\n\n";
            $content .= "**Providers:** " . implode(', ', array_keys($auth['providers'] ?? [])) . "\n\n";
        }

        if (!empty($data['policies'])) {
            $content .= "### Policies\n\n";
            foreach ($data['policies'] as $policy) {
                $content .= "- **{$policy['short_name']}**\n";
                $content .= "  - Methods: " . implode(', ', $policy['methods']) . "\n\n";
            }
        }

        if (!empty($data['gates'])) {
            $content .= "### Gates\n\n";
            foreach ($data['gates'] as $gate) {
                $content .= "- `{$gate}`\n";
            }
            $content .= "\n";
        }

        return $content;
    }

    protected function generateApiStructuredContent($data)
    {
        $content = "### API Endpoints\n\n";
        
        if (!empty($data['api_endpoints'])) {
            foreach ($data['api_endpoints'] as $endpoint) {
                $content .= "- **{$endpoint['method']}** `{$endpoint['uri']}`\n";
                $content .= "  - Handler: {$endpoint['handler']}\n\n";
            }
        }

        return $content;
    }

    protected function generateDatabaseStructuredContent($data)
    {
        $content = "### Models\n\n";
        
        if (!empty($data['models'])) {
            foreach ($data['models'] as $model) {
                $content .= "- **{$model['short_name']}** (Table: {$model['table']})\n";
                if (!empty($model['fillable'])) {
                    $content .= "  - Fillable: " . implode(', ', $model['fillable']) . "\n";
                }
                $content .= "\n";
            }
        }

        return $content;
    }

    protected function generateFrontendStructuredContent($data)
    {
        $content = "### Views\n\n";
        
        if (!empty($data['views'])) {
            foreach ($data['views'] as $view) {
                $content .= "- **{$view['name']}**\n";
                $content .= "  - Size: {$view['size']} bytes\n";
                if (!empty($view['components'])) {
                    $content .= "  - Components: " . implode(', ', $view['components']) . "\n";
                }
                $content .= "\n";
            }
        }

        return $content;
    }

    protected function generateBusinessLogicStructuredContent($data)
    {
        $content = "### Controllers\n\n";
        
        if (!empty($data['controllers'])) {
            foreach ($data['controllers'] as $controller) {
                $content .= "- **{$controller['short_name']}**\n";
                $content .= "  - Methods: " . implode(', ', array_column($controller['methods'], 'name')) . "\n\n";
            }
        }

        if (!empty($data['validation_rules'])) {
            $content .= "### Validation Rules\n\n";
            foreach ($data['validation_rules'] as $rule) {
                $content .= "- **{$rule['short_name']}**\n";
                if (!empty($rule['rules'])) {
                    $content .= "  - Rules: " . json_encode($rule['rules']) . "\n";
                }
                $content .= "\n";
            }
        }

        return $content;
    }

    protected function saveDocumentation($documentation)
    {
        $outputPath = $this->config['documentation']['output_path'];
        
        if (!File::exists($outputPath)) {
            File::makeDirectory($outputPath, 0755, true);
        }

        // Save main documentation file
        $mainContent = "# Laravel Project Documentation\n\n";
        $mainContent .= "Generated on: " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        foreach ($documentation as $key => $doc) {
            $mainContent .= "## [{$doc['title']}]({$key}.md)\n\n";
            $description = isset($doc['description']) ? $doc['description'] : '';
            $mainContent .= "{$description}\n\n";
        }

        File::put($outputPath . '/README.md', $mainContent);

        // Save individual module files
        foreach ($documentation as $key => $doc) {
            File::put($outputPath . "/{$key}.md", $doc['content']);
        }

        // Save JSON data for search indexing
        File::put($outputPath . '/data.json', json_encode($documentation, JSON_PRETTY_PRINT));
    }

    public function getDocumentation($module = null)
    {
        $outputPath = $this->config['documentation']['output_path'];
        
        if ($module) {
            $filePath = $outputPath . "/{$module}.md";
            if (File::exists($filePath)) {
                return [
                    'content' => File::get($filePath),
                    'module' => $module,
                ];
            }
        } else {
            $filePath = $outputPath . '/README.md';
            if (File::exists($filePath)) {
                return [
                    'content' => File::get($filePath),
                    'module' => 'overview',
                ];
            }
        }

        return null;
    }

    public function updateDocumentation($module, $content)
    {
        $outputPath = $this->config['documentation']['output_path'];
        $filePath = $outputPath . "/{$module}.md";
        
        File::put($filePath, $content);
        
        return [
            'success' => true,
            'module' => $module,
            'message' => 'Documentation updated successfully',
        ];
    }
} 