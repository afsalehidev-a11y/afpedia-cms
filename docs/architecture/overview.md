# Architecture Overview

## Deployment boundary

AFPedia CMS is installed independently for each organization. Each installation owns its application runtime, database, uploaded media, configuration, and backups. There is no central runtime, tenant registry, or required persistent connection to AFPedia.

## Architectural style

The MVP uses a Modular Monolith. Modules are separated by business responsibility while being deployed as one application. Microservices are not part of the MVP.

## Initial technology choices

- C# and ASP.NET Core on .NET 10
- PostgreSQL as the planned relational database
- Entity Framework Core as the planned data access layer
- REST API under `/api/v1`
- React + TypeScript for the administration interface (planned)

## Initial module boundaries

- Identity and Access
- Content and Revisions
- Workflow and Publishing
- Localization
- Media
- Navigation
- Themes
- Plugins
- SEO and Routing
- Search
- Notifications
- Audit
- Installation and Updates
- Backup and Export

These are logical module boundaries, not separate deployable services.

## Update integrity

Official core updates are available only while the core matches an approved official release. Local content, settings, themes, and plugins do not by themselves disable core updates. A modification to core code suspends official updates until the original official core is restored and integrity is verified.
