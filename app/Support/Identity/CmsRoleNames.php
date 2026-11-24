<?php

declare(strict_types=1);

namespace App\Support\Identity;

final class CmsRoleNames
{
    public const ORGANIZATION_MANAGER = 'organization_manager';
    public const AUTHOR = 'author';

    /** @return list<string> */
    public static function all(): array
    {
        return [self::ORGANIZATION_MANAGER, self::AUTHOR];
    }
}
