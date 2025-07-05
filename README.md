# Laradoc - Intelligent Laravel Documentation Generator

> **Visit our website**: [https://laradoc.com](https://laradoc.com)

Laradoc is an intelligent Laravel documentation generator that automatically analyzes your Laravel project and generates comprehensive documentation using AI-powered insights. Save hours of manual work and keep your documentation always up-to-date.

## 🌟 Features

- **🤖 AI-Powered Analysis**: Automatically analyze your Laravel project structure using advanced AI algorithms
- **🔍 Advanced Search**: Lightning-fast search with Meilisearch integration and database fallback
- **💬 AI Chatbot**: Ask questions about your codebase and get intelligent, contextual answers
- **✏️ Live Editing**: Edit documentation directly in the web interface with real-time preview
- **🎨 Modern UI**: Beautiful Bootstrap 5 interface with responsive design
- **🔧 Multiple AI Providers**: Support for OpenAI GPT-4, Claude, and Gemini
- **📱 Responsive Design**: Works perfectly on all devices
- **⚡ High Performance**: Optimized for speed with caching and efficient queries

## 🚀 Quick Start

### Installation

```bash
composer require laradoc/laradoc
```

### Publish Configuration

```bash
php artisan vendor:publish --provider="Laradoc\Laradoc\LaradocServiceProvider"
```

### Run Migrations

```bash
php artisan migrate
```

### Configure AI Provider

Add your AI provider API key to your `.env` file:

```env
# AI Provider Configuration
LARADOC_AI_PROVIDER=openai

# OpenAI Configuration
OPENAI_API_KEY=your_openai_api_key
OPENAI_MODEL=gpt-4

# Or Claude
ANTHROPIC_API_KEY=your_claude_api_key
CLAUDE_MODEL=claude-3-sonnet-20240229

# Or Gemini
GOOGLE_API_KEY=your_gemini_api_key
GEMINI_MODEL=gemini-pro
```

### Generate Documentation

```bash
php artisan laradoc:generate
```

### Access the Interface

Visit `/laradoc` in your browser to access the documentation interface.

## 📖 Documentation

For detailed documentation, visit our website: [https://laradoc.com/docs](https://laradoc.com/docs)

### What Gets Documented

Laradoc automatically generates documentation for:

- **Authentication & Authorization**: Guards, policies, gates, middleware
- **API Documentation**: Controllers, routes, requests, responses
- **Database & Models**: Eloquent models, relationships, migrations
- **Frontend & Views**: Blade templates, components, assets
- **Services & Providers**: Service providers, facades, helpers
- **Configuration**: App config, environment variables
- **Testing**: Test files, test coverage
- **Deployment**: Deployment scripts, server configurations

## 🎯 Use Cases

- **Development Teams**: Keep documentation in sync with code changes
- **Open Source Projects**: Automatically generate comprehensive docs
- **Client Projects**: Provide detailed documentation for clients
- **Code Reviews**: Understand project structure quickly
- **Onboarding**: Help new developers understand the codebase
- **Maintenance**: Keep documentation updated as projects evolve

## 🛠️ Configuration

### AI Provider Settings

```php
// config/laradoc.php
'ai' => [
    'provider' => env('LARADOC_AI_PROVIDER', 'openai'), // openai, claude, gemini
    'model' => env('LARADOC_AI_MODEL', 'gpt-4'),
    'temperature' => env('LARADOC_AI_TEMPERATURE', 0.7),
    'max_tokens' => env('LARADOC_AI_MAX_TOKENS', 4000),
],
```

### Search Settings

```php
'search' => [
    'driver' => env('LARADOC_SEARCH_DRIVER', 'database'), // database, meilisearch
    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
    ],
],
```

### Documentation Settings

```php
'documentation' => [
    'modules' => [
        'authentication' => true,
        'api' => true,
        'database' => true,
        'frontend' => true,
        'services' => true,
        'configuration' => true,
        'testing' => true,
        'deployment' => true,
    ],
    'auto_generate' => env('LARADOC_AUTO_GENERATE', true),
    'update_frequency' => env('LARADOC_UPDATE_FREQUENCY', 'daily'),
],
```

## 🌐 Website

Visit our official website for:

- **📋 Feature Overview**: Detailed feature descriptions and demos
- **📚 Documentation**: Complete installation and usage guides
- **💰 Pricing**: Transparent pricing plans
- **👥 About Us**: Learn about our team and mission
- **📞 Contact**: Get support and ask questions
- **🎯 Use Cases**: See how others are using Laradoc

**Website**: [https://laradoc.com](https://laradoc.com)

## 🔧 Commands

### Generate Documentation

```bash
php artisan laradoc:generate
```

### Analyze Project Structure

```bash
php artisan laradoc:analyze
```

### Clear Documentation Cache

```bash
php artisan laradoc:clear
```

## 🎨 Customization

### Custom Views

Publish the views to customize the interface:

```bash
php artisan vendor:publish --tag=laradoc-views
```

### Custom CSS/JS

Add your own styles and scripts:

```bash
php artisan vendor:publish --tag=laradoc-assets
```

### Custom Modules

Extend Laradoc with your own documentation modules:

```php
// app/Providers/AppServiceProvider.php
public function boot()
{
    Laradoc::addModule('custom', [
        'name' => 'Custom Module',
        'description' => 'Your custom documentation module',
        'analyzer' => CustomAnalyzer::class,
        'generator' => CustomGenerator::class,
    ]);
}
```

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](https://github.com/laradoc/laradoc/blob/main/CONTRIBUTING.md) for details.

### Development Setup

```bash
git clone https://github.com/laradoc/laradoc.git
cd laradoc
composer install
npm install
npm run dev
```

### Running Tests

```bash
composer test
```

## 📄 License

Laradoc is open-sourced software licensed under the [MIT license](https://github.com/laradoc/laradoc/blob/main/LICENSE).

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The amazing PHP framework
- [OpenAI](https://openai.com) - For GPT-4 integration
- [Anthropic](https://anthropic.com) - For Claude integration
- [Google AI](https://ai.google.dev) - For Gemini integration
- [Meilisearch](https://meilisearch.com) - For search functionality
- [Bootstrap](https://getbootstrap.com) - For the beautiful UI
- [Livewire](https://laravel-livewire.com) - For reactive components

## 📞 Support

- **Website**: [https://laradoc.com](https://laradoc.com)
- **Documentation**: [https://laradoc.com/docs](https://laradoc.com/docs)
- **GitHub Issues**: [https://github.com/laradoc/laradoc/issues](https://github.com/laradoc/laradoc/issues)
- **Discord**: [https://discord.gg/laradoc](https://discord.gg/laradoc)
- **Email**: support@laradoc.com

## ⭐ Star History

[![Star History Chart](https://api.star-history.com/svg?repos=laradoc/laradoc&type=Date)](https://star-history.com/#laradoc/laradoc&Date)

---

Made with ❤️ for the Laravel community 