<?php

namespace App\MessageHandler;

use App\Entity\Forfait;
use App\Message\AddForfaitMessage;
use App\Repository\ForfaitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddForfaitMessageHandler
{

    private $validator;
    private $em;
    private $forfaitRepository;

    public function __construct(ValidatorInterface $validator,
                                EntityManagerInterface $em,
                                ForfaitRepository $forfaitRepository
    )
    {
        $this->validator = $validator;
        $this->em = $em;
        $this->forfaitRepository = $forfaitRepository;
    }

    public function __invoke(AddForfaitMessage $forfaitMessage)
    {
        // GET INFORMATION FROM DATA
        $forfait = new Forfait();

        if (!$forfaitMessage->name()) {
            return new JsonResponse(
                [
                    "message" => "Saisir Informations Obligatoires",
                    "errorCode" => 300
                ], 403);
        }

        // DEMATERIALISATION EN MODE JSON
        $forfait->setName($forfaitMessage->name());
        $forfait->setCodeForfait($forfaitMessage->codeForfait());

        $forfait->setCreatedAt(new \DateTime('now'));

        if ($this->forfaitRepository->findBy(['codeForfait' => $forfait->getCodeForfait()])) {
            return new JsonResponse(
                [
                    "message" => "Ce forfait est deja defini",
                    "errorCode" => 302
                ], Response::HTTP_CONFLICT);
        }

        try {
            $this->em->persist($forfait);
            $this->em->flush();
            return new JsonResponse(
                [
                    "message" => "Enregistrement effectué avec succès",
                    "id" => $forfait->getId(),
                    "errorCode" => 200
                ], Response::HTTP_OK);
        } catch (\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(
                [
                    "message" => "Impossible d'enrégistrer droit",
                    "errorCode" => 500
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
