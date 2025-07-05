@extends('laradoc::layouts.app')

@section('content')
    @php
        $title = 'Generate Documentation';
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-2">Generate Documentation</h1>
                    <p class="text-muted mb-0">Create comprehensive documentation for your Laravel project</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="laradoc-card card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-gear me-2"></i>Generation Options
                    </h5>
                    
                    <form action="{{ route('laradoc.generate.post') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="module" class="form-label">Documentation Scope</label>
                            <select name="module" id="module" class="form-select">
                                <option value="">Generate Complete Documentation</option>
                                @foreach($modules as $key => $module)
                                    <option value="{{ $key }}">{{ $module['title'] }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Choose a specific module or generate documentation for the entire project.</div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="force" id="force" value="1">
                                <label class="form-check-label" for="force">
                                    Force regeneration (overwrite existing documentation)
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="no_ai" id="no_ai" value="1">
                                <label class="form-check-label" for="no_ai">
                                    Skip AI generation (faster, basic documentation only)
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn laradoc-btn laradoc-btn-primary">
                            <i class="bi bi-play me-2"></i>Generate Documentation
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="laradoc-card card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-info-circle me-2"></i>Generation Info
                    </h5>
                    
                    <div class="mb-3">
                        <h6>What will be analyzed:</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle text-success me-2"></i>Controllers & Methods</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Models & Relationships</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Routes & Endpoints</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Database Migrations</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Views & Components</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Policies & Gates</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Validation Rules</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Service Providers</li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Search Statistics:</h6>
                        @if($stats)
                            <p class="mb-1"><strong>Driver:</strong> {{ $stats['driver'] ?? 'Unknown' }}</p>
                            @if(isset($stats['total_documents']))
                                <p class="mb-1"><strong>Documents:</strong> {{ $stats['total_documents'] }}</p>
                            @endif
                            @if(isset($stats['index_size']))
                                <p class="mb-1"><strong>Index Size:</strong> {{ $stats['index_size'] }}</p>
                            @endif
                        @else
                            <p class="text-muted">No search statistics available</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="laradoc-card card mt-3">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h5>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('laradoc.analyze') }}" class="btn btn-outline-primary">
                            <i class="bi bi-search me-2"></i>Analyze Project
                        </a>
                        <a href="{{ route('laradoc.settings') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-sliders me-2"></i>Configure AI
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('generation_progress'))
        <div class="row mt-4">
            <div class="col-12">
                <div class="laradoc-card card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-clock me-2"></i>Generation Progress
                        </h5>
                        
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: {{ session('generation_progress') }}%">
                                {{ session('generation_progress') }}%
                            </div>
                        </div>
                        
                        <p class="text-muted mb-0">Documentation generation is in progress...</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection 