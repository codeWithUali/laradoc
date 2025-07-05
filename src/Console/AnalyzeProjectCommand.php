<?php

namespace Laradoc\Laradoc\Console;

use Illuminate\Console\Command;
use Laradoc\Laradoc\Services\ProjectAnalyzer;

class AnalyzeProjectCommand extends Command
{
    protected $signature = 'laradoc:analyze 
                            {--output=json : Output format (json, table, summary)}
                            {--save : Save analysis to file}';

    protected $description = 'Analyze Laravel project structure and components';

    protected $projectAnalyzer;

    public function __construct(ProjectAnalyzer $projectAnalyzer)
    {
        parent::__construct();
        $this->projectAnalyzer = $projectAnalyzer;
    }

    public function handle()
    {
        $this->info('🔍 Analyzing Laravel project structure...');

        try {
            $analysis = $this->projectAnalyzer->analyzeProject();
            $output = $this->option('output');
            $save = $this->option('save');

            switch ($output) {
                case 'json':
                    $this->outputJson($analysis);
                    break;
                case 'table':
                    $this->outputTable($analysis);
                    break;
                case 'summary':
                default:
                    $this->outputSummary($analysis);
                    break;
            }

            if ($save) {
                $this->saveAnalysis($analysis);
            }

            $this->info('✅ Project analysis completed successfully!');

        } catch (\Exception $e) {
            $this->error('❌ Project analysis failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    protected function outputJson($analysis)
    {
        $this->line(json_encode($analysis, JSON_PRETTY_PRINT));
    }

    protected function outputTable($analysis)
    {
        $this->info('📊 Project Overview');
        $this->table(
            ['Property', 'Value'],
            [
                ['Name', $analysis['project_info']['name']],
                ['Description', $analysis['project_info']['description']],
                ['Laravel Version', $analysis['project_info']['laravel_version']],
                ['PHP Version', $analysis['project_info']['php_version']],
                ['Environment', $analysis['project_info']['environment']],
            ]
        );

        $this->info('📁 Project Structure');
        $this->table(
            ['Component', 'Count'],
            [
                ['Controllers', count($analysis['controllers'])],
                ['Models', count($analysis['models'])],
                ['Views', count($analysis['views'])],
                ['API Endpoints', count($analysis['api_endpoints'])],
                ['Database Tables', count($analysis['database_structure'])],
                ['Migrations', count($analysis['migrations'])],
                ['Policies', count($analysis['policies'])],
                ['Gates', count($analysis['gates'])],
                ['Validation Rules', count($analysis['validation_rules'])],
                ['Middleware', count($analysis['middleware'])],
                ['Providers', count($analysis['providers'])],
            ]
        );

        if (!empty($analysis['controllers'])) {
            $this->info('🎮 Controllers');
            $this->table(
                ['Controller', 'Methods', 'Namespace'],
                collect($analysis['controllers'])->take(10)->map(function ($controller) {
                    return [
                        $controller['short_name'],
                        count($controller['methods']),
                        $controller['namespace'],
                    ];
                })->toArray()
            );
        }

        if (!empty($analysis['models'])) {
            $this->info('🗄️  Models');
            $this->table(
                ['Model', 'Table', 'Fillable Fields'],
                collect($analysis['models'])->take(10)->map(function ($model) {
                    return [
                        $model['short_name'],
                        $model['table'] ?? 'N/A',
                        count($model['fillable'] ?? []),
                    ];
                })->toArray()
            );
        }

        if (!empty($analysis['api_endpoints'])) {
            $this->info('🔌 API Endpoints');
            $this->table(
                ['Method', 'URI', 'Handler'],
                collect($analysis['api_endpoints'])->take(10)->map(function ($endpoint) {
                    return [
                        $endpoint['method'],
                        $endpoint['uri'],
                        $endpoint['handler'],
                    ];
                })->toArray()
            );
        }
    }

    protected function outputSummary($analysis)
    {
        $this->info('📋 Project Analysis Summary');
        $this->line('');

        $this->line("🏷️  <info>Project:</info> {$analysis['project_info']['name']}");
        $this->line("📝 <info>Description:</info> {$analysis['project_info']['description']}");
        $this->line("🔄 <info>Laravel Version:</info> {$analysis['project_info']['laravel_version']}");
        $this->line("🐘 <info>PHP Version:</info> {$analysis['project_info']['php_version']}");
        $this->line("🌍 <info>Environment:</info> {$analysis['project_info']['environment']}");
        $this->line('');

        $this->line('📊 <info>Project Statistics:</info>');
        $this->line("  • Controllers: " . count($analysis['controllers']));
        $this->line("  • Models: " . count($analysis['models']));
        $this->line("  • Views: " . count($analysis['views']));
        $this->line("  • API Endpoints: " . count($analysis['api_endpoints']));
        $this->line("  • Database Tables: " . count($analysis['database_structure']));
        $this->line("  • Migrations: " . count($analysis['migrations']));
        $this->line("  • Policies: " . count($analysis['policies']));
        $this->line("  • Gates: " . count($analysis['gates']));
        $this->line("  • Validation Rules: " . count($analysis['validation_rules']));
        $this->line("  • Middleware: " . count($analysis['middleware']));
        $this->line("  • Providers: " . count($analysis['providers']));
        $this->line('');

        if (!empty($analysis['modules'])) {
            $this->line('📦 <info>Identified Modules:</info>');
            foreach ($analysis['modules'] as $module => $data) {
                $this->line("  • {$module}: " . count($data['routes']) . " routes");
            }
            $this->line('');
        }

        if (!empty($analysis['database_structure'])) {
            $this->line('🗄️  <info>Database Tables:</info>');
            foreach (array_keys($analysis['database_structure']) as $table) {
                $this->line("  • {$table}");
            }
            $this->line('');
        }

        if (!empty($analysis['api_endpoints'])) {
            $this->line('🔌 <info>API Endpoints (first 5):</info>');
            foreach (array_slice($analysis['api_endpoints'], 0, 5) as $endpoint) {
                $this->line("  • {$endpoint['method']} {$endpoint['uri']}");
            }
            $this->line('');
        }
    }

    protected function saveAnalysis($analysis)
    {
        $outputPath = storage_path('app/laradoc');
        
        if (!file_exists($outputPath)) {
            mkdir($outputPath, 0755, true);
        }

        $filename = $outputPath . '/project-analysis-' . date('Y-m-d-H-i-s') . '.json';
        file_put_contents($filename, json_encode($analysis, JSON_PRETTY_PRINT));

        $this->info("💾 Analysis saved to: {$filename}");
    }
} 