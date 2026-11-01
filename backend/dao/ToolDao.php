<?php

namespace App\Dao;

class ToolDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('tools');
        $this->columns = ['category_id', 'name', 'brand', 'description', 'price', 'stock'];
    }

    public function getByCategoryId(int $categoryId): array
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM `tools` WHERE `category_id` = :category_id'
        );
        $stmt->execute([':category_id' => $categoryId]);

        return $stmt->fetchAll();
    }
}
