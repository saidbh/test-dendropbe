<?php
    namespace App\MessageHandler;
    use App\Entity\User;
    use App\Message\ChangePasswordMessage;
    
    use App\Service\AuthService;
    use Doctrine\ORM\EntityManagerInterface;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Response;

    class ChangePasswordMessageHandler extends AbstractController {
        private $em;
        private $_authService;

        public function __construct(
            EntityManagerInterface $em,
            AuthService $authService
        ) {
            $this->em = $em;
            $this->_authService = $authService;
        }

        /**
         * @param ChangePasswordMessage $changePassword
         * @return JsonResponse
         */
        public function __invoke(ChangePasswordMessage $changePassword): JsonResponse {
            
            // CORPS DU PROGRAMME
            if(!is_numeric($changePassword->id())) {
                return new JsonResponse(
                    [
                        "message" => "Saisir informations obligatoires",
                        "errorCode" => 301
                    ], Response::HTTP_BAD_REQUEST);
            }

            // UTILISATEUR AVEC ID CORRESPONDANT
            /** @var User $user */
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(["id" => $changePassword->id()]);

            if(!$user) {
                return new JsonResponse(
                    [
                        "message" => "Cet utilisateur n'existe pas",
                        "errorCode" => 301
                    ], Response::HTTP_BAD_REQUEST);
            }

            // NEW AND CORFIRM PASSWORD CORRECT
            if($changePassword->nouveau() !== $changePassword->confirm()){
                return new JsonResponse(
                    [
                        "message" => "Veillez confirmer votre mot de passe",
                        "errorCode" => 302
                    ], Response::HTTP_BAD_REQUEST);
            }

            // MOT DE PASSE N'EST PAS CONFORME
            if(!$this->_authService->passwordValid($changePassword->nouveau())){
                // NOUVEAU PASSWORD
                return new JsonResponse(
                    [
                        "message" => "Format de mot passe incorrect",
                        "errorCode" => 303
                    ], Response::HTTP_BAD_REQUEST);
            }

            // MOT DE PASSE INCORRECT
            if(!$this->_authService->decrypt($user, $changePassword->ancien())) {
                return new JsonResponse(
                    [
                        "message" => "Mot de passe n'est pas conforme a l'ancien",
                        "errorCode" => 304
                    ], Response::HTTP_BAD_REQUEST);
            }

            // TOUT EST OK ON PASSE A LA MODIFICATION DU USER
            $user->setPassword($this->_authService->encrypt($changePassword->nouveau()));

            try {
                $this->em->flush();
                return new JsonResponse(
                    [
                        "message" => "Mot de passe modifié avec succès", 
                        "id" => $user->getId(),
                        "errorCode" => 200
                    ], 200);
            }
            catch(\Doctrine\DBAL\Exception $e) {
                return new JsonResponse([
                    "message" => "Impossible de modifier le mot de passe",
                    'errorCode' => 500
                ], Response::HTTP_BAD_REQUEST);
                
            }
        }
    }
