<?php
    namespace App\Service;

    use App\Entity\Nuisible;
    use PhpParser\Node\Expr\Array_;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Serializer\Serializer;

    class NuisibleService extends AbstractController {

        private $_tokenService;

        public function __construct(TokenService $tokenService) {
            $this->_tokenService = $tokenService;
        }
        public function add(Request $request) {

            /** @var Serializer $serializer */
            $serializer = $this->get('serializer');
            $data = $serializer->decode($request->getContent(), 'json');

            if (!isset($data['name'])) {
                return [
                    "data" => [
                        "message" => "Le nom du nuisible est obligatoire"
                    ],
                    'statusCode' => Response::HTTP_BAD_REQUEST
                ];
            }
            $nuisibleExist = $this->getDoctrine()->getManager()->getRepository(Nuisible::class)->findOneBy(['name' => $data['name']]);

            if($nuisibleExist instanceof Nuisible) {
                return [
                    "data" => [
                        "message" => "Ce nuisible existe déjà"
                    ],
                    "statusCode" => Response::HTTP_CONFLICT
                ];
            }
            $nuisible = new Nuisible();

            $nuisible->setCreatedAt(new \DateTime('now'));
            $nuisible->setName($data['name']);
            try{
                $em = $this->getDoctrine()->getManager();
                $em->persist($nuisible);
                $em->flush();
                return [
                    'data' => [
                            "message" => "Nuisible créé avec succès",
                            "id" => $nuisible->getId()
                        ],
                    'statusCode' => Response::HTTP_CREATED
                ];
            } catch(\Doctrine\DBAL\DBALException $e) {
                return [
                    'data' => [
                        "message" => "Impossible d'ajouter un nuisible",
                    ], Response::HTTP_BAD_REQUEST];
            }

        }
        /**
         * @return array
         **/
        public function list(): array {
            // Get all nuisible
            $lists = $this->getDoctrine()->getRepository(Nuisible::class)->findAll();

            $result = array_map(function(Nuisible $data) {
                    return $this->serializer($data);
                }, $lists);
            return [
                'data' => $result,
                'statusCode' => Response::HTTP_OK
            ];
        }

        public function getOne(Nuisible $nuisible): array {
            return [
                'data' => $this->serializer($nuisible),
                'statusCode' => Response::HTTP_OK
            ];
        }
        /**
         * @param Nuisible $nuisible
         * @param Request $request
         * @return array
         */
        public function delete(Request $request, Nuisible $nuisible): array {

            $data = $this->_tokenService->MiddlewareNormalUser($request->headers->get('Authorization'));
            if (!isset($data['user']) || !$data['user']) {
                return $data;
            }

            try{
                $em = $this->getDoctrine()->getManager();
                $em->remove($nuisible);
                $em->flush();
                return [
                    'data' => [
                        "message" => "Nuisible supprimé avec succès"
                    ],
                    'statusCode' => Response::HTTP_NO_CONTENT
                ];
            } catch(\Doctrine\DBAL\DBALException $e) {
                return [
                    'data' => [
                        "message" => "Impossible d'ajouter un nuisible",
                        'error' => $e->getMessage()
                    ], 'statusCode' => Response::HTTP_BAD_REQUEST];
            }
        }

        /**
         * @param Request $request
         * @param Nuisible $nuisible
         * @return array
         **/
        public function edit(Request $request, Nuisible $nuisible): array {

            /** @var Serializer $serializer */
            $serializer = $this->get('serializer');
            $data = $serializer->decode($request->getContent(), 'json');

            if (!isset($data['name'])) {
                return [
                    "data" => [
                        "message" => "Le nom du nuisible est obligatoire"
                    ],
                    'statusCode' => Response::HTTP_BAD_REQUEST
                ];
            }
            $data['createdAt'] = $nuisible->getCreatedAt();
            $nuisible->setUpdatedAt(new \DateTime('now'));
            $nuisible->setName($data['name']);

            try {
                $em = $this->getDoctrine()->getManager();
                $em->persist($nuisible);
                $em->flush();
                return [
                    'data' => [
                        "message" => "Nuisible mis a jour avec succès",
                        "id" => $nuisible->getId()
                    ],
                    'statusCode' => Response::HTTP_OK
                ];
            } catch(\Doctrine\DBAL\DBALException $e) {
                return [
                    'data' => [
                        "message" => "Impossible d'ajouter un nuisible",
                    ], Response::HTTP_BAD_REQUEST];
            }
        }

        /**
         * @param Nuisible $nuisible
         * @return array
         */
        public function serializer(Nuisible $nuisible): array {
            return $this->get('serializer')->normalize($nuisible, 'json', ['groups' => ["read"]]);
        }

        /**
         * @param array|null $data
         * @return array
         */
        public function setNuisible(?array $data): array {
            if(!isset($data) || !$data) {
                return [];
            }
            $nuisibles = [];
            foreach (array_unique($data) as $nuisibleId) {
                /** @var Nuisible $data */
                $champ = $this->getDoctrine()->getRepository(Nuisible::class)->findOneBy(['id' => $nuisibleId]);
                if($champ instanceof Nuisible) {
                     $nuisibles[] = $this->serializer($champ);
                }
            }
            return $nuisibles;
        }
    }
