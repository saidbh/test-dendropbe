<?php

    namespace  App\MessageHandler;

    use App\Entity\User;
    use App\Message\ResetPasswordMessage;

    use App\Service\AuthService;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Response;


    class ResetPasswordMessageHandler extends AbstractController {

        private $_authService;

        public function __construct(
                AuthService $authService
            ) {
            $this->_authService = $authService;
        }

        /**
         * @param ResetPasswordMessage $resetPassword
         * @return JsonResponse
         */
        public function __invoke(ResetPasswordMessage $resetPassword): JsonResponse {

            // VERIFIE VALEUR SONT BIEN ENVOYE
            if(!$resetPassword->id() || !$resetPassword->password() || !$resetPassword->confirmPassword()) {
                return $this->json(
                    [
                        "message" => "Saisir Informations obligatoires",
                        "errorCode" => "reset Password",
                    ], Response::HTTP_BAD_REQUEST);
            }

            // PASSWORD FORMAT IS NOT VALID
            if(!$this->_authService->passwordValid($resetPassword->password())) {
                return $this->json(
                    [
                        "message" => "Mot de passe doit contenir 1 caractère speciaux, numeric et une majiscule",
                        "errorCode" => 301
                    ], Response::HTTP_CONFLICT);
            }

            if($resetPassword->password() != $resetPassword->confirmPassword()) {
                return $this->json(
                    [
                        "message" => "Les 2 mots de passe ne sont pas conformes",
                        "errorCode" => 301
                    ], Response::HTTP_CONFLICT);
            }
            // GET USER
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $resetPassword->id()]);

            // ENCODER PASSWORD
            $user->setPassword($this->_authService->encrypt($resetPassword->password()));

            try {
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                return $this->json([
                    "message" => "Mot de passe reinitialiser", 
                    "errorCode" => 200
                ], Response::HTTP_OK);

            } catch(\Doctrine\DBAL\Exception $e) {
                return $this->json(
                [
                    "message" => "Une erreur est survenu", 
                    "messageError" => $e->getMessage(),
                    "errorCode" => 500
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

