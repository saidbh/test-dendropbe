<?php

namespace App\MessageHandler;
// SERVICE
use App\Controller\UtilsController;
use App\Entity\Groupe;
use App\Entity\Profil;
use App\Entity\User;
use App\Message\AddUserMessage;
use App\Repository\GroupeRepository;
use App\Repository\ProfilRepository;
use App\Repository\UserRepository;
use App\Service\AuthService;
use App\Service\MailService;
use App\Service\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddUserMessageHandler extends AbstractController
{

    // ADD USER MESSAGE
    private $validator;
    private $em;
    private $userRepository;
    private $profilRepository;
    private $groupeRepository;

    private $_tokenService;

    public function __construct(
        ValidatorInterface $validator,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        ProfilRepository $profilRepository,
        GroupeRepository $groupeRepository,
        TokenService $tokenService
    )
    {
        $this->validator = $validator;
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->profilRepository = $profilRepository;
        $this->groupeRepository = $groupeRepository;
        $this->_tokenService = $tokenService;
    }

    public function __invoke(AddUserMessage $addUser):JsonResponse
    {
        // HANDLER ADD USER
        $user = new User();

        $user->setNom($addUser->nom());
        $user->setPrenom($addUser->prenom());
        $user->setUsername($addUser->username());
        $user->setEmail($addUser->email());

        $user->setIsActive($addUser->isActive);
        $user->setIsInit($addUser->isInit);
        $user->setIsRoot($addUser->isRoot);
        $user->setEmailActive($addUser->emailActive);
        $user->setCreatedAt(new \DateTime('now'));
        $user->setDeleted(false);

        // coord facturations
        $user->setAddress($addUser->address());
        $user->setAddress2($addUser->address2());
        $user->setCity($addUser->city());
        $user->setPhoneNumber($addUser->phoneNumber());
        $user->setZipCode($addUser->zipCode());

        $user->setPassword($addUser->password());
        $user->setPassword(AuthService::encrypt($user->getPassword()));

        // VERIFY IMAGE

        if (is_numeric($addUser->profil())) {
            $profil = $this->profilRepository->findOneBy(['id' => $addUser->profil()]);
        } else {
            $profil = $this->profilRepository->findOneBy(['name' => $addUser->profil()]);
        }

        if ($profil instanceof Profil) {
            $user->setProfil($profil);
            // SET GROUPE
            if ($addUser->groupe()) {
                if (is_numeric($addUser->groupe())) {
                    $groupe = $this->groupeRepository->findOneBy(['id' => $addUser->groupe()]);
                } else {
                    $groupe = $this->groupeRepository->findOneBy(['name' => $addUser->groupe()]);
                }
                // GROUPE UTILISATEUR

                if ($groupe instanceof Groupe) {
                    $user->setGroupe($groupe);
                } else {
                    return $this->json(
                        [
                            "message" => "Le groupe n'est pas défini",
                            "errorCode" => 303
                        ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                return $this->json(
                    [
                        "message" => "Le groupe n'est pas défini",
                        "errorCode" => 303
                    ], Response::HTTP_BAD_REQUEST);
            }
            // IF IMG IS REQUIRRED
            $serializer = $this->get('serializer');

            try {
                $this->em->persist($user);
                $this->em->flush();
                if ($addUser->isAuth()) {
                    MailService::sendMailWhenSubscribed($user);
                }
                
                return $this->json([
                    'errorCode' => 200,
                    'data' => $serializer->normalize($user, 'json', ['groups' => ['read']])
                ], Response::HTTP_OK);

            } catch (\Doctrine\DBAL\Exception $e) {
                return $this->json(
                    [
                        "message" => "Impossible d'enrégistrer l'utilisateur",
                        "messageError" => $e->getMessage(),
                        "errorCode" => 500
                    ], Response::HTTP_BAD_REQUEST);
            }
        } else {
            return $this->json(
                [
                    "message" => "Impossible d'enrégistrer l'utilisateur",
                    "errorCode" => 500
                ], Response::HTTP_BAD_REQUEST);
        }

    }

}
