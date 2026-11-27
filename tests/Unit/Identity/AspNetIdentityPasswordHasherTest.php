<?php

declare(strict_types=1);

namespace Tests\Unit\Identity;

use App\Support\Identity\AspNetIdentityPasswordHasher;
use PHPUnit\Framework\TestCase;

final class AspNetIdentityPasswordHasherTest extends TestCase
{
    public function test_verifies_identity_v3_sha256_hash(): void
    {
        $password = 'Legacy-Password-123';
        $salt = random_bytes(16);
        $iterations = 10000;
        $subkey = hash_pbkdf2('sha256', $password, $salt, $iterations, 32, true);
        $hash = base64_encode("\x01".pack('N3', 1, $iterations, strlen($salt)).$salt.$subkey);

        $hasher = new AspNetIdentityPasswordHasher();

        self::assertTrue($hasher->verify($hash, $password));
        self::assertFalse($hasher->verify($hash, 'incorrect'));
    }

    public function test_verifies_identity_v3_supported_prf_algorithms(): void
    {
        $password = 'Legacy-Password-123';
        $algorithms = [0 => 'sha1', 1 => 'sha256', 2 => 'sha512'];
        $hasher = new AspNetIdentityPasswordHasher();

        foreach ($algorithms as $prf => $algorithm) {
            $salt = random_bytes(16);
            $iterations = 12000;
            $subkey = hash_pbkdf2($algorithm, $password, $salt, $iterations, 32, true);
            $hash = base64_encode("\\x01".pack('N3', $prf, $iterations, strlen($salt)).$salt.$subkey);

            self::assertTrue($hasher->verify($hash, $password), "PRF {$algorithm} should verify.");
            self::assertFalse($hasher->verify($hash, 'incorrect'), "PRF {$algorithm} should reject a wrong password.");
        }
    }

    public function test_rejects_identity_v3_hashes_with_invalid_parameters(): void
    {
        $password = 'Legacy-Password-123';
        $salt = random_bytes(16);
        $subkey = hash_pbkdf2('sha256', $password, $salt, 10000, 32, true);
        $hasher = new AspNetIdentityPasswordHasher();

        $unsupportedPrf = base64_encode("\\x01".pack('N3', 99, 10000, strlen($salt)).$salt.$subkey);
        $zeroIterations = base64_encode("\\x01".pack('N3', 1, 0, strlen($salt)).$salt.$subkey);
        $shortSalt = base64_encode("\\x01".pack('N3', 1, 10000, 8).substr($salt, 0, 8).$subkey);

        self::assertFalse($hasher->verify($unsupportedPrf, $password));
        self::assertFalse($hasher->verify($zeroIterations, $password));
        self::assertFalse($hasher->verify($shortSalt, $password));
    }

    public function test_verifies_identity_v2_hash(): void
    {
        $password = 'Legacy-Password-123';
        $salt = random_bytes(16);
        $subkey = hash_pbkdf2('sha1', $password, $salt, 1000, 32, true);
        $hash = base64_encode("\x00".$salt.$subkey);

        self::assertTrue((new AspNetIdentityPasswordHasher())->verify($hash, $password));
    }

    public function test_rejects_malformed_or_unsupported_hashes(): void
    {
        $hasher = new AspNetIdentityPasswordHasher();

        self::assertFalse($hasher->verify('not-base64!', 'password'));
        self::assertFalse($hasher->verify(base64_encode("\x01".str_repeat("\x00", 12)), 'password'));
    }
}
