# Laradoc Project Structure

This document explains the structure and organization of the Laradoc project, which consists of two main components:

1. **Laradoc Package** - The actual Laravel package that users install
2. **Laradoc Website** - The marketing website for the package

## 📦 Laradoc Package

The Laradoc package is a comprehensive Laravel documentation generator that provides:

- **AI-powered analysis** of Laravel projects
- **Web interface** for viewing and editing documentation
- **Advanced search** capabilities
- **Multiple AI providers** support (OpenAI, Claude, Gemini)
- **Real-time collaboration** features

### Package Structure

```
laradoc/
├── src/
│   ├── LaradocServiceProvider.php
│   ├── Http/Controllers/
│   │   └── LaradocController.php
│   ├── Livewire/
│   │   ├── DocumentationComponent.php
│   │   ├── SearchComponent.php
│   │   └── ChatbotComponent.php
│   ├── Services/
│   │   ├── DocumentationGenerator.php
│   │   ├── ProjectAnalyzer.php
│   │   ├── AIService.php
│   │   └── SearchService.php
│   └── Console/
│       ├── GenerateDocumentationCommand.php
│       └── AnalyzeProjectCommand.php
├── resources/
│   └── views/
│       ├── layouts/
│       ├── livewire/
│       └── *.blade.php
├── public/
│   └── vendor/laradoc/
│       ├── css/
│       └── js/
├── config/
│   └── laradoc.php
├── database/
│   └── migrations/
├── tests/
├── composer.json
└── README.md
```

### Installation

Users install the package via Composer:

```bash
composer require codewithuali/laradoc
```

### Configuration

The package publishes configuration files and assets:

```bash
php artisan vendor:publish --provider="Laradoc\Laradoc\LaradocServiceProvider"
```

### Access

- **Package**: https://github.com/codewithuali/laradoc
- **Website**: https://github.com/codewithuali/laradoc-website
- **Live Website**: https://umair.lu/community/laradoc
- **Packagist**: https://packagist.org/packages/codewithuali/laradoc

## 🌐 Laradoc Website

The Laradoc website is a separate Laravel application that serves as the marketing and documentation site for the package.

### Website Structure

```
laradoc-website/
├── app/
│   └── Http/Controllers/
│       └── WebsiteController.php
├── resources/
│   └── views/website/
│       ├── layout.blade.php
│       ├── home.blade.php
│       ├── features.blade.php
│       ├── documentation.blade.php
│       ├── pricing.blade.php
│       ├── about.blade.php
│       └── contact.blade.php
├── public/
│   ├── css/
│   └── js/
├── routes/
│   └── web.php
└── README.md
```

### Features

- **Modern Design**: Bootstrap 5 with custom styling
- **Responsive Layout**: Works on all devices
- **SEO Optimized**: Proper meta tags and structure
- **Contact Forms**: Functional contact and support forms
- **Documentation**: Complete installation and usage guides

### Pages

1. **Home** (`/`) - Landing page with hero section and features
2. **Features** (`/features`) - Detailed feature overview
3. **Documentation** (`/docs`) - Installation and usage guides
4. **Pricing** (`/pricing`) - Pricing plans and comparison
5. **About** (`/about`) - Team and company information
6. **Contact** (`/contact`) - Contact form and support information

## 🔄 Development Workflow

### Package Development

1. **Make changes** to the package code
2. **Test locally** using a test Laravel application
3. **Update version** in `composer.json`
4. **Commit and push** to GitHub
5. **Create release** on GitHub
6. **Update Packagist** (if published)

### Website Development

1. **Make changes** to the website code
2. **Test locally** using `php artisan serve`
3. **Commit and push** to GitHub
4. **Deploy** to production server

## 🚀 Deployment

### Package Deployment

The package is automatically available via Composer once pushed to GitHub and published on Packagist.

### Website Deployment

The website can be deployed to any Laravel-compatible hosting service:

- **Shared Hosting**: Upload files and configure
- **VPS/Dedicated**: Use Laravel deployment scripts
- **Cloud Platforms**: Deploy to Heroku, DigitalOcean, etc.

## 📚 Documentation

- **Package Documentation**: Available in the package README
- **Website Documentation**: Available on the live website
- **API Documentation**: Generated automatically by the package
- **Contributing Guide**: Available in the package repository

## 🤝 Contributing

Both the package and website welcome contributions:

1. **Fork** the repository
2. **Create** a feature branch
3. **Make** your changes
4. **Test** thoroughly
5. **Submit** a pull request

## 📞 Support

- **GitHub Issues**: For bug reports and feature requests
- **Website Contact**: For general inquiries and support
- **Documentation**: For installation and usage help 