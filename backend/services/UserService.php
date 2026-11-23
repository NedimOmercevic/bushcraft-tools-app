<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\UserDao;
use InvalidArgumentException;

final class UserService extends CrudService
{
    protected array $requiredOnCreate = ['email', 'password', 'display_name'];
    protected array $requiredOnReplace = ['email', 'display_name', 'role'];

    public function __construct()
    {
        parent::__construct(new UserDao());
    }

    protected function fields(): array
    {
        return [
            'email' => static function ($value): string {
                $email = Validation::text($value, 'email', 255);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new InvalidArgumentException('Enter a valid email address.');
                }
                return $email;
            },
            'display_name' => static fn ($value) => Validation::text($value, 'display_name', 100),
            'password' => static function ($value): string {
                if (!is_string($value) || strlen($value) < 8 || strlen($value) > 72
                    || str_contains($value, "\0") || trim($value) === '') {
                    throw new InvalidArgumentException('Password must contain 8 to 72 bytes without null characters.');
                }
                return $value;
            },
            'role' => static function ($value): string {
                if (!in_array($value, ['user', 'admin'], true)) {
                    throw new InvalidArgumentException('Role must be user or admin.');
                }
                return $value;
            },
        ];
    }
}
