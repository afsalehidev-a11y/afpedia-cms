<?php

declare(strict_types=1);

namespace Tests\\Unit\\Identity;

use App\\Support\\Identity\\AspNetIdentityPasswordHasher;
use PHPUnit\\Framework\\TestCase;

final class AspNetIdentityPasswordHasherTest extends TestCase
{
    public function test_verifies_identity_v3_sha256_hash(): void
    {
        $password = 'Legacy-Password-123';
        $salt = random_bytes(16);
        $iterations = 10000;
        $subkey = hash_pbkdf2('sha256', $password, $salt, $iterations, 32, true);
        $hash = base64_encode("\\x01".pack('N3', 1, $iterations, strlen($salt)).$salt.$subkey);

        $hasher = new AspNetIdentityPasswordHasher();

        self::assertTrue($hasher->verify($hash, $password));
        self::assertFalse($hasher->verify($hash, 'incorrect'));
    }

    public function test_verifies_identity_v2_hash(): void
    {
        $password = 'Legacy-Password-123';
        $salt = random_bytes(16);
        $subkey = hash_pbkdf2('sha1', $password, $salt, 1000, 32, true);
        $hash = base64_encode("\\x00".$salt.$subkey);

        self::assertTrue((new AspNetIdentityPasswordHasher())->verify($hash, $password));
    }

    public function test_rejects_malformed_or_unsupported_hashes(): void
    {
        $hasher = new AspNetIdentityPasswordHasher();

        self::assertFalse($hasher->verify('not-base64!', 'password'));
        self::assertFalse($hasher->verify(base64_encode("\\x01".str_repeat("\\x00", 12)), 'password'));
    }
}
