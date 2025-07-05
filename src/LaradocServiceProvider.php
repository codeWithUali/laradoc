<?php

namespace Laradoc\Laradoc;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Laradoc\Laradoc\Console\GenerateDocumentationCommand;
use Laradoc\Laradoc\Console\AnalyzeProjectCommand;
use Laradoc\Laradoc\Services\ProjectAnalyzer;
use Laradoc\Laradoc\Services\DocumentationGenerator;
use Laradoc\Laradoc\Services\AIService;
use Laradoc\Laradoc\Services\SearchService;

class LaradocServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laradoc.php', 'laradoc');

        $this->app->singleton(ProjectAnalyzer::class);
        $this->app->singleton(DocumentationGenerator::class);
        $this->app->singleton(AIService::class);
        $this->app->singleton(SearchService::class);
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laradoc');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laradoc.php' => config_path('laradoc.php'),
                __DIR__ . '/../resources/views' => resource_path('views/vendor/laradoc'),
                __DIR__ . '/../public' => public_path('vendor/laradoc'),
            ], 'laradoc');

            $this->commands([
                GenerateDocumentationCommand::class,
                AnalyzeProjectCommand::class,
            ]);
        }

        // Register Livewire components
        $this->app->make('livewire')->component('laradoc-documentation', \Laradoc\Laradoc\Livewire\DocumentationComponent::class);
        $this->app->make('livewire')->component('laradoc-chatbot', \Laradoc\Laradoc\Livewire\ChatbotComponent::class);
        $this->app->make('livewire')->component('laradoc-search', \Laradoc\Laradoc\Livewire\SearchComponent::class);
    }
} 