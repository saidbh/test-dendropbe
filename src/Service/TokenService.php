<?php

namespace App\Service;

use App\Entity\User;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TokenService extends AbstractController
{

    const NOT_AUTHORIZATION = Response::HTTP_UNAUTHORIZED;
    const OK = Response::HTTP_OK;

    public function __construct()
    {
    }

    // TOKEN AND AUTHORIZATION
    public function decode($token)
    {
        // DECODE TOKEN
        JWT::$leeway = 60;
        $tokenParse = $this->parseAuth($token);
        return JWT::decode($tokenParse, getenv('SECRET'), array('HS256'));
    }

    /**
     * @param string $token
     * @return int|object
     */
    public function isTokenValid(string $token)
    {
        $reponse = -1;
        $tokenParse = self::parseAuth($token);
        if (!$tokenParse || $tokenParse != 'Bearer') {
            // VERIFY TOKEN EXPIRED
            try {
                $decoded = self::decode($tokenParse);
                if ($decoded !== null) {
                    $reponse = $decoded;
                }
            } catch (\Exception $e) {
                switch ($e->getMessage()) {
                    case 'Expired token':
                        $reponse = 3;
                        break;
                    default:
                        $reponse = -1;
                        break;
                }
            }
        }
        return $reponse;
    }

    public function parseAuth(string $token): ?string
    {
        //FONCTION QUI ENLEVE LE BEARER
        return $token != null ? str_replace('Bearer ', '', $token) : null;
    }

    public function generateToken(User $user)
    {
        // CREATION TOKEN
        $isAdmin = false;
        // VERIFY IF IS ADMIN
        if ($user->getProfil()->getDroit()) {
            if (strtoupper($user->getProfil()->getDroit()->getName()) == 'ADMIN') {
                $isAdmin = true;
            }
        };

        $token = [
            'data' => [
                "id" => $user->getId(),
                "isAdmin" => $isAdmin,
                "groupe" => $user->getGroupe()->getGroupeType(),
                "forfait" => $user->getGroupe()->getForfait() ? $user->getGroupe()->getForfait()->getName() : null,
                "role" => $user->getProfil()->getDroit()->getName()
            ],
            // 3 MOIS VALIDATION
            'exp' => \time() + (131400 * 60),
            'iat' => \time()
        ];

        return JWT::encode($token, getenv('SECRET'));
    }

    // GENERATE EMAIL TOKEN CONFIRMATION

    /**
     * @param User $user
     * @return string
     */
    public function generateTokenPasswordConfirmation(User $user): string
    {
        // GENERATE TOKEN WHEN CHANGING PASSWORD
        $token = array(
            'data' => [
                "id" => $user->getId(),
                "email" => $user->getEmail()
            ],
            //'exp' => \time() + (24 * 60 * 60), // 24 heures
            'exp' => \time() + (2 * 60 * 60), // 2 heures
            'iat' => \time()
        );

        $jwt = JWT::encode($token, getenv('SECRET'));
        return $jwt;
    }

    /**
     * @param ?string $headers
     * @return array
     */
    public function MiddlewareNormalUser(?string $headers): array
    {
        $data = [];

        if (!$headers) {
            return [
                'data' => [
                    'message' => 'Access refusé',
                    'errorCode' => 401
                ],
                'statusCode' => self::NOT_AUTHORIZATION,
            ];
        }

        // VALID TOKEN MESSAGE
        $token = self::isTokenValid($headers);
        if ($token === -1) {
            return [
                'data' => [
                    'message' => 'Access refusé',
                    'errorCode' => 401
                ],
                'statusCode' => self::NOT_AUTHORIZATION
            ];
        } else if ($token === 3) {
            return [
                'data' => [
                    'message' => 'session expiré',
                    'errorCode' => 401
                ],
                'statusCode' => self::NOT_AUTHORIZATION
            ];
        }
        $userToken = $this->getDoctrine()->getRepository(User::class)->findOneBy(["id" => $token->data->id]);
        if ($userToken instanceof User) {
            if (!$userToken->getIsActive() || !$userToken->getEmailActive()) {
                return [
                    'data' => [
                        'message' => 'session expiré',
                        'errorCode' => 401
                    ],
                    'statusCode' => self::NOT_AUTHORIZATION
                ];
            } else {
                $data['user'] = $userToken;
                return $data;
            }

        } else {
            return [
                'data' => [
                    'message' => 'Access refusé',
                    'errorCode' => 401
                ],
                'statusCode' => self::NOT_AUTHORIZATION
            ];
        }

    }

    /**
     * @param string $header
     * @return array
     */
    public function MiddlewareDendroUser(string $header): array
    {
        $data = $this->MiddlewareNormalUser($header);
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        $user = $data['user'];

        if ($user instanceof User) {

            // CHECK IF END SUBSCRIPTION
            if (!$user->getIsActive() || !$user->getEmailActive()) {
                return [
                    'data' => [
                        'message' => 'session expiré',
                        'errorCode' => 401

                    ],
                    'statusCode' => self::NOT_AUTHORIZATION
                ];
            }

            if ($user->getGroupe()->getGroupeType() != 'DENDROMAP') {
                return [
                    'data' => [
                        'message' => 'Access refusé',
                        'errorCode' => 401
                    ],
                    'statusCode' => self::NOT_AUTHORIZATION
                ];
            }

            return $data;

        } else {
            return [
                'data' => [
                    'message' => 'Access refusé',
                    'errorCode' => 401
                ],
                'statusCode' => self::NOT_AUTHORIZATION
            ];
        }
    }

    /**
     * @param string $headers
     * @return array
     */
    public function MiddlewareAdminDedroUser(string $headers): array
    {
        $data = $this->MiddlewareAdminUser($headers);
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        $user = $data['user'];

        if ($user instanceof User) {

            // CHECK IF END SUBSCRIPTION
            if (!$user->getIsActive() || !$user->getEmailActive()) {
                return [
                    'data' => [
                        'message' => 'session expiré',
                        'errorCode' => 401
                    ],
                    'statusCode' => self::NOT_AUTHORIZATION
                ];
            }

            if ($user->getGroupe()->getGroupeType() != 'DENDROMAP') {
                return [
                    'data' => [
                        'message' => 'Access refusé',
                        'errorCode' => 401
                    ],
                    'statusCode' => self::NOT_AUTHORIZATION
                ];
            }

            return $data;

        } else {
            return [
                'data' => [
                    'message' => 'Access refusé',
                    'errorCode' => 401
                ],
                'statusCode' => self::NOT_AUTHORIZATION
            ];
        }
    }

    public function MiddlewareAdminUser($headers)
    {
        $data = [];
        if (!$headers) {
            $data = [
                'data' => [
                    'message' => 'Access refusé',
                    'errorCode' => 401
                ],
                'statusCode' => self::NOT_AUTHORIZATION
            ];
        }
        // VALID TOKEN MESSAGE
        $token = self::isTokenValid($headers);

        if ($token === -1) {
            return [
                'data' => [
                    'message' => 'Access refusé',
                    'errorCode' => 401
                ],
                'statusCode' => self::NOT_AUTHORIZATION
            ];
        } else if ($token === 3) {
            return [
                'data' => [
                    'message' => 'session expiré',
                    'errorCode' => 401
                ],
                'statusCode' => self::NOT_AUTHORIZATION
            ];
        }

        $userToken = $this->getDoctrine()->getRepository(User::class)->findOneBy(["id" => $token->data->id]);

        if ($userToken instanceof User) {
            // CHECK IF END SUBSCRIPTION
            if (!$userToken->getIsActive() || !$userToken->getEmailActive()) {
                return [
                    'data' => [
                        'message' => 'session expiré',
                        'errorCode' => 401
                    ],
                    'statusCode' => self::NOT_AUTHORIZATION
                ];
            } else {

                $rep = self::hasRight($userToken->getId());

                if (!json_decode($rep->getContent())->reponse) {
                    return [
                        'data' => [
                            'message' => 'Access refusé',
                            'errorCode' => 401
                        ],
                        'statusCode' => self::NOT_AUTHORIZATION
                    ];
                }

                $data['user'] = $userToken;
                return $data;
            }

        } else {
            return [
                'data' => [
                    'message' => 'Access refusé',
                    'errorCode' => 401
                ],
                'statusCode' => self::NOT_AUTHORIZATION
            ];
        }

    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function hasRight(int $id): JsonResponse
    {

        $valeur = false;
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(["id" => $id]);

        if (!$user instanceof User) {

            return new JsonResponse(
                [
                    "message" => "Cet utilisateur n'existe pas",
                    "errorCode" => 301
                ], 403);
        }
        // PARCOURIR TOUS LES DROIT
        if (strtoupper($user->getProfil()->getDroit()->getName()) == 'ADMIN') {
            $valeur = true;
        }
        return new JsonResponse([
            "reponse" => $valeur,
            "errorCode" => 200
        ], 200);
    }

    /**
     * @param User $user
     * @return string
     */
    public function generateTokenConfirmation(User $user): string
    {
        // CREATION D'UN TOKEN DE  HOURS
        $token = array(
            'data' => [
                "id" => $user->getId(),
                "groupe" => $user->getGroupe()->getGroupeType()
            ],
            'exp' => \time() + (24 * 60 * 60),
            'iat' => \time()
        );

        return JWT::encode($token, getenv('SECRET'));
    }

}

