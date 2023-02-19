<?php

namespace App\Service;

use App\Entity\Espece;
use App\Entity\User;
use App\Form\EspeceType;
use App\Repository\EspeceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;


class EspeceService extends AbstractController
{

    private $repository;
    private $_tokenService;

    public function __construct(EspeceRepository $repository, TokenService $_tokenService)
    {
        $this->repository = $repository;
        $this->_tokenService = $_tokenService;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function addEspece(Request $request): array
    {
        // MIDDLEWARE
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        $user = $data['user'];

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        /** @var Espece $espece */
        $espece = $serializer->deserialize($request->getContent(), Espece::class, 'json');

        //dd($user);
        $espece->setUserAdded($user);
        // END SERIALIZATION
        if (!$espece->getName() || !$espece->getGenre()) {
            return
                [
                    "data" => [
                        "message" => "Saisir Informations obligatoires",
                        "errorCode" => 300],
                    "statusCode" => Response::HTTP_BAD_REQUEST
                ];
        }
        // DISTATCH THE MESSAGE
        if ($this->repository->findBy(
            [
                'name' => $espece->getName(),
                'genre' => $espece->getGenre(),
                'cultivar' => $espece->getCultivar(),
                "nomFr" => $espece->getNomFr()
            ])) {
            return
                [
                    "data" => [
                        "message" => "Espece existe deja",
                        "errorCode" => 301],
                    "statusCode" => Response::HTTP_CONFLICT
                ];
        }
        $espece->setCreatedAt(new \DateTime('now'));
        $espece->setIsDeleted(false);

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($espece);
            $em->flush();
            return [
                "data" => $this->especeSerializer($espece),
                "statusCode" => Response::HTTP_CREATED
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "data" => [
                    "message" => "Impossible d'enregistrer l'essence",
                    "errorCode" => 500
                ],
                "statusCode" => Response::HTTP_CONFLICT];
        }

    }

    /**
     * @param Request $request
     * @return array
     */
    public function getEspeces(Request $request): array
    {
        // GET LIST ESPECES
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        /** @var User $user */
        $user = $data['user'];
        // Paginate webServices
        $page = $request->query->get('page');

        if (!isset($page) || !is_numeric($page)) {
            return [
                "data" => $this->especeListSerializer($this->repository->findByCriteria(), $user),
                "statusCode" => Response::HTTP_OK
            ];
        }
        return PaginatedService::paginateList($this->especeListSerializer($this->repository->findByCriteria(), $user), $page, 20);
    }

    /**
     * @param $request
     * @param Espece $espece
     * @return array
     */
    public function getEspece($request, Espece $espece): array
    {
        // GET ONE ESPECE
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        $user = $data['user'];
        if ($user->getGroupe()->getGroupeType() !== 'DENDROMAP') {
            if (($espece->getUserAdded() == null) || ($espece->getUserAdded()->getGroupe()->getId() == $user->getGroupe()->getId())) {
                $especeSerialize = $this->especeSerializer($espece);
            } else {
                return [
                    "data" => ["message" => "Impossible d'acceder a cette ressource"],
                    "statusCode" => Response::HTTP_FORBIDDEN
                ];
            }
        } else {
            $especeSerialize = $this->especeSerializer($espece);
        }
        return [
            "data" => $especeSerialize,
            "statusCode" => Response::HTTP_OK
        ];
    }

    /**
     * @param Request $request
     * @param Espece $espece
     * @return array
     */
    public function delete(Request $request, Espece $espece): array
    {
        $data = $this->_tokenService->MiddlewareDendroUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        // VERIFY IF ESPECE ALREADY IN INVENTORY
        // Logic remove
        $espece->setIsDeleted(true);
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($espece);
            $em->flush();
            return [
                "data" => [
                    "message" => "Espece supprimé avec succès",
                    "id" => $espece->getId()
                ],
                "statusCode" => Response::HTTP_CREATED
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "data" => [
                    "message" => "Impossible de supprimer l'essence",
                    "errorCode" => 500,
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST];
        }
    }

    /**
     * @param Request $request
     * @param Espece $espece
     * @return array
     */
    public function update(Request $request, Espece $espece): array
    {
        // CONFIG AUTHORIZATION
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {

            $statusCode = Response::HTTP_UNAUTHORIZED;
            return [
                'data' => $data,
                'statusCode' => $statusCode
            ];
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['name']) || !isset($data['categorie']) || !isset($data['genre'])) {
            return [
                'data' => [
                    "message" => "Saisir Informations obligatoires",
                    "errorCode" => 300
                ], 'statusCode' => Response::HTTP_BAD_REQUEST];
        }
        // HYDRATATION DE DONNEES
        $form = $this->createForm(EspeceType::class, $espece);
        $form->submit($data);

        $existEspece = $this->getDoctrine()->getRepository(Espece::class)->findByUpdate($espece);

        if ($existEspece instanceof Espece) {
            return [
                'data' => [
                    "message" => "Espece existe déjà",
                    "errorCode" => 301
                ], 'statusCode' => Response::HTTP_CONFLICT
            ];
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return [
                'data' => $this->especeSerializer($espece), 'statusCode' => Response::HTTP_ACCEPTED
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                'data' => [
                    "message" => "Impossible d'enrégistrer ce espece",
                    "errorCode" => 500
                ], 'statusCode' => Response::HTTP_BAD_REQUEST];

        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function searchEspece(Request $request): array
    {
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        $user = $data['user'];

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['infos'])) {
            return [
                "data" => [
                    "message" => "Infos manquantes",
                    "errorCode" => 300
                ],
                "statusCode" => Response::HTTP_CONFLICT
            ];
        }

        $page = $request->query->get('page');
        if (!isset($page) || !is_numeric($page)) {
            return [
                "data" => $this->especeListSerializer($this->repository->findByCustom($data['infos']), $user),
                "statusCode" => Response::HTTP_OK
            ];
        }
        return PaginatedService::paginateList($this->especeListSerializer($this->repository->findByCustom($data['infos']), $user), $page, 20);
    }

    /**
     * @param Espece $espece
     * @return array
     */
    public function especeSerializer(Espece $espece): array
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        return $serializer->normalize($espece, 'json', ['groups' => ['read']]);
    }

    public function especeListSerializer(array $especes, User $user): array
    {
        return array_values(array_filter(array_map(function (Espece $espece) use ($user) {
            if ($user->getGroupe()->getGroupeType() !== 'DENDROMAP') {
                if (($espece->getUserAdded() == null) || ($espece->getUserAdded()->getGroupe()->getId() == $user->getGroupe()->getId())) {
                    return $this->especeSerializer($espece);
                }
            } else {
                return $this->especeSerializer($espece);
            }
        }, $especes)));
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getGenres(Request $request): array
    {
        /** @var Espece[] $datas */
        $especes = $this->getDoctrine()->getManager()->getRepository(Espece::class)->findByGenre();

        return [
            'data' => array_map(function (array $data) {
                return $data['genre'];
            }, $especes),
            'statusCode' => Response::HTTP_OK
        ];
    }
}
