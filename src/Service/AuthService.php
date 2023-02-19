<?php

namespace App\Service;

use App\Entity\Groupe;
use App\Entity\Profil;
use App\Entity\User;
use App\Repository\GroupeRepository;
use App\Repository\ProfilRepository;
use App\Repository\UserRepository;
use GuzzleHttp\Client;
use Stripe\Subscription;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Serializer;

class AuthService extends AbstractController
{

    private $_userRepository;
    private $_tokenService;
    private $_profilRepository;
    private $_bus;
    private $_groupeRepository;
    private $_stripeService;
    private $_groupeService;
    private $_userService;

    public function __construct(
        UserRepository      $userRepository,
        TokenService        $tokenService,
        MessageBusInterface $bus,
        ProfilRepository    $profilRepository,
        GroupeRepository    $groupeRepository,
        StripeService       $stripeService,
        GroupeService       $groupeService,
        UserService         $userService
    )
    {
        $this->_userRepository = $userRepository;
        $this->_tokenService = $tokenService;
        $this->_bus = $bus;
        $this->_profilRepository = $profilRepository;
        $this->_groupeRepository = $groupeRepository;
        $this->_stripeService = $stripeService;
        $this->_groupeService = $groupeService;
        $this->_userService = $userService;
    }

    // VALIDATE EMAIL

    /**
     * @param string $email
     * @return bool
     */
    static function emailValid(string $email): bool
    {
        // VERIFIE SI EMAIL EST VALIDE 
        return (bool)preg_match(UserService::EMAIL_REGEX, $email);
    }

    /**
     * @param $password
     * @return bool
     */
    static function passwordValid($password): bool
    {
        return (bool)preg_match_all(UserService::PWD_REGEX, $password);
    }

    //  ENCRYPT PASSWORD
    static function encrypt($password)
    {
        $options = [
            'cost' => 12
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    /**
     * @param User $user
     * @param $plainPassword
     * @return bool
     */
    public static function decrypt(User $user, $plainPassword): bool
    {
        return password_verify($plainPassword, $user->getPassword());
    }

    /**
     * @param Request $request
     * @return array
     */
    public function login(Request $request): array
    {

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        // CHECK ALL DATA IF IS CORRECT
        if (!isset($data['email']) && !isset($data['password'])) {
            return [
                "data" => [
                    "message" => "Saisir informations valides",
                    "errorCode" => 301,
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        if (!self::emailValid($data['email'])) {
            return [
                "data" => [
                    "message" => "Saisir informations valides",
                    "errorCode" => 301,
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        if (!self::passwordValid($data['password'])) {
            return [
                "data" => [
                    "message" => "Password format not correct",
                    "errorCode" => 302,
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
        // CAS D'UN EMAIL
        /** @var User $user */
        $user = $this->_userRepository->findOneBy(["email" => $data['email']]);

        if (!$user instanceof User) {
            return [
                "data" => [
                    "message" => "Email ou mot de passe invalide",
                    "errorCode" => 303
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
        // VERIFY IF PASSWORD READY
        if (!self::decrypt($user, $data['password'])) {
            return
                [
                    "data" => [
                        "message" => "email ou mot de passe invalide",
                        "errorCode" => 304
                    ],
                    "statusCode" => Response::HTTP_UNAUTHORIZED
                ];
        }


        if (!$user->getEmailActive()) {
            return [
                "data" => [
                    "message" => "Votre email n'est pas activé",
                    "errorCode" => 303
                ],
                "statusCode" => Response::HTTP_UNAUTHORIZED
            ];
        }

        if (strtoupper($user->getGroupe()->getGroupeType()) != 'DENDROMAP') {
            if ($user->getGroupe()->getIsStripped()) {
                if ($user->getGroupe()->getForfait()->getCodeForfait() !== 'GRATUIT') {
                    if (!$this->_stripeService->isSubscriptionValid($user->getGroupe()->getSubId())) {
                        return [
                            "data" => [
                                "message" => "l'abonnement n'est pas actif sur ce compte",
                                "errorCode" => 303
                            ],
                            "statusCode" => Response::HTTP_UNAUTHORIZED
                        ];
                    }
                }
            }
            // TO FIX UPDATE DATE
        }

        if (!$user->getIsActive()) {
            return [
                "data" => [
                    "message" => "Compte inactif",
                    "errorCode" => 306
                ],
                "statusCode" => Response::HTTP_UNAUTHORIZED
            ];
        }

        // CONFIGURATION TOKEN
        $token = $this->_tokenService->generateToken($user);
        $_user = $this->_userService->generateUserObjectAuth($user);

        return [
            "data" => [
                "message" => "Connexion reussi",
                "token" => $token,
                "user" => $_user,
                "errorCode" => 200
            ],
            "statusCode" => Response::HTTP_OK
        ];
    }

    public static function isEmailValid(string $email)
    {
        $val = 1;
        $url = getenv('BASE_URL_EMAIL_LAYER') . '?access_key=' . getenv('EMAIL_LAYER_KEY') . '&email=' . $email . '&smtp=' . $val . '&format=' . $val;
        $client = new Client(['headers' =>
            [
                'content-type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
        $request = new \GuzzleHttp\Psr7\Request('GET', $url);
        $response = $client->send($request, ['timeout' => 7.0]);

        return json_decode($response->getBody())->smtp_check;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function signUp(Request $request): array
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        // TEST VARIABLE IS CORRECT
        $handleError = $this->HandleUserInfos($data);

        if (!$handleError['isDone']) {
            return [
                "data" => [
                    "message" => $handleError['message']
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
        // VERIFIE LICENCE NUMBER
        $groupe = $this->_groupeRepository->findOneBy(["name" => $data['email']]);

        if ($groupe instanceof Groupe) {
            return [
                "data" => [
                    "message" => "groupe already defined"
                ],
                "statusCode" => response::HTTP_CONFLICT
            ];
        }
        // GETTING TOKENID
        try {
            // Insert dans API STRIPE que si forfait payant sélectionné
            if ($data['forfait'] != "GRATUIT") {
                $data['changingMode'] = false;
                $data['country'] = 'France';

                $token = $this->_stripeService->createCard($data);

                if (!isset($token['token'])) {
                    return [
                        "data" => [
                            "message" => $token['error']
                        ],
                        "statusCode" => Response::HTTP_BAD_REQUEST
                    ];
                }
                $data['token'] = $token['token'];

                $data['changingMode'] = true;
                /** @var Subscription $subscription */
                $subscription = $this->_stripeService->setSubscription($data);
                if (isset($subscription['error'])) {
                    return [
                        "data" => [
                            "message" => "Informations de la carte incorrectes"
                        ],
                        "statusCode" => Response::HTTP_BAD_REQUEST
                    ];
                }
            }
        } catch (\Exception $e) {
            return [
                "data" => [
                    "message" => "Vérifier vos informations bancaires",
                    "error" => $e->getMessage()
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        // VALUE FOR EDITING GROUPE
        $tableauGroupe ['name'] = $data['email']; // Name du Groupe
        // promo forfait
        $tableauGroupe ['forfait'] = $data['forfait'] == '1M' ? '1M_FREE' : $data['forfait']; // Forfait , CodeForfait
        $tableauGroupe['groupeType'] = 'FORMULE PREMUIM';
        $tableauGroupe['isStripped'] = true;

        $tableauGroupe['dateEcheance'] = $this->_stripeService->setDateEcheance($tableauGroupe['forfait']);
        // IF PAIMENT SELECTED ( with Stripe)       
        if ($data['forfait'] != "GRATUIT") {
            $tableauGroupe['subId'] = $subscription['sub']->id;
            $tableauGroupe['customerId'] = $subscription['sub']->customer;
        }

        $tableauGroupe['licence'] = 1;
        // CREATE A GROUP OF AGENT

        $reponse = $this->_groupeService->newGroupe($tableauGroupe);
        $val_rep = $serializer->decode($reponse->getContent(), 'json');

        switch ($val_rep['errorCode']) {
            case Response::HTTP_OK :
                // GET PROFIL AGENT
                $profil = $this->_profilRepository->findOneBy(["name" => "Manager"]);

                if ($profil instanceof Profil) {
                    $data['profil'] = $profil->getId();
                    $data['groupe'] = $val_rep['data']['id'];
                    $data['isAuth'] = true;
                    $data['emailActive'] = isset($data['emailActive']) ? $data['emailActive'] : 1;
                    $reponseUser = $this->_userService->newUser($data);
                    $reponseUser = $serializer->decode($reponseUser->getContent(), 'json');

                    if ($reponseUser['errorCode'] == Response::HTTP_OK) {
                        return [
                            "data" => [
                                "message" => "User added successfully",
                                "errorCode" => 200
                            ],
                            "statusCode" => Response::HTTP_OK];
                    } else {
                        return [
                            "data" => [
                                "message" => "User not added",
                                "errorCode" => 405
                            ],
                            "statusCode" => Response::HTTP_BAD_REQUEST];
                    }
                } else {
                    return [
                        "data" => [
                            "message" => "Informations obligatoires",
                            "errorCode" => 300
                        ],
                        "statusCode" => Response::HTTP_BAD_REQUEST];
                }

            case 300 :
                return [
                    "data" => [
                        "message" => "Informations obligatoires",
                        "errorCode" => 300
                    ],
                    "statusCode" => Response::HTTP_BAD_REQUEST];

            default :
                return [
                    "data" => [
                        "message" => "Impossible de s'inscrire",
                        "errorCode" => 500
                    ],
                    "statusCode" => Response::HTTP_BAD_REQUEST];
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function verifyTokenConfirmChangePassword(Request $request): array
    {

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['token'])) {
            return [
                "data" => [
                    "message" => "Saisir informations valide",
                    "errorCode" => 301,
                ],
                "statusCode" => Response::HTTP_UNAUTHORIZED
            ];
        }

        // VAILD TOKEN
        if ($this->_tokenService->isTokenValid($data['token'])) {
            return [
                "data" => [
                    "message" => "token valid successfully",
                    "errorCode" => 200
                ],
                "statusCode" => Response::HTTP_OK
            ];
        } else {
            return [
                "data" => [
                    "message" => "auth invalide",
                    "errorCode" => 301,
                ],
                "statusCode" => Response::HTTP_UNAUTHORIZED
            ];
        }

    }

    /**
     * @param Request $request
     * @return array
     */
    public function sendMailPsdConfirmation(Request $request): array
    {

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['email']) || !self::emailValid($data['email'])) {
            return [
                "data" => [
                    "message" => "Saisir informations valide",
                    "errorCode" => 301,
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        // GET USER BY EMAIL
        $user = $this->_userRepository->findOneBy(['email' => $data['email']]);

        if ($user instanceof User) {
            MailService::generateUrlConfirmChangePsd($user, $this->_tokenService->generateTokenPasswordConfirmation($user));
            return [
                "data" => [
                    "message" => "Email send successfull",
                    "errorCode" => 301,
                ],
                "statusCode" => Response::HTTP_OK
            ];
        } else {
            return [
                "data" => [
                    "message" => "Ce compte n'existe pas",
                    "errorCode" => 302,
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

    }

    // CONFIRM EMAIL AND CREATE ACCOUNT STRIPE

    /**
     * @param Request $request
     * @return array
     */
    public function confirmEmail(Request $request): array
    {

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['token']) || !$data['token']) {

            return [
                "data" => [
                    "message" => "Informations obligatoires",
                    "errorCode" => 300
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        // VERIF IF TOKEN IS VALID
        if (!$this->_tokenService->isTokenValid($data['token'])) {
            return [
                "data" => [
                    "message" => "token invalid",
                    "errorCode" => 301
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        $tokenParse = $this->_tokenService->decode($data['token']);
        $user = $this->_userRepository->findOneBy(['id' => $tokenParse->data->id]);

        if ($user instanceof User) {
            // CREATE CARD TOKEN WHEN ISSTRIPPED
            $user->setEmailActive(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return [
                "data" => [
                    "message" => "Email confirmé avec succès",
                    "successCode" => 200
                ],
                "statusCode" => Response::HTTP_OK
            ];
        } else {
            return [
                "data" => [
                    "message" => "Email n'existe pas",
                    "errorCode" => 303
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
    public function changePasswordLanding(Request $request, User $user): array
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');


        if (!isset($data['new']) || !isset($data['confirm'])) {
            return [
                "data" => [
                    "message" => "Saisir informations valides",
                    "errorCode" => 301,
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        if ($data['new'] !== $data['confirm'] || !self::passwordValid($data['new'])) {
            return [
                "data" => [
                    "message" => "Paswword Not conform",
                    "errorCode" => 301,
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        try {
            $user->setPassword(self::encrypt($data['new']));

            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            return [
                "data" => [
                    "message" => "Password change successfully",
                    "errorCode" => 200
                ],
                "statusCode" => Response::HTTP_OK
            ];

        } catch (\Exception $e) {
            return [
                "data" => [
                    "message" => "Impossible to change password",
                    "errorCode" => 200
                ],
                "statusCode" => Response::HTTP_OK
            ];
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function HandleUserInfos(array $data): array
    {
        // END EMAIL AND PASSWORD CONFORME
        if (!isset($data['username']) || !isset($data['email'])
            || !isset($data['password']) || !isset($data['forfait'])
        ) {
            return [
                "message" => "information requirred",
                "isDone" => false
            ];
        }

        if ($data['forfait'] !== 'GRATUIT') {
            if (!isset($data['ccNumber']) || !isset($data['nameCard'])
                || !isset($data['ccCvc']) || !isset($data['ccExp']) || !isset($data['phoneNumber'])
                || !isset($data['address']) || !isset($data['zipCode']) || !isset($data['city']) || !isset($data['name'])) {
                return [
                    "message" => "information requirred 22",
                    "isDone" => false
                ];
            }

            // TEST NUMBER PHONE
            if (!preg_match_all(UserService::TEL_REGEX, $data['phoneNumber'])) {
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

        if (!self::passwordValid($data['password'])) {
            return [
                "message" => "password not valide",
                "isDone" => false
            ];
        }

        if (!self::emailValid($data['email'])) {
            return [
                "message" => "password not valid",
                "isDone" => false
            ];
        }

        // CHECK IF EMAIL ALREADY EXIST
        $user = $this->_userRepository->findOneBy(['email' => $data['email']]);

        if ($user instanceof User) {
            return [
                "message" => "user already exist",
                "isDone" => false
            ];
        }

        return ['isDone' => true];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function verifyToken(Request $request): array
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['token'])) {
            return [
                'data' => [
                    'message' => 'Informations obligatoire'
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        $token = $this->_tokenService->isTokenValid($request['token']);
        if ($token instanceof User) {
            return [
                'data' => [
                    'message' => 'Token valid successfull'
                ],
                'statusCode' => Response::HTTP_OK
            ];
        }
        $statusCode = Response::HTTP_BAD_REQUEST;

        switch ($token) {
            case 3 :
                $data = ['message' => 'Session expiré'];
                return [$data, 'statusCode' => $statusCode];
            case -1 :
                $data = ['message' => 'Access refused'];
                return [$data, 'statusCode' => $statusCode];
            default :
                $data = ['message' => 'Token not valid'];
                return [$data, 'statusCode' => $statusCode];
        }
    }
}

