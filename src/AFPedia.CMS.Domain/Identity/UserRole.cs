namespace AFPedia.CMS.Domain.Identity;

/// <summary>
/// Predefined roles available in the first CMS release.
/// Anonymous public visitors are not represented as user accounts.
/// </summary>
public enum UserRole
{
    OrganizationManager = 1,
    Author = 2
}
