<?php

namespace App\Service;

use App\Entity\Forfait;
use App\Entity\Groupe;
use App\Entity\Inventaire;
use App\Entity\Profil;
use App\Entity\User;
use App\Message\NotificationMessage;
use App\Repository\InventaireRepository;
use App\Repository\ProfilRepository;
use App\Repository\UserRepository;
use Stripe\Subscription;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Serializer;

class UserService extends AbstractController
{

    private $_tokenService;
    private $_bus;
    private $_repository;
    private $_profilRepository;
    private $_mailService;
    private $_inventaireRepository;
    private $_stripeService;
    private $_imageService;
    private $_forfaitService;

    const PWD_REGEX = '^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$^';
    const TEL_REGEX = '^0[1-9]([-. ]?[0-9]{2}){4}$^';
    const EMAIL_REGEX = '#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i';
    const UPLOAD_IMG_DIR = '../public/api/images/';

    public function __construct(
        TokenService         $tokenService,
        MessageBusInterface  $bus,
        UserRepository       $repository,
        ProfilRepository     $profilRepository,
        InventaireRepository $inventaireRepository,
        MailService          $mailService,
        StripeService        $stripeService,
        ImageService         $imageService,
        ForfaitService       $forfaitService
    )
    {
        $this->_tokenService = $tokenService;
        $this->_bus = $bus;
        $this->_repository = $repository;
        $this->_profilRepository = $profilRepository;
        $this->_inventaireRepository = $inventaireRepository;
        $this->_mailService = $mailService;
        $this->_stripeService = $stripeService;
        $this->_imageService = $imageService;
        $this->_forfaitService = $forfaitService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function addUser(Request $request): array
    {
        // ADD USER
        $data = $this->_tokenService->MiddlewareAdminUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        /** @var User $userToken */
        $userToken = $data['user'];

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['email']) || !isset($data['profil']) || !isset($data['password']) || !isset($data['username'])) {
            return [
                "data" => [
                    "message" => "Information required",
                    "errorCode" => 401
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        if ($userToken->getGroupe()->getGroupeType() !== GroupeService::getGroupType()[0]) { // When Client Dendro add a user
            $data['groupe'] = $userToken->getGroupe()->getName(); // get the group name user
        } else {
            if (!isset($data['groupe'])) {
                return [
                    "data" => [
                        "message" => "Information required",
                    ], "statusCode" => Response::HTTP_BAD_REQUEST
                ];
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                "data" => [
                    "message" => "Information required",
                    "errorCode" => 302
                ], "statusCode" => Response::HTTP_CONFLICT
            ];
        }

        if (!AuthService::passwordValid($data['password'])) {
            return [
                "data" => [
                    "message" => "mot de passe doit contenir 1 caractère speciaux, numeric et une majiscule",
                    "errorCode" => 303
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        $user = $this->_repository->findOneBy(["email" => $data['email']]);

        if ($user instanceof User) {
            return [
                "data" => [
                    "message" => "Ce compte existe deja",
                    "errorCode" => 302
                ], "statusCode" => Response::HTTP_CONFLICT
            ];
        }

        // Get groupe user we need to crate
        /** @var Groupe $groupe */
        $groupe = $this->getDoctrine()->getRepository(Groupe::class)->findOneBy(['id' =>
            (strtoupper($userToken->getGroupe()->getGroupeType()) != 'DENDROMAP') ? $userToken->getGroupe()->getId() : $data['groupe']]);

        if(!$groupe instanceof Groupe) {
            return [
                'data' => [
                    "message" => "Information required",
                    "errorCode" => 403
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        // verify if licence user when added user
        if($userToken->getGroupe()->getGroupeType() !== $groupe->getGroupeType()) {

            if(self::isLicenceOver($groupe, $this->_repository->findBy([]))) {
                return [
                    "data" => [
                        "message" => "Nombre de licence atteint",
                        "errorCode" => 403
                    ],
                    "statusCode" => Response::HTTP_BAD_REQUEST
                ];
            }
        }

        $data['emailActive'] = 1;

        $response = self::newUser($data);
        $val_rep = $serializer->decode($response->getContent(), 'json');

        if ($val_rep['errorCode'] != Response::HTTP_OK) {
            return [
                "data" => [
                    "message" => "operation not successfull",
                    "errorCode" => 400
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        return [
            "data" => $val_rep['data'],
            "statusCode" => Response::HTTP_CREATED
        ];
    }

    private static function isLicenceOver(Groupe $groupe, array $usersList): bool
    {
        return $groupe->getLicence() <= count(self::userGroupeList($usersList, $groupe));
    }

    /**
     * @param $users
     * @param Groupe $groupe
     * @return array
     */
    private static function userGroupeList($users, Groupe $groupe): array
    {
        $_users = [];
        foreach ($users as $user) {
            if ($user->getGroupe()->getId() == $groupe->getid()) {
                array_push($_users, $user);
            }
        }

        return $_users;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function users(Request $request): array
    {
        $data = $this->_tokenService->MiddlewareAdminUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        $userRepo = $this->_repository->findOneBy(['id' => $data['user']->getId()]);
        $users = $this->_repository->findBy(["isRoot" => 0, "deleted" => false], ["id" => "ASC"]);

        return [
            "data" => self::generateArray($users, $userRepo),
            "statusCode" => Response::HTTP_OK
        ];
    }

    /**
     * @param Request $request
     * @param User $user
     * @return array
     */
    public function user(Request $request, User $user): array
    {
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        return [
            "data" => $this->serializer($user),
            "statusCode" => Response::HTTP_OK
        ];
    }

    /**
     * @param Request $request
     * @param User $user
     * @return array
     */
    public function modifUser(Request $request, User $user): array
    {
        /**
         *  MODIFY ONE USER WITH JUST EMAIL, USERNAME && PROFIL NAME
         */
        $data = $this->_tokenService->MiddlewareAdminUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        // HANDLE INFO FROM SERVER
        if (!isset($data['email']) || !isset($data['username']) || !isset($data['profil']) || !AuthService::emailValid($data['email'])) {
            return [
                "data" => [
                    "message" => "Information required",
                    "errorCode" => 300
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        $val = $this->_repository->existUserModif($user, $data['email']);

        if ($val) {
            return [
                "data" => [
                    "message" => "User already exist"
                ],
                "statusCode" => Response::HTTP_CONFLICT
            ];
        }

        /** @var Profil $profil */
        $profil = $this->_profilRepository->findOneBy(['id' => $data['profil']]);

        if (!$profil instanceof Profil) {
            return [
                "data" => [
                    "message" => "Impossible d'enregistrer ce compte"
                ],
                "statusCode" => Response:: HTTP_BAD_REQUEST
            ];
        }

        // IMpossible changing group
        if ((strtoupper($user->getProfil()->getName()) === 'MANAGER') || (strtoupper($user->getProfil()->getName()) === 'VISITEUR') ) {
            return [
                "data" => [
                    "message" => "Modification impossible"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        if( (strtoupper($user->getProfil()->getName()) === 'AGENT') && (strtoupper($profil->getName()) === 'MANAGER') ) {
            return [
                "data" => [
                    "message" => "Modification Impossible"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        try {
            $user->setUsername($data['username']);
            $user->setProfil($profil);
            $user->setEmail($data['email']);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return [
                "data" => $this->serializer($user),
                "statusCode" => Response::HTTP_OK
            ];
        } catch (\Exception $e) {
            return [
                "data" => [
                    "message" => "Impossible to update",
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    /**
     * @param Request $request
     * @param User $user
     * @return array
     */
    public function delete(Request $request, User $user): array
    {
        $data = $this->_tokenService->MiddlewareAdminUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        $userInit = $this->_repository->findOneBy(['email' => $user->getEmail(), 'isInit' => 1]);
        if ($userInit instanceof User) {
            return [
                "data" => [
                    "message" => "Impossible de supprimer ce compte"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
        // VERIFY IF USER IS MAPPING TO INVENTORY
        $em = $this->getDoctrine()->getManager();

        /** Possibility to delete Groupe
         *  If just One Users
         *  IsUsersMappingGroup >= 1 not delete
         */
        /** @var User[] $users */
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        try {
            // Test if User isStripped
            if ($user->getGroupe()->getIsStripped()) {
                if ($user->getGroupe()->getForfait()->getCodeForfait() == 'GRATUIT') {
                    $g = $user->getGroupe();
                    $em->remove($user);
                    $em->remove($g);
                    $em->flush();
                    return [
                        "data" => [
                            "message" => "Suppression effectuée avec succès"
                        ],
                        "statusCode" => Response::HTTP_NO_CONTENT
                    ];
                }
                if (GroupeService::isDateExpired($user->getGroupe())) {
                    $em->remove($user->getGroupe());
                    $em->flush();
                    return [
                        "data" => [
                            "message" => "Suppression effectuée avec succès"
                        ],
                        "statusCode" => Response::HTTP_NO_CONTENT
                    ];
                } else {
                    return [
                        "data" => [
                            "message" => "Impossible de supprimer ce compte car il a un abonnement actif"
                        ],
                        "statusCode" => Response::HTTP_BAD_REQUEST
                    ];
                }
            } else {
                count(self::isUserMappingGroupe($users, $user->getGroupe())) == 1 ?
                    $em->remove($user->getGroupe()) : $em->remove($user);
                $em->flush();
            }

            $inventaires = $this->_inventaireRepository->findBy([]);
            if (count(UserService::isUserMappingInventaire($inventaires, $user)) >= 1) {
                $user->setDeleted(true);
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return [
                    "data" => [
                        "message" => "Impossible to delete compte"
                    ],
                    "statusCode" => Response::HTTP_NO_CONTENT
                ];
            }

            return [
                "data" => [
                    "message" => "Impossible de supprimer ce compte"
                ],
                "statusCode" => Response::HTTP_NO_CONTENT
            ];

        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "data" => ["message" => "Souppression impossible"],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    /**
     * @param array $inventaires
     * @param User $user
     * @return array
     */
    public static function isUserMappingInventaire(array $inventaires, User $user): array
    {
        return array_filter($inventaires, function (Inventaire $e) use ($user) {
            return $e->getUser()->getId() === $user->getId();
        });
    }

    /**
     * @param array $users
     * @param Groupe $groupe
     * @return array
     */
    public static function isUserMappingGroupe(array $users, Groupe $groupe): array
    {
        return array_filter($users, function (User $e) use ($groupe) {
            return $e->getGroupe()->getId() === $groupe->getId();
        });
    }

    /**
     * @param Request $request
     * @param User $user
     * @return array
     */
    public function update(Request $request, User $user): array
    {
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        $userToken = $data['user'];

        $body = $request->getContent();
        $data = json_decode($body, true);

        if (!isset($data['email']) || !isset($data['username']) || !isset($data['profil'])) {
            return [
                "data" => [
                    "message" => "Saisir informations obligatoires",
                    "errorCode" => 300
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        if ($user->getGroupe()['groupeType'] != 'DENDROMAP') {
            // PERMETTRE LE MODIFICATION DE PROFIL PAR GROUPE
            $profil = $this->_profilRepository->findOneBy(['name' => $data['profil'], 'groupeType' => $user->getGroupe()->getGroupeType()]);
            if (!$profil instanceof Profil) {
                return [
                    "data" => [
                        "message" => "Saisir informations obligatoires",
                        "errorCode" => 300

                    ], "statusCode" => Response::HTTP_BAD_REQUEST];
            }

            if ($userToken->getGroupe()->getGroupeType() != $user->getGroupe()->getGroupeType()) {
                return [
                    "data" => [
                        "message" => "Accès refusé",
                        "errorCode" => 301
                    ], Response::HTTP_UNAUTHORIZED];
            }
            // ON TESTE SI LE PROFIL FOURNI APPARTIENT BIEN AU GROUPE
        }

        $user->setUpdatedAt(new \DateTime('now'));
        $user->setCreatedAt($data['createdAt']);
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $user->setProfil($data['profil']);

        try {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return [
                "data" => [
                    "message" => "Mis a jour effetcué avec succès",
                    "errorCode" => 200
                ], "statusCode" => Response::HTTP_ACCEPTED];
        } catch (\Doctrine\DBAL\DBALException $e) {
            return [
                "data" =>
                    [
                        "message" => "Impossible de modifier",
                        "errorCode" => 500
                    ],
                "statusCode" => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }
    }

    /**
     * @param Request $request
     * @param $user
     * @return array
     */
    public function activeOrDesactiveUser(Request $request, $user): array
    {
        $data = $this->_tokenService->MiddlewareAdminUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        if ($user instanceof User) {
            $user->getIsActive() ? $user->setIsActive(false) : $user->setIsActive(true);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return [
                "data" => [
                    "message" => "operation success"
                ],
                "statusCode" => Response::HTTP_ACCEPTED
            ];
        } else {
            return [
                "data" => [
                    "message" => "User not found"
                ],
                "statusCode" => Response::HTTP_NOT_FOUND
            ];
        }

    }

    /**
     * @param Request $request
     * @param User $user
     * @return array
     */
    public function updateProfilUser(Request $request, User $user): array
    {
        $data = $this->_tokenService->MiddlewareAdminUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        /** @var User $userConnected */
        $userConnected = $data['user'];

        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['profilName'])) {
            return [
                "data" => [
                    "message" => "Information ogligatoire",
                    "errorCode" => 300
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        $profil = $this->_profilRepository->findOneBy(['id' => $data['profilName']]);

        if (!$profil instanceof Profil) { // profil not exist
            return [
                "data" => [
                    "message" => "impossible de changer de profil"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        if($userConnected->getGroupe()->getGroupeType() != 'DENDROMAP') { // Case changing Groupe as Admin
            // Impossible changing group
            if ((strtoupper($user->getProfil()->getName()) === 'MANAGER') || (strtoupper($user->getProfil()->getName()) === 'VISITEUR') ) {
                return [
                    "data" => [
                        "message" => "Modification impossible"
                    ],
                    "statusCode" => Response::HTTP_BAD_REQUEST
                ];
            }

            if( (strtoupper($user->getProfil()->getName()) === 'AGENT') && (strtoupper($profil->getName()) === 'MANAGER') ) {
                return [
                    "data" => [
                        "message" => "Modification Impossible"
                    ],
                    "statusCode" => Response::HTTP_BAD_REQUEST
                ];
            }
        }

        $user->setProfil($profil);

        if ($user->getGroupe()->getGroupeType() !== $profil->getGroupeType()) {
            return [
                "data" => [
                    "message" => "Ce profil ne peut être affecté a ce groupe"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return [
                "data" => [
                    "message" => "profil changé avec succès"
                ],
                "statusCode" => Response::HTTP_ACCEPTED
            ];
        } catch (\Exception $e) {
            return [
                "data" => [
                    "message" => "profil changé avec succès"
                ],
                "statusCode" => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function confirmationUser(Request $request): array
    {
        /**
         *  VERIFY IF TOKEN SEND TO THE SERVER IS VALID
         *  THEN CHANGE EMAIL TO ACTIVE STATUS
         */
        $token = $request->request->get('token');

        if (!$token) {
            return [
                "data" => [
                    'message' => 'Not found',
                    'errorCode' => Response::HTTP_UNAUTHORIZED
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
        // VALID TOKEN MESSAGE
        $headers = 'Bearer ' . $token;
        $token = $this->_tokenService->isTokenValid($headers);

        if ($token === -1) {
            return [
                "data" => [
                    'message' => 'Le lien a expiré',
                    'errorCode' => 402
                ], "statusCode" => Response::HTTP_UNAUTHORIZED];
        } else if ($token === 3) {
            return [
                "data" => [
                    'message' => 'Le lien a expiré',
                    'errorCode' => 402
                ], "statusCode" => Response::HTTP_BAD_REQUEST];
        }

        // GET TOKEN DATA
        $user = $this->_repository->findOneBy(['id' => $token->data->id]);

        if (!$user) {
            return [
                "data" => [
                    'message' => 'Aucun fichier envoyer',
                    'errorCode' => 303
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
        // UPDATE STATE ACTIVE
        $user->setEmailActive(1);

        try {
            return [
                "data" => [
                    "message" => "Email confirmé avec succès"
                ], "statusCode" => Response::HTTP_OK
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "data" => [
                    "message" => "Impossible de supprimer"
                ], "statusCode" => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }
    }

    /**
     * @param Request $request
     * @param User $user
     * @return array
     */
    public function sendMailConfirmation(Request $request, User $user): array
    {
        /**
         *  SEND AN MAIL TO ACTIVE AN EMAIL ACCOUNT
         *  WHEN TIME IS EXPIRED
         *  EMAIL TIME VALUE VALIDITY IS 1 DAY
         *  ONLY ADMIN USER CAN DO THIS
         */
        $data = $this->_tokenService->MiddlewareAdminUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        if ($user instanceof User) {

            try {
                $token = $this->_tokenService->generateTokenConfirmation($user);
                MailService::sendEmailConfirmation($user, $token);
                return [
                    "data" => [
                        "message" => "Mail envoyé avec succès",
                    ],
                    "statusCode" => Response::HTTP_BAD_REQUEST
                ];
            } catch (\Exception $e) {
                return [
                    "data" => [
                        "message" => "Le mail n'a pas été envoyé",
                        "error" => 301
                    ],
                    "statusCode" => Response::HTTP_BAD_REQUEST
                ];
            }
        } else {
            return [
                "data" => [
                    "message" => "Ce compte n'existe pas",
                    "error" => 300
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    /**
     * @param User $user
     * @param Request $request
     * @return array
     */
    public function generateDefaultPassword(User $user, Request $request)
    {
        /**
         *  GENERATE A DEFAULT PASSWORD WHEN USER IS CREATED
         * ONLY AN ADMIN USER CAN DO THIS
         */
        $data = $this->_tokenService->MiddlewareAdminUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        try {
            MailService::generateUrlConfirmChangePsd($user, $this->_tokenService->generateTokenPasswordConfirmation($user));
            return [
                "data" => [
                    "message" => "Un mail a été envoyé a l'utilisateur pour confirmation"
                ],
                "statusCode" => Response::HTTP_OK
            ];
        } catch (\Exception $e) {
            return [
                "data" => [
                    "message" => "Impossible de reinitialiser le mot de passe"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

    }

    // STATIC FUNCTION GENERATE ARRAY

    /**
     * @param array $users
     * @param User $userRepo
     * @return array
     */
    public function generateArray(array $users, User $userRepo): array
    {
        $_users = [];
        foreach ($users as $user) {
            if ($userRepo->getGroupe()->getGroupeType() != 'DENDROMAP') {
                if ($user->getGroupe()->getId() == $userRepo->getGroupe()->getId()) {
                    array_push($_users, $this->serializer($user));
                }
            } else {
                array_push($_users, $this->serializer($user));
            }
        }
        return $_users;
    }

    /**
     * @param Request $request
     * @param User $user
     * @return array
     */
    public function updateForfaitWithStripe(Request $request, User $user): array
    {
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        $data['email'] = $user->getEmail();

        if ($data['forfait'] !== 'GRATUIT') {
            if ($data['changingMode']) {
                $handleError = $this->HandleUserInfos($data);

                if (!$handleError['isDone']) {
                    return [
                        "data" => [
                            "message" => $handleError['message']
                        ],
                        "statusCode" => Response::HTTP_BAD_REQUEST
                    ];
                }
            }
        }

        // Pro  === Agent and isStripped == true when subscribed
        // Pro == Agent and isStripped == false when not subscribed
        if ($user->getGroupe()->getIsStripped() && $data['forfait'] !== 'GRATUIT') { // SUBSCRIBE TO PAY FORFAIT

            if ($user->getGroupe()->getForfait()->getCodeForfait() === 'GRATUIT') { // WANT TO CHANGE FROM FREE
                // FREE TO PAYANT
                $response = $this->updateGroupeWithCard($user->getGroupe(), $data);
                if ($response['statusCode'] !== Response::HTTP_OK) {
                    return $response;
                }

                MailService::sendMailForfait(ForfaitService::getInfoDataForfait($data['forfait']), $user, 'FREE_TO_PAY');
                return $this->updateUserWithCard($user, $data);
            } else if ($user->getGroupe()->getForfait()->getCodeForfait() !== 'GRATUIT') {
                if (GroupeService::isRightTimeChangingForfaitBis($user->getGroupe())) { // if the customer is on the right period to change
                    $response = $this->updateGroupeWithCard($user->getGroupe(), $data, 'PAY_TO_PAY');
                    if ($response['statusCode'] != Response::HTTP_OK) {
                        return $response;
                    }
                    MailService::sendMailForfait(ForfaitService::getInfoDataForfait($data['forfait']), $user, 'PAY_TO_PAY');
                    return $response;
                } else {
                    return [
                        "data" => [
                            "message" => 'Votre abonnement actuel n\'a pas encore pris fin'
                        ],
                        "statusCode" => Response::HTTP_BAD_REQUEST
                    ];
                }
            }
        } else if ($user->getGroupe()->getIsStripped() && $data['forfait'] === 'GRATUIT') { // PAY TO FREE
            if ($user->getGroupe()->getForfait()->getCodeForfait() !== 'GRATUIT') { // END SUBSCRIPTION
                // END SUBSCRIPTION
                return $this->unsubscribe($request, $user);
            } else {
                return [
                    'data' => [
                        'message' => 'Vous n\'avez pas modifié votre abonnement'
                    ],
                    'statusCode' => Response::HTTP_BAD_REQUEST
                ];
            }
        }
        return [
            'data' => [
                'message' => 'Vous n\'avez aucun droit pour effectuer cette opération'
            ],
            'statusCode' => Response::HTTP_BAD_REQUEST
        ];
    }

    /**
     * @param Request $request
     * @param User $user
     * @return array
     */
    public function unsubscribe(Request $request, User $user): array
    {
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        // VERIFY IF USER IS STRIPPING
        if ($user->getGroupe()->getIsStripped()) {
            // Send notification to Admin Compte and inform you to users
            $this->_bus->dispatch(new NotificationMessage(
                [
                    'type' => NotificationMessage::TYPE_UNSUBSCRIPTION_ABO,
                    'groupeId' => $user->getGroupe()->getId()
                ]
            ));

            if (GroupeService::isDateExpired($user->getGroupe())) { // WHEN FORFAIT END
                GroupeService::endSubscription($user, $this->_forfaitService);
                // SET DATE ECHEANCE
                $now = new \DateTime('now');
                $groupe = $this->getDoctrine()->getRepository(Groupe::class)->findOneBy(['id' => $user->getGroupe()->getId()]);

                $groupe->setDateEcheance($now->add(new \DateInterval("P6M")));
                $em = $this->getDoctrine()->getManager();
                $em->persist($groupe);
                $em->flush();
            }
            // DEMANDE DE RESILIATION DE CONTRAT
            MailService::sendMailForfait([], $user, 'DOWN_TO_PAY');
            return [
                'data' => ['message' => "Votre résiliation sera prise en compte à la fin de votre abonnement"],
                'statusCode' => Response::HTTP_OK
            ];
        }
        return [
            'data' => [
                'message' => "Vous n'avez pas de droit pour effectuer cette operation"
            ],
            'statusCode' => Response::HTTP_BAD_REQUEST
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function alertUserBeforeExpiredAccount(Request $request): array
    {
        // AVANT ET DEBUT DE LA RECONDUCTION DU COMPTE
        $data = $this->_tokenService->MiddlewareDendroUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['userIds'])) {
            return [
                'data' => ['message' => 'Informations obligatoires'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        foreach ($data['userIds'] as $userId) {
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['userId' => $userId]);
            if (!$user instanceof User) {
                return [
                    'data' => [
                        'message' => 'Certains Utilisateurs sont introuvables'
                    ],
                    'statusCode' => Response::HTTP_BAD_REQUEST
                ];
            }
            MailService::sendMailForfait([], $user, 'RENEW_TO_PAY');
        }
        return [
            'data' => ['message' => 'Impossible d\'envoyer le mail'],
            'statusCode' => Response::HTTP_BAD_REQUEST
        ];
    }

    /**
     * @param Groupe $groupe
     * @param array $data
     * @param string $type
     * @return array
     */
    private function updateGroupeWithCard(Groupe $groupe, array $data, $type = 'FREE_TO_PAY'): array
    {
        $data['country'] = 'France';
        $data['customerID'] = $groupe->getCustomerId();
        // Add CARD
        // Case of no forfait first and possibility to add an another forfait
        $data['forfait'] = (!$groupe->getCustomerId() && $data['forfait'] === '1M') ? '1M_FREE' : $data['forfait'];

        /** @var Forfait $forfait */
        $forfait = $this->getDoctrine()->getRepository(Forfait::class)->findOneBy(['codeForfait' => $data['forfait']]);

        if (!$forfait instanceof Forfait) {
            return [
                "data" => [
                    "message" => "Le forfait n'est pas valide"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        if ($data['changingMode']) {
            try {
                $token = $this->_stripeService->createCard($data);
                $data['token'] = $token['token'];
            } catch (\Exception $e) {
                if (!isset($token['token'])) {
                    return [
                        "data" => [
                            "message" => 'Informations de la carte n\'est pas valide'
                        ],
                        "statusCode" => Response::HTTP_BAD_REQUEST
                    ];
                }
            }

            // dans le cas d'un compte nn gratuit
            // update customer token and update subscription
            /** @var Subscription $subscription */
            $subscription = ($type == 'FREE_TO_PAY') ? $this->_stripeService->setSubscription($data)
                : $this->_stripeService->updateSubscription($data, $groupe->getSubId());

            if (isset($subscription['error'])) {
                return [
                    "data" => [
                        "message" => $subscription['error']
                    ],
                    "statusCode" => Response::HTTP_BAD_REQUEST
                ];
            }
            $groupe->setCustomerId($subscription['sub']->customer);
            $groupe->setSubId($subscription['sub']->id);
        } else {
            /** @var Subscription $subscription */
            $subscription = $this->_stripeService->updateSubscription($data, $groupe->getSubId());
            if (isset($subscription['error'])) {
                return [
                    "data" => [
                        "message" => 'Impossible de changer votre forfait',
                        'error' => 'for update'
                    ],
                    "statusCode" => Response::HTTP_BAD_REQUEST
                ];
            }
            $groupe->setSubId($subscription['sub']->id);
        }
        // UPDATE GROUPE
        $groupe->setLicence(1);
        $groupe->setIsStripped(true);
        $groupe->setDateSubscribed(new \DateTime('now'));
        $groupe->setForfait($forfait);
        $groupe->setDateEcheance($this->_stripeService->setDateEcheance($forfait->getCodeForfait()));
        $groupe->setUpdatedAt(new \DateTime('now'));

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($groupe);
            $em->flush();
            return [
                "data" => [
                    "message" => "Groupe update with succès"
                ],
                "statusCode" => Response::HTTP_OK];
        } catch (\Exception $e) {
            return [
                "data" => [
                    "message" => "User not added",
                    "errorCode" => 405
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST];
        }
    }

    /**
     * @param User $user
     * @param array $data
     * @return array
     */
    private function updateUserWithCard(User $user, array $data): array
    {
        $user->setAddress($data['address']);
        $user->setAddress2($data['address2']);
        $user->setCity($data['city']);
        $user->setPhoneNumber($data['phoneNumber']);
        $user->setZipCode($data['zipCode']);

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return [
                "data" => [
                    "message" => "Forfait mis a jour avec succèss",
                ],
                "statusCode" => Response::HTTP_OK];
        } catch (\Exception $e) {
            return [
                "data" => [
                    "message" => "Impossible de mettre les informations à jour",
                    "errorCode" => 405
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST];
        }
    }

    /**
     * @param Request $request
     * @param User $user
     * @return array
     */
    public function modifCompteProfil(Request $request, User $user): array
    {
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        $data = $this->get('serializer')->decode($request->getContent(), 'json');

        if (!isset($data['groupe']) || !isset($data['email'])) {
            return [
                'data' => [
                    'message' => 'information obligatoire'
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        // TO GET IF EMAIL EXIST

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                "data" => [
                    "message" => "Email invalide",
                    "errorCode" => 302
                ], "statusCode" => Response::HTTP_CONFLICT
            ];
        }

        // GET IF GROUP NAME EXIST
        if (!$data['groupe']) {
            return [
                'data' => [
                    'message' => 'information obligatoire'
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        if (strtoupper($user->getProfil()->getName()) == 'MANAGER' || strtoupper($user->getGroupe()->getGroupeType()) == 'DENDROMAP' || $user->getGroupe()->getIsStripped()) {

            /** @var Groupe $groupe */
            $groupe = $this->getDoctrine()->getRepository(Groupe::class)->findOneByUpdate($user->getGroupe(), $data['groupe']);

            if ($groupe instanceof Groupe) {
                return [
                    'data' => [
                        'message' => 'Vous tentez de modifier un groupe qui existe déjà'
                    ],
                    'statusCode' => Response::HTTP_BAD_REQUEST
                ];
            }
            // IF NOT EXIST GROUPE NAME
            try {
                $groupe = $user->getGroupe();
                $groupe->setName($data['groupe']);

                // all serial things
                $groupe->setSiret($data['siret']);
                $groupe->setNumCertification($data['numCertification']);
                $groupe->setCp($data['cp']);
                $groupe->setVille($data['ville']);
                $groupe->setAddressSociete($data['addressSociete']);
                $groupe->setNameSociete($data['nameSociete']);

                $this->getDoctrine()->getManager()->persist($groupe);
                $this->getDoctrine()->getManager()->flush();
            } catch (\Exception $e) {
                return [
                    'data' => [
                        'message' => 'Impossible to update groupe',
                        'error' => $e->getMessage()
                    ],
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
                ];
            }
        }
        // VERIFY IF EMAIL ON UPDATE EXIST
        $userExist = $this->getDoctrine()->getRepository(User::class)->existUserModif($user, $data['email']);
        if ($userExist instanceof User) {
            return [
                "data" => [
                    "message" => "Vous tentez de modifier un email qui existe déjà",
                    "errorCode" => 303
                ], "statusCode" => Response::HTTP_CONFLICT
            ];
        }

        isset($data['nom']) ? $user->setNom($data['nom']) : null;
        isset($data['prenom']) ? $user->setPrenom($data['prenom']) : null;
        isset($data['email']) ? $user->setEmail($data['email']) : null;

        try {
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
            return [
                'data' => [
                    'message' => 'Modification effectuée avec succès'
                ],
                'statusCode' => Response::HTTP_OK
            ];
        } catch (\Exception $e) {
            return [
                'data' => [
                    'message' => 'Impossible de modifier certaines informations'
                ],
                'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ];
        }
    }

    /**
     * @param array $data
     * @return array|bool[]
     */
    public function HandleUserInfos(array $data): array
    {
        // END EMAIL AND PASSWORD CONFORME
        if ($data['forfait'] !== 'GRATUIT') {
            if (!isset($data['ccNumber']) || !isset($data['nameCard'])
                || !isset($data['ccCvc']) || !isset($data['ccExp']) || !isset($data['phoneNumber'])
                || !isset($data['address']) || !isset($data['zipCode']) || !isset($data['city']) || !isset($data['name'])) {
                return [
                    "message" => "information required 22",
                    "isDone" => false
                ];
            }

            // TEST NUMBER PHONE
            if (!preg_match_all(self::TEL_REGEX, $data['phoneNumber'])) {
                return [
                    "message" => "Phone not correct",
                    "isDone" => false
                ];
            }

            if (!$data['address'] || !$data['zipCode']
                || !$data['city']) {
                return [
                    "message" => "Informations requirred",
                    "isDone" => false
                ];
            }
        }

        return ['isDone' => true];
    }

    /**
     * @param User $user
     * @return array|bool|float|int|mixed|string|null
     */
    public function serializer(User $user)
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        return $serializer->normalize($user, 'json', ['groups' => ['read']]);
    }

    /**
     * @param User $user
     * @return array|bool|float|int|mixed|string|null
     */
    public function generateUserObjectAuth(User $user)
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        return $serializer->normalize($user, 'json', ['groups' => ['auth']]);
    }

    /**
     * @param Request $request
     * @param User $user
     * @return array
     */
    public function uploadImage(Request $request, User $user): array
    {

        $data['img'] = $request->files->get('img');
        $data['username'] = $request->request->get('username');

        if (!isset($data['username'])) {
            return [
                'data' => [
                    'message' => 'Information obligatoire',
                    'errorCode' => 303
                ], 'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        if (isset($data['img']) && $data['img']) {
            $response = $this->_imageService->addImage($data['img'], self::UPLOAD_IMG_DIR, false);

            if (is_array($response)) {
                return $response;
            }
            $user->setImg($response);
        }
        $user->setUsername($data['username']);

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return ["data" => [
                "message" => "Image upload avec succès"], 'statusCode' => Response::HTTP_ACCEPTED];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "data" => ["message" => "Impossible d'uploader le fichier"],
                'statusCode' => Response::HTTP_BAD_REQUEST];
        }

    }

    /**
     * @param array $addUser
     * @return JsonResponse
     */
    public function newUser(array $addUser): JsonResponse
    {
        // HANDLER ADD USER
        $user = new User();

        $user->setNom($addUser['nom'] ?? '');

        $user->setPrenom($addUser['prenom'] ?? '');
        $user->setUsername($addUser['username'] ?? '');
        $user->setEmail($addUser['email'] ?? '');

        $user->setIsActive($addUser['isActive'] ?? 1);
        $user->setIsInit($addUser['isInit'] ?? '');
        $user->setIsRoot($addUser['isRoot'] ?? '');
        $user->setEmailActive($addUser['emailActive'] ?? '');
        $user->setCreatedAt(new \DateTime('now'));
        $user->setDeleted(false);

        // coord facturations
        $user->setAddress($addUser['address'] ?? '');
        $user->setAddress2($addUser['address2'] ?? '');
        $user->setCity($addUser['city'] ?? '');
        $user->setPhoneNumber($addUser['phoneNumber'] ?? '');
        $user->setZipCode($addUser['zipCode'] ?? '');

        $user->setPassword($addUser['password'] ?? '');

        $user->setPassword(AuthService::encrypt($user->getPassword()));
        // VERIFY IMAGE

        if (is_numeric($addUser['profil'])) {
            $profil = $this->getDoctrine()->getRepository(Profil::class)->findOneBy(['id' => $addUser['profil']]);
        } else {
            $profil = $this->getDoctrine()->getRepository(Profil::class)->findOneBy(['name' => $addUser['profil']]);
        }

        if ($profil instanceof Profil) {
            $user->setProfil($profil);
            // SET GROUPE

            if ($addUser['groupe']) {
                if (is_numeric($addUser['groupe'])) {
                    $groupe = $this->getDoctrine()->getRepository(Groupe::class)->findOneBy(['id' => $addUser['groupe']]);
                } else {
                    $groupe = $this->getDoctrine()->getRepository(Groupe::class)->findOneBy(['name' => $addUser['groupe']]);
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

                $this->getDoctrine()->getManager()->persist($user);
                $this->getDoctrine()->getManager()->flush();
                if (isset($addUser['isAuth'])) {
                    $discount = isset($addUser['discount']) ?  $addUser['discount'] : '';
                    MailService::sendMailWhenSubscribed($user, $discount);
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
