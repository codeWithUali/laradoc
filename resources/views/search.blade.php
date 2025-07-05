@extends('laradoc::layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Search Documentation</h1>
            <p class="text-muted mb-0">Find information across all project documentation</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card laradoc-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-search"></i> Search Filters
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('laradoc.search') }}">
                        <div class="mb-3">
                            <label for="q" class="form-label">Search Query</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="q" 
                                name="q" 
                                value="{{ request('q') }}"
                                placeholder="Enter search terms..."
                            >
                        </div>
                        
                        <div class="mb-3">
                            <label for="module" class="form-label">Module</label>
                            <select class="form-select" id="module" name="module">
                                <option value="">All Modules</option>
                                @foreach($modules as $key => $module)
                                    <option value="{{ $key }}" {{ request('module') == $key ? 'selected' : '' }}>
                                        {{ $module['title'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="type" class="form-label">Content Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">All Types</option>
                                <option value="overview" {{ request('type') == 'overview' ? 'selected' : '' }}>Overview</option>
                                <option value="api" {{ request('type') == 'api' ? 'selected' : '' }}>API Documentation</option>
                                <option value="code" {{ request('type') == 'code' ? 'selected' : '' }}>Code Examples</option>
                                <option value="guide" {{ request('type') == 'guide' ? 'selected' : '' }}>Guides</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            @if(request('q'))
                <div class="card laradoc-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            Search Results
                            @if(isset($results) && count($results) > 0)
                                <span class="badge bg-primary ms-2">{{ count($results) }}</span>
                            @endif
                        </h5>
                        @if(request('q'))
                            <small class="text-muted">
                                Results for: "{{ request('q') }}"
                            </small>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(isset($results) && count($results) > 0)
                            <div class="laradoc-search-results">
                                @foreach($results as $result)
                                    <div class="card mb-3 laradoc-search-result">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <a href="{{ route('laradoc.module', $result['module']) }}" class="text-decoration-none">
                                                    {{ $result['title'] }}
                                                </a>
                                            </h6>
                                            <p class="card-text text-muted">
                                                {{ Str::limit($result['excerpt'], 200) }}
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    Module: {{ $modules[$result['module']]['title'] ?? $result['module'] }}
                                                </small>
                                                @if(isset($result['score']))
                                                    <small class="text-muted">
                                                        Relevance: {{ number_format($result['score'] * 100, 1) }}%
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif(request('q'))
                            <div class="text-center py-5">
                                <i class="bi bi-search display-1 text-muted"></i>
                                <h4 class="mt-3">No Results Found</h4>
                                <p class="text-muted">
                                    No documentation found matching "{{ request('q') }}"
                                </p>
                                <div class="mt-3">
                                    <h6>Suggestions:</h6>
                                    <ul class="list-unstyled">
                                        <li>• Check your spelling</li>
                                        <li>• Try different keywords</li>
                                        <li>• Use more general terms</li>
                                        <li>• Try searching in a specific module</li>
                                    </ul>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-search display-1 text-muted"></i>
                                <h4 class="mt-3">Start Searching</h4>
                                <p class="text-muted">
                                    Enter a search query to find documentation
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card laradoc-card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-search display-1 text-muted"></i>
                        <h4 class="mt-3">Search Documentation</h4>
                        <p class="text-muted">
                            Use the search form to find information across all project documentation
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @livewire('laradoc-search')
@endsection 