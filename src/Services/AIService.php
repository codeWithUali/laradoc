<?php

namespace Laradoc\Laradoc\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected $config;
    protected $provider;
    protected $apiKey;
    protected $baseUrl;
    protected $model;

    public function __construct()
    {
        $this->config = config('laradoc');
        $this->provider = $this->config['ai_provider'];
        $this->apiKey = $this->config['providers'][$this->provider]['api_key'];
        $this->baseUrl = $this->config['providers'][$this->provider]['base_url'];
        $this->model = $this->config['providers'][$this->provider]['model'];
    }

    public function generateDocumentation($projectAnalysis, $module = null)
    {
        $prompt = $this->buildDocumentationPrompt($projectAnalysis, $module);
        
        try {
            $response = $this->makeRequest($prompt);
            return $this->parseDocumentationResponse($response);
        } catch (\Exception $e) {
            Log::error('AI Documentation Generation Error: ' . $e->getMessage());
            return $this->generateFallbackDocumentation($projectAnalysis, $module);
        }
    }

    public function chatResponse($message, $context = [])
    {
        $prompt = $this->buildChatPrompt($message, $context);
        
        try {
            $response = $this->makeRequest($prompt, 'chat');
            return $this->parseChatResponse($response);
        } catch (\Exception $e) {
            Log::error('AI Chat Error: ' . $e->getMessage());
            return [
                'response' => 'I apologize, but I\'m having trouble processing your request right now. Please try again later.',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function makeRequest($prompt, $type = 'completion')
    {
        $headers = $this->getHeaders();
        
        switch ($this->provider) {
            case 'openai':
                return $this->makeOpenAIRequest($prompt, $type, $headers);
            case 'claude':
                return $this->makeClaudeRequest($prompt, $type, $headers);
            case 'gemini':
                return $this->makeGeminiRequest($prompt, $type, $headers);
            default:
                throw new \Exception("Unsupported AI provider: {$this->provider}");
        }
    }

    protected function makeOpenAIRequest($prompt, $type, $headers)
    {
        $url = $this->baseUrl . '/chat/completions';
        
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->config['chatbot']['system_prompt']
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => $this->config['chatbot']['temperature'],
            'max_tokens' => $this->config['chatbot']['max_tokens'],
        ];

        $response = Http::withHeaders($headers)
            ->timeout(60)
            ->post($url, $data);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('OpenAI API Error: ' . $response->body());
    }

    protected function makeClaudeRequest($prompt, $type, $headers)
    {
        $url = $this->baseUrl . '/v1/messages';
        
        $data = [
            'model' => $this->model,
            'max_tokens' => $this->config['chatbot']['max_tokens'],
            'temperature' => $this->config['chatbot']['temperature'],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
        ];

        $response = Http::withHeaders($headers)
            ->timeout(60)
            ->post($url, $data);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Claude API Error: ' . $response->body());
    }

    protected function makeGeminiRequest($prompt, $type, $headers)
    {
        $url = $this->baseUrl . '/v1beta/models/' . $this->model . ':generateContent';
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $this->config['chatbot']['temperature'],
                'maxOutputTokens' => $this->config['chatbot']['max_tokens'],
            ]
        ];

        $response = Http::withHeaders($headers)
            ->timeout(60)
            ->post($url, $data);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Gemini API Error: ' . $response->body());
    }

    protected function getHeaders()
    {
        switch ($this->provider) {
            case 'openai':
                return [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ];
            case 'claude':
                return [
                    'x-api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'anthropic-version' => '2023-06-01',
                ];
            case 'gemini':
                return [
                    'Content-Type' => 'application/json',
                ];
            default:
                return [];
        }
    }

    protected function buildDocumentationPrompt($projectAnalysis, $module = null)
    {
        $prompt = "You are an expert Laravel developer and technical writer. ";
        $prompt .= "Please analyze the following Laravel project structure and generate comprehensive documentation. ";
        
        if ($module) {
            $prompt .= "Focus specifically on the '{$module}' module. ";
        }
        
        $prompt .= "\n\nProject Information:\n";
        $prompt .= json_encode($projectAnalysis['project_info'], JSON_PRETTY_PRINT);
        
        if ($module && isset($projectAnalysis['modules'][$module])) {
            $prompt .= "\n\nModule Information:\n";
            $prompt .= json_encode($projectAnalysis['modules'][$module], JSON_PRETTY_PRINT);
        }
        
        $prompt .= "\n\nPlease generate documentation that includes:\n";
        $prompt .= "1. Overview and purpose of the application\n";
        $prompt .= "2. Architecture and structure\n";
        $prompt .= "3. Database schema and relationships\n";
        $prompt .= "4. API endpoints and their functionality\n";
        $prompt .= "5. Authentication and authorization\n";
        $prompt .= "6. Business logic and workflows\n";
        $prompt .= "7. Frontend components and views\n";
        $prompt .= "8. Configuration and environment setup\n";
        $prompt .= "9. Deployment and maintenance\n";
        
        $prompt .= "\n\nFormat the response as structured markdown with proper headings, code blocks, and examples.";
        
        return $prompt;
    }

    protected function buildChatPrompt($message, $context = [])
    {
        $prompt = $this->config['chatbot']['system_prompt'] . "\n\n";
        
        if (!empty($context)) {
            $prompt .= "Context from project documentation:\n";
            $prompt .= json_encode($context, JSON_PRETTY_PRINT) . "\n\n";
        }
        
        $prompt .= "User Question: " . $message . "\n\n";
        $prompt .= "Please provide a helpful and accurate response based on the project documentation and context provided.";
        
        return $prompt;
    }

    protected function parseDocumentationResponse($response)
    {
        switch ($this->provider) {
            case 'openai':
                return $response['choices'][0]['message']['content'] ?? '';
            case 'claude':
                return $response['content'][0]['text'] ?? '';
            case 'gemini':
                return $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
            default:
                return '';
        }
    }

    protected function parseChatResponse($response)
    {
        $content = $this->parseDocumentationResponse($response);
        
        return [
            'response' => $content,
            'provider' => $this->provider,
            'model' => $this->model,
            'timestamp' => now(),
        ];
    }

    protected function generateFallbackDocumentation($projectAnalysis, $module = null)
    {
        $doc = "# Laravel Project Documentation\n\n";
        
        $doc .= "## Project Overview\n\n";
        $doc .= "**Name:** " . ($projectAnalysis['project_info']['name'] ?? 'Laravel Application') . "\n";
        $doc .= "**Description:** " . ($projectAnalysis['project_info']['description'] ?? 'No description available') . "\n";
        $doc .= "**Laravel Version:** " . ($projectAnalysis['project_info']['laravel_version'] ?? 'Unknown') . "\n";
        $doc .= "**PHP Version:** " . ($projectAnalysis['project_info']['php_version'] ?? 'Unknown') . "\n\n";
        
        if ($module && isset($projectAnalysis['modules'][$module])) {
            $doc .= "## Module: " . ucfirst($module) . "\n\n";
            $doc .= "This module contains the following routes:\n\n";
            
            foreach ($projectAnalysis['modules'][$module]['routes'] as $route) {
                $doc .= "- **" . $route['method'] . "** `" . $route['uri'] . "`\n";
            }
        } else {
            $doc .= "## Project Structure\n\n";
            
            if (!empty($projectAnalysis['controllers'])) {
                $doc .= "### Controllers\n\n";
                foreach ($projectAnalysis['controllers'] as $controller) {
                    $doc .= "- " . $controller['short_name'] . "\n";
                }
                $doc .= "\n";
            }
            
            if (!empty($projectAnalysis['models'])) {
                $doc .= "### Models\n\n";
                foreach ($projectAnalysis['models'] as $model) {
                    $doc .= "- " . $model['short_name'] . " (Table: " . ($model['table'] ?? 'unknown') . ")\n";
                }
                $doc .= "\n";
            }
            
            if (!empty($projectAnalysis['api_endpoints'])) {
                $doc .= "### API Endpoints\n\n";
                foreach ($projectAnalysis['api_endpoints'] as $endpoint) {
                    $doc .= "- **" . $endpoint['method'] . "** `" . $endpoint['uri'] . "`\n";
                }
                $doc .= "\n";
            }
        }
        
        $doc .= "## Note\n\n";
        $doc .= "This is a fallback documentation generated when AI services are unavailable. ";
        $doc .= "For more detailed and comprehensive documentation, please ensure your AI provider is properly configured.\n";
        
        return $doc;
    }

    public function getAvailableProviders()
    {
        return array_keys($this->config['providers']);
    }

    public function testConnection()
    {
        try {
            $testPrompt = "Hello, this is a test message. Please respond with 'Connection successful' if you can read this.";
            $response = $this->makeRequest($testPrompt, 'chat');
            return [
                'success' => true,
                'provider' => $this->provider,
                'model' => $this->model,
                'response' => $this->parseDocumentationResponse($response),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'provider' => $this->provider,
                'error' => $e->getMessage(),
            ];
        }
    }
} 