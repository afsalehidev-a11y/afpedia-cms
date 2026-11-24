using AFPedia.CMS.Domain.Identity;

namespace AFPedia.CMS.Application.Identity;

/// <summary>
/// Persistence boundary for organization-local user accounts.
/// Implementations belong to the infrastructure layer.
/// </summary>
public interface IUserAccountRepository
{
    Task<UserAccount?> GetByIdAsync(
        Guid id,
        CancellationToken cancellationToken = default);

    Task<UserAccount?> GetByEmailAsync(
        string normalizedEmail,
        CancellationToken cancellationToken = default);

    Task AddAsync(
        UserAccount account,
        CancellationToken cancellationToken = default);

    Task UpdateAsync(
        UserAccount account,
        CancellationToken cancellationToken = default);
}
