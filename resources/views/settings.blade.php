@extends('laradoc::layouts.app')

@section('content')
    @php
        $title = 'Laradoc Settings';
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-2">Settings</h1>
                    <p class="text-muted mb-0">Configure AI providers and search options</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="laradoc-card card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-robot me-2"></i>AI Provider Configuration
                    </h5>
                    
                    <form action="{{ route('laradoc.settings.update') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="ai_provider" class="form-label">AI Provider</label>
                            <select name="ai_provider" id="ai_provider" class="form-select">
                                @foreach($aiProviders as $provider)
                                    <option value="{{ $provider }}" {{ $config['ai_provider'] === $provider ? 'selected' : '' }}>
                                        {{ ucfirst($provider) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Choose your preferred AI provider for documentation generation and chatbot.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="search_driver" class="form-label">Search Driver</label>
                            <select name="search_driver" id="search_driver" class="form-select">
                                <option value="meilisearch" {{ $config['search']['driver'] === 'meilisearch' ? 'selected' : '' }}>
                                    Meilisearch (Recommended)
                                </option>
                                <option value="database" {{ $config['search']['driver'] === 'database' ? 'selected' : '' }}>
                                    Database (Fallback)
                                </option>
                            </select>
                            <div class="form-text">Choose search engine for documentation indexing.</div>
                        </div>
                        
                        <button type="submit" class="btn laradoc-btn laradoc-btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Save Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="laradoc-card card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-info-circle me-2"></i>Current Status
                    </h5>
                    
                    <div class="mb-3">
                        <h6>AI Provider Status:</h6>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-{{ $config['ai_provider'] ? 'success' : 'danger' }} me-2">
                                {{ $config['ai_provider'] ? 'Configured' : 'Not Configured' }}
                            </span>
                            <span class="text-muted">{{ ucfirst($config['ai_provider'] ?? 'None') }}</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Search Status:</h6>
                        @if($searchStats)
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2">Active</span>
                                <span class="text-muted">{{ ucfirst($searchStats['driver']) }}</span>
                            </div>
                            @if(isset($searchStats['total_documents']))
                                <small class="text-muted d-block mt-1">
                                    {{ $searchStats['total_documents'] }} documents indexed
                                </small>
                            @endif
                        @else
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning me-2">Warning</span>
                                <span class="text-muted">Search not configured</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <h6>Environment Variables:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td><code>LARADOC_AI_PROVIDER</code></td>
                                        <td>{{ env('LARADOC_AI_PROVIDER', 'Not set') }}</td>
                                    </tr>
                                    <tr>
                                        <td><code>LARADOC_SEARCH_DRIVER</code></td>
                                        <td>{{ env('LARADOC_SEARCH_DRIVER', 'Not set') }}</td>
                                    </tr>
                                    <tr>
                                        <td><code>OPENAI_API_KEY</code></td>
                                        <td>{{ env('OPENAI_API_KEY') ? 'Set' : 'Not set' }}</td>
                                    </tr>
                                    <tr>
                                        <td><code>ANTHROPIC_API_KEY</code></td>
                                        <td>{{ env('ANTHROPIC_API_KEY') ? 'Set' : 'Not set' }}</td>
                                    </tr>
                                    <tr>
                                        <td><code>GOOGLE_API_KEY</code></td>
                                        <td>{{ env('GOOGLE_API_KEY') ? 'Set' : 'Not set' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="laradoc-card card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-gear me-2"></i>Configuration Guide
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <h6>OpenAI Configuration</h6>
                            <p class="text-muted small">Add to your <code>.env</code> file:</p>
                            <pre class="bg-light p-2 rounded"><code>LARADOC_AI_PROVIDER=openai
OPENAI_API_KEY=your_openai_api_key
OPENAI_MODEL=gpt-4</code></pre>
                        </div>
                        
                        <div class="col-md-4">
                            <h6>Claude Configuration</h6>
                            <p class="text-muted small">Add to your <code>.env</code> file:</p>
                            <pre class="bg-light p-2 rounded"><code>LARADOC_AI_PROVIDER=claude
ANTHROPIC_API_KEY=your_anthropic_api_key
CLAUDE_MODEL=claude-3-sonnet-20240229</code></pre>
                        </div>
                        
                        <div class="col-md-4">
                            <h6>Gemini Configuration</h6>
                            <p class="text-muted small">Add to your <code>.env</code> file:</p>
                            <pre class="bg-light p-2 rounded"><code>LARADOC_AI_PROVIDER=gemini
GOOGLE_API_KEY=your_google_api_key
GEMINI_MODEL=gemini-pro</code></pre>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>Meilisearch Setup</h6>
                            <p class="text-muted small">Install and configure Meilisearch:</p>
                            <ol class="small">
                                <li>Install Meilisearch: <code>curl -L https://install.meilisearch.com | sh</code></li>
                                <li>Start server: <code>./meilisearch --master-key=your_master_key</code></li>
                                <li>Add to <code>.env</code>:
                                    <pre class="bg-light p-2 rounded mt-2"><code>LARADOC_SEARCH_DRIVER=meilisearch
MEILISEARCH_HOST=http://localhost:7700
MEILISEARCH_KEY=your_master_key</code></pre>
                                </li>
                            </ol>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>Database Search</h6>
                            <p class="text-muted small">Use database as fallback:</p>
                            <pre class="bg-light p-2 rounded"><code>LARADOC_SEARCH_DRIVER=database</code></pre>
                            <p class="text-muted small mt-2">Run migrations to create search tables:</p>
                            <pre class="bg-light p-2 rounded"><code>php artisan migrate</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="laradoc-card card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-tools me-2"></i>Quick Actions
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('laradoc.generate') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-gear me-2"></i>Generate Docs
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('laradoc.search') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-search me-2"></i>Test Search
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button onclick="openChat()" class="btn btn-outline-info w-100">
                                <i class="bi bi-chat-dots me-2"></i>Test Chat
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('laradoc.index') }}" class="btn btn-outline-success w-100">
                                <i class="bi bi-house me-2"></i>Go Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 