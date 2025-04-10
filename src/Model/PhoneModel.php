<?php

namespace App\Model;

use DateTimeImmutable;
use App\Model\UserModel as User;
class PhoneModel
{
    private int $id;
    private string $phone;
    private string $verificationCode;
    private DateTimeImmutable $createdAt;
    private User $user;

    public function __construct(
        int $id,
        string $phone,
        string $verificationCode,
        DateTimeImmutable $createdAt,
        User $user
    ) {
        $this->id = $id;
        $this->phone = $phone;
        $this->verificationCode = $verificationCode;
        $this->createdAt = $createdAt;
        $this->user = $user;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['phone'],
            $data['verification_code'],
            new DateTimeImmutable($data['created_at']),
            User::fromId((int)$data['user_id'])
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getVerificationCode(): string
    {
        return $this->verificationCode;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}