<?php
    
    namespace App\MessageHandler;

    use App\Message\AddDroitMessage;
    use App\Entity\Droit;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\Validator\Validator\ValidatorInterface;
    use App\Repository\DroitRepository;
    
    use Symfony\Component\HttpFoundation\JsonResponse;

    class AddDroitMessageHandler {

        private $validator;
        private $em;
        private $droitRepository;

        public function __construct(ValidatorInterface $validator, 
                                    EntityManagerInterface $em, 
                                    DroitRepository $droitRepository) 
        {
            $this->validator = $validator;
            $this->em = $em;
            $this->droitRepository = $droitRepository;
        }

        public function __invoke(AddDroitMessage $droitMessage){
            // ADD DROIT
            // GET INFORMATION FROM DATA
            $droit = new Droit();

            // DEMATERIALISATION EN MODE JSON
            $droit->setName(strtoupper($droitMessage->getName()));
            // HYDRATATION DE DONNEES
            $droit->setCreatedAt(new \DateTime('now'));
            $droit->setIsInit($droitMessage->isInit);

            if(!$droit->getName()){
                return new JsonResponse(["message" => "Aucun paramètre specifier"], 409);
                die();
            }
            if($this->droitRepository->findBy(['name' => $droit->getName()])) {
                return new JsonResponse(["message" => "ce droit est deja défini"], 409);
                die();
            }

            $errors = $this->validator->validate($droit);

            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return new JsonResponse(
                    ["message" => "Certaines valeurs ne sont pas conformes"], 
                409);
                die();
            }
            try{
                $this->em->persist($droit);
                $this->em->flush();
                return new JsonResponse(["message" => "Enregistrement effectué avec succès", "id" => $droit->getId()], 200); 
            } catch(\Doctrine\DBAL\DBALException $e) {
                return new JsonRespone(["message" => "Impossible d'enrégistrer droit"], 500);
            }
        }
    }