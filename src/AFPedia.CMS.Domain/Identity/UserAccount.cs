namespace AFPedia.CMS.Domain.Identity;

/// <summary>
/// Domain representation of a user account. Credential storage and
/// authentication mechanisms belong to the infrastructure layer.
/// </summary>
public sealed class UserAccount
{
    public Guid Id { get; }
    public string Email { get; private set; }
    public string DisplayName { get; private set; }
    public UserRole Role { get; private set; }
    public bool IsActive { get; private set; }
    public DateTimeOffset CreatedAtUtc { get; }

    public UserAccount(
        Guid id,
        string email,
        string displayName,
        UserRole role,
        DateTimeOffset createdAtUtc)
    {
        if (id == Guid.Empty)
            throw new ArgumentException("User ID cannot be empty.", nameof(id));

        Id = id;
        Email = RequireText(email, nameof(email));
        DisplayName = RequireText(displayName, nameof(displayName));

        if (!Enum.IsDefined(role))
            throw new ArgumentOutOfRangeException(nameof(role));

        Role = role;
        CreatedAtUtc = createdAtUtc;
        IsActive = true;
    }

    public void ChangeRole(UserRole role)
    {
        if (!Enum.IsDefined(role))
            throw new ArgumentOutOfRangeException(nameof(role));

        Role = role;
    }

    public void ChangeDisplayName(string displayName) =>
        DisplayName = RequireText(displayName, nameof(displayName));

    public void Deactivate() => IsActive = false;

    public void Activate() => IsActive = true;

    private static string RequireText(string value, string parameterName)
    {
        if (string.IsNullOrWhiteSpace(value))
            throw new ArgumentException("Value is required.", parameterName);

        return value.Trim();
    }
}
