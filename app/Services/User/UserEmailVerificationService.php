<?php

namespace App\Services\User;

use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;

class UserEmailVerificationService
{
    const TYPE = 'email_verification';

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function get(string $email): string
    {
        return cache()->get(self::TYPE . '_' . $email);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function create(string $email): string
    {
        $code = Str::upper(Str::random(5));

        cache()->set(self::TYPE . '_' . $email, $code, 900);

        return $code;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function delete(string $email): void
    {
        cache()->delete(self::TYPE . '_' . $email);
    }
}