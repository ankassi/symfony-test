<?php

namespace App\Controller;

use App\Service\VerificationCodeService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Random\RandomException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AuthController extends AbstractController
{
    private VerificationCodeService $verificationCodeService;

    public function __construct(VerificationCodeService $verificationCodeService)
    {
        $this->verificationCodeService = $verificationCodeService;
    }

    /**
     * @throws RandomException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    #[Route('/api/request-code', name: 'request_code', methods: ['POST'])]
    public function requestCode(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $phone = $data['phone'] ?? null;

        if (!$phone) {
            return $this->json(['error' => 'Phone number is required'], 400);
        }

        $response = $this->verificationCodeService->requestCode($phone);

        return $this->json($response);
    }

    /**
     * @throws Exception
     */
    #[Route('/api/verify-code', name: 'verify_code', methods: ['POST'])]
    public function verifyCode(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $phone = $data['phone'] ?? null;
        $code = $data['code'] ?? null;

        if (!$phone || !$code) {
            return $this->json(['error' => 'Phone number and code are required'], 400);
        }

        $response = $this->verificationCodeService->verifyCode($phone, $code);

        return $this->json($response);
    }
}
