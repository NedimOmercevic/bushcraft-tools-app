<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\ToolDao;

final class ToolService extends CrudService
{
    protected array $requiredOnCreate = ['category_id', 'name', 'price'];
    protected array $requiredOnReplace = ['category_id', 'name', 'price', 'stock'];

    public function __construct()
    {
        parent::__construct(new ToolDao());
    }

    public function list(array $query): array
    {
        $categoryId = $query['category_id'] ?? '';
        return $categoryId === ''
            ? parent::list($query)
            : $this->dao->getByCategoryId(Validation::integer($categoryId, 'category_id'));
    }

    protected function fields(): array
    {
        return [
            'category_id' => static fn ($value) => Validation::integer($value, 'category_id'),
            'name' => static fn ($value) => Validation::text($value, 'name', 150),
            'brand' => static fn ($value) => Validation::optionalText($value, 'brand', 100),
            'description' => static fn ($value) => Validation::optionalText($value, 'description', 4000),
            'price' => [Validation::class, 'price'],
            'stock' => static fn ($value) => Validation::integer($value, 'stock', 0, 2147483647),
        ];
    }
}
