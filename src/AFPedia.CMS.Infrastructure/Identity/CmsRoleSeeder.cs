using Microsoft.AspNetCore.Identity;
using Microsoft.Extensions.Logging;

namespace AFPedia.CMS.Infrastructure.Identity;

/// <summary>
/// Creates the predefined roles for a fresh installation.
/// Invoke from the installer after the identity database schema is available.
/// </summary>
public sealed class CmsRoleSeeder(
    RoleManager<CmsRole> roleManager,
    ILogger<CmsRoleSeeder> logger)
{
    public async Task SeedAsync(CancellationToken cancellationToken = default)
    {
        foreach (var roleName in CmsRoleNames.All)
        {
            cancellationToken.ThrowIfCancellationRequested();

            if (await roleManager.RoleExistsAsync(roleName))
            {
                continue;
            }

            var result = await roleManager.CreateAsync(new CmsRole(roleName));
            if (!result.Succeeded)
            {
                var errors = string.Join("; ", result.Errors.Select(error => error.Description));
                logger.LogError("Could not create predefined CMS role {RoleName}: {Errors}", roleName, errors);
                throw new InvalidOperationException(
                    $"Could not create predefined CMS role '{roleName}': {errors}");
            }
        }
    }
}
