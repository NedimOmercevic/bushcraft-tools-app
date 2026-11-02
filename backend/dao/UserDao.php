<?php

namespace App\Dao;

class UserDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('users');
        $this->columns = ['email', 'password_hash', 'display_name', 'role'];
    }

    public function getAll(): array
    {
        return array_map([$this, 'withoutPassword'], parent::getAll());
    }

    public function getById(int $id): ?array
    {
        $user = parent::getById($id);

        return $user === null ? null : $this->withoutPassword($user);
    }

    public function getByEmail(string $email): ?array
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM `users` WHERE `email` = :email'
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        return $row === false ? null : $this->withoutPassword($row);
    }

    public function insert(array $data): array
    {
        $data = $this->hashPasswordIfPresent($data);

        return parent::insert($data);
    }

    public function update(int $id, array $data): ?array
    {
        $data = $this->hashPasswordIfPresent($data);

        return parent::update($id, $data);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function hashPasswordIfPresent(array $data): array
    {
        if (isset($data['password']) && $data['password'] !== '') {
            $data['password_hash'] = password_hash((string) $data['password'], PASSWORD_BCRYPT);
        }
        unset($data['password']);

        return $data;
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    private function withoutPassword(array $user): array
    {
        unset($user['password_hash']);

        return $user;
    }
}
