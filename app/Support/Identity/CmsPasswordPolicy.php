<?php

declare(strict_types=1);

namespace App\Support\Identity;

final class CmsPasswordPolicy
{
    /** @return list<string> */
    public static function rules(): array
    {
        return [
            'required',
            'string',
            'min:12',
            'regex:/[a-z]/',
            'regex:/[A-Z]/',
            'regex:/[0-9]/',
        ];
    }
}
