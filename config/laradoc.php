<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laradoc Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Laradoc package.
    |
    */

    'route_prefix' => env('LARADOC_ROUTE_PREFIX', 'laradoc'),
    'route_middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | AI Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your preferred AI provider for documentation generation
    | and chatbot functionality.
    |
    */

    'ai_provider' => env('LARADOC_AI_PROVIDER', 'openai'), // openai, claude, gemini

    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        ],
        'claude' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('CLAUDE_MODEL', 'claude-3-sonnet-20240229'),
            'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
        ],
        'gemini' => [
            'api_key' => env('GOOGLE_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-pro'),
            'base_url' => env('GOOGLE_BASE_URL', 'https://generativelanguage.googleapis.com'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    |
    | Configure search functionality for documentation.
    |
    */

    'search' => [
        'driver' => env('LARADOC_SEARCH_DRIVER', 'meilisearch'), // meilisearch, database
        'meilisearch' => [
            'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
            'key' => env('MEILISEARCH_KEY'),
            'index' => env('MEILISEARCH_INDEX', 'laradoc'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Documentation Configuration
    |--------------------------------------------------------------------------
    |
    | Configure documentation generation settings.
    |
    */

    'documentation' => [
        'output_path' => storage_path('app/laradoc'),
        'include_patterns' => [
            'app/Http/Controllers/**/*.php',
            'app/Models/**/*.php',
            'app/Services/**/*.php',
            'app/Providers/**/*.php',
            'routes/**/*.php',
            'database/migrations/**/*.php',
            'resources/views/**/*.blade.php',
            'app/Http/Requests/**/*.php',
            'app/Policies/**/*.php',
            'app/Gates/**/*.php',
        ],
        'exclude_patterns' => [
            'vendor/**',
            'node_modules/**',
            'storage/**',
            'bootstrap/cache/**',
        ],
        'modules' => [
            'authentication' => [
                'title' => 'Authentication & Authorization',
                'description' => 'User authentication, authorization, and security features',
            ],
            'api' => [
                'title' => 'API Documentation',
                'description' => 'API endpoints, requests, and responses',
            ],
            'database' => [
                'title' => 'Database & Models',
                'description' => 'Database structure, models, and relationships',
            ],
            'frontend' => [
                'title' => 'Frontend & Views',
                'description' => 'User interface, views, and frontend components',
            ],
            'business_logic' => [
                'title' => 'Business Logic',
                'description' => 'Services, business rules, and application logic',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Chatbot Configuration
    |--------------------------------------------------------------------------
    |
    | Configure chatbot behavior and responses.
    |
    */

    'chatbot' => [
        'max_context_length' => 4000,
        'temperature' => 0.7,
        'max_tokens' => 1000,
        'system_prompt' => 'You are a helpful assistant for a Laravel application. You have access to the complete project documentation and can help users understand the codebase, find specific functionality, and answer questions about the application structure.',
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the appearance and behavior of the documentation interface.
    |
    */

    'ui' => [
        'theme' => 'bootstrap5',
        'sidebar_collapsible' => true,
        'enable_edit_mode' => true,
        'enable_search' => true,
        'enable_chatbot' => true,
        'enable_export' => true,
    ],
]; 