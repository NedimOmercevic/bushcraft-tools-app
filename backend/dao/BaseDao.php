<?php

namespace App\Dao;

use InvalidArgumentException;
use PDO;

/**
 * Reusable CRUD helpers. All SQL uses PDO prepared statements.
 * Column names are never taken from request input unless they appear
 * in the entity whitelist ($columns).
 */
class BaseDao
{
    protected PDO $connection;
    protected string $table;
    protected string $primaryKey = 'id';

    /** @var string[] Writable / insertable column names */
    protected array $columns = [];

    public function __construct(string $table)
    {
        $this->table = $table;
        $this->connection = Database::getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function insert(array $data): array
    {
        $data = $this->filterColumns($data);
        unset($data[$this->primaryKey]);

        if ($data === []) {
            throw new InvalidArgumentException('No valid fields to insert.');
        }

        $columnSql = implode(', ', array_map(
            static fn (string $column): string => "`{$column}`",
            array_keys($data)
        ));
        $placeholderSql = implode(', ', array_map(
            static fn (string $column): string => ":{$column}",
            array_keys($data)
        ));

        $sql = "INSERT INTO `{$this->table}` ({$columnSql}) VALUES ({$placeholderSql})";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($this->namedParams($data));

        $id = (int) $this->connection->lastInsertId();
        $created = $this->getById($id);

        if ($created === null) {
            throw new InvalidArgumentException('Insert succeeded but the new row could not be read.');
        }

        return $created;
    }

    public function update(int $id, array $data): ?array
    {
        if ($this->getById($id) === null) {
            return null;
        }

        $data = $this->filterColumns($data);
        unset($data[$this->primaryKey]);

        if ($data === []) {
            return $this->getById($id);
        }

        $assignments = implode(', ', array_map(
            static fn (string $column): string => "`{$column}` = :{$column}",
            array_keys($data)
        ));

        $sql = "UPDATE `{$this->table}` SET {$assignments} WHERE `{$this->primaryKey}` = :id";
        $params = $this->namedParams($data);
        $params[':id'] = $id;

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $this->getById($id);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function filterColumns(array $data): array
    {
        if ($this->columns === []) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->columns));
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function namedParams(array $data): array
    {
        $params = [];
        foreach ($data as $column => $value) {
            $params[":{$column}"] = $value;
        }

        return $params;
    }
}
