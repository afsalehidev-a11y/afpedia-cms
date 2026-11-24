# Identity and access (initial implementation)

## Scope

Each independent CMS installation owns its own user accounts, role records, authentication cookies, and identity database tables. There is no central AFPedia account or shared identity service.

## Roles in v1

- `OrganizationManager`: manages the local installation, users, settings, themes, and plugins.
- `Author`: creates and manages content within the permissions defined by the CMS.
- Public visitors are anonymous users; they do not need CMS accounts.

Custom role creation is not part of v1.

## Implementation

- ASP.NET Core Identity handles password hashing, sign-in, lockout, and user security stamps.
- Entity Framework Core with PostgreSQL persists identity data.
- User IDs and role IDs are GUIDs.
- Authentication uses an HTTP-only, secure application cookie.
- Authorization must be enforced on the server for every protected operation.
- The identity database context is isolated in Infrastructure; API startup registers it through `AddCmsInfrastructure`.

## Initial security settings

- Unique email address per local installation.
- Minimum password length: 12 characters.
- Failed sign-in lockout after 5 attempts for 15 minutes.
- Cookie marked HttpOnly, Secure, and SameSite=Lax.
- HTTPS is required for production because the authentication cookie is Secure.

## Configuration

Set `ConnectionStrings:CmsDatabase` using environment-specific configuration or environment variables. An example (with placeholder credentials only) is provided in `src/AFPedia.CMS.Api/appsettings.example.json`.

## Not implemented yet

- Login/logout HTTP endpoints and anti-forgery strategy.
- Initial manager creation during installation.
- Seeding the two predefined roles.
- User-management endpoints and role assignment policies.
- Database migrations and integration tests.
- Recovery flows such as password reset.

These are follow-up implementation tasks; this document does not claim they are available yet.
