<?php

namespace App\Factory;

use App\Model\PhoneModel as Phone;
use App\Model\UserModel as User;
use DateTimeImmutable;

class PhoneFactory
{
    public static function create(
        string $phone,
        string $verificationCode,
        DateTimeImmutable $createdAt,
        User $user
    ): Phone {
        return new Phone($phone, $verificationCode, $createdAt, $user);
    }

    public static function fromArray(array $data): Phone
    {
        $user = User::fromId((int)$data['user_id']);

        return new Phone(
            $data['id'],
            $data['phone'],
            $data['verification_code'],
            new DateTimeImmutable($data['created_at']),
            $user
        );
    }
}
