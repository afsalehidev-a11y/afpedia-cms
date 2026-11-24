using Microsoft.AspNetCore.Identity;

namespace AFPedia.CMS.Infrastructure.Identity;

public sealed class CmsRole : IdentityRole<Guid>
{
    public CmsRole() { }

    public CmsRole(string roleName) : base(roleName) { }
}
