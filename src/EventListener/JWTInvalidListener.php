<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class JWTInvalidListener
{
    /**
     * Gère les tokens JWT invalides
     */
    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $response = new JsonResponse([
            'error' => 'Token JWT invalide',
            'message' => 'Veuillez vous reconnecter',
            'code' => 401
        ], 401);
        
        $event->setResponse($response);
    }

    /**
     * Gère les tokens JWT expirés
     */
    public function onJWTExpired(JWTExpiredEvent $event)
    {
        $response = new JsonResponse([
            'error' => 'Token JWT expiré',
            'message' => 'Votre session a expiré, veuillez vous reconnecter',
            'code' => 401
        ], 401);
        
        $event->setResponse($response);
    }
}