<?php
namespace App\Repository;

use App\Factory\PhoneFactory;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use App\Model\PhoneModel as Phone;
use Doctrine\DBAL\Exception;

class PhoneRepository
{
    private Connection $connection;
    private PhoneFactory $phoneFactory;

    public function __construct(Connection $connection, PhoneFactory $phoneFactory)
    {
        $this->connection = $connection;
        $this->phoneFactory = $phoneFactory;
    }


    /**
     * @throws Exception
     */
    public function save(Phone $phone): void
    {
        $sql = 'INSERT INTO phone (user_id, phone, verification_code, created_at, status) 
                VALUES (:user_id, :phone, :verification_code, :created_at, :status)';
        $this->connection->prepare($sql)->executeQuery([
            'id' => $phone->getId(),
            'user_id' => $phone->getUser()->getId(),
            'phone' => $phone->getPhone(),
            'verification_code' => $phone->getVerificationCode(),
            'created_at' => $phone->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
    }


    /**
     * @throws Exception
     */
    public function findByPhone(string $phone): ?Phone
    {
        $sql = 'SELECT * FROM phone WHERE phone = :phone';
        $stmt = $this->connection->executeQuery($sql, ['phone' => $phone]);

        $data = $stmt->fetchAssociative();

        if ($data) {
            return $this->phoneFactory->fromArray($data);
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function updatePhoneCode(int $id, string $verificationCode, DateTimeImmutable $createdAt): void
    {
        $sql = 'UPDATE phone SET verification_code = :verification_code, created_at = :created_at WHERE id = :id';
        $this->connection->prepare($sql)->executeQuery([
            'verification_code' => $verificationCode,
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }

    /**
     * @throws Exception
     */
    public function createPhoneForUser(int $userId, string $phone, string $verificationCode, DateTimeImmutable $createdAt): void
    {
        $sql = 'INSERT INTO phone (user_id, phone, verification_code, created_at, status) 
                VALUES (:user_id, :phone, :verification_code, :created_at, :status)';
        $this->connection->prepare($sql)->executeQuery([
            'user_id' => $userId,
            'phone' => $phone,
            'verification_code' => $verificationCode,
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
            'status' => 'unverified',
        ]);
    }
}
