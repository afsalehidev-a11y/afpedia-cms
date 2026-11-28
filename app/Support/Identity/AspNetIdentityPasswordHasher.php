<?php

declare(strict_types=1);

namespace App\Support\Identity;

final class AspNetIdentityPasswordHasher
{
    public function verify(string $encodedHash, string $password): bool
    {
        $decoded = base64_decode($encodedHash, true);

        if ($decoded === false || $decoded === '') {
            return false;
        }

        $format = ord($decoded[0]);

        if ($format === 0x00) {
            return $this->verifyV2($decoded, $password);
        }

        if ($format !== 0x01 || strlen($decoded) < 14) {
            return false;
        }

        $header = unpack('Nprf/Niterations/NsaltLength', substr($decoded, 1, 12));
        $prf = $header['prf'];
        $iterations = $header['iterations'];
        $saltLength = $header['saltLength'];
        $remainingLength = strlen($decoded) - 13;

        $algorithms = [0 => 'sha1', 1 => 'sha256', 2 => 'sha512'];

        // Reject malformed lengths before slicing or invoking the expensive KDF.
        if (! isset($algorithms[$prf]) || $iterations < 1 || $iterations > 1000000
            || $saltLength < 16 || $saltLength > $remainingLength - 16) {
            return false;
        }

        $subkeyLength = $remainingLength - $saltLength;
        $salt = substr($decoded, 13, $saltLength);
        $expected = substr($decoded, 13 + $saltLength);
        $actual = hash_pbkdf2($algorithms[$prf], $password, $salt, $iterations, $subkeyLength, true);

        return hash_equals($expected, $actual);
    }

    private function verifyV2(string $decoded, string $password): bool
    {
        if (strlen($decoded) !== 49) {
            return false;
        }

        $salt = substr($decoded, 1, 16);
        $expected = substr($decoded, 17, 32);
        $actual = hash_pbkdf2('sha1', $password, $salt, 1000, 32, true);

        return hash_equals($expected, $actual);
    }
}
