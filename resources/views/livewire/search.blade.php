<div>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-2">Search Documentation</h2>
                    <p class="text-muted mb-0">Find specific information in your project documentation</p>
                </div>
                <div>
                    <button wire:click="clearSearch" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="laradoc-card card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input 
                                    type="text" 
                                    wire:model.debounce.300ms="query" 
                                    class="form-control form-control-lg" 
                                    placeholder="Search for controllers, models, routes, or any documentation..."
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-6">
                                    <select wire:model="filters.module" class="form-select">
                                        <option value="">All Modules</option>
                                        @foreach($modules as $key => $module)
                                            <option value="{{ $key }}">{{ $module }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <select wire:model="filters.type" class="form-select">
                                        <option value="">All Types</option>
                                        <option value="documentation">Documentation</option>
                                        <option value="section">Sections</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <div class="row">
        <div class="col-12">
            @if($isLoading)
                <div class="text-center py-5">
                    <div class="laradoc-loading d-inline-block mb-3"></div>
                    <p class="text-muted">Searching documentation...</p>
                </div>
            @elseif(!empty($query) && empty($results))
                <div class="laradoc-card card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">No results found</h4>
                        <p class="text-muted">Try adjusting your search terms or filters</p>
                    </div>
                </div>
            @elseif(!empty($results))
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Search Results</h4>
                    <span class="badge bg-primary">{{ $totalResults }} result{{ $totalResults !== 1 ? 's' : '' }}</span>
                </div>

                @foreach($results as $result)
                    <div class="laradoc-card card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h5 class="card-title">
                                        <a href="{{ $this->getResultUrl($result) }}" class="text-decoration-none">
                                            {!! $this->highlightText($result['title'], $query) !!}
                                        </a>
                                    </h5>
                                    
                                    @if(isset($result['module']))
                                        <span class="badge bg-secondary me-2">{{ $result['module'] }}</span>
                                    @endif
                                    
                                    @if(isset($result['type']))
                                        <span class="badge bg-info">{{ $result['type'] }}</span>
                                    @endif
                                    
                                    <p class="card-text mt-2">
                                        {!! $this->getResultExcerpt($result['content'], $query) !!}
                                    </p>
                                    
                                    @if(isset($result['description']) && !empty($result['description']))
                                        <small class="text-muted">
                                            {!! $this->highlightText($result['description'], $query) !!}
                                        </small>
                                    @endif
                                </div>
                                
                                <div class="ms-3">
                                    <a href="{{ $this->getResultUrl($result) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="laradoc-card card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Start searching</h4>
                        <p class="text-muted">Enter your search query above to find documentation</p>
                        
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <i class="bi bi-controller text-primary mb-2" style="font-size: 2rem;"></i>
                                    <h6>Controllers</h6>
                                    <small class="text-muted">Find controller methods and logic</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <i class="bi bi-database text-primary mb-2" style="font-size: 2rem;"></i>
                                    <h6>Models</h6>
                                    <small class="text-muted">Search for models and relationships</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <i class="bi bi-diagram-3 text-primary mb-2" style="font-size: 2rem;"></i>
                                    <h6>Routes</h6>
                                    <small class="text-muted">Find API and web routes</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Search Tips -->
    @if(empty($query))
        <div class="row mt-5">
            <div class="col-12">
                <div class="laradoc-card card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-lightbulb me-2"></i>Search Tips
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Quick Searches</h6>
                                <ul class="list-unstyled">
                                    <li><code>User model</code> - Find User model documentation</li>
                                    <li><code>auth middleware</code> - Authentication middleware</li>
                                    <li><code>POST /api/users</code> - API endpoints</li>
                                    <li><code>validation rules</code> - Form validation</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Advanced Features</h6>
                                <ul class="list-unstyled">
                                    <li>Use quotes for exact phrases</li>
                                    <li>Filter by module or content type</li>
                                    <li>Search is case-insensitive</li>
                                    <li>Results are ranked by relevance</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .card-title a:hover {
        color: var(--laradoc-primary) !important;
    }
    
    mark {
        background-color: #fef08a;
        padding: 2px 4px;
        border-radius: 3px;
    }
</style> 