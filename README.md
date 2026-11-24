# AFPedia CMS

Open-source, independently deployable content management system for cultural organizations.

This repository contains AFPedia CMS only. Each organization runs an independent installation, database, media storage, configuration, and backups. The central AFPedia website and central multi-tenant management platform are out of scope.

## Technology
- PHP 8.2+
- Laravel 12
- MySQL 8+ / MariaDB 10.6+ initial targets
- Modular Monolith
- Browser-based installer as a core MVP requirement

## Development
Requires PHP, Composer, and Laravel's required PHP extensions.

    cp .env.example .env
    composer install
    php artisan key:generate
    php artisan migrate
    php artisan test
    php artisan serve

The first manager account must be created by the installer. Never ship a default production password.

## Hosting
The goal is compatibility with common PHP shared hosting, not an unsupported claim that every provider works. Publish the supported PHP/database/extension matrix only after real provider testing.
