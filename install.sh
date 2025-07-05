#!/bin/bash

# Laradoc Package Installation Script
echo "🚀 Installing Laradoc Package..."

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo "❌ Error: This doesn't appear to be a Laravel project (no artisan file found)"
    echo "Please run this script from your Laravel project root directory"
    exit 1
fi

# Check if composer is available
if ! command -v composer &> /dev/null; then
    echo "❌ Error: Composer is not installed or not in PATH"
    echo "Please install Composer first: https://getcomposer.org/download/"
    exit 1
fi

# Install the package
echo "📦 Installing Laradoc package..."
composer require laradoc/laradoc

# Publish configuration
echo "⚙️  Publishing configuration..."
php artisan vendor:publish --provider="Laradoc\Laradoc\LaradocServiceProvider" --tag="laradoc"

# Run migrations (if using database search)
echo "🗄️  Running migrations..."
php artisan migrate

# Create storage directory
echo "📁 Creating storage directory..."
mkdir -p storage/app/laradoc

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage/app/laradoc

echo ""
echo "✅ Laradoc package installed successfully!"
echo ""
echo "📋 Next steps:"
echo "1. Configure your AI provider in .env file:"
echo "   LARADOC_AI_PROVIDER=openai"
echo "   OPENAI_API_KEY=your_api_key"
echo ""
echo "2. Configure search (optional):"
echo "   LARADOC_SEARCH_DRIVER=meilisearch"
echo "   MEILISEARCH_HOST=http://localhost:7700"
echo ""
echo "3. Generate documentation:"
echo "   php artisan laradoc:generate"
echo ""
echo "4. Access the web interface:"
echo "   http://your-app.com/laradoc"
echo ""
echo "📚 For more information, visit: https://github.com/laradoc/laradoc" 