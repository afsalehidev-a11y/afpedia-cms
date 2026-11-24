using AFPedia.CMS.Infrastructure.Identity;
using Microsoft.AspNetCore.Identity.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore;

namespace AFPedia.CMS.Infrastructure.Persistence;

public sealed class CmsIdentityDbContext(
    DbContextOptions<CmsIdentityDbContext> options)
    : IdentityDbContext<CmsUser, CmsRole, Guid>(options)
{
    protected override void OnModelCreating(ModelBuilder builder)
    {
        base.OnModelCreating(builder);

        builder.Entity<CmsUser>(entity =>
        {
            entity.ToTable("users");
            entity.Property(user => user.DisplayName)
                .HasMaxLength(160)
                .IsRequired();
            entity.Property(user => user.IsActive).IsRequired();
            entity.Property(user => user.CreatedAtUtc).IsRequired();
        });

        builder.Entity<CmsRole>().ToTable("roles");
        builder.Entity<Microsoft.AspNetCore.Identity.IdentityUserClaim<Guid>>()
            .ToTable("user_claims");
        builder.Entity<Microsoft.AspNetCore.Identity.IdentityUserRole<Guid>>()
            .ToTable("user_roles");
        builder.Entity<Microsoft.AspNetCore.Identity.IdentityUserLogin<Guid>>()
            .ToTable("user_logins");
        builder.Entity<Microsoft.AspNetCore.Identity.IdentityRoleClaim<Guid>>()
            .ToTable("role_claims");
        builder.Entity<Microsoft.AspNetCore.Identity.IdentityUserToken<Guid>>()
            .ToTable("user_tokens");
    }
}
