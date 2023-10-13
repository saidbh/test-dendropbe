<?php

namespace App\Service;

use App\Entity\Droit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

class DroitService extends AbstractController
{
    private $_tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->_tokenService = $tokenService;
    }

    /**
     * @param Request $request
     * @param Droit $droit
     * @return array
     */
    public function getOne(Request $request, Droit $droit): array
    {
        return ['data' => $this->getSerializer($droit), 'statusCode' => Response::HTTP_OK];
    }

    public function getAll(Request $request)
    {

        $data = $this->_tokenService->MiddlewareDendroUser($request->headers->get('Authorization'));

        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }
        $users = $this->getDoctrine()->getRepository(Droit::class)->findAll();
        $_users = array_map(function (Droit $d) {
            return $this->getSerializer($d);
        }, $users);
        return ['data' => $_users, 'statusCode' => Response::HTTP_OK];
    }

    public function add(Request $request)
    {
        $data = $this->_tokenService->MiddlewareDendroUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $data = $serializer->decode($request->getContent(), 'json');

        if (!isset($data['name'])) {
            return [
                'data' => ['message' => 'Informations obligatoire'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }

        // ADD DROIT
        /** @var Droit $droit */
        $droit = $serializer->denormalize($data, Droit::class);
        $droit->setCreatedAt(new \DateTime('now'));

        if (!$droit instanceof Droit) {
            return [
                'data' => [
                    "message" => "Saisir informations obligatoires",
                    "errorCode" => 301
                ],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        $existingDroit = $this->getDoctrine()->getRepository(Droit::class)->findOneBy(['name' => $droit->getName()]);

        if ($existingDroit instanceof Droit) {
            return [
                'data' => [
                    "message" => "Ce droit est deja defini",
                    "errorCode" => 302
                ],
                'statusCode' => Response::HTTP_CONFLICT
            ];
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $em->persist($droit);
            $em->flush();
            return [
                'data' => $this->getSerializer($droit),
                'statusCode' => Response::HTTP_CREATED
            ];
        } catch (\Doctrine\DBAL\DBALException $e) {
            return [
                'data' => [
                    "message" => "Impossible d'enrégistrer ce profil",
                    "errorCode" => 500
                ], Response::HTTP_BAD_REQUEST
            ];
        }
    }

    public function delete(Request $request, Droit $droit): array
    {
        $data = $this->_tokenService->MiddlewareAdminUser($request->headers->get('Authorization'));
        if (!isset($data['user']) || !$data['user']) {
            return $data;
        }

        $droitInit = $this->getDoctrine()->getRepository(Droit::class)->findOneBy(['name' => $droit->getName(), 'isInit' => 1]);

        if ($droitInit instanceof Droit) {
            return [
                'data' => ['message' => 'Impossible de supprimer ce compte'],
                'statusCode' => Response::HTTP_BAD_REQUEST
            ];
        }
        //
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($droit);
            $em->flush();
            return [
                'data' => ["message" => "Droit supprimé avec succès"],
                'statusCode' => Response::HTTP_NO_CONTENT
            ];
        } catch (\Doctrine\DBAL\Exception $e) {
            return [
                'data' => [
                    "message" => "Impossible de supprimer ce droit",
                    "errorCode" => 500
                ], Response::HTTP_BAD_REQUEST
            ];
        }
    }

    public function getSerializer(Droit $droit)
    {
        return $this->get('serializer')->normalize($droit, 'json', ['groups' => ["read"]]);
    }
}
