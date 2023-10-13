<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use App\Repository\UserRepository;

use App\Service\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use App\Message\TokenValidMessage;

/**
 * @Route("/contact")
 */
class ContactController extends AbstractController
{
    /**
     * @Route("", name="contact_index", methods="GET")
     */
    public function index(Request $request, MessageBusInterface $bus, UserRepository $userRepository, ContactRepository $contactRepository): Response
    {
        /**************** START WRIGHT AUTHORIZATION ****************/
        // $headers = $request->headers->get('Authorization');

        // if(!$headers){
        //     return new JsonResponse([
        //         'message' => 'Access refusé',
        //         'errorCode' => 401
        //     ], 401);
        // }
        // // VALID TOKEN MESSAGE
        // $token = $bus->dispatch(new TokenValidMessage($headers));

        // if($token === -1) {
        //     return new JsonResponse([
        //         'message' => 'Access refusé',
        //         'errorCode' => 401
        //     ], 401);
        // }
        // else if($token === 3) {
        //     return new JsonResponse([
        //         'message' => 'session expiré',
        //         'errorCode' => 402
        //     ], 401);
        // }

        // $user = $userRepository->findOneBy(["id" => $token->data->id]);
        
        /******************* END WRIGHT AUTHORIZATION ********************/
        $objects = $contactRepository->findAll();

        $_object = [];
        $_objects = [];
        foreach($objects as $object) {
            // objectR LES objectS

            $_object['id'] = $object->getId();
            $_object['nom'] = $object->getNom();
            $_object['prenom'] = $object->getPrenom();
            $_object['tel'] = $object->getTel();
            $_object['email'] = $object->getEmail();
            $_object['groupe'] = $object->getGroupe();
            $_object['objet'] = $object->getObjet();
            $_object['message'] = $object->getMessage();
            $_object['etat'] = $object->getEtat();

            $_objects[] = $_object;
        }
        return new JsonResponse($_objects, 200);
    }

    /**
     * @Route("", name="contact_new", methods="POST")
     */
    public function new(ValidatorInterface $validator, MessageBusInterface $bus, ContactRepository $contactRepository, Request $request): Response
    {
        $contact = new Contact();

        $body = $request->getContent();
        $data = json_decode($body, true); 
        
        // VERIFY ITEM EXIST
        if(!isset($data['nom']) || !isset($data['fonction']) || !isset($data['tel'])
        || !isset($data['email']) || !isset($data['objet']) || !isset($data['groupe']) ) {
            return new JsonResponse([
                "message" => "Saisir informations obligatoire",
                "errorCode" => 300
            ], 403);
        }

        $data['createdAt'] = new \DateTime('now');
        // HYDRATATION DE DONNEES
        $form = $this->createForm(ContactType::class, $contact);
        $form->submit($data);

        $contact->setCreatedAt(new \DateTime('now'));

        $errors = $validator->validate($contact);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new JsonResponse(
                [
                    "message" => "Certaines valeurs ne sont pas conformes",
                    "errorCode" => 301,
                ], 
            409);
        }
        
        // ENVOIE LE MAIL
        MailService::sendEmailContact($contact);
        
        try{
            $em = $this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();
            return new JsonResponse(
                [
                    "message" => "Contact créé avec succès",
                    "errorCode" => 200,
                    "id" => $contact->getId()
                ], 200);
        } catch(\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(
                [
                    "message" => "création impossible",
                    "errorCode" => 302
                ], 500);
        }

    }

    /**
     * @Route("/{id}", name="contact_show", methods="GET")
     */
    public function show(Contact $contact): Response
    {
        // SHOW CONTACT 
        
    }

    /**
     * @Route("/{id}", name="contact_delete", methods="DELETE")
     */
    public function delete(MessageBusInterface $bus, UserRepository $userRepository, Request $request, Contact $contact): Response
    {
       /**************** START WRIGHT AUTHORIZATION ****************/
       $headers = $request->headers->get('Authorization');

       if(!$headers){
           return new JsonResponse([
               'message' => 'Access refusé',
               'errorCode' => 401
           ], 401);
       }
       // VALID TOKEN MESSAGE
       $token = $bus->dispatch(new TokenValidMessage($headers));

       if($token === -1) {
           return new JsonResponse([
               'message' => 'Access refusé',
               'errorCode' => 401
           ], 401);
       }
       else if($token === 3) {
           return new JsonResponse([
               'message' => 'session expiré',
               'errorCode' => 402
           ], 401);
       }

       $user = $userRepository->findOneBy(["id" => $token->data->id]);

       if($user->getGroupe()['groupeType'] !== 'DENDROMAP'){
           return new JsonResponse([
               'message' => 'accès refusé',
               'errorCode' => 401
           ], 401);
       };

       /******************* END WRIGHT AUTHORIZATION ********************/
       try{
        $em = $this->getDoctrine()->getManager();
        $em->remove($contact);
        $em->flush();
        return new JsonResponse(
            [
                "message" => "Suppression effectué avec succès",
                "errorCode" => 200
            ], 200);            
        }
        catch(\Doctrine\DBAL\DBALException $e) {
            return new JsonResponse(["message" => "Operation impossible"], 500);
        }
    }
}
