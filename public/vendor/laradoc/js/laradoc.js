/**
 * Laradoc Package JavaScript
 */

class Laradoc {
    constructor() {
        this.initializeComponents();
        this.bindEvents();
    }

    initializeComponents() {
        // Initialize marked.js for markdown rendering
        if (typeof marked !== 'undefined') {
            marked.setOptions({
                highlight: function(code, lang) {
                    if (lang && hljs.getLanguage(lang)) {
                        try {
                            return hljs.highlight(code, { language: lang }).value;
                        } catch (err) {}
                    }
                    return hljs.highlightAuto(code).value;
                }
            });
        }

        // Initialize search functionality
        this.initializeSearch();
        
        // Initialize chatbot
        this.initializeChatbot();
        
        // Initialize documentation editor
        this.initializeEditor();
    }

    bindEvents() {
        // Sidebar toggle for mobile
        document.addEventListener('DOMContentLoaded', () => {
            const sidebarToggle = document.querySelector('.laradoc-sidebar-toggle');
            const sidebar = document.querySelector('.laradoc-sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('show');
                });
            }

            // Auto-save functionality for editor
            const editor = document.querySelector('.laradoc-editor');
            if (editor) {
                let saveTimeout;
                editor.addEventListener('input', () => {
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(() => {
                        this.autoSave();
                    }, 2000);
                });
            }
        });
    }

    initializeSearch() {
        const searchInput = document.querySelector('.laradoc-search-input');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 300);
            });
        }
    }

    initializeChatbot() {
        const chatForm = document.querySelector('.laradoc-chat-form');
        if (chatForm) {
            chatForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.sendChatMessage();
            });
        }
    }

    initializeEditor() {
        const editToggle = document.querySelector('.laradoc-edit-toggle');
        if (editToggle) {
            editToggle.addEventListener('click', () => {
                this.toggleEditMode();
            });
        }
    }

    performSearch(query) {
        if (query.length < 2) return;

        fetch(`/laradoc/api/search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                this.displaySearchResults(data);
            })
            .catch(error => {
                console.error('Search error:', error);
            });
    }

    displaySearchResults(results) {
        const resultsContainer = document.querySelector('.laradoc-search-results');
        if (!resultsContainer) return;

        if (results.length === 0) {
            resultsContainer.innerHTML = '<p class="text-muted">No results found.</p>';
            return;
        }

        const html = results.map(result => `
            <div class="card mb-3 laradoc-search-result">
                <div class="card-body">
                    <h6 class="card-title">${result.title}</h6>
                    <p class="card-text">${result.excerpt}</p>
                    <small class="text-muted">Module: ${result.module}</small>
                </div>
            </div>
        `).join('');

        resultsContainer.innerHTML = html;
    }

    sendChatMessage() {
        const messageInput = document.querySelector('.laradoc-chat-input');
        const message = messageInput.value.trim();
        
        if (!message) return;

        // Add user message to chat
        this.addChatMessage(message, 'user');
        messageInput.value = '';

        // Show loading indicator
        this.showChatLoading();

        // Send to API
        fetch('/laradoc/api/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ message })
        })
        .then(response => response.json())
        .then(data => {
            this.hideChatLoading();
            this.addChatMessage(data.response, 'assistant');
        })
        .catch(error => {
            this.hideChatLoading();
            this.addChatMessage('Sorry, I encountered an error. Please try again.', 'assistant');
            console.error('Chat error:', error);
        });
    }

    addChatMessage(message, type) {
        const chatContainer = document.querySelector('.laradoc-chat-messages');
        if (!chatContainer) return;

        const messageDiv = document.createElement('div');
        messageDiv.className = `laradoc-chat-message laradoc-chat-${type}`;
        messageDiv.innerHTML = marked(message);
        
        chatContainer.appendChild(messageDiv);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    showChatLoading() {
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'laradoc-chat-loading';
        loadingDiv.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div>';
        
        const chatContainer = document.querySelector('.laradoc-chat-messages');
        if (chatContainer) {
            chatContainer.appendChild(loadingDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }

    hideChatLoading() {
        const loadingDiv = document.querySelector('.laradoc-chat-loading');
        if (loadingDiv) {
            loadingDiv.remove();
        }
    }

    toggleEditMode() {
        const content = document.querySelector('.laradoc-content');
        const editor = document.querySelector('.laradoc-editor');
        
        if (content && editor) {
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
            editor.style.display = editor.style.display === 'none' ? 'block' : 'none';
        }
    }

    autoSave() {
        const editor = document.querySelector('.laradoc-editor');
        if (!editor) return;

        const content = editor.value;
        const module = this.getCurrentModule();

        fetch(`/laradoc/api/update/${module}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ content })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('Documentation saved automatically', 'success');
            }
        })
        .catch(error => {
            console.error('Auto-save error:', error);
            this.showNotification('Failed to save automatically', 'error');
        });
    }

    getCurrentModule() {
        const path = window.location.pathname;
        const match = path.match(/\/module\/([^\/]+)/);
        return match ? match[1] : 'overview';
    }

    showNotification(message, type = 'info') {
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
        }, 5000);
    }
}

// Initialize Laradoc when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.laradoc = new Laradoc();
}); 