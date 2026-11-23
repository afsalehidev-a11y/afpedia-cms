# AFPedia CMS

Open-source, independently deployable content management system for cultural organizations.

> **Scope:** This repository contains the AFPedia CMS only. Each organization runs its own installation on infrastructure it owns or procures. The central AFPedia website and any central multi-tenant management platform are out of scope.

## Technology
- Backend: C# / ASP.NET Core (.NET 10)
- Architecture: Modular Monolith
- Database: PostgreSQL (planned)
- API: REST, versioned under `/api/v1`
- Admin frontend: React + TypeScript (planned)

## Deployment model
Every organization has an independent CMS installation, database, media storage, and backups. After initial installation and handover, the organization is responsible for its server and ongoing infrastructure operations.

## Initial status
The repository skeleton is being established. Runnable setup instructions will be added with the first application implementation.
