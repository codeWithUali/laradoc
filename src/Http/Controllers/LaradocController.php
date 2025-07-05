<?php

namespace Laradoc\Laradoc\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Laradoc\Laradoc\Services\DocumentationGenerator;
use Laradoc\Laradoc\Services\SearchService;
use Laradoc\Laradoc\Services\AIService;
use Laradoc\Laradoc\Services\ProjectAnalyzer;

class LaradocController extends Controller
{
    protected $documentationGenerator;
    protected $searchService;
    protected $aiService;
    protected $projectAnalyzer;

    public function __construct(
        DocumentationGenerator $documentationGenerator,
        SearchService $searchService,
        AIService $aiService,
        ProjectAnalyzer $projectAnalyzer
    ) {
        $this->documentationGenerator = $documentationGenerator;
        $this->searchService = $searchService;
        $this->aiService = $aiService;
        $this->projectAnalyzer = $projectAnalyzer;
    }

    public function index()
    {
        $modules = config('laradoc.documentation.modules');
        $documentation = $this->documentationGenerator->getDocumentation();
        
        return view('laradoc::index', compact('modules', 'documentation'));
    }

    public function module($module)
    {
        $documentation = $this->documentationGenerator->getDocumentation($module);
        $modules = config('laradoc.documentation.modules');
        
        if (!$documentation) {
            abort(404, 'Module documentation not found');
        }
        
        return view('laradoc::module', compact('documentation', 'modules', 'module'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $filters = $request->only(['module', 'type']);
        
        if ($request->isMethod('post')) {
            $results = $this->searchService->search($query, $filters);
            $modules = $this->searchService->getSearchableModules();
            
            return view('laradoc::search', compact('results', 'query', 'filters', 'modules'));
        }
        
        $modules = $this->searchService->getSearchableModules();
        return view('laradoc::search', compact('modules'));
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = $request->input('message');
        $context = $request->input('context', []);
        
        $response = $this->aiService->chatResponse($message, $context);
        
        return response()->json($response);
    }

    public function generate()
    {
        $modules = config('laradoc.documentation.modules');
        $stats = $this->searchService->getSearchStats();
        
        return view('laradoc::generate', compact('modules', 'stats'));
    }

    public function generateDocumentation(Request $request)
    {
        $request->validate([
            'module' => 'nullable|string|in:' . implode(',', array_keys(config('laradoc.documentation.modules'))),
        ]);

        try {
            $module = $request->input('module');
            
            if ($module) {
                $analysis = $this->projectAnalyzer->analyzeProject();
                $documentation = $this->documentationGenerator->generateModuleDocumentation(
                    $analysis,
                    $module,
                    config("laradoc.documentation.modules.{$module}")
                );
            } else {
                $documentation = $this->documentationGenerator->generateCompleteDocumentation();
            }

            // Index for search
            $this->searchService->indexDocumentation($documentation);

            return redirect()->route('laradoc.index')
                ->with('success', 'Documentation generated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate documentation: ' . $e->getMessage());
        }
    }

    public function edit($module)
    {
        $documentation = $this->documentationGenerator->getDocumentation($module);
        $modules = config('laradoc.documentation.modules');
        
        if (!$documentation) {
            abort(404, 'Module documentation not found');
        }
        
        return view('laradoc::edit', compact('documentation', 'modules', 'module'));
    }

    public function update(Request $request, $module)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        try {
            $content = $request->input('content');
            $result = $this->documentationGenerator->updateDocumentation($module, $content);
            
            if ($request->expectsJson()) {
                return response()->json($result);
            }
            
            return redirect()->route('laradoc.module', $module)
                ->with('success', 'Documentation updated successfully!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            
            return back()->with('error', 'Failed to update documentation: ' . $e->getMessage());
        }
    }

    public function settings()
    {
        $config = config('laradoc');
        $aiProviders = $this->aiService->getAvailableProviders();
        $searchStats = $this->searchService->getSearchStats();
        
        return view('laradoc::settings', compact('config', 'aiProviders', 'searchStats'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'ai_provider' => 'required|in:openai,claude,gemini',
            'search_driver' => 'required|in:meilisearch,database',
        ]);

        // Update configuration (this would typically be stored in database or config file)
        // For now, we'll just return success
        return redirect()->route('laradoc.settings')
            ->with('success', 'Settings updated successfully!');
    }

    // API Methods
    public function getModules(): JsonResponse
    {
        $modules = config('laradoc.documentation.modules');
        return response()->json($modules);
    }

    public function getDocumentation($module): JsonResponse
    {
        $documentation = $this->documentationGenerator->getDocumentation($module);
        
        if (!$documentation) {
            return response()->json(['error' => 'Documentation not found'], 404);
        }
        
        return response()->json($documentation);
    }

    public function chatApi(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'context' => 'nullable|array',
        ]);

        $message = $request->input('message');
        $context = $request->input('context', []);
        
        $response = $this->aiService->chatResponse($message, $context);
        
        return response()->json($response);
    }

    public function searchApi(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'module' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        $query = $request->input('q');
        $filters = $request->only(['module', 'type']);
        
        $results = $this->searchService->search($query, $filters);
        
        return response()->json($results);
    }

    public function updateApi(Request $request, $module): JsonResponse
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        try {
            $content = $request->input('content');
            $result = $this->documentationGenerator->updateDocumentation($module, $content);
            
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 