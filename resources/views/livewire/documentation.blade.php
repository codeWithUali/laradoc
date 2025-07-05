<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-2">{{ $title }}</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('laradoc.index') }}">Home</a></li>
                    <li class="breadcrumb-item active">{{ $title }}</li>
                </ol>
            </nav>
        </div>
        
        @if(config('laradoc.ui.enable_edit_mode'))
            <div class="btn-group" role="group">
                @if($isEditing)
                    <button wire:click="saveDocumentation" class="btn laradoc-btn laradoc-btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Save
                    </button>
                    <button wire:click="cancelEditing" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>Cancel
                    </button>
                @else
                    <button wire:click="startEditing" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                    <button wire:click="refreshDocumentation" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                @endif
            </div>
        @endif
    </div>

    @if($isEditing)
        <div class="laradoc-card card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="editContent" class="form-label">Documentation Content (Markdown)</label>
                    <textarea 
                        wire:model="editContent" 
                        id="editContent" 
                        class="form-control" 
                        rows="20" 
                        placeholder="Enter markdown content..."
                    ></textarea>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Use Markdown syntax for formatting. You can use headers (# ## ###), lists, code blocks, tables, and more.
                    </small>
                    
                    <div class="btn-group">
                        <button wire:click="saveDocumentation" class="btn laradoc-btn laradoc-btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Save Changes
                        </button>
                        <button wire:click="cancelEditing" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="laradoc-card card">
            <div class="card-body">
                <div class="markdown-content" id="documentation-content">
                    {!! marked($content) !!}
                </div>
            </div>
        </div>
    @endif

    <!-- Module Navigation -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="laradoc-card card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-folder me-2"></i>Other Modules
                    </h5>
                    
                    <div class="row">
                        @foreach($modules as $key => $module)
                            <div class="col-md-6 col-lg-4 mb-2">
                                <a href="{{ route('laradoc.module', $key) }}" 
                                   class="btn btn-outline-primary btn-sm w-100 {{ $key === $module ? 'active' : '' }}">
                                    <i class="bi bi-folder me-1"></i>{{ $module['title'] }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize syntax highlighting for code blocks
    document.addEventListener('livewire:load', function () {
        hljs.highlightAll();
    });
    
    // Re-highlight after content updates
    document.addEventListener('livewire:update', function () {
        setTimeout(function() {
            hljs.highlightAll();
        }, 100);
    });
</script>
@endpush 