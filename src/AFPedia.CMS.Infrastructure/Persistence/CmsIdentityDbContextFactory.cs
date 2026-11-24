using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Design;

namespace AFPedia.CMS.Infrastructure.Persistence;

public sealed class CmsIdentityDbContextFactory
    : IDesignTimeDbContextFactory<CmsIdentityDbContext>
{
    public CmsIdentityDbContext CreateDbContext(string[] args)
    {
        var connectionString =
            Environment.GetEnvironmentVariable("ConnectionStrings__CmsDatabase")
            ?? "Host=localhost;Database=afpedia_cms_design;Username=postgres;Password=design-time-only";

        var options = new DbContextOptionsBuilder<CmsIdentityDbContext>()
            .UseNpgsql(connectionString)
            .Options;

        return new CmsIdentityDbContext(options);
    }
}
