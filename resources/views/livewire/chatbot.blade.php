<div class="chat-container h-100 d-flex flex-column">
    <!-- Chat Header -->
    <div class="chat-header bg-primary text-white p-3 rounded-top">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="bi bi-robot me-2"></i>
                <h6 class="mb-0">Laradoc AI Assistant</h6>
            </div>
            <div class="btn-group btn-group-sm">
                <button wire:click="clearChat" class="btn btn-outline-light btn-sm" title="Clear Chat">
                    <i class="bi bi-trash"></i>
                </button>
                <button onclick="closeChat()" class="btn btn-outline-light btn-sm" title="Close Chat">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Chat Messages -->
    <div class="chat-messages flex-grow-1 p-3" id="chatMessages" style="overflow-y: auto; max-height: 350px;">
        @foreach($messages as $message)
            <div class="message mb-3 {{ $message['type'] === 'user' ? 'text-end' : '' }}">
                <div class="d-flex {{ $message['type'] === 'user' ? 'justify-content-end' : 'justify-content-start' }}">
                    @if($message['type'] !== 'user')
                        <div class="avatar me-2">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="bi bi-robot text-white" style="font-size: 0.8rem;"></i>
                            </div>
                        </div>
                    @endif
                    
                    <div class="message-content {{ $message['type'] === 'user' ? 'bg-primary text-white' : 'bg-light' }} rounded p-2" style="max-width: 80%;">
                        <div class="message-text">
                            @if($message['type'] === 'system')
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    {{ $message['content'] }}
                                </small>
                            @else
                                {!! nl2br(e($message['content'])) !!}
                            @endif
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                            {{ $message['timestamp'] }}
                        </small>
                    </div>
                    
                    @if($message['type'] === 'user')
                        <div class="avatar ms-2">
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="bi bi-person text-white" style="font-size: 0.8rem;"></i>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
        
        @if($isLoading)
            <div class="message mb-3">
                <div class="d-flex justify-content-start">
                    <div class="avatar me-2">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-robot text-white" style="font-size: 0.8rem;"></i>
                        </div>
                    </div>
                    <div class="message-content bg-light rounded p-2">
                        <div class="d-flex align-items-center">
                            <div class="laradoc-loading me-2"></div>
                            <span class="text-muted">AI is thinking...</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Chat Input -->
    <div class="chat-input p-3 border-top">
        <form wire:submit.prevent="sendMessage">
            <div class="input-group">
                <input 
                    type="text" 
                    wire:model="message" 
                    class="form-control" 
                    placeholder="Ask me about your Laravel project..."
                    {{ $isLoading ? 'disabled' : '' }}
                >
                <button 
                    type="submit" 
                    class="btn btn-primary" 
                    {{ $isLoading ? 'disabled' : '' }}
                >
                    <i class="bi bi-send"></i>
                </button>
            </div>
        </form>
        
        <div class="mt-2">
            <small class="text-muted">
                <i class="bi bi-lightbulb me-1"></i>
                Try asking: "How do I add a new route?" or "Explain the User model"
            </small>
        </div>
    </div>
</div>

<style>
    .chat-container {
        height: 100%;
        background: white;
    }
    
    .chat-messages {
        background: #f8f9fa;
    }
    
    .message-content {
        word-wrap: break-word;
    }
    
    .message-content pre {
        background: #1e293b;
        color: #e2e8f0;
        padding: 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        overflow-x: auto;
    }
    
    .message-content code {
        background: #f1f5f9;
        padding: 2px 4px;
        border-radius: 3px;
        font-size: 0.9em;
    }
    
    .chat-input input:focus {
        border-color: var(--laradoc-primary);
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
    }
</style>

<script>
    // Auto-scroll to bottom when new messages arrive
    document.addEventListener('livewire:update', function () {
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    });
    
    // Focus on input when chat opens
    document.addEventListener('livewire:load', function () {
        Livewire.on('openChat', function () {
            setTimeout(function() {
                const input = document.querySelector('.chat-input input');
                if (input) {
                    input.focus();
                }
            }, 100);
        });
    });
</script> 