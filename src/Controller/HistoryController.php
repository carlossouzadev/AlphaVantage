<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\User;
use App\Service\AlphaVantageClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Email;

class HistoryController extends AbstractController
{
    /**
     * @Route("/history", name="history_data")
     */
    public function getHistoryData(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $response = [];
        try {
            $username = $request->headers->get('php-auth-user');
            if (!$username) {
                throw $this->createNotFoundException(
                    'No user found'
                );
            }
            $userEntity = $entityManager->getRepository(User::class)->findBy(['email' => $username]);

            $logs = $entityManager->getRepository(Log::class)->findBy(['fk_idUser' => $userEntity[0]->getId()]);
            if (!$logs) {
                throw $this->createNotFoundException(
                    'No log found for user'
                );
            }
            foreach ($logs as $log) {
                $response[] = array_merge(['date' => $log->getCreatedAt()->format('Y-m-d H:i:s')], json_decode($log->getData(), true));
            }
            return new JsonResponse($response);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
