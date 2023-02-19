<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\AuthService;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/auth")
 */
class AuthController extends AbstractController
{
    private $_service;

    public function __construct(AuthService $service)
    {
        $this->_service = $service;
    }

    /**
     * login to get token identification
     * @Route("/login", name="login", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @SWG\Response(
     *     response=200,
     *     description="Returns an object",
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"email", "password"},
     *          @SWG\Property(type="string", property="email", maxLength=255),
     *          @SWG\Property(type="string", property="password", minLength=8, maxLength=255, pattern="/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/", format="password",
     *     description="Password contain less than 8 characters, 1 uppercase, lowercase and 1 digit"
     *      )
     *    )
     * )
     * @SWG\Tag(name="Auth")
     */
    public function login(Request $request): JsonResponse
    {
        $data = $this->_service->login($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * signup from landing page
     * @Route("/signup", name="auth", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @SWG\Response(
     *     response=200,
     *     description="Return a object of message confirmation",
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"email", "password", "forfait"},
     *          @SWG\Property(type="string", property="email", maxLength=255),
     *          @SWG\Property(type="string", property="password", minLength=8, maxLength=255, pattern="/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/", format="password",
     *     description="Password contain less than 8 characters, 1 uppercase, lowercase and 1 digit"
     *      ),
     *          @SWG\Property(type="string", property="username", maxLength=50),
     *          @SWG\Property(type="string", property="forfait", maxLength=255, description="codeForfait"),
     *          @SWG\Property(type="string", property="name", minLength=2),
     *          @SWG\Property(type="string", property="phoneNumber"),
     *          @SWG\Property(type="string", property="city"),
     *          @SWG\Property(type="string", property="zipCode"),
     *          @SWG\Property(type="string", property="address"),
     *          @SWG\Property(type="string", property="address2", description="address society"),
     *          @SWG\Property(type="string", property="ccNumber", description="card number" ),
     *          @SWG\Property(type="string", property="nameCard"),
     *          @SWG\Property(type="string", property="ccCvc", description="ex. 123", maxLength=3),
     *          @SWG\Property(type="string", property="ccExp", description="expiration date", maxLength=5)
     *    )
     * )
     * )
     * @SWG\Tag(name="Auth")
     */
    public function signUp(Request $request): JsonResponse
    {
        $response = $this->_service->signUp($request);
        return $this->json($response['data'], $response['statusCode']);
    }

    /**
     * Confirmation valid token when changing password
     * @Route("/confirm", name="confirm_auth", methods="POST")
     * @param Request $request
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"token"},
     *        @SWG\Property(type="string", property="token", maxLength=255)
     *    )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return a object of message confirmation"
     * )
     * @SWG\Tag(name="Auth")
     * @return JsonResponse
     */
    public function confirmationEmail(Request $request): JsonResponse
    {
        $response = $this->_service->confirmEmail($request);
        return $this->json($response['data'], $response['statusCode']);
    }

    /**
     * Sending mail confirmation for changing password
     * @Route("/mailPassword", name="mail_password_confirmation", methods="POST")
     * @param Request $request
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"email"},
     *        @SWG\Property(type="string", property="email", maxLength=255)
     *    )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return a object of message confirmation",
     * )
     * @SWG\Tag(name="Auth")
     * @return JsonResponse
     */
    public function mailPasswordConfirm(Request $request): JsonResponse
    {
        $data = $this->_service->sendMailPsdConfirmation($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * verify token changing password
     * @Route("/verifyToken", name="verify_token_confirm_password", methods="POST")
     * @param Request $request
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"token"},
     *        @SWG\Property(type="string", property="token", maxLength=255)
     *    )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return a object of message confirmation"
     * )
     * @SWG\Tag(name="Auth")
     * @return JsonResponse
     */
    public function verifyTokenConfirmChangePassword(Request $request): JsonResponse
    {
        $data = $this->_service->verifyTokenConfirmChangePassword($request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * Reset password from Landing page
     * @Route("/{id}/resetPassword", name="reset_password", methods="PUT")
     * @param Request $request
     * @param User $user
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"new", "confirm"},
     *          @SWG\Property(type="string", property="new", pattern="/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/", format="password",
     *     description="Password contain less than 8 characters, 1 uppercase, lowercase and 1 digit"),
     *          @SWG\Property(type="string", property="confirm", minLength=8, maxLength=255, pattern="/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/", format="password",
     *     description="Password contain less than 8 characters, 1 uppercase, lowercase and 1 digit"
     *      )
     *    )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return a object of message confirmation",
     * )
     * @SWG\Tag(name="Auth")
     * @return JsonResponse
     */
    public function changePassword(User $user, Request $request): JsonResponse
    {
        $data = $this->_service->changePasswordLanding($request, $user);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * Verify valid token
     * @Route("/isTokenValid", name="change_password", methods="POST")
     * @param Request $request
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"token"},
     *        @SWG\Property(type="string", property="token", maxLength=255)
     *    )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Return a object of message confirmation",
     * )
     * @SWG\Tag(name="Auth")
     * @return JsonResponse
     */
    public function validityToken(Request $request): JsonResponse
    {
        $data = $this->_service->verifyToken($request);
        return $this->json($data['data'], $data['statusCode']);
    }
}
