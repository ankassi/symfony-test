<?php

namespace App\Model;

class UserModel
{
    private int $id;

    private function __construct() {}

    public static function fromId(int $id): self
    {
        $user = new self();

        $reflection = new \ReflectionProperty(self::class, 'id');
        $reflection->setValue($user, $id);

        return $user;
    }

    public function getId(): int
    {
        return $this->id;
    }
}