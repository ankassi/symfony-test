<?php

namespace App\Service;

use App\Repository\PhoneRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Psr\Cache\InvalidArgumentException;
use Random\RandomException;

class VerificationCodeService
{
    private UserRepository $userRepository;
    private LimitService $limitService;
    private PhoneRepository $phoneRepository;

    private int $timeInterval = 10;
    private int $codeLength = 4;

    public function __construct(UserRepository $userRepository, LimitService $limitService, PhoneRepository $phoneRepository)
    {
        $this->userRepository = $userRepository;
        $this->limitService = $limitService;
        $this->phoneRepository = $phoneRepository;
    }

    /**
     * @throws RandomException
     */
    public function generateCode(): string
    {
        return str_pad(random_int(0, 9999), $this->codeLength, '0', STR_PAD_LEFT);
    }

    private function validatePhoneNumber(string $phone): bool
    {
        $pattern = '/^\+?\d{10,15}$/';
        return preg_match($pattern, $phone);
    }

    /**
     * @throws RandomException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function requestCode(string $phone): array
    {
        if (!$this->validatePhoneNumber($phone)) {
            return ['error' => 'Invalid phone number format'];
        }

        if ($this->limitService->isBlocked($phone)) {
            return ['error' => 'Too many requests. Try again later.'];
        }

        if (!$this->limitService->checkRequestLimit($phone)) {
            $this->limitService->blockUser($phone);
            return ['error' => 'Too many requests. You have been blocked for 1 hour.'];
        }

        $now = new DateTimeImmutable();
        $phoneModel = $this->phoneRepository->findByPhone($phone);
        $code = $this->generateCode();

        if ($phoneModel) {
            $interval = $now->getTimestamp() - $phoneModel->getCreatedAt()->getTimestamp();

            if ($interval < $this->timeInterval) {
                return [
                    'message' => 'Code already sent (within 1 minute)',
                    'phone' => $phone,
                    'code' => $phoneModel->getVerificationCode(),
                ];
            }
            $this->phoneRepository->updatePhoneCode($phoneModel->getId(), $code, $now);
        } else {
            $user = $this->userRepository->findUserByPhone($phone);

            if (!$user) {
                $userId = $this->userRepository->createUser();
            } else {
                $userId = $user['user_id'];
            }

            $this->phoneRepository->createPhoneForUser($userId, $phone, $code, $now);
        }

        return [
            'message' => 'Code sent',
            'phone' => $phone,
            'code' => $code,
        ];
    }

    /**
     * @throws Exception
     */
    public function verifyCode(string $phone, string $code): array
    {
        $data = $this->phoneRepository->findByPhone($phone);

        if (!$data) {
            return ['error' => 'User not found'];
        }

        if ($data->getVerificationCode() === $code) {
            return [
                'message' => 'Verification successful',
                'phone' => $phone,
                'user_id' => $data->getUser()->getId(),
            ];
        }

        return ['error' => 'Invalid code'];
    }
}
