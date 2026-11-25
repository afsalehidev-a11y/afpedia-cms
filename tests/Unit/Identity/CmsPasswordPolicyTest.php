<?php

declare(strict_types=1);

namespace Tests\Unit\Identity;

use App\Support\Identity\CmsPasswordPolicy;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class CmsPasswordPolicyTest extends TestCase
{
    #[DataProvider('invalidPasswords')]
    public function test_it_rejects_passwords_that_do_not_match_csharp_identity_policy(string $password): void
    {
        $validator = Validator::make(['password' => $password], ['password' => CmsPasswordPolicy::rules()]);
        self::assertTrue($validator->fails());
    }

    public function test_it_accepts_a_long_password_with_uppercase_lowercase_and_digit(): void
    {
        $validator = Validator::make(
            ['password' => 'CulturalCms2026'],
            ['password' => CmsPasswordPolicy::rules()],
        );

        self::assertFalse($validator->fails());
    }

    public static function invalidPasswords(): array
    {
        return [
            'too short' => ['Short1Aa'],
            'no digit' => ['CulturalCmsPass'],
            'no uppercase' => ['culturalcms2026'],
            'no lowercase' => ['CULTURALCMS2026'],
        ];
    }
}
