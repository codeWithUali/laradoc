<?php

namespace Laradoc\Laradoc\Livewire;

use Livewire\Component;
use Laradoc\Laradoc\Services\AIService;
use Laradoc\Laradoc\Services\DocumentationGenerator;

class ChatbotComponent extends Component
{
    public $message = '';
    public $messages = [];
    public $isLoading = false;
    public $isOpen = false;
    public $context = [];

    protected $listeners = ['openChat', 'closeChat'];

    public function mount()
    {
        $this->addSystemMessage('Hello! I\'m your Laravel project assistant. I can help you understand your codebase, find specific functionality, and answer questions about your application. How can I help you today?');
    }

    public function openChat()
    {
        $this->isOpen = true;
    }

    public function closeChat()
    {
        $this->isOpen = false;
    }

    public function sendMessage()
    {
        if (empty(trim($this->message))) {
            return;
        }

        $userMessage = trim($this->message);
        $this->addUserMessage($userMessage);
        $this->message = '';
        $this->isLoading = true;

        try {
            $aiService = app(AIService::class);
            $response = $aiService->chatResponse($userMessage, $this->context);
            
            $this->addAssistantMessage($response['response']);
            
            // Update context with recent conversation
            $this->updateContext($userMessage, $response['response']);
            
        } catch (\Exception $e) {
            $this->addAssistantMessage('I apologize, but I encountered an error while processing your request. Please try again or check your AI provider configuration.');
        }

        $this->isLoading = false;
    }

    public function clearChat()
    {
        $this->messages = [];
        $this->context = [];
        $this->addSystemMessage('Chat history cleared. How can I help you?');
    }

    public function getDocumentationContext($module = null)
    {
        try {
            $documentationGenerator = app(DocumentationGenerator::class);
            $documentation = $documentationGenerator->getDocumentation($module);
            
            if ($documentation) {
                $this->context['documentation'] = [
                    'module' => $module,
                    'title' => $documentation['title'],
                    'content' => substr($documentation['content'], 0, 2000), // Limit context size
                ];
                
                $this->addSystemMessage("I've loaded documentation for the '{$documentation['title']}' module. You can now ask me specific questions about it.");
            }
        } catch (\Exception $e) {
            $this->addSystemMessage('Failed to load documentation context.');
        }
    }

    protected function addUserMessage($message)
    {
        $this->messages[] = [
            'type' => 'user',
            'content' => $message,
            'timestamp' => now()->format('H:i'),
        ];
    }

    protected function addAssistantMessage($message)
    {
        $this->messages[] = [
            'type' => 'assistant',
            'content' => $message,
            'timestamp' => now()->format('H:i'),
        ];
    }

    protected function addSystemMessage($message)
    {
        $this->messages[] = [
            'type' => 'system',
            'content' => $message,
            'timestamp' => now()->format('H:i'),
        ];
    }

    protected function updateContext($userMessage, $assistantResponse)
    {
        // Keep only last 5 exchanges for context
        $recentMessages = array_slice($this->messages, -10);
        
        $this->context['conversation'] = array_map(function ($msg) {
            return [
                'role' => $msg['type'],
                'content' => $msg['content'],
            ];
        }, $recentMessages);
    }

    public function render()
    {
        return view('laradoc::livewire.chatbot', [
            'messages' => $this->messages,
            'isLoading' => $this->isLoading,
            'isOpen' => $this->isOpen,
        ]);
    }
} 