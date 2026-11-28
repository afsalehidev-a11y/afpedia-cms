<?php

declare(strict_types=1);

namespace App\Support\Identity;

final class CmsAuthorizationPolicies
{
    // Keep the policy identifiers aligned with the C# authorization contract.
    public const MANAGER_ONLY = 'ManagerOnly';
    public const CONTENT_EDITORS = 'ContentEditors';
}
