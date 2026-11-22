<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\ReviewDao;

final class ReviewService extends CrudService
{
    protected array $requiredOnCreate = ['user_id', 'tool_id', 'rating'];
    protected array $requiredOnReplace = ['user_id', 'tool_id', 'rating'];

    public function __construct()
    {
        parent::__construct(new ReviewDao());
    }

    public function list(array $query): array
    {
        $toolId = $query['tool_id'] ?? '';
        if ($toolId !== '') {
            return $this->dao->getByToolId(Validation::integer($toolId, 'tool_id'));
        }

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
            'rating' => static fn ($value) => Validation::integer($value, 'rating', 1, 5),
            'comment' => static fn ($value) => Validation::optionalText($value, 'comment', 4000),
        ];
    }
}
