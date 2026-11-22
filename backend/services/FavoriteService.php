<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\FavoriteDao;

final class FavoriteService extends CrudService
{
    protected array $requiredOnCreate = ['user_id', 'tool_id'];
    protected array $requiredOnReplace = ['user_id', 'tool_id'];

    public function __construct()
    {
        parent::__construct(new FavoriteDao());
    }

    public function list(array $query): array
    {
        $userId = $query['user_id'] ?? '';
        return $userId === ''
            ? parent::list($query)
            : $this->dao->getByUserId(Validation::integer($userId, 'user_id'));
    }

    protected function fields(): array
    {
        return [
            'user_id' => static fn ($value) => Validation::integer($value, 'user_id'),
            'tool_id' => static fn ($value) => Validation::integer($value, 'tool_id'),
        ];
    }
}
