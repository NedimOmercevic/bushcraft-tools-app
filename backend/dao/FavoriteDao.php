<?php

namespace App\Dao;

class FavoriteDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('favorites');
        $this->columns = ['user_id', 'tool_id'];
    }

    public function getByUserId(int $userId): array
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM `favorites` WHERE `user_id` = :user_id ORDER BY `created_at` DESC'
        );
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll();
    }
}
