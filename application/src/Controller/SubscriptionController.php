<?php

namespace App\Controller;

use App\Entity\Subscription;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

final class SubscriptionController extends AbstractController
{
    #[Route('/subscription', name: 'app_subscription')]
    public function index(EntityManagerInterface $em): Response
    {
        $subscriptions = $em->getRepository(Subscription::class)->findAll();

        return $this->render('subscription/index.html.twig', [
            'subscriptions' => $subscriptions,
        ]);
    }

    #[Route('/subscription/change/{id}', name: 'app_subscription_change')]
    public function changeSubscription(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Trouver l'abonnement à changer
        $subscription = $em->getRepository(Subscription::class)->find($id);

        if (!$subscription) {
            throw $this->createNotFoundException('Abonnement non trouvé');
        }

        // Mettre à jour l'abonnement de l'utilisateur
        $user->setSubscription($subscription);
        $em->flush();

        return $this->render('subscription/change/change_sucess.html.twig');
    }
}
