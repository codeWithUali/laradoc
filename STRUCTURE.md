# Laradoc Project Structure

This repository contains the **Laravel package** for Laradoc. The **website** is maintained in a separate repository.

## Project Structure

```
laradoc/                    # This repository - Laravel Package
├── src/                    # Package source code
├── resources/              # Package views and assets
├── routes/                 # Package routes
├── config/                 # Package configuration
├── database/               # Package migrations
├── tests/                  # Package tests
├── composer.json           # Package dependencies
└── README.md              # Package documentation

laradoc-website/           # Separate repository - Marketing Website
├── app/                   # Laravel application
├── resources/views/       # Website views
├── public/                # Website assets
├── routes/                # Website routes
└── README.md             # Website documentation
```

## Package vs Website

### Laradoc Package (`laradoc/`)
- **Purpose**: Laravel package that developers install in their projects
- **Functionality**: 
  - AI-powered documentation generation
  - Project analysis
  - Web interface for documentation
  - Search functionality
  - AI chatbot
- **Installation**: `composer require laradoc/laradoc`
- **Access**: `/laradoc` route in Laravel applications

### Laradoc Website (`laradoc-website/`)
- **Purpose**: Marketing website and documentation hub
- **Functionality**:
  - Product information
  - Installation guides
  - Pricing information
  - Contact forms
  - Company information
- **Access**: `https://laradoc.com`
- **Technology**: Laravel application with Bootstrap 5

## Development Workflow

### Package Development
```bash
cd laradoc
composer install
php artisan test
```

### Website Development
```bash
cd laradoc-website
composer install
php artisan serve
```

## Deployment

### Package
- Published to Packagist
- Available via Composer
- Versioned releases

### Website
- Deployed to hosting platform
- Custom domain: laradoc.com
- Continuous deployment

## Contributing

- **Package Issues**: Report in this repository
- **Website Issues**: Report in laradoc-website repository
- **Documentation**: Update website repository
- **Code**: Follow respective contribution guidelines

## Links

- **Package**: https://github.com/laradoc/laradoc
- **Website**: https://github.com/laradoc/laradoc-website
- **Live Website**: https://laradoc.com
- **Packagist**: https://packagist.org/packages/laradoc/laradoc 