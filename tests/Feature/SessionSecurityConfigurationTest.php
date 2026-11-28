<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class SessionSecurityConfigurationTest extends TestCase
{
    public function test_authentication_cookie_matches_the_cms_security_baseline(): void
    {
        self::assertSame('__Host-AFPedia.CMS.Auth', config('session.cookie'));
        self::assertSame('/', config('session.path'));
        self::assertNull(config('session.domain'));
        self::assertTrue(config('session.secure'));
        self::assertTrue(config('session.http_only'));
        self::assertSame('lax', config('session.same_site'));
        self::assertSame(480, config('session.lifetime'));
    }
}
