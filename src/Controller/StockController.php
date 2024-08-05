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

class StockController extends AbstractController
{
    private AlphaVantageClient $alphaVantageClient;

    public function __construct(AlphaVantageClient $alphaVantageClient)
    {
        $this->alphaVantageClient = $alphaVantageClient;
    }

    /**
     * @Route("/stock/{symbol}", name="stock_data")
     */
    public function getStockData(string $symbol, EntityManagerInterface $entityManager, Request $request): JsonResponse
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
            if (!$userEntity) {
                throw $this->createNotFoundException(
                    'No user found'
                );
            }
            $data = $this->alphaVantageClient->getDailyTimeSeries($symbol);
            if (isset($data['Time Series (Daily)'])) {
                $mostRecentData = $data['Meta Data']['3. Last Refreshed'];
                $response['symbol'] = $symbol;
                $response['open'] = number_format((float)$data['Time Series (Daily)'][$mostRecentData]['1. open'], 2, '.', '');
                $response['high'] = number_format((float)$data['Time Series (Daily)'][$mostRecentData]['2. high'], 2, '.', '');
                $response['low'] = number_format((float)$data['Time Series (Daily)'][$mostRecentData]['3. low'], 2, '.', '');
                $response['close'] = number_format((float)$data['Time Series (Daily)'][$mostRecentData]['4. close'], 2, '.', '');
            }
            $transport = Transport::fromDsn($this->getParameter('mailer_dns'));
            $mailer = new Mailer($transport);
            $email = (new Email())
                ->from('noreply@gmail.com')
                ->to($username)
                ->subject("Stock data was requested")
                ->html("<p>".json_encode($response)."</p>");

            $log = new Log();
            $log->setData(json_encode($response));
            $log->setFkIdUser($userEntity[0]->getId());
            $log->setCreatedAt(new \DateTimeImmutable('now'));
            $entityManager->persist($log);

            $entityManager->flush();

            $mailer->send($email);
            return new JsonResponse($response);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
