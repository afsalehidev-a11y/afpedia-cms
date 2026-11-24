# ADR-0002: PHP and Laravel are the sole implementation platform

- Status: Accepted
- Date: 2026-11-24

PHP with Laravel is the approved and sole implementation platform for AFPedia CMS. C# and ASP.NET Core are not part of the target architecture. The earlier .NET prototype is retained only in Git history until the PHP replacement is reviewed; it is not a supported product path.

The target remains a modular monolith with independently deployed installations, PHP shared-hosting compatibility as an MVP goal, MySQL/MariaDB as initial database targets, and a browser-based installer that does not require customers to run Composer or use SSH.
