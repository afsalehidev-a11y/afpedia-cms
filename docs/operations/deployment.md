# Deployment requirements

AFPedia CMS is a standalone ASP.NET Core application. Each organization runs its own installation and database.

## Supported hosting model

The primary deployment target is a customer-managed Linux VPS or dedicated server. A Windows Server deployment is also possible. Ordinary PHP-only shared hosting is not sufficient: the host must allow an ASP.NET Core process to run continuously and provide access to PostgreSQL.

The application can be published as a self-contained deployment so the customer server does not need a separately installed .NET runtime. The published output is still specific to an operating system and CPU architecture.

## Initial Linux publish

From the repository root, using the .NET 10 SDK:

```bash
dotnet publish src/AFPedia.CMS.Api/AFPedia.CMS.Api.csproj \
  --configuration Release \
  --runtime linux-x64 \
  --self-contained true \
  -p:UseAppHost=true \
  -o ./artifacts/publish/linux-x64
```

For ARM64 Linux servers, use `--runtime linux-arm64`. For Windows Server, use the matching Windows RID, for example `win-x64`. Confirm the customer's OS and CPU architecture before choosing the publish target.

## Server components

A production installation needs:
- A supported Linux or Windows server where the ASP.NET Core process can run.
- PostgreSQL, either on the same server or on a separately managed database server reachable over a private network.
- Persistent writable storage for uploaded media and application data.
- HTTPS termination and a reverse proxy such as Nginx on Linux, or an equivalent supported web-server configuration.
- A process supervisor/service manager so the CMS restarts after a server reboot and can be monitored by the customer.

## Configuration and secrets

Set the production database connection string through the environment variable `ConnectionStrings__CmsDatabase` or the deployment's secret-management facility. Do not commit production credentials to Git or place them in the example settings file.

The design-time EF Core factory contains only a non-production placeholder connection string for generating migrations; it is not a production default.

## Hosting limitations

PHP-only shared hosting without support for long-running ASP.NET Core applications is unsupported. Before accepting a customer hosting environment, verify OS, CPU architecture, ability to run a managed service, PostgreSQL connectivity, HTTPS/reverse-proxy support, writable persistent storage, and backup/restore access.

Minimum CPU, RAM, storage, and production capacity figures remain to be established through representative load testing; this document does not claim unmeasured capacity or an SLA.
