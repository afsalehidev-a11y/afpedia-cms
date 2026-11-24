# ADR-0001: Adopt PHP and Laravel for the CMS

- Status: Accepted
- Date: 2026-11-24
- Decision owners: AFPedia CMS project

## Context

The CMS must be straightforward to develop and maintain, approachable for a broad developer community, and deployable by organizations with limited technical knowledge. A key MVP hosting target is conventional PHP shared hosting. The project may later offer a centrally hosted multi-tenant edition, but the first release remains independently installable by each organization.

The existing repository contains an early C# / ASP.NET Core and PostgreSQL prototype. That prototype assumes a managed application process and PostgreSQL access, which excludes many PHP-only shared-hosting plans.

## Decision

Use PHP with Laravel as the application framework. Keep the application as a modular monolith.

For MVP:
- Support common PHP shared-hosting environments with MySQL or MariaDB, subject to a published and tested minimum platform matrix.
- Provide a browser-based installer and setup wizard; do not require the customer to use SSH or run Composer on the hosting account.
- Package production dependencies in the release artifact where the host does not provide Composer.
- Keep each organization's installation, database, uploaded files, configuration, and backups independent.
- Do not build a central multi-tenant control plane as part of the MVP.

## Future multi-tenancy readiness

Do not implement multi-tenancy prematurely. Instead:
- Keep organization-specific business logic behind application services and repositories.
- Avoid assuming that one process or one database is the only possible deployment topology.
- Centralize authorization and data access; never rely on UI filtering for tenant isolation.
- Avoid hard-coding filesystem paths, domains, or global settings as if only one organization can ever exist.
- Document tenant ownership boundaries in the domain model.
- Revisit shared-database (tenant_id), database-per-tenant, and hybrid models when a multi-tenant product is actually approved.

The future topology is intentionally undecided. This ADR does not select a shared database or a database-per-tenant model.

## Consequences

Positive:
- Better fit for conventional PHP shared hosting.
- Broad pool of PHP developers and mature Laravel ecosystem.
- Faster path to a guided installation experience for non-specialist administrators.

Trade-offs:
- The existing .NET implementation is a prototype and must not be treated as the production foundation.
- A Laravel release must be tested against representative shared-hosting providers; compatibility with literally every host cannot be guaranteed.
- Long-running jobs, scheduled tasks, cache drivers, filesystem permissions, and update mechanisms must have fallbacks suitable for restricted hosting.
- The installer and release packaging become first-class product components.

## Migration approach

1. Preserve the existing .NET work in Git history and on the default branch until the PHP replacement is validated.
2. Build the Laravel foundation on the migration/php-laravel branch.
3. Establish the supported PHP/MySQL/MariaDB hosting matrix and verify it with real deployment tests.
4. Implement and test the browser installer before claiming non-technical installation.
5. Replace the default branch only after the PHP application, CI, and deployment checks are usable.

## Out of scope for this decision

- Central multi-tenant hosting in MVP.
- A central AFPedia management or monitoring service.
- Final choice of future tenant database topology.
- A guarantee that the CMS runs on every shared-hosting provider.
