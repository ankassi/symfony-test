<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class UserRepository
{
    private Connection $connection;
    private PhoneRepository $phoneRepository;

    public function __construct(Connection $connection, PhoneRepository $phoneRepository)
    {
        $this->connection = $connection;
        $this->phoneRepository = $phoneRepository;
    }

    /**
     * @throws Exception
     */
    public function findUserByPhone(string $phone): ?array
    {
        $sql = 'SELECT u.* FROM "user" u
            JOIN phone p ON u.id = p.user_id
            WHERE p.phone = :phone
            ORDER BY p.created_at DESC LIMIT 1';
        $stmt = $this->connection->executeQuery($sql, ['phone' => $phone]);
        return $stmt->fetchAssociative() ?: null;
    }

    /**
     * @throws Exception
     */
    public function createUser(): int
    {
        $name = 'userName';
        $sql = 'INSERT INTO "user" (name) VALUES (:name) RETURNING id';
        $stmt = $this->connection->executeQuery($sql, ['name' => $name]);
        return (int)$stmt->fetchOne();
    }
}
