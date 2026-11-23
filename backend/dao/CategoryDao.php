<?php

namespace App\Dao;

class CategoryDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('categories');
        $this->columns = ['name', 'slug'];
    }

    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM `categories` WHERE `slug` = :slug'
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }
}
