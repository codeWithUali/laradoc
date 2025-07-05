<?php

namespace Laradoc\Laradoc\Console;

use Illuminate\Console\Command;
use Laradoc\Laradoc\Services\DocumentationGenerator;
use Laradoc\Laradoc\Services\SearchService;

class GenerateDocumentationCommand extends Command
{
    protected $signature = 'laradoc:generate 
                            {--module= : Generate documentation for specific module only}
                            {--force : Force regeneration of existing documentation}
                            {--no-ai : Skip AI-powered documentation generation}';

    protected $description = 'Generate comprehensive documentation for Laravel project';

    protected $documentationGenerator;
    protected $searchService;

    public function __construct(DocumentationGenerator $documentationGenerator, SearchService $searchService)
    {
        parent::__construct();
        $this->documentationGenerator = $documentationGenerator;
        $this->searchService = $searchService;
    }

    public function handle()
    {
        $this->info('🚀 Starting Laradoc documentation generation...');

        try {
            $module = $this->option('module');
            $force = $this->option('force');
            $noAi = $this->option('no-ai');

            if ($noAi) {
                $this->warn('⚠️  AI-powered documentation generation is disabled');
            }

            $this->info('📊 Analyzing project structure...');
            
            if ($module) {
                $this->info("📝 Generating documentation for module: {$module}");
                $documentation = $this->documentationGenerator->generateModuleDocumentation(
                    $this->documentationGenerator->getProjectAnalyzer()->analyzeProject(),
                    $module,
                    config("laradoc.documentation.modules.{$module}")
                );
            } else {
                $this->info('📝 Generating complete documentation...');
                $documentation = $this->documentationGenerator->generateCompleteDocumentation();
            }

            $this->info('🔍 Indexing documentation for search...');
            $searchResult = $this->searchService->indexDocumentation($documentation);

            if ($searchResult['success']) {
                $this->info("✅ Documentation indexed successfully ({$searchResult['indexed']} documents)");
            } else {
                $this->warn("⚠️  Search indexing failed: {$searchResult['error']}");
            }

            $this->info('📁 Documentation saved to: ' . config('laradoc.documentation.output_path'));

            $this->table(
                ['Module', 'Title', 'Status'],
                collect($documentation)->map(function ($doc, $key) {
                    return [$key, $doc['title'], '✅ Generated'];
                })->toArray()
            );

            $this->info('🎉 Documentation generation completed successfully!');
            $this->info('🌐 Access your documentation at: /' . config('laradoc.route_prefix'));

        } catch (\Exception $e) {
            $this->error('❌ Documentation generation failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
} 