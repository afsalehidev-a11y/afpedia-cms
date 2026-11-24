namespace AFPedia.CMS.Infrastructure.Identity;

public static class CmsRoleNames
{
    public const string OrganizationManager = "OrganizationManager";
    public const string Author = "Author";

    public static readonly IReadOnlyList<string> All =
    [
        OrganizationManager,
        Author
    ];
}
