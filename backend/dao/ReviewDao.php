<?php

namespace App\Dao;

class ReviewDao extends BaseDao
{
    public function __construct()
    {
        parent::__construct('reviews');
        $this->columns = ['user_id', 'tool_id', 'rating', 'comment'];
    }

    public function getByToolId(int $toolId): array
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM `reviews` WHERE `tool_id` = :tool_id ORDER BY `created_at` DESC'
        );
        $stmt->execute([':tool_id' => $toolId]);

        return $stmt->fetchAll();
    }

    public function getByUserId(int $userId): array
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM `reviews` WHERE `user_id` = :user_id ORDER BY `created_at` DESC'
        );
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll();
    }
}
