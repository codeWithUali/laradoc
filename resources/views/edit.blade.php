@extends('laradoc::layouts.app')

@section('content')
    @php
        $title = 'Edit Documentation - ' . ($documentation['title'] ?? ucfirst($module));
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Edit Documentation</h1>
            <p class="text-muted mb-0">{{ $documentation['title'] ?? ucfirst($module) }}</p>
        </div>
        <div class="btn-group" role="group">
            <a href="{{ route('laradoc.module', $module) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Documentation
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card laradoc-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-pencil"></i> Edit Content
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('laradoc.update', $module) }}" id="editForm">
                        @csrf
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="title" 
                                name="title" 
                                value="{{ $documentation['title'] ?? '' }}"
                                required
                            >
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Content (Markdown)</label>
                            <textarea 
                                id="content" 
                                name="content" 
                                class="form-control" 
                                rows="25" 
                                style="font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;"
                                required
                            >{{ $documentation['content'] ?? '' }}</textarea>
                            <div class="form-text">
                                Use Markdown syntax for formatting. 
                                <a href="https://www.markdownguide.org/basic-syntax/" target="_blank">Markdown Guide</a>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check"></i> Save Changes
                            </button>
                            <a href="{{ route('laradoc.module', $module) }}" class="btn btn-secondary">
                                <i class="bi bi-x"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card laradoc-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-eye"></i> Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div id="preview" class="markdown-content">
                        @if($documentation && isset($documentation['content']))
                            {!! marked($documentation['content']) !!}
                        @else
                            <p class="text-muted">Start typing to see a preview...</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Real-time preview
        const contentTextarea = document.getElementById('content');
        const previewDiv = document.getElementById('preview');
        
        if (contentTextarea && previewDiv) {
            contentTextarea.addEventListener('input', function() {
                const markdown = this.value;
                if (markdown.trim()) {
                    // Use marked.js to convert markdown to HTML
                    previewDiv.innerHTML = marked(markdown);
                } else {
                    previewDiv.innerHTML = '<p class="text-muted">Start typing to see a preview...</p>';
                }
            });
        }

        // Auto-save functionality
        let saveTimeout;
        contentTextarea.addEventListener('input', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                autoSave();
            }, 5000); // Auto-save after 5 seconds of inactivity
        });

        function autoSave() {
            const formData = new FormData(document.getElementById('editForm'));
            
            fetch('{{ route("laradoc.update", $module) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    content: formData.get('content'),
                    title: formData.get('title')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Documentation saved automatically', 'success');
                }
            })
            .catch(error => {
                console.error('Auto-save error:', error);
                showNotification('Failed to save automatically', 'error');
            });
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
@endsection 