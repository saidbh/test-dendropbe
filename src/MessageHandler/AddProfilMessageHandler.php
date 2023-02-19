<?php

namespace App\MessageHandler;

use App\Entity\Profil;
use App\Message\AddProfilMessage;
use App\Repository\DroitRepository;
use App\Repository\GroupeRepository;
use App\Repository\ProfilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class AddProfilMessageHandler
{
    // ATTRIBUT
    private $validator;
    private $em;
    private $profilRepository;
    private $droitRepository;


    public function __construct(ValidatorInterface $validator,
                                EntityManagerInterface $em,
                                ProfilRepository $profilRepository,
                                DroitRepository $droitRepository,
                                GroupeRepository $groupeRepository)
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->profilRepository = $profilRepository;
        $this->groupeRepository = $groupeRepository;
        $this->droitRepository = $droitRepository;

    }

    // MAIN FUNCTION
    public function __invoke(AddProfilMessage $addProfil)
    {
        //
        $profil = new Profil();

        // DEMATERIALISATION EN MODE JSON
        $profil->setName($addProfil->getName());
        // HYDRATATION DE DONNEES
        $profil->setCreatedAt(new \DateTime('now'));
        $profil->setIsInit($addProfil->isInit);

        $profil->setGroupeType($addProfil->getGroupeType());

        if (!$profil->getGroupeType() || !$profil->getName()) {
            return new JsonResponse(
                [
                    "message" => "Information obligatoire",
                    "errorCode" => 301
                ], Response::HTTP_BAD_REQUEST);
        }

        // SET DROITS
        if ($addProfil->getDroit()) {
            // ADD DATA
            if (is_numeric($addProfil->getDroit())) {
                $droit = $this->droitRepository->findOneBy(['id' => $addProfil->getDroit()]);
            } else {
                $droit = $this->droitRepository->findOneBy(['name' => $addProfil->getDroit()]);
            }

            $profil->setDroit($droit);
        }

        if ($this->profilRepository->findBy(['name' => $profil->getName()])) {
            return new JsonResponse(
                [
                    "message" => "Ce profil est deja defini",
                    "errorCode" => 302
                ], 409);
        }

        // ADD PROFIL
        try {
            $this->em->persist($profil);
            $this->em->flush();
            return new JsonResponse(
                [
                    "message" => "Enregistrement effectué avec succès",
                    "id" => $profil->getId(),
                    "errorCode" => 200
                ], 200);
        } catch (\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(
                [
                    "message" => "Impossible d'enrégistrer ce profil",
                    "errorCode" => 500
                ], 500);
        }
    }

}
