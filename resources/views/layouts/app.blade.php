<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Laradoc</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Highlight.js for code syntax highlighting -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github.min.css" rel="stylesheet">
    <!-- Marked.js for markdown rendering -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <!-- Highlight.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>

    @livewireStyles

    <style>
        :root {
            --laradoc-primary: #6366f1;
            --laradoc-secondary: #64748b;
            --laradoc-success: #10b981;
            --laradoc-warning: #f59e0b;
            --laradoc-danger: #ef4444;
            --laradoc-light: #f8fafc;
            --laradoc-dark: #1e293b;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--laradoc-light);
        }

        .laradoc-sidebar {
            background: linear-gradient(135deg, var(--laradoc-primary) 0%, #8b5cf6 100%);
            min-height: 100vh;
            color: white;
        }

        .laradoc-sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }

        .laradoc-sidebar .nav-link:hover,
        .laradoc-sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .laradoc-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin: 20px;
            padding: 30px;
        }

        .laradoc-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 0;
        }

        .laradoc-search {
            position: relative;
        }

        .laradoc-search input {
            border-radius: 25px;
            border: 2px solid #e2e8f0;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .laradoc-search input:focus {
            border-color: var(--laradoc-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .laradoc-btn {
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .laradoc-btn-primary {
            background: linear-gradient(135deg, var(--laradoc-primary) 0%, #8b5cf6 100%);
            border: none;
            color: white;
        }

        .laradoc-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .laradoc-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .laradoc-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .laradoc-chat-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--laradoc-primary) 0%, #8b5cf6 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .laradoc-chat-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(99, 102, 241, 0.4);
        }

        .laradoc-chat-window {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 400px;
            height: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
        }

        .laradoc-chat-window.show {
            display: block;
        }

        .markdown-content {
            line-height: 1.6;
        }

        .markdown-content h1,
        .markdown-content h2,
        .markdown-content h3,
        .markdown-content h4,
        .markdown-content h5,
        .markdown-content h6 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-weight: 600;
            color: var(--laradoc-dark);
        }

        .markdown-content h1 {
            font-size: 2.5rem;
            border-bottom: 3px solid var(--laradoc-primary);
            padding-bottom: 0.5rem;
        }

        .markdown-content h2 {
            font-size: 2rem;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0.3rem;
        }

        .markdown-content h3 {
            font-size: 1.5rem;
        }

        .markdown-content code {
            background-color: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .markdown-content pre {
            background-color: #1e293b;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
        }

        .markdown-content pre code {
            background: none;
            padding: 0;
            color: inherit;
        }

        .markdown-content blockquote {
            border-left: 4px solid var(--laradoc-primary);
            padding-left: 1rem;
            margin: 1rem 0;
            color: var(--laradoc-secondary);
        }

        .markdown-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        .markdown-content th,
        .markdown-content td {
            border: 1px solid #e2e8f0;
            padding: 0.5rem;
            text-align: left;
        }

        .markdown-content th {
            background-color: #f8fafc;
            font-weight: 600;
        }

        .laradoc-loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--laradoc-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .laradoc-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }

        @media (max-width: 768px) {
            .laradoc-sidebar {
                min-height: auto;
            }
            
            .laradoc-content {
                margin: 10px;
                padding: 20px;
            }
            
            .laradoc-chat-window {
                width: 90vw;
                height: 70vh;
                right: 5vw;
                bottom: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 laradoc-sidebar">
                <div class="p-4">
                    <div class="d-flex align-items-center mb-4">
                        <i class="bi bi-book me-2" style="font-size: 1.5rem;"></i>
                        <h4 class="mb-0">Laradoc</h4>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link {{ request()->routeIs('laradoc.index') ? 'active' : '' }}" href="{{ route('laradoc.index') }}">
                            <i class="bi bi-house me-2"></i> Overview
                        </a>
                        
                        @foreach(config('laradoc.documentation.modules') as $key => $module)
                            <a class="nav-link {{ request()->is('*/module/' . $key) ? 'active' : '' }}" href="{{ route('laradoc.module', $key) }}">
                                <i class="bi bi-folder me-2"></i> {{ $module['title'] }}
                            </a>
                        @endforeach
                        
                        <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                        
                        <a class="nav-link {{ request()->routeIs('laradoc.search*') ? 'active' : '' }}" href="{{ route('laradoc.search') }}">
                            <i class="bi bi-search me-2"></i> Search
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('laradoc.generate*') ? 'active' : '' }}" href="{{ route('laradoc.generate') }}">
                            <i class="bi bi-gear me-2"></i> Generate
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('laradoc.settings*') ? 'active' : '' }}" href="{{ route('laradoc.settings') }}">
                            <i class="bi bi-sliders me-2"></i> Settings
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <!-- Header -->
                <div class="laradoc-header">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h1 class="h3 mb-0">{{ $title ?? 'Laradoc Documentation' }}</h1>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-end align-items-center">
                                    <div class="laradoc-search me-3">
                                        <form action="{{ route('laradoc.search') }}" method="GET" class="d-flex">
                                            <input type="text" name="q" class="form-control" placeholder="Search documentation..." value="{{ request('q') }}">
                                            <button type="submit" class="btn laradoc-btn laradoc-btn-primary ms-2">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </form>
                                    </div>
                                    
                                    @if(config('laradoc.ui.enable_chatbot'))
                                        <button class="btn laradoc-btn laradoc-btn-primary" onclick="openChat()">
                                            <i class="bi bi-chat-dots me-1"></i> Chat
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="laradoc-content">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Button -->
    @if(config('laradoc.ui.enable_chatbot'))
        <button class="laradoc-chat-btn" onclick="openChat()">
            <i class="bi bi-chat-dots" style="font-size: 1.5rem;"></i>
        </button>
    @endif

    <!-- Chat Window -->
    @if(config('laradoc.ui.enable_chatbot'))
        <div class="laradoc-chat-window" id="chatWindow">
            @livewire('laradoc-chatbot')
        </div>
    @endif

    <!-- Toast Container -->
    <div class="laradoc-toast" id="toastContainer"></div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @livewireScripts

    <script>
        // Configure marked.js for markdown rendering
        marked.setOptions({
            highlight: function(code, lang) {
                if (lang && hljs.getLanguage(lang)) {
                    return hljs.highlight(code, { language: lang }).value;
                }
                return hljs.highlightAuto(code).value;
            },
            breaks: true,
            gfm: true
        });

        // Chat functionality
        function openChat() {
            const chatWindow = document.getElementById('chatWindow');
            if (chatWindow) {
                chatWindow.classList.toggle('show');
                if (chatWindow.classList.contains('show')) {
                    Livewire.emit('openChat');
                } else {
                    Livewire.emit('closeChat');
                }
            }
        }

        // Toast notifications
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `alert alert-${type} alert-dismissible fade show`;
            toast.innerHTML = `
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            toastContainer.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        // Livewire event listeners
        document.addEventListener('livewire:load', function () {
            Livewire.on('documentation-saved', function (data) {
                showToast(data.message, 'success');
            });

            Livewire.on('documentation-error', function (data) {
                showToast(data.message, 'danger');
            });

            Livewire.on('search-error', function (data) {
                showToast(data.message, 'danger');
            });
        });

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>

    @stack('scripts')
</body>
</html> 