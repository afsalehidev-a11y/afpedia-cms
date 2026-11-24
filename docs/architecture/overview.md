# Architecture overview

AFPedia CMS is open-source software installed independently on each organization's hosting account or server. This repository contains the CMS only, not the central AFPedia website.

## Platform
- PHP 8.2+ and Laravel 12.
- MySQL 8+ or MariaDB 10.6+ are initial targets; validate against actual shared-hosting providers before release.
- Modular Monolith: one deployable application organized into bounded modules.
- Browser-based installation is a core requirement; customers must not need SSH or Composer.
- Each installation owns its database, files, configuration, and backups. No central connection is required after handover.

## Modules
Identity & Access; Content and Revisions; Workflow and Publication; Localization; Media; Navigation; Themes; Plugins; SEO and Routing; Search; Notifications; Audit; Installer and Updater; Backup and Export.

Modules communicate through application services and explicit contracts. Avoid direct cross-module table manipulation when a service contract is appropriate.

## Content and localization
A content record is the shared identity. Each locale has its own translation record, slug, SEO fields, and publication state. Persian (fa) is default; English (en) is enabled for MVP. Future locales are data-driven.

## Roles
Organization Manager and Author are the MVP roles. Authorization is enforced server-side.

## Future multi-tenancy
Multi-tenancy is not part of MVP. Keep organization-specific concerns behind services and data-access boundaries. Do not add a tenant identifier to independent installations. Future shared-hosting topology remains undecided.

## Security and updates
Use Laravel password hashing, CSRF protection, validation, escaped templates, and authorization policies. Apply upload allowlists and size limits. Official updates require integrity verification, backup, staging checks where hosting permits, and rollback. A modified core is ineligible for official updates until restored and verified.
