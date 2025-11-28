<?php

namespace App\Controller\Api;

use App\Entity\AppelOffres;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NotificationController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/notifications', name: 'api_notification', methods: ['GET'])]
    public function checkAchèvementDate(TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        
        // Supprimer toutes les anciennes notifications
        $this->entityManager->getRepository(Notification::class)->createQueryBuilder('n')->delete()->getQuery()->execute();
    
        // Récupérer la date actuelle en utilisant le fuseau horaire de la Tunisie
        $dateActuelle = new \DateTime('now', new \DateTimeZone('Africa/Tunis'));
    
        // Récupérer tous les AppelOffres
        $appelOffres = $this->entityManager->getRepository(AppelOffres::class)->findAll();
    
        // Initialiser un tableau pour stocker les IDs des AppelOffres affectés
        $affectedAppelOffresIds = [];
    
        foreach ($appelOffres as $appelOffresItem) {
            // Vérifier si la participation est égale à 1
            if ($appelOffresItem->getAppelOffresParticipation() !== 1) {
                continue;
            }

            // Convertir la date de remise en utilisant le fuseau horaire de la Tunisie
            $appelOffresDateLimiteRemise = $appelOffresItem->getAppelOffresDateLimiteRemise();
            if ($appelOffresDateLimiteRemise) {
                $appelOffresDateLimiteRemise->setTimezone(new \DateTimeZone('Africa/Tunis'));
            }
            
            // Vérifier si la date d'achèvement est dans les 10 jours à partir de la date actuelle
            $limiteNotification = (clone $dateActuelle)->add(new \DateInterval('P10D'));
            if ($appelOffresDateLimiteRemise && $appelOffresDateLimiteRemise <= $limiteNotification && $appelOffresDateLimiteRemise > $dateActuelle) {
                // Calculer la différence entre la date limite et la date actuelle
                $diff = date_diff($dateActuelle, $appelOffresDateLimiteRemise);
                $joursRestants = $diff->days;
    
                // Construire le message de la notification avec le nombre de jours restants
                $message = "La date limite de remise de l'appel d'offres est dans $joursRestants jours.";
    
                // Créer une nouvelle notification
                $notification = new Notification();
                $notification->setMessage($message);
                $notification->setDateCreation(new \DateTime('now', new \DateTimeZone('Africa/Tunis')));
                $notification->setAppelOffre($appelOffresItem);
    
                // Enregistrer la notification en base de données
                $this->entityManager->persist($notification);
    
                // Ajouter l'ID de l'AppelOffres à la liste des IDs affectés
                $affectedAppelOffresIds[] = $appelOffresItem->getAppelOffresId();
            }
        }
    
        // Enregistrer toutes les notifications créées
        $this->entityManager->flush();
        
        // Retourner une réponse JSON avec les IDs des AppelOffres affectés
        return $this->json([
            'message' => 'Notifications générées avec succès !',
            'affected_appel_offres_ids' => $affectedAppelOffresIds,
        ]);
    }

    #[Route('/api/notifications/unread-count', name: 'api_notifications_unread_count', methods: ['GET'])]
    public function getUnreadNotificationCount(NotificationRepository $notificationRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $unreadNotificationCount = $notificationRepository->countUnreadNotifications();

        return new JsonResponse(['unread_notification_count' => $unreadNotificationCount]);
    }

    #[Route('/api/notifications/all', name: 'api_notifications_all', methods: ['GET'])]
    public function getNotifications(NotificationRepository $notificationRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $notifications = $notificationRepository->findAllWithAppelOffre();

        $notificationsArray = [];
        foreach ($notifications as $notification) {
            $aop = $notification->getAppelOffre();
            $notificationsArray[] = [
                'id' => $notification->getId(),
                'message' => $notification->getMessage(),
                'dateCreation' => $notification->getDateCreation() ? $notification->getDateCreation()->format('Y-m-d H:i:s') : null,
                'appelOffres' => [
                    'id' => $aop ? $aop->getAppelOffresId() : null,
                    'numero' => $aop ? $aop->getAppelOffresNumero() : null,
                    'devis' => $aop ? $aop->getAppelOffresNumeroDevisParticipation() : null,
                    'objet' => $aop ? $aop->getAppelOffresObjet() : null,
                    'annee' => $aop ? $aop->getAppelOffresAnnee() : null,
                ],
            ];
        }

        return new JsonResponse($notificationsArray);
    }

    public function checkToken(TokenStorageInterface $tokenStorage): void
    {
        $token = $tokenStorage->getToken();

        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }
    }
}
