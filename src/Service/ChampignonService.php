<?php

namespace App\Service;

use App\Entity\Champignons;
use App\Form\ChampignonsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class ChampignonService extends AbstractController
{
    private $tokenService;
    private $imageService;
    private $entityManager;
    private $serializer;
    const CAT_TAB = ['R', 'F'];
    private $errorsService;

    public function __construct(TokenService $tokenService, ImageService $imageService, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidationErrorsService $errorsService)
    {
        $this->tokenService = $tokenService;
        $this->imageService = $imageService;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;

        $this->errorsService = $errorsService;
    }

    public function getAll(Request $request): array
    {
        $list = $this->getDoctrine()->getRepository(Champignons::class)->findBy(['isDeleted' => 0], ['name' => 'ASC']);
        $page = $request->query->get('page');

        if (!isset($page) || !is_numeric($page)) {
            return [
                "data" => $this->serializertListChampignons($list),
                "statusCode" => Response::HTTP_OK
            ];
        }

        $data = $this->serializertListChampignons($list);
        // Paginate config
        return PaginatedService::paginateList($data, $page, 20);
    }

    public function getOne(Request $request, Champignons $champignons): array
    {
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        return [
            'statusCode' => Response::HTTP_OK,
            'data' => $this->serializerChampignons($champignons)
        ];
    }

    /**
     * @param array|null $data
     * @return array
     */
    public function setChampignons(?array $data): array
    {
        if (!isset($data) || !$data) {
            return [];
        }
        $champignons = [];
        foreach (array_unique($data) as $champignonId) {
            $champ = $this->getDoctrine()->getRepository(Champignons::class)->findOneBy(['id' => $champignonId]);
            if ($champ instanceof Champignons) {
                $champignons[] = $this->serializerChampignons($champ);
            }
        }
        return $champignons;
    }

    // serializer of all champignons list, get list champignons list parameters
    public function serializertListChampignons(array $data): array
    {
        return array_map(function (Champignons $champ) {
            return $this->serializerChampignons($champ);
        }, $data);
    }

    public function serializerChampignons(Champignons $champignons)
    {
        return $this->get('serializer')->normalize($champignons, 'json', ['groups' => ["read"]]);
    }

    public function delete(Request $request, Champignons $champignons): array
    {
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        //  END AUTHORIZATION
        $champignons->setIsDeleted(true);

        try {
            $repertoire = '../public/api/images/champignons/';
            foreach ($champignons->getImgUrl() as $url) {
                if (is_file($repertoire . $url)) {
                    unlink($repertoire . $url);
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($champignons);
            $em->flush();
            return [
                'data' => [
                    "message" => "Champignons supprimé avec succès",
                ], 'statusCode' => Response::HTTP_NO_CONTENT
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "data" => [
                    "message" => "Impossible de supprimer cet espece"
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

    }

    /**
     * @param Request $request
     * @return array
     */
    public function getListResineuOrF(Request $request): array
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['categorie']) || !in_array(strtoupper($data['categorie']), self::CAT_TAB)) {
            return [
                "data" => [
                    "message" => "Information sur la catégorie est obligatoire"
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        // list from R or F
        /** @var Champignons[] $champignons */
        $champignons = strtoupper($data['categorie']) == 'F' ?
            $this->getDoctrine()->getRepository(Champignons::class)->getAllByFeuillux()
            : $this->getDoctrine()->getRepository(Champignons::class)->getAllByResineux();

        $page = $request->query->get('page');

        if (!isset($page) || !is_numeric($page)) {
            return [
                "data" => $this->serializertListChampignons($champignons),
                "statusCode" => Response::HTTP_OK
            ];
        }
        // Paginate data Server
        return PaginatedService::paginateList($this->serializertListChampignons($champignons), $page, 20);
    }

    public function add(Request $request): array
    {
        $data['name'] = $request->request->get('name');
        $data['attaqueF'] = $request->request->get('attaqueF');
        $data['attaqueR'] = $request->request->get('attaqueR');
        $data['category'] = $request->request->get('category');
        $data['img1'] = $request->files->get('img1');
        $data['img2'] = $request->files->get('img2');

        $data['imgUrl'] = $data['img2'] ? [$data['img1'], $data['img2']] : [$data['img1']];

        if (!isset($data['name'])) {
            return [
                "data" => [
                    "message" => "Information sur la nom est obligatoire"
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        $champignon = new Champignons();

        $champignon->setCreatedAt(new \DateTime('now'));
        $champignon->setName($data['name']);
        $champignon->setAttaqueF($data['attaqueF']);
        $champignon->setAttaqueR($data['attaqueR']);
        $champignon->setCategory($data['category']);
        $champignon->setIsDeleted(0);

        /** @var Champignons $isExistChamp */
        $isExistChamp = $this->getDoctrine()->getRepository(Champignons::class)->findBy(['name' => $champignon->getName()]);

        if ($isExistChamp instanceof Champignons) {
            return [
                'data' => [
                    "message" => "Ce champignon existe déjà",
                    "errorCode" => 301
                ],
                'statusCode' => Response::HTTP_CONFLICT
            ];
        }

        // Check if data image url
        if ($data['imgUrl'][0]) {
            $dataJsonImg = [];
            $i = 0;
            foreach ($data['imgUrl'] as $img) {
                $i++;
                $repertoire = '../public/api/images/champignons/';
                $fileName = $this->imageService->addImage($img, $repertoire, false);
                if (is_array($fileName)) {
                    $dataJsonImg[] = $fileName;
                }
                $dataJsonImg['img' . $i] = $fileName;
            }
            $champignon->setImgUrl($dataJsonImg);
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($champignon);
            $em->flush();
            return [
                'data' => $this->serializerChampignons($champignon), 'statusCode' => Response::HTTP_OK
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                'data' => [
                    "message" => "Impossible d'enrégistrer ce espece",
                    "errorCode" => 302
                ], 'statusCode' => Response::HTTP_BAD_REQUEST];
        }
    }

    /**
     * @param Request $request
     * @param Champignons $champignon
     * @return array
     */
    public function oldEdit(Request $request, Champignons $champignon): array
    {

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['name'])) {
            return [
                "data" => [
                    "message" => "Le nom du champignons est obligatoire"
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        $data += ["createdAt" => $champignon->getCreatedAt()];
        // HYDRATATION DE DONNEES
        $form = $this->createForm(ChampignonsType::class, $champignon);
        $form->submit($data);

        $champignon->setUpdatedAt(new \DateTime('now'));

        /** @var Champignons $champignonsExist */
        $champignonsExist = $this->getDoctrine()->getRepository(Champignons::class)->findByUpdate($champignon);

        if ($champignonsExist instanceof Champignons) {
            return [
                'data' => [
                    "message" => "Vous modifiez sur un nom d'espece existant",
                    "errorCode" => 301
                ],
                'statusCode' => Response::HTTP_CONFLICT
            ];
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return [
                'data' => $this->serializerChampignons($champignon), 'statusCode' => Response::HTTP_OK
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                'data' => [
                    "message" => 'Impossible de modifier la resource',
                    "errorCode" => 302
                ], 'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    /**
     * Nouvelle fonction pour la modification d'un champignon
     * @param Champignons $champignon
     * @param Request $request
     * @return array
     */
    public function edit(Request $request, Champignons $champignon): array
    {
        $data = $this->tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        $this->serializer->deserialize($request->getContent(), Champignons::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $champignon]);

        $errors = $this->errorsService->getErrors($champignon);
        if (count($errors) > 0) {
            return [
                'data' => [
                    "message" => "Vous modifiez sur un nom d'espece existant",
                    "errorCode" => 301
                ],
                'statusCode' => Response::HTTP_CONFLICT
            ];
        }

        try {
            $this->entityManager->flush();
            return [
                'data' => $this->serializerChampignons($champignon), 'statusCode' => Response::HTTP_OK
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                'data' => [
                    "message" => 'Impossible de modifier la resource',
                    "errorCode" => 302
                ], 'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    /**
     * @param Request $request
     * @param Champignons $champignon
     * @return array|string
     */
    public function uploadFileChampignons(Request $request, Champignons $champignon)
    {
        $data['img1'] = $request->files->get('img1');
        $data['img2'] = $request->files->get('img2');
        $data['imgUrl']= [];
        if ($data['img1']) {
            $data['imgUrl']['img1'] = $data['img1'];
        }

        if ($data['img2']) {
            $data['imgUrl']['img2'] = $data['img2'];
        }

        if ($data['imgUrl']) {
            $dataJsonImg = [];
            foreach ($data['imgUrl'] as $key => $img) {
                $repertoire = '../public/api/images/champignons/';
                $fileName = $this->imageService->addImage($img, $repertoire, false);
                if (is_array($fileName)) {
                    return $fileName;
                }
                $dataJsonImg[$key] = $fileName;
            }
            $champignon->setImgUrl(array_merge($champignon->getImgUrl(), $dataJsonImg));
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return [
                'data' => $this->serializerChampignons($champignon), 'statusCode' => Response::HTTP_OK
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                'data' => [
                    "message" => 'Impossible de modifier la resource',
                    "errorCode" => 302
                ], 'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function search(Request $request): array
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['infos'])) {
            return [
                "data" => [
                    'message' => 'Informations obligatoires'
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

        $page = $request->query->get('page');
        /** @var Champignons[] $list */
        $list = $this->getDoctrine()->getRepository(Champignons::class)->search($data['infos']);

        if (!isset($page) || !is_numeric($page)) {
            return [
                "data" => 'Informations obligatoires',
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }
        return PaginatedService::paginateList($this->serializertListChampignons($list), $page, 20);
    }

    /**
     * Fonction pour supprimer une image
     * @param Champignons $champignon
     * @param Request $request
     * @return array
     */
    public function deleteImage(Champignons $champignon, Request $request)
    {
        $img = json_decode($request->getContent(), true)['img'];
        $imgUrl = array_filter($champignon->getImgUrl(), function ($ele) use ($img) {
            return $img !== $ele;
        });

        $newImgUrl = [];

        $i = 1;
        foreach ($imgUrl as $key => $value) {
            $newImgUrl['img'.$i] = $value;
            $i++;
        }

        $champignon->setImgUrl($newImgUrl);

        try {
            $repertoire = '../public/api/images/champignons/';
                if (is_file($repertoire . $img)) {
                    unlink($repertoire . $img);
                }

            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return [
                'data' => [
                    "message" => "Image du champignon supprimé avec succès",
                ], 'statusCode' => Response::HTTP_NO_CONTENT
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                "data" => [
                    "message" => "Impossible de supprimer cette image"
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

    }
}
