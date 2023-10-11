<?php

namespace App\Service;

use App\Entity\Droit;
use App\Entity\Groupe;
use App\Entity\Profil;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;


class ProfilService extends AbstractController
{
    private $_tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->_tokenService = $tokenService;
    }

    public function add(Request $request)
    {
        // MIDDLEWARE
        $data = $this->_tokenService->MiddlewareAdminDedroUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['name']) || !isset($data['groupeType']) || !isset($data['droit'])) {
            return [
                'data' => ["message" => "Saisir informations obligatoires",
                    "errorCode" => 300
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        if (!$data['name'] || !$data['groupeType'] || !$data['droit']) {
            return [
                'data' => ['message' => 'Certaines valeurs obligatoires sont nulles'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        $droitRepo = $this->getDoctrine()->getRepository(Droit::class);
        $droit = is_numeric($data['droit']) ?
            $droitRepo->findOneBy(['id' => $data['droit']]) :
            $droitRepo->findOneBy(['name' => $data['droit']]);

        if (!$droit instanceof Droit) {
            return [
                'data' => [
                    "message" => "Saisir informations obligatoires",
                    "errorCode" => 301
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        $data['droit'] = $droit;

        /** @var Profil $profil */
        $profil = $serializer->denormalize($data, Profil::class);
        $profil->setCreatedAt(new \DateTime('now'));

        if (!$profil instanceof Profil) {
            return [
                'data' => [
                    "message" => "Saisir informations obligatoires",
                    "errorCode" => 301
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        $profil->setDroit($droit);
        $existProfil = $this->getDoctrine()->getRepository(Profil::class)
            ->findOneBy(['name' => $profil->getName(), 'groupeType' => $profil->getGroupeType()]);

        if ($existProfil instanceof Profil) {
            return [
                'data' => [
                    "message" => "Ce profil est deja defini",
                    "errorCode" => 302
                ],
                'statusCode' => Response::HTTP_CONFLICT
            ];
        }
        // PERSIST DATA
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($profil);
            $em->flush();
            return [
                'data' => $this->getSerializerProfil($profil),
                'statusCode' => Response::HTTP_CREATED
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                'data' => [
                    "message" => "Impossible d'enrégistrer ce profil",
                    "errorCode" => 500
                ], Response::HTTP_BAD_REQUEST
            ];
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getAll(Request $request): array
    {
        $data = $this->_tokenService->MiddlewareAdminUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        //
        $repo = $this->getDoctrine()->getRepository(Profil::class);

        $profils = $data['user']->getGroupe()->getGroupeType() != 'DENDROMAP' ?
            $repo->findBy(["groupeType" => $data['user']->getGroupe()->getGroupeType(), "deleted" => false], ['id' => 'DESC'])
            : $repo->findBy(["deleted" => false], ['id' => 'DESC']);

        $_profils = array_map(function ($p) {
            return $this->getSerializerProfil($p);
        }, $profils);
        return [
            'data' => $_profils,
            'statusCode' => Response::HTTP_OK
        ];
    }

    public function getOne(Request $request, Profil $profil): array
    {
        $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        return [
            'data' => $this->getSerializerProfil($profil),
            'statusCode' => Response::HTTP_OK
        ];
    }

    public function delete(Request $request, Profil $profil): array
    {

        $data = $this->_tokenService->MiddlewareAdminUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        $profilData = $this->getDoctrine()->getRepository(Profil::class)->findOneBy(['name' => $profil->getName(), 'isInit' => 1]);

        if ($profilData instanceof Profil) {
            return [
                'data' => ['message' => 'Impossible de supprimer ce compte'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        // Test if profile is mapping with a user
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        if (self::isUserMapping($users, $profil)) {
            $profil->setDeleted(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($profil);
            $em->flush();
            return [
                'data' => ["message" => "Suppression réussie"],
                'statusCode' => Response::HTTP_NO_CONTENT
            ];
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($profil);
            $em->flush();
            return [
                'data' => ["message" => "Suppression réussie"],
                'statusCode' => Response::HTTP_NO_CONTENT
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                'data' => [
                    "message" => "Impossible de supprimer ce profil",
                    "errorCode" => 500
                ], 'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
    }

    public function edit(Request $request, Profil $profil): array
    {
        $data = $this->_tokenService->MiddlewareAdminDedroUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['name']) || !isset($data['groupeType']) || !isset($data['droit'])) {
            return [
                "data" => ["message" => "Saisir Information obligatoire"],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        if (($data['groupeType'] != "DENDROMAP") && ($data['groupeType'] != "FORMULE PREMUIM")) {
            return [
                "data" => ["message" => "Saisir Information obligatoire"],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        // ADD DATA
        $droitRepo = $this->getDoctrine()->getRepository(Droit::class);

        /** @var Droit $droit */
        $droit = is_numeric($data['droit']) ?
            $droitRepo->findOneBy(['id' => $data['droit']]) :
            $droitRepo->findOneBy(['name' => $data['droit']]);

        if (!$droit instanceof Droit) {
            return [
                'data' => [
                    "message" => "Saisir informations obligatoires",
                    "errorCode" => 301
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        $data['droit'] = $droit;
        $serializer = new Serializer([CustomSerializationObject::denormalizeDateTime()], [new JsonEncoder()]);
        /** @var Profil $profilNormalizer */
        $profilNormalizer = $serializer->denormalize($data, Profil::class, 'json', ['object_to_populate' => $profil]);
        // If profil already exist
        $existProfil = $this->getDoctrine()->getRepository(Profil::class)->findOneByUpdate($profil);
        if ($existProfil instanceof Profil) {
            return [
                "data" => ["message" => "Aucune modification sur ce profil"],
                "statusCode" => Response::HTTP_CONFLICT
            ];
        }
        // SET UPDATE DATE
        $profilNormalizer->setUpdatedAt(new \DateTime('now'));
        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($profilNormalizer);
            $em->flush();
            return [
                'data' => ['message' => "Profil mis a jour avec succès", "id" => $profil->getId()],
                'statusCode' => Response::HTTP_OK
            ];
        } catch (\Doctrine\DBAL\DBALException $e) {
            return [
                'data' => [
                    "message" => "Impossible de supprimer ce profil",
                    "errorCode" => 500
                ], Response::HTTP_BAD_REQUEST
            ];
        }
    }

    public function getProfilGroupe(Request $request)
    {
        $data = $this->_tokenService->MiddlewareAdminUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['id'])) {
            return [
                'data' => ['message' => 'Information obligatoire'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        /** @var Profil[] $profils */
        $profils = $this->getDoctrine()->getRepository(Profil::class)->findBy(["deleted" => false], ['id' => 'DESC']);
        /** @var Groupe $groupe */
        $groupe = $this->getDoctrine()->getRepository(Groupe::class)->find($data['id']);

        if (!$groupe instanceof Groupe) {
            return [
                'data' => ['message' => 'Groupe n\'est pas encore defini'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        $_profils = [];
        foreach ($profils as $p) {
            if ($p->getGroupeType() == $groupe->getGroupeType()) {
                $_profils [] = self::getSerializerProfil($p);
            }
        }
        return ['data' => $_profils, 'statusCode' => Response::HTTP_OK];
    }

    public function getSerializerProfil(Profil $profil)
    {
        /** @var Serializer $serializer */
        return $this->get('serializer')->normalize(
            $profil,
            'json', ['groups' => ['read', "imported"]]
        );
    }

    static function isUserMapping(array $users, Profil $profile): bool
    {
        $data = array_filter($users, function (User $user) use ($profile) {
            return $user->getProfil()->getId() === $profile->getId();
        });

        return count($data) > 0;
    }
}
