<?php

namespace App\Controller;
use App\Entity\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;


/**
 * @Route("/api/user")
 */
class UserController extends AbstractController
{
    private $_service;

    public function __construct(
        UserService $service)
    {
        $this->_service = $service;
    }

    /**
     * @Route("", name="user_index", methods="GET")
     * @param Request $request
     * @SWG\Response(
     *  response=200,
     *     description="return the list of a User",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"read"}))
     *     )
     * )
     * @SWG\Tag(name="User")
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $response = $this->_service->users($request);
        return $this->json($response['data'], $response['statusCode']);
    }

    /**
     * @Route("", name="user_new", methods="POST")
     * @param Request $request
     *  @SWG\Response(
     *  response=201,
     *     description="return an object of a User",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"username", "email", "password", "profil", "groupe"},
     *          @SWG\Property(type="string", property="username", maxLength=255),
     *          @SWG\Property(type="string", property="email", maxLength=255, ),
     *          @SWG\Property(type="string", property="password",maxLength=255, ),
     *          @SWG\Property(type="integer", property="profil"),
     *          @SWG\Property(type="integer", property="groupe"),
     *      )
     *    )
     * )
     * @SWG\Tag(name="User")
     * @return JsonResponse
     */
    public function new(Request $request): JsonResponse
    {
        $response = $this->_service->addUser($request);
        return $this->json($response['data'], $response['statusCode']);
    }

    /**
     * @Route("/{id}", name="user_show", methods="GET", requirements={"id"="\d+"})
     * @param Request $request
     * @param User $user
     * @SWG\Response(
     *  response=200,
     *     description="return an object of a User",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of User"
     * )
     * @SWG\Tag(name="User")
     * @return JsonResponse
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $response = $this->_service->user($request, $user);
        return $this->json($response['data'], $response['statusCode']);
    }

    /**
     * @Route("/{id}", name="user_edit_few", methods="PATCH")
     * @param Request $request
     * @param User $user
     * @SWG\Response(
     *  response=200,
     *     description="return an object of a User",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"username", "email", "password", "profil", "groupe"},
     *          @SWG\Property(type="string", property="username", maxLength=255),
     *          @SWG\Property(type="string", property="email", maxLength=255, ),
     *          @SWG\Property(type="integer", property="profil"),
     *      )
     *    )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of User"
     * )
     * @SWG\Tag(name="User")
     * @return JsonResponse
     *
     */
    public function modify(Request $request, User $user):JsonResponse
    {
        $response = $this->_service->modifUser($request, $user);
        return $this->json($response['data'], $response['statusCode']);
    }

    /**
     * @Route("/{id}", name="user_delete", methods="DELETE", requirements={"id"="\d+"})
     * @param User $user
     * @param Request $request
     * @SWG\Response(
     *  response=204,
     *  description="Return no content",
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of user"
     * )
     * @SWG\Tag(name="User")
     * @return JsonResponse
     */
    public function delete(Request $request, User $user): JsonResponse
    {
        $response = $this->_service->delete($request, $user);
        return $this->json($response['data'], $response['statusCode']);
    }

    /**
     * @Route("/confirmation", name="user_confirmation", methods="GET|POST")
     * @param Request $request
     * @return JsonResponse
     * @SWG\Response(
     *  response=200,
     *  description="Return a bject message confirmation",
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"token"},
     *          @SWG\Property(type="text", property="token"),
     *      )
     *    )
     * )
     * @SWG\Tag(name="User")
     */
    public function confirmation(Request $request): JsonResponse
    {
        $response = $this->_service->confirmationUser($request);
        return new JsonResponse($response['data'], $response['statusCode']);
    }

    /**
     * @Route("/{id}/upload", name="user_upload", methods="POST")
     * @SWG\Response(
     *  response=200,
     *  description="return a object of message confirmation"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of user"
     * )
     * @SWG\Parameter(
     *     name="img",
     *     in="formData",
     *     description="Logo of society",
     *     type="file"
     * )
     * @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="username utilisateur",
     *     type="string"
     * )
     * @SWG\Tag(name="User")
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(User $user, Request $request): JsonResponse
    {
        $data = $this->_service->uploadImage($request, $user);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/active", name="user_desactive_active", methods="PUT")
     * @param Request   $request
     * @SWG\Response(
     *  response=200,
     *     description="return an object of a User",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"read"}))
     *     )
     * )
     * @param User $user
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of user"
     * )
     * @SWG\Tag(name="User")
     * @return JsonResponse
     */
    public function activeOrDesactive(Request $request, User $user):JsonResponse
    {
        $data = $this->_service->activeOrDesactiveUser($request, $user);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/profilUser", name="user_modif_profil", methods="PUT")
     * @SWG\Response(
     *  response=200,
     *     description="return an object of a User",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of user"
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"profilId"},
     *          @SWG\Property(type="text", property="profilId"),
     *      )
     *    )
     * )
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     * @SWG\Tag(name="User")
     */
    public function modifProfilUser(Request $request, User $user):JsonResponse
    {
        $data = $this->_service->updateProfilUser($request, $user);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/mailconfirm", name="mail_confirm_email", methods="GET")
     * @SWG\Response(
     *  response=200,
     *  description="return a object of message confirmation"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of user"
     * )
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     * @SWG\Tag(name="User")
     */
    public function mailConfirm(Request $request, User $user): JsonResponse
    {
        $data = $this->_service->sendMailConfirmation($request, $user);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/resetpassword", name="mail_reinitialise_passsword", methods="GET")
     * @param Request $request
     * @param User $user
     * @SWG\Response(
     *  response=200,
     *  description="return a object of message confirmation"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of user"
     * )
     * @SWG\Tag(name="User")
     * @return JsonResponse
     */
    public function reinitPassword(User $user, Request $request):JsonResponse
    {
        $data = $this->_service->generateDefaultPassword($user, $request);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/changeForfait", name="update_forfait_with_Stripe", methods="PATCH")
     * @param Request $request
     * @param User $user
     * @SWG\Response(
     *  response=200,
     *     description="return an object of a User",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object",
     *          @SWG\Property(type="string", property="name"),
     *          @SWG\Property(type="string", property="forfait", description="codeForfait of user"),
     *          @SWG\Property(type="phoneNumber", property="phoneNumber"),
     *          @SWG\Property(type="string", property="city"),
     *          @SWG\Property(type="string", property="zipCode", description="Code postal"),
     *          @SWG\Property(type="string", property="address"),
     *          @SWG\Property(type="string", property="address2"),
     *          @SWG\Property(type="string", property="ccNumber", description="card number" ),
     *          @SWG\Property(type="string", property="nameCard"),
     *          @SWG\Property(type="string", property="ccCvc", description="ex. 123", maxLength=3),
     *          @SWG\Property(type="string", property="ccExp", description="expiration date", maxLength=5),
     *          @SWG\Property(type="boolean", property="changingMode", description="false if you don't need to update currency mode card"),
     *      )
     *    )
     * )
     * @return JsonResponse
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of user"
     * )
     * @SWG\Tag(name="User")
     */
    public function updateForfaitWithStripe(Request $request, User $user): JsonResponse
    {
        $data = $this->_service->updateForfaitWithStripe($request, $user);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/modifCompte", name="update_compte_profil", methods="PUT")
     * @param Request $request
     * @param User $user
     * @SWG\Response(
     *  response=200,
     *     description="return an object of a User",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"read"}))
     *     )
     * )
     * @SWG\Parameter(
     *     name="params",
     *     in="body",
     *     @SWG\Schema(type="object", required={"email"},
     *          @SWG\Property(type="string", property="nom", maxLength=255),
     *          @SWG\Property(type="string", property="prenom", maxLength=255),
     *          @SWG\Property(type="string", property="email", maxLength=255),
     *          @SWG\Property(type="string", property="numCertification"),
     *          @SWG\Property(type="string", property="cp"),
     *          @SWG\Property(type="string", property="ville"),
     *          @SWG\Property(type="string", property="siret"),
     *          @SWG\Property(type="string", property="addressSociete"),
     *          @SWG\Property(type="string", property="societe"),
     *      )
     *    )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of user"
     * )
     * @SWG\Tag(name="User")
     * @return JsonResponse
     */
    public function updateCompteProfil(Request $request, User $user): JsonResponse
    {
        $data = $this->_service->modifCompteProfil($request, $user);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/{id}/unsubscribe", name="unbscribe_compte", methods="PATCH")
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     * @SWG\Response(
     *  response=200,
     *  description="return a object of message confirmation"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="integer",
     *     description="slug of user"
     * )
     * @SWG\Tag(name="User")
     */
    public function unsubscribe(Request $request, User $user): JsonResponse
    {
        $data = $this->_service->unsubscribe($request, $user);
        return $this->json($data['data'], $data['statusCode']);
    }

    /**
     * @Route("/mailAccount", name="mail_account_ready_expired", methods="POST")
     * @param Request $request
     * @return JsonResponse
     * @SWG\Response(
     *  response=200,
     *  description="Return a object message confirmation",
     * )
     * @SWG\Parameter(
     *     parameter="ids",
     *     name="ids",
     *     in="body",
     *     required=true,
     *     description="ObjectArray",
     * @SWG\Schema(type="object",
     *         @SWG\Property(property="ids", type="array",
     *          @SWG\Items(type="integer")
     *      )
     *   )
     * )
     * @SWG\Tag(name="User")
     */
    public function mailAccountReadyToExpired(Request $request): JsonResponse
    {
        $data = $this->_service->alertUserBeforeExpiredAccount($request);
        return $this->json($data['data'], $data['statusCode']);
    }

}
