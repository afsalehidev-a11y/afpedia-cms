# Deployment and hosting requirements

AFPedia CMS targets common PHP shared-hosting plans. Compatibility is not guaranteed for literally every provider; publish a tested minimum matrix before release.

## MVP platform baseline
- PHP 8.2 or newer, with required extensions checked by installer.
- MySQL 8+ or MariaDB 10.6+ (minimums subject to provider testing).
- HTTPS support.
- Writable application storage, cache, and session directories.
- Document root points to public/, or a documented safe layout keeps source code and .env outside public web access.
- PHP CLI, SSH, cron, and Composer are not assumed available to the customer.

## Release packaging and installation
CI should produce a release archive with production dependencies included. Browser installer checks PHP version, extensions, DB connection, storage permissions, and application key. It must never expose .env, source, logs, or backups through the public document root.

1. Upload prepared release package with hosting file manager.
2. Follow browser installer.
3. Enter database credentials and organization details.
4. Create the first Organization Manager account; no default password is shipped.
5. Run schema migrations through a locked, one-time installer operation.
6. Verify the site and disable installer access.

Shared hosting differs widely. Queue workers, cron, symlinks, shell access, atomic deployments, and long-running processes may be unavailable. MVP features need synchronous or file/database-backed fallbacks where practical. Installer reports unsupported requirements clearly.
