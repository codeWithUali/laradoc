<?php

namespace Laradoc\Laradoc\Tests;

use Orchestra\Testbench\TestCase;
use Laradoc\Laradoc\LaradocServiceProvider;
use Laradoc\Laradoc\Services\ProjectAnalyzer;
use Laradoc\Laradoc\Services\AIService;
use Laradoc\Laradoc\Services\DocumentationGenerator;
use Laradoc\Laradoc\Services\SearchService;

class LaradocTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaradocServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('laradoc.ai_provider', 'openai');
        $app['config']->set('laradoc.search.driver', 'database');
    }

    public function test_service_provider_registers_services()
    {
        $this->assertInstanceOf(ProjectAnalyzer::class, app(ProjectAnalyzer::class));
        $this->assertInstanceOf(AIService::class, app(AIService::class));
        $this->assertInstanceOf(DocumentationGenerator::class, app(DocumentationGenerator::class));
        $this->assertInstanceOf(SearchService::class, app(SearchService::class));
    }

    public function test_project_analyzer_can_analyze_project()
    {
        $analyzer = app(ProjectAnalyzer::class);
        $analysis = $analyzer->analyzeProject();

        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('project_info', $analysis);
        $this->assertArrayHasKey('routes', $analysis);
        $this->assertArrayHasKey('controllers', $analysis);
        $this->assertArrayHasKey('models', $analysis);
    }

    public function test_config_is_loaded()
    {
        $this->assertEquals('openai', config('laradoc.ai_provider'));
        $this->assertEquals('database', config('laradoc.search.driver'));
    }
} 