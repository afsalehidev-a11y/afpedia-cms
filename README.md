# AFPedia CMS

Open-source, independently deployable content management system for cultural organizations.

> **Scope:** This repository contains the AFPedia CMS only. Each organization runs its own installation on infrastructure it owns or procures. The central AFPedia website and any central multi-tenant management platform are out of scope.

## Technology
- Backend: C# / ASP.NET Core (.NET 10)
- Architecture: Modular Monolith
- Database: PostgreSQL (planned)
- API: REST, versioned under `/api/v1`
- Admin frontend: React + TypeScript (planned)

## Solution layout

- `src/AFPedia.CMS.Api` — ASP.NET Core host and HTTP endpoints
- `src/AFPedia.CMS.Domain` — domain model and business invariants
- `src/AFPedia.CMS.Application` — use cases and application contracts
- `src/AFPedia.CMS.Infrastructure` — persistence and external integrations
- `docs/` — architecture and operational documentation

The projects are logical layers within one deployable application, not separate services.

## Deployment model
Every organization has an independent CMS installation, database, media storage, and backups. After initial installation and handover, the organization is responsible for its server and ongoing infrastructure operations.

## Build
Requires the .NET 10 SDK.

```bash
dotnet restore AFPedia.CMS.slnx
dotnet build AFPedia.CMS.slnx
dotnet run --project src/AFPedia.CMS.Api
```

The initial API exposes `GET /health`. Database integration, migrations, and automated tests are subsequent implementation steps.
