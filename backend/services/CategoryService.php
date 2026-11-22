<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\CategoryDao;
use InvalidArgumentException;

final class CategoryService extends CrudService
{
    protected array $requiredOnCreate = ['name', 'slug'];
    protected array $requiredOnReplace = ['name', 'slug'];

    public function __construct()
    {
        parent::__construct(new CategoryDao());
    }

    protected function fields(): array
    {
        return [
            'name' => static fn ($value) => Validation::text($value, 'name', 100),
            'slug' => static function ($value): string {
                $slug = Validation::text($value, 'slug', 100);
                if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)) {
                    throw new InvalidArgumentException('Slug must use lowercase letters, numbers and single hyphens.');
                }
                return $slug;
            },
        ];
    }
}
