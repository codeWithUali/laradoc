<?php

namespace Laradoc\Laradoc\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class ProjectAnalyzer
{
    protected $basePath;
    protected $config;

    public function __construct()
    {
        $this->basePath = base_path();
        $this->config = config('laradoc');
    }

    public function analyzeProject()
    {
        $analysis = [
            'project_info' => $this->getProjectInfo(),
            'routes' => $this->analyzeRoutes(),
            'controllers' => $this->analyzeControllers(),
            'models' => $this->analyzeModels(),
            'migrations' => $this->analyzeMigrations(),
            'views' => $this->analyzeViews(),
            'middleware' => $this->analyzeMiddleware(),
            'providers' => $this->analyzeProviders(),
            'policies' => $this->analyzePolicies(),
            'gates' => $this->analyzeGates(),
            'validation_rules' => $this->analyzeValidationRules(),
            'database_structure' => $this->analyzeDatabaseStructure(),
            'api_endpoints' => $this->analyzeApiEndpoints(),
            'authentication' => $this->analyzeAuthentication(),
            'modules' => $this->identifyModules(),
        ];

        return $analysis;
    }

    protected function getProjectInfo()
    {
        $composerJson = json_decode(File::get($this->basePath . '/composer.json'), true);
        
        return [
            'name' => $composerJson['name'] ?? 'Laravel Application',
            'description' => $composerJson['description'] ?? '',
            'version' => $composerJson['version'] ?? '1.0.0',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => app()->environment(),
        ];
    }

    protected function analyzeRoutes()
    {
        $routes = [];
        $routeFiles = ['web.php', 'api.php', 'console.php', 'channels.php'];

        foreach ($routeFiles as $file) {
            $path = base_path("routes/{$file}");
            if (File::exists($path)) {
                $routes[$file] = $this->parseRouteFile($path);
            }
        }

        return $routes;
    }

    protected function parseRouteFile($path)
    {
        $content = File::get($path);
        $routes = [];

        // Simple regex to extract route definitions
        preg_match_all('/Route::(get|post|put|patch|delete|any)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,?\s*([^)]+)\)/i', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $routes[] = [
                'method' => strtoupper($match[1]),
                'uri' => $match[2],
                'handler' => trim($match[3]),
                'middleware' => $this->extractMiddleware($match[3]),
            ];
        }

        return $routes;
    }

    protected function analyzeControllers()
    {
        $controllers = [];
        $controllerPath = app_path('Http/Controllers');
        
        if (File::exists($controllerPath)) {
            $files = File::allFiles($controllerPath);
            
            foreach ($files as $file) {
                $className = 'App\\Http\\Controllers\\' . str_replace('/', '\\', $file->getRelativePathname());
                $className = str_replace('.php', '', $className);
                
                if (class_exists($className)) {
                    $controllers[] = $this->analyzeController($className);
                }
            }
        }

        return $controllers;
    }

    protected function analyzeController($className)
    {
        $reflection = new ReflectionClass($className);
        $methods = [];
        
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class === $className && !$method->isConstructor()) {
                $methods[] = [
                    'name' => $method->getName(),
                    'parameters' => $this->getMethodParameters($method),
                    'doc_comment' => $method->getDocComment(),
                    'return_type' => $method->getReturnType() ? $method->getReturnType()->getName() : null,
                ];
            }
        }

        return [
            'name' => $className,
            'short_name' => class_basename($className),
            'namespace' => $reflection->getNamespaceName(),
            'methods' => $methods,
            'doc_comment' => $reflection->getDocComment(),
        ];
    }

    protected function analyzeModels()
    {
        $models = [];
        $modelPath = app_path('Models');
        
        if (File::exists($modelPath)) {
            $files = File::allFiles($modelPath);
            
            foreach ($files as $file) {
                $className = 'App\\Models\\' . str_replace('/', '\\', $file->getRelativePathname());
                $className = str_replace('.php', '', $className);
                
                if (class_exists($className)) {
                    $models[] = $this->analyzeModel($className);
                }
            }
        }

        return $models;
    }

    protected function analyzeModel($className)
    {
        $reflection = new ReflectionClass($className);
        $properties = [];
        
        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $properties[] = [
                'name' => $property->getName(),
                'type' => $this->getPropertyType($property),
                'doc_comment' => $property->getDocComment(),
            ];
        }

        return [
            'name' => $className,
            'short_name' => class_basename($className),
            'table' => $this->getModelTable($className),
            'fillable' => $this->getModelFillable($className),
            'hidden' => $this->getModelHidden($className),
            'casts' => $this->getModelCasts($className),
            'relationships' => $this->getModelRelationships($className),
            'properties' => $properties,
        ];
    }

    protected function analyzeMigrations()
    {
        $migrations = [];
        $migrationPath = database_path('migrations');
        
        if (File::exists($migrationPath)) {
            $files = File::allFiles($migrationPath);
            
            foreach ($files as $file) {
                $migrations[] = $this->analyzeMigration($file);
            }
        }

        return $migrations;
    }

    protected function analyzeMigration($file)
    {
        $content = File::get($file->getPathname());
        $className = $this->extractMigrationClassName($content);
        
        return [
            'file' => $file->getFilename(),
            'class' => $className,
            'table' => $this->extractMigrationTable($content),
            'operations' => $this->extractMigrationOperations($content),
            'timestamp' => $this->extractMigrationTimestamp($file->getFilename()),
        ];
    }

    protected function analyzeViews()
    {
        $views = [];
        $viewPath = resource_path('views');
        
        if (File::exists($viewPath)) {
            $files = File::allFiles($viewPath);
            
            foreach ($files as $file) {
                if ($file->getExtension() === 'blade.php') {
                    $views[] = [
                        'name' => $file->getRelativePathname(),
                        'path' => $file->getPathname(),
                        'size' => $file->getSize(),
                        'components' => $this->extractViewComponents($file->getPathname()),
                    ];
                }
            }
        }

        return $views;
    }

    protected function analyzeMiddleware()
    {
        $middleware = [];
        $middlewarePath = app_path('Http/Middleware');
        
        if (File::exists($middlewarePath)) {
            $files = File::allFiles($middlewarePath);
            
            foreach ($files as $file) {
                $className = 'App\\Http\\Middleware\\' . str_replace('/', '\\', $file->getRelativePathname());
                $className = str_replace('.php', '', $className);
                
                if (class_exists($className)) {
                    $middleware[] = [
                        'name' => $className,
                        'short_name' => class_basename($className),
                        'alias' => $this->getMiddlewareAlias($className),
                    ];
                }
            }
        }

        return $middleware;
    }

    protected function analyzeProviders()
    {
        $providers = [];
        $providerPath = app_path('Providers');
        
        if (File::exists($providerPath)) {
            $files = File::allFiles($providerPath);
            
            foreach ($files as $file) {
                $className = 'App\\Providers\\' . str_replace('/', '\\', $file->getRelativePathname());
                $className = str_replace('.php', '', $className);
                
                if (class_exists($className)) {
                    $providers[] = [
                        'name' => $className,
                        'short_name' => class_basename($className),
                        'methods' => $this->getProviderMethods($className),
                    ];
                }
            }
        }

        return $providers;
    }

    protected function analyzePolicies()
    {
        $policies = [];
        $policyPath = app_path('Policies');
        
        if (File::exists($policyPath)) {
            $files = File::allFiles($policyPath);
            
            foreach ($files as $file) {
                $className = 'App\\Policies\\' . str_replace('/', '\\', $file->getRelativePathname());
                $className = str_replace('.php', '', $className);
                
                if (class_exists($className)) {
                    $policies[] = [
                        'name' => $className,
                        'short_name' => class_basename($className),
                        'methods' => $this->getPolicyMethods($className),
                    ];
                }
            }
        }

        return $policies;
    }

    protected function analyzeGates()
    {
        $gates = [];
        $authServiceProvider = app_path('Providers/AuthServiceProvider.php');
        
        if (File::exists($authServiceProvider)) {
            $content = File::get($authServiceProvider);
            $gates = $this->extractGates($content);
        }

        return $gates;
    }

    protected function analyzeValidationRules()
    {
        $rules = [];
        $requestPath = app_path('Http/Requests');
        
        if (File::exists($requestPath)) {
            $files = File::allFiles($requestPath);
            
            foreach ($files as $file) {
                $className = 'App\\Http\\Requests\\' . str_replace('/', '\\', $file->getRelativePathname());
                $className = str_replace('.php', '', $className);
                
                if (class_exists($className)) {
                    $rules[] = [
                        'name' => $className,
                        'short_name' => class_basename($className),
                        'rules' => $this->extractValidationRules($className),
                    ];
                }
            }
        }

        return $rules;
    }

    protected function analyzeDatabaseStructure()
    {
        $tables = [];
        
        try {
            $connection = \DB::connection();
            $tablesList = $connection->select('SHOW TABLES');
            
            foreach ($tablesList as $table) {
                $tableName = array_values((array) $table)[0];
                $tables[$tableName] = [
                    'name' => $tableName,
                    'columns' => $this->getTableColumns($tableName),
                    'indexes' => $this->getTableIndexes($tableName),
                    'foreign_keys' => $this->getTableForeignKeys($tableName),
                ];
            }
        } catch (\Exception $e) {
            // Database connection might not be available
        }

        return $tables;
    }

    protected function analyzeApiEndpoints()
    {
        $endpoints = [];
        $routes = $this->analyzeRoutes();
        
        if (isset($routes['api.php'])) {
            foreach ($routes['api.php'] as $route) {
                $endpoints[] = [
                    'method' => $route['method'],
                    'uri' => $route['uri'],
                    'handler' => $route['handler'],
                    'middleware' => $route['middleware'],
                ];
            }
        }

        return $endpoints;
    }

    protected function analyzeAuthentication()
    {
        $auth = [
            'guards' => config('auth.guards'),
            'providers' => config('auth.providers'),
            'passwords' => config('auth.passwords'),
            'defaults' => config('auth.defaults'),
        ];

        return $auth;
    }

    protected function identifyModules()
    {
        $modules = [];
        $routes = $this->analyzeRoutes();
        
        // Group routes by module based on URI patterns
        foreach ($routes as $file => $fileRoutes) {
            foreach ($fileRoutes as $route) {
                $module = $this->identifyModuleFromRoute($route['uri']);
                if (!isset($modules[$module])) {
                    $modules[$module] = [
                        'name' => $module,
                        'routes' => [],
                        'controllers' => [],
                    ];
                }
                $modules[$module]['routes'][] = $route;
            }
        }

        return $modules;
    }

    protected function identifyModuleFromRoute($uri)
    {
        $segments = explode('/', trim($uri, '/'));
        return $segments[0] ?? 'main';
    }

    // Helper methods
    protected function extractMiddleware($handler)
    {
        // Extract middleware from route handler
        return [];
    }

    protected function getMethodParameters($method)
    {
        $parameters = [];
        foreach ($method->getParameters() as $param) {
            $parameters[] = [
                'name' => $param->getName(),
                'type' => $param->getType() ? $param->getType()->getName() : null,
                'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
            ];
        }
        return $parameters;
    }

    protected function getPropertyType($property)
    {
        $docComment = $property->getDocComment();
        if (preg_match('/@var\s+([^\s]+)/', $docComment, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function getModelTable($className)
    {
        try {
            $model = new $className();
            return $model->getTable();
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getModelFillable($className)
    {
        try {
            $model = new $className();
            return $model->getFillable();
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getModelHidden($className)
    {
        try {
            $model = new $className();
            return $model->getHidden();
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getModelCasts($className)
    {
        try {
            $model = new $className();
            return $model->getCasts();
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getModelRelationships($className)
    {
        $relationships = [];
        $reflection = new ReflectionClass($className);
        
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class === $className && !$method->isConstructor()) {
                $methodName = $method->getName();
                if (in_array($methodName, ['hasOne', 'hasMany', 'belongsTo', 'belongsToMany', 'hasManyThrough'])) {
                    $relationships[] = [
                        'type' => $methodName,
                        'method' => $method->getName(),
                    ];
                }
            }
        }

        return $relationships;
    }

    protected function extractMigrationClassName($content)
    {
        if (preg_match('/class\s+(\w+)\s+extends\s+Migration/', $content, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function extractMigrationTable($content)
    {
        if (preg_match('/Schema::(create|table)\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
            return $matches[2];
        }
        return null;
    }

    protected function extractMigrationOperations($content)
    {
        $operations = [];
        $patterns = [
            'create' => '/Schema::create\s*\(\s*[\'"]([^\'"]+)[\'"]/',
            'table' => '/Schema::table\s*\(\s*[\'"]([^\'"]+)[\'"]/',
            'drop' => '/Schema::drop\s*\(\s*[\'"]([^\'"]+)[\'"]/',
        ];

        foreach ($patterns as $type => $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                $operations[$type] = $matches[1];
            }
        }

        return $operations;
    }

    protected function extractMigrationTimestamp($filename)
    {
        if (preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_/', $filename, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function extractViewComponents($path)
    {
        $content = File::get($path);
        $components = [];
        
        // Extract Blade components
        preg_match_all('/<x-([^>]+)>/', $content, $matches);
        foreach ($matches[1] as $component) {
            $components[] = $component;
        }

        return array_unique($components);
    }

    protected function getMiddlewareAlias($className)
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $middleware = $kernel->getMiddleware();
        
        foreach ($middleware as $alias => $class) {
            if ($class === $className) {
                return $alias;
            }
        }

        return null;
    }

    protected function getProviderMethods($className)
    {
        $reflection = new ReflectionClass($className);
        $methods = [];
        
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class === $className) {
                $methods[] = $method->getName();
            }
        }

        return $methods;
    }

    protected function getPolicyMethods($className)
    {
        $reflection = new ReflectionClass($className);
        $methods = [];
        
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class === $className && !$method->isConstructor()) {
                $methods[] = $method->getName();
            }
        }

        return $methods;
    }

    protected function extractGates($content)
    {
        $gates = [];
        preg_match_all('/Gate::define\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches);
        
        foreach ($matches[1] as $gate) {
            $gates[] = $gate;
        }

        return $gates;
    }

    protected function extractValidationRules($className)
    {
        try {
            $reflection = new ReflectionClass($className);
            $rulesMethod = $reflection->getMethod('rules');
            $rules = $rulesMethod->invoke(new $className());
            return $rules;
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getTableColumns($tableName)
    {
        try {
            $columns = \DB::select("DESCRIBE {$tableName}");
            return $columns;
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getTableIndexes($tableName)
    {
        try {
            $indexes = \DB::select("SHOW INDEX FROM {$tableName}");
            return $indexes;
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getTableForeignKeys($tableName)
    {
        try {
            $foreignKeys = \DB::select("
                SELECT 
                    COLUMN_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$tableName]);
            return $foreignKeys;
        } catch (\Exception $e) {
            return [];
        }
    }
} 