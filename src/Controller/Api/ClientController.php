<?php

namespace App\Controller\Api;

use App\Entity\Client;
use App\Entity\Pays;
use App\Entity\Projet;
use App\Entity\NatureClient;
use App\Entity\Reference;
use App\Entity\SecteurActivite;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ClientController extends AbstractController
{
    #[Route('/api/getAll/clients', name: 'api_client_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $clients = $entityManager->getRepository(Client::class)->findBy([], ['clientPersonneContact1' => 'ASC']);

        $data = [];
        foreach ($clients as $client) {
            $data[] = $this->serializeClientNom($client);
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/get/client/{id}', name: 'api_client_show', methods: ['GET'])]
    public function show(Client $client): JsonResponse
    {
        $data = $this->serializeClient($client);
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/getOne/client/{id}', name: 'api_client_info', methods: ['GET'])]
    public function getClientInfo(Client $client): JsonResponse
    {
        $data = $this->serializeClientInfo($client);
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/create/clients', name: 'api_client_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $client = new Client();
        $client->setClientRaisonSocial($data['clientRaisonSocial']);
        $client->setClientRaisonSocialShort($data['clientRaisonSocialShort']);
        $client->setClientPersonneContact1($data['clientPersonneContact1']);
        $client->setClientPersonneContact2($data['clientPersonneContact2']);
        $client->setClientPersonneContact3($data['clientPersonneContact3']);
        $client->setClientAdresse($data['clientAdresse']);
        $client->setClientTelephone1($data['clientTelephone1']);
        $client->setClientTelephone2($data['clientTelephone2']);
        $client->setClientTelephone3($data['clientTelephone3']);
        $client->setClientEmail($data['clientEmail']);

        // Ajouter plusieurs pays
        foreach ($data['paysIds'] as $paysId) {
            $pays = $entityManager->getRepository(Pays::class)->find($paysId);
            if ($pays) {
                $client->addPays($pays);
            }
        }

        // Nature du client
        $natureClient = $entityManager->getRepository(NatureClient::class)->find($data['natureClientId']);
        if (!$natureClient) {
            return new JsonResponse(['message' => 'Nature client non trouvé.'], Response::HTTP_NOT_FOUND);
        }
        $client->setNatureClient($natureClient);

        // Secteurs
        foreach ($data['secteurs'] as $secteurId) {
            $secteur = $entityManager->getRepository(SecteurActivite::class)->find($secteurId);
            if ($secteur) {
                $client->addSecteurActivite($secteur);
            }
        }

        $entityManager->persist($client);
        $entityManager->flush();

        return new JsonResponse($this->serializeClient($client), Response::HTTP_CREATED);
    }

    #[Route('/api/update/client/{id}', name: 'api_client_update', methods: ['PUT'])]
    public function update(Request $request, Client $client, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $client->setClientRaisonSocial($data['clientRaisonSocial']);
        $client->setClientRaisonSocialShort($data['clientRaisonSocialShort']);
        $client->setClientPersonneContact1($data['clientPersonneContact1']);
        $client->setClientPersonneContact2($data['clientPersonneContact2']);
        $client->setClientPersonneContact3($data['clientPersonneContact3']);
        $client->setClientAdresse($data['clientAdresse']);
        $client->setClientTelephone1($data['clientTelephone1']);
        $client->setClientTelephone2($data['clientTelephone2']);
        $client->setClientTelephone3($data['clientTelephone3']);
        $client->setClientEmail($data['clientEmail']);

        // Mettre à jour les pays
        $client->getPays()->clear();
        foreach ($data['paysIds'] as $paysId) {
            $pays = $entityManager->getRepository(Pays::class)->find($paysId);
            if ($pays) {
                $client->addPays($pays);
            }
        }

        // Nature du client
        $natureClient = $entityManager->getRepository(NatureClient::class)->find($data['natureClientId']);
        if (!$natureClient) {
            return new JsonResponse(['message' => 'Nature client non trouvé.'], Response::HTTP_NOT_FOUND);
        }
        $client->setNatureClient($natureClient);

        // Secteurs
        $client->getSecteurActivites()->clear();
        foreach ($data['secteurs'] as $secteurId) {
            $secteur = $entityManager->getRepository(SecteurActivite::class)->find($secteurId);
            if ($secteur) {
                $client->addSecteurActivite($secteur);
            }
        }

        $entityManager->flush();

        return new JsonResponse($this->serializeClient($client), Response::HTTP_OK);
    }

    #[Route('/api/delete/client/{id}', name: 'api_client_delete', methods: ['DELETE'])]
    public function deleteClient(Client $client, EntityManagerInterface $entityManager): JsonResponse
    {
        $references = $entityManager->getRepository(Reference::class)->findBy(['client' => $client]);

        if ($references) {
            foreach ($references as $reference) {
                $reference->setClient(null);
                $entityManager->persist($reference);
            }
        }

        $entityManager->remove($client);
        $entityManager->flush();

        return new JsonResponse('Client supprimé avec succès', Response::HTTP_OK);
    }

    // ===================== Sérialisation =====================

    private function serializeClient(Client $client): array
    {
        $secteurs = [];
        foreach ($client->getSecteurActivites() as $secteurActivite) {
            $secteurs[] = ['id' => $secteurActivite->getId()];
        }

        $paysList = [];
        foreach ($client->getPays() as $pays) {
            $paysList[] = ['id' => $pays->getId(), 'libelle' => $pays->getPaysLibelle()];
        }

        return [
            'clientId' => $client->getClientId(),
            'natureClientId' => $client->getNatureClient() ? $client->getNatureClient()->getId() : null,
            'pays' => $paysList,
            'clientRaisonSocial' => $client->getClientRaisonSocial(),
            'clientRaisonSocialShort' => $client->getClientRaisonSocialShort(),
            'clientAdresse' => $client->getClientAdresse(),
            'clientTelephone1' => $client->getClientTelephone1(),
            'clientTelephone2' => $client->getClientTelephone2(),
            'clientTelephone3' => $client->getClientTelephone3(),
            'clientEmail' => $client->getClientEmail(),
            'clientPersonneContact1' => $client->getClientPersonneContact1(),
            'clientPersonneContact2' => $client->getClientPersonneContact2(),
            'clientPersonneContact3' => $client->getClientPersonneContact3(),
            'secteurs' => $secteurs,
        ];
    }

    private function serializeClientInfo(Client $client): array
    {
        $secteurs = [];
        foreach ($client->getSecteurActivites() as $secteurActivite) {
            $secteurs[] = ['secteur' => $secteurActivite->getSecteurActiviteLibelle()];
        }

        $paysList = [];
        foreach ($client->getPays() as $pays) {
            $paysList[] = $pays->getPaysLibelle();
        }

        return [
            'natureClient' => $client->getNatureClient() ? $client->getNatureClient()->getNatureClient() : null,
            'paysClient' => $paysList,
            'clientRaisonSocial' => $client->getClientRaisonSocial(),
            'clientRaisonSocialShort' => $client->getClientRaisonSocialShort(),
            'clientAdresse' => $client->getClientAdresse(),
            'clientTelephone1' => $client->getClientTelephone1(),
            'clientTelephone2' => $client->getClientTelephone2(),
            'clientTelephone3' => $client->getClientTelephone3(),
            'clientEmail' => $client->getClientEmail(),
            'clientPersonneContact1' => $client->getClientPersonneContact1(),
            'clientPersonneContact2' => $client->getClientPersonneContact2(),
            'clientPersonneContact3' => $client->getClientPersonneContact3(),
            'secteurs' => $secteurs,
        ];
    }

    private function serializeClientNom(Client $client): array
    {
        $paysList = [];
        foreach ($client->getPays() as $pays) {
            $paysList[] = $pays->getPaysLibelle();
        }

        return [
            'clientId' => $client->getClientId(),
            'personneContact' => $client->getClientPersonneContact1(),
            'clientRaisonSociale' => $client->getClientRaisonSocial(),
            'clientAdresse' => $client->getClientAdresse(),
            'clientEmail' => $client->getClientEmail(),
            'natureClient' => $client->getNatureClient() ? $client->getNatureClient()->getNatureClient() : null,
            'paysClient' => $paysList,
        ];
    }

    public function checkToken(TokenStorageInterface $tokenStorage): void
    {
        $token = $tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }
    }
}
