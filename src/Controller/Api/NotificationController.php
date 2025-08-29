<?php

namespace App\Controller\Api;

use App\Entity\AppelOffre;
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
        $appelOffres = $this->entityManager->getRepository(AppelOffre::class)->findAll();
    
        // Initialiser un tableau pour stocker les IDs des AppelOffres affectés
        $affectedAppelOffresIds = [];
    
        foreach ($appelOffres as $appelOffre) {
            // Vérifier si la participation est égale à 1
            if ($appelOffre->getAppelOffreParticipation() !== 1) {
                continue;
            }

            // Convertir la date de remise en utilisant le fuseau horaire de la Tunisie
            $appelOffreDateRemise = $appelOffre->getAppelOffreDateRemise();
            if ($appelOffreDateRemise) {
                $appelOffreDateRemise->setTimezone(new \DateTimeZone('Africa/Tunis'));
            }
            
            // Vérifier si la date d'achèvement est dans les 10 jours à partir de la date actuelle
            $limiteNotification = (clone $dateActuelle)->add(new \DateInterval('P10D'));
            if ($appelOffreDateRemise && $appelOffreDateRemise <= $limiteNotification && $appelOffreDateRemise > $dateActuelle) {
                // Calculer la différence entre la date d'achèvement et la date actuelle
                $diff = date_diff($dateActuelle, $appelOffreDateRemise);
                $joursRestants = $diff->days;
    
                // Construire le message de la notification avec le nombre de jours restants
                $message = "La date d'achèvement de l'appel d'offre est dans $joursRestants jours.";
    
                // Créer une nouvelle notification
                $notification = new Notification();
                $notification->setMessage($message);
                $notification->setDateCreation(new \DateTime('now', new \DateTimeZone('Africa/Tunis'))); // Définir la date de création
                $notification->setAppelOffre($appelOffre);
    
                // Enregistrer la notification en base de données
                $this->entityManager->persist($notification);
    
                // Ajouter l'ID de l'AppelOffre à la liste des IDs affectés
                $affectedAppelOffresIds[] = $appelOffre->getId();
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
        // Récupérer le nombre de notifications non lues
        $unreadNotificationCount = $notificationRepository->countUnreadNotifications();

        // Retourner le nombre de notifications non lues au format JSON
        return new JsonResponse(['unread_notification_count' => $unreadNotificationCount]);
    }

    #[Route('/api/notifications/all', name: 'api_notifications_all', methods: ['GET'])]
    public function getNotifications(NotificationRepository $notificationRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        // Récupérer toutes les notifications avec les données de l'appel d'offre associées
        $notifications = $notificationRepository->findAllWithAppelOffre();

        // Convertir les notifications en un tableau associatif pour le retour JSON
        $notificationsArray = [];
        foreach ($notifications as $notification) {
            $notificationsArray[] = [
                'id' => $notification->getId(),
                'message' => $notification->getMessage(),
                'dateCreation' => $notification->getDateCreation()->format('Y-m-d H:i:s'), // Formatage de la date
                'appelOffre' => [
                    'id' => $notification->getAppelOffre()->getId(),
                    'devis' => $notification->getAppelOffre()->getAppelOffreDevis(),
                    'objet' => $notification->getAppelOffre()->getAppelOffreObjet(),
                    'dateRemise' => $notification->getAppelOffre()->getAppelOffreDateRemise() ? $notification->getAppelOffre()->getAppelOffreDateRemise()->format('Y-m-d') : null,
                    // Ajoutez d'autres propriétés de l'appel d'offre si nécessaire
                ],
                
            ];
        }

        // Retourner les notifications au format JSON
        return new JsonResponse($notificationsArray);
    }

    public function checkToken(TokenStorageInterface $tokenStorage): void
    {
        // Récupérer le token d'authentification de Symfony
        $token = $tokenStorage->getToken();

        // Vérifier si le token d'authentification est présent et est de type TokenInterface
        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }
    }
}
