<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\BaseDao;
use InvalidArgumentException;
use PDOException;

abstract class CrudService
{
    protected array $requiredOnCreate = [];
    protected array $requiredOnReplace = [];

    public function __construct(protected BaseDao $dao)
    {
    }

    public function list(array $query): array
    {
        return $this->dao->getAll();
    }

    public function find(int $id): ?array
    {
        return $this->dao->getById($id);
    }

    public function create(array $data): array
    {
        $data = $this->validated($data, $this->requiredOnCreate);
        return $this->write(fn () => $this->dao->insert($data));
    }

    public function update(int $id, array $data, bool $replace): ?array
    {
        $data = $this->validated($data, $replace ? $this->requiredOnReplace : []);
        return $this->write(fn () => $this->dao->update($id, $data));
    }

    public function delete(int $id): bool
    {
        return $this->write(fn () => $this->dao->delete($id));
    }

    /** Maps writable field names to their validators. */
    abstract protected function fields(): array;

    private function validated(array $data, array $required): array
    {
        foreach ($required as $field) {
            if (!array_key_exists($field, $data)) {
                throw new InvalidArgumentException("Missing required field: $field.");
            }
        }

        $validators = $this->fields();
        if ($data === [] || array_diff_key($data, $validators) !== []) {
            throw new InvalidArgumentException('Provide one or more supported fields only.');
        }

        foreach ($data as $field => $value) {
            $data[$field] = $validators[$field]($value);
        }

        return $data;
    }

    private function write(callable $operation): mixed
    {
        try {
            return $operation();
        } catch (PDOException $exception) {
            // The database enforces these rules even if requests write concurrently.
            $message = match ((int) ($exception->errorInfo[1] ?? 0)) {
                1062 => 'A record with those unique values already exists.',
                1451 => 'This record is still used by another record.',
                1452 => 'A referenced record does not exist.',
                default => null,
            };
            if ($message !== null) {
                throw new InvalidArgumentException($message, 0, $exception);
            }
            throw $exception;
        }
    }
}
