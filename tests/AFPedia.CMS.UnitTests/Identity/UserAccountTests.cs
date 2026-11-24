using AFPedia.CMS.Domain.Identity;
using Xunit;

namespace AFPedia.CMS.UnitTests.Identity;

public sealed class UserAccountTests
{
    [Fact]
    public void NewAccount_IsActive_AndTrimsTextFields()
    {
        var account = new UserAccount(
            Guid.NewGuid(),
            "  manager@example.org ",
            "  Organization Manager ",
            UserRole.OrganizationManager,
            DateTimeOffset.UtcNow);

        Assert.True(account.IsActive);
        Assert.Equal("manager@example.org", account.Email);
        Assert.Equal("Organization Manager", account.DisplayName);
        Assert.Equal(UserRole.OrganizationManager, account.Role);
    }

    [Fact]
    public void Account_CanBeDeactivatedAndReactivated()
    {
        var account = CreateAccount();

        account.Deactivate();
        Assert.False(account.IsActive);

        account.Activate();
        Assert.True(account.IsActive);
    }

    [Fact]
    public void Account_CanChangePredefinedRole()
    {
        var account = CreateAccount();

        account.ChangeRole(UserRole.Author);

        Assert.Equal(UserRole.Author, account.Role);
    }

    [Fact]
    public void Constructor_RejectsEmptyId()
    {
        Assert.Throws<ArgumentException>(() =>
            new UserAccount(
                Guid.Empty,
                "author@example.org",
                "Author",
                UserRole.Author,
                DateTimeOffset.UtcNow));
    }

    private static UserAccount CreateAccount() =>
        new(
            Guid.NewGuid(),
            "author@example.org",
            "Author",
            UserRole.Author,
            DateTimeOffset.UtcNow);
}
