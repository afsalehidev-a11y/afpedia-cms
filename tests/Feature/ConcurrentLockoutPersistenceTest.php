<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ConcurrentLockoutPersistenceTest extends TestCase
{
    use DatabaseMigrations;

    public function test_for_update_serializes_concurrent_failed_attempt_updates(): void
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)
            || ! function_exists('pcntl_fork')
            || ! function_exists('stream_socket_pair')) {
            self::markTestSkipped('This concurrency test requires MySQL/MariaDB and process control support.');
        }

        $user = User::query()->create([
            'name' => 'Lockout concurrency',
            'email' => 'lockout-concurrency@example.org',
            'password' => 'ValidPassword123',
            'role' => UserRole::Author,
            'is_active' => true,
            'access_failed_count' => 0,
        ]);

        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        self::assertIsArray($sockets, 'Unable to create IPC sockets for the concurrency test.');

        [$parentSocket, $childSocket] = $sockets;
        $parentConnection = DB::connection();
        $parentConnection->beginTransaction();
        $lockedUser = $parentConnection->table('users')
            ->where('id', $user->getKey())
            ->lockForUpdate()
            ->first();

        self::assertNotNull($lockedUser);

        $pid = pcntl_fork();
        if ($pid === -1) {
            $parentConnection->rollBack();
            fclose($parentSocket);
            fclose($childSocket);
            self::fail('Unable to fork the concurrency test worker.');
        }

        if ($pid === 0) {
            fclose($parentSocket);

            try {
                // Never reuse the inherited PDO socket after fork.
                DB::purge();
                fwrite($childSocket, "ready\n");

                $childConnection = DB::connection();
                $childConnection->beginTransaction();
                $childUser = $childConnection->table('users')
                    ->where('id', $user->getKey())
                    ->lockForUpdate()
                    ->first();

                if ($childUser === null) {
                    throw new \RuntimeException('User row disappeared during lockout concurrency test.');
                }

                $childConnection->table('users')
                    ->where('id', $user->getKey())
                    ->update(['access_failed_count' => $childUser->access_failed_count + 1]);

                $childConnection->commit();
                fwrite($childSocket, "done\n");
                fclose($childSocket);
                exit(0);
            } catch (\Throwable $exception) {
                fwrite($childSocket, "error:".$exception->getMessage()."\n");
                fclose($childSocket);
                exit(1);
            }
        }

        fclose($childSocket);
        self::assertSame("ready\n", fgets($parentSocket));

        // Give the child time to reach SELECT ... FOR UPDATE while our row lock is held.
        usleep(250000);

        $parentConnection->table('users')
            ->where('id', $user->getKey())
            ->update(['access_failed_count' => 1]);
        $parentConnection->commit();

        $childResult = fgets($parentSocket);
        fclose($parentSocket);

        pcntl_waitpid($pid, $status);
        self::assertSame("done\n", $childResult);
        self::assertTrue(pcntl_wifexited($status));
        self::assertSame(0, pcntl_wexitstatus($status));

        self::assertSame(2, (int) User::query()->findOrFail($user->getKey())->access_failed_count);
    }
}
