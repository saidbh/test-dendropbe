<?php

namespace App\Controller;

use App\Message\AddDroitMessage;
use App\Message\AddForfaitMessage;
use App\Message\AddProfilMessage;
use App\Message\AddUserMessage;
use App\Repository\DroitRepository;
use App\Repository\ForfaitRepository;
use App\Repository\GroupeRepository;
use App\Repository\ProfilRepository;
use App\Repository\UserRepository;
use App\Service\GroupeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

// MESSAGE


class DefaultController extends AbstractController
{
    /**
     * @Route("/api/init", name="default")
     */
    public function index(MessageBusInterface $bus, DroitRepository $droitRepository,
                          ProfilRepository    $profilRepository,
                          UserRepository      $userRepository, GroupeRepository $groupeRepository,
                          ForfaitRepository   $forfaitRepository,
                          GroupeService       $_groupeService)
    {

        // 3 NAMES FORFAIT AGILE
        $flex = ['name' => "Flex", "codeForfait" => "GRATUIT"];
        $agile1M = ['name' => "Agile", "codeForfait" => "1M"];
        $agile6M = ['name' => "Agile", "codeForfait" => "6M"];
        $agile12M = ['name' => "Agile", "codeForfait" => "12M"];

        if (!$forfaitRepository->findOneBy(["codeForfait" => $flex['codeForfait']])) {
            $bus->dispatch(new AddForfaitMessage($flex));
        }

        if (!$forfaitRepository->findOneBy(["codeForfait" => $agile1M['codeForfait']])) {
            $bus->dispatch(new AddForfaitMessage($agile1M));
        }

        if (!$forfaitRepository->findOneBy(["codeForfait" => $agile6M['codeForfait']])) {
            $bus->dispatch(new AddForfaitMessage($agile6M));
        }

        if (!$forfaitRepository->findOneBy(["codeForfait" => $agile12M['codeForfait']])) {
            $bus->dispatch(new AddForfaitMessage($agile12M));
        }

        // GROUPE
        $groupeDendromap = ["name" => "DENDROMAP", "groupeType" => "DENDROMAP", "isInit" => 1];
        $groupeMairie = ["name" => "Nantes", "forfait" => $flex['name'], "groupeType" => "FORMULE PREMUIM", "isInit" => 1];

        if (!$groupeRepository->findOneBy(["name" => $groupeDendromap["name"]])) {
            $_groupeService->newGroupe($groupeDendromap);
        }

        if (!$groupeRepository->findOneBy(["name" => $groupeMairie["name"]])) {
            $_groupeService->newGroupe($groupeMairie);
        }

        // DROIT ADMIN, LECTURE ET ECRITURE
        $droitAdmin = ["name" => "ADMIN", "isInit" => 1];
        $droitLecture = ["name" => "LECTURE", "isInit" => 1];
        $droitLecEcriture = ["name" => "LECTURE/ECRITURE", "isInit" => 1];

        // INIT DROIT
        if (!$droitRepository->findOneBy(["name" => $droitAdmin["name"]])) {
            $bus->dispatch(new AddDroitMessage($droitAdmin['name'], $droitAdmin['isInit']));
        }
        if (!$droitRepository->findOneBy(["name" => $droitLecture["name"]])) {
            $bus->dispatch(new AddDroitMessage($droitLecture['name'], $droitLecture['isInit']));
        }
        if (!$droitRepository->findOneBy(["name" => $droitLecEcriture["name"]])) {
            $bus->dispatch(new AddDroitMessage($droitLecEcriture['name'], $droitLecEcriture['isInit']));
        }

        // DEFINIR LES PROFILS

        $profil2M = [
            "name" => "2M-ADVISORY",
            "isInit" => 1
        ];

        if (!$profilRepository->findOneBy(["name" => $profil2M['name']])) {
            // DEFINE GROUPE
            $groupeDendro = $groupeRepository->findOneBy(["name" => $groupeDendromap["name"]]);
            $droit = $droitRepository->findOneBy(["name" => $droitAdmin["name"]]);

            $_objectDendro = 'DENDROMAP';

            $bus->dispatch(new AddProfilMessage(
            // DENDROMAP
                $profil2M['name'],
                $_objectDendro,
                $droit->getId(),
                $profil2M['isInit']));
        }

        $profilDendro = [
            "name" => "DENDROMAP",
            "isInit" => 1
        ];

        if (!$profilRepository->findOneBy(["name" => $profilDendro['name']])) {
            // DEFINE GROUPE
            $groupeDendro = $groupeRepository->findOneBy(["name" => $groupeDendromap["name"]]);
            $droit = $droitRepository->findOneBy(["name" => $droitAdmin["name"]]);

            $_objectDendro = 'DENDROMAP';

            $bus->dispatch(new AddProfilMessage(
            // DENDROMAP
                $profilDendro['name'],
                $_objectDendro,
                $droit->getId(),
                $profilDendro['isInit']));
        }

        $profilMairie = [
            "name" => "Manager",
            "isInit" => 1
        ];

        if (!$profilRepository->findOneBy(["name" => $profilMairie['name']])) {
            // DEFINE GROUPE
            $groupeM = $groupeRepository->findOneBy(["name" => $groupeMairie["name"]]);
            $droit = $droitRepository->findOneBy(["name" => $droitAdmin["name"]]);

            $_objectDendro = 'FORMULE PREMUIM';

            $bus->dispatch(new AddProfilMessage(
            // DENDROMAP
                $profilMairie['name'],
                $_objectDendro,
                $droit->getId(),
                $profilMairie['isInit']
            ));
        }

        $profilAgent = [
            "name" => "Agent",
            "isInit" => 1
        ];

        if (!$profilRepository->findOneBy(["name" => $profilAgent['name']])) {
            // DEFINE GROUPE
            $groupeM = $groupeRepository->findOneBy(["name" => $groupeMairie["name"]]);
            $droit = $droitRepository->findOneBy(["name" => $droitLecEcriture["name"]]);

            $_objectDendro = 'FORMULE PREMUIM';

            $bus->dispatch(new AddProfilMessage(
            // DENDROMAP
                $profilAgent['name'],
                $_objectDendro,
                $droit->getId(),
                $profilAgent['isInit']
            ));
        }

        // // USER INIT DENDRO ADMIN ET USER
        $profil2M = $profilRepository->findOneBy(["name" => $profil2M['name']]);

        $user2M = [
            "email" => "2m-advisory@dendromap.com",
            "password" => "Advisory@123",
            "username" => "2M",
            "profil" => $profil2M->getId(),
            "groupe" => $groupeDendromap['name'],
            "emailActive" => 1,
            "isActive" => 1,
            "isInit" => 1,
            "isRoot" => 0,
        ];

        if (!$userRepository->findBy(["email" => $user2M['email']])) {
            $bus->dispatch(new AddUserMessage(
                $user2M,
                $user2M["emailActive"],
                $user2M["isActive"],
                $user2M["isInit"],
                $user2M["isRoot"]
            ));
        }
        return new Jsonresponse(['message' => 'Server init successfull'], 200);
    }
}
