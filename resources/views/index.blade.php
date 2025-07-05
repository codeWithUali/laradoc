@extends('laradoc::layouts.app')

@section('content')
    @php
        $title = 'Laradoc Documentation';
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-2">Welcome to Laradoc</h1>
                    <p class="text-muted mb-0">Intelligent documentation for your Laravel project</p>
                </div>
                <div>
                    <a href="{{ route('laradoc.generate') }}" class="btn laradoc-btn laradoc-btn-primary">
                        <i class="bi bi-gear me-2"></i>Generate Documentation
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($documentation)
        <div class="row mb-4">
            <div class="col-12">
                <div class="laradoc-card card">
                    <div class="card-body">
                        <h3 class="card-title">
                            <i class="bi bi-file-text me-2"></i>Project Overview
                        </h3>
                        <div class="markdown-content" id="overview-content">
                            {!! marked($documentation['content']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <h3 class="mb-4">
                <i class="bi bi-folder me-2"></i>Documentation Modules
            </h3>
        </div>
    </div>

    <div class="row">
        @foreach($modules as $key => $module)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="laradoc-card card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                <i class="bi bi-folder text-primary"></i>
                            </div>
                            <h5 class="card-title mb-0">{{ $module['title'] }}</h5>
                        </div>
                        
                        <p class="card-text text-muted">{{ $module['description'] }}</p>
                        
                        <div class="mt-auto">
                            <a href="{{ route('laradoc.module', $key) }}" class="btn laradoc-btn laradoc-btn-primary btn-sm">
                                <i class="bi bi-arrow-right me-1"></i>View Documentation
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="laradoc-card card">
                <div class="card-body">
                    <h4 class="card-title">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('laradoc.search') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-search me-2"></i>Search Documentation
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('laradoc.generate') }}" class="btn btn-outline-success w-100">
                                <i class="bi bi-gear me-2"></i>Generate Docs
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('laradoc.settings') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-sliders me-2"></i>Settings
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            @if(config('laradoc.ui.enable_chatbot'))
                                <button onclick="openChat()" class="btn btn-outline-info w-100">
                                    <i class="bi bi-chat-dots me-2"></i>AI Chat
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!$documentation)
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle me-3" style="font-size: 1.5rem;"></i>
                        <div>
                            <h5 class="alert-heading">No Documentation Found</h5>
                            <p class="mb-0">It looks like documentation hasn't been generated yet. Click the "Generate Documentation" button to create comprehensive documentation for your Laravel project.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    // Initialize syntax highlighting for code blocks
    document.addEventListener('DOMContentLoaded', function() {
        hljs.highlightAll();
    });
</script>
@endpush 