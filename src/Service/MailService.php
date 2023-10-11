<?php

namespace App\Service;

use App\Entity\Contact;
use App\Entity\User;
use App\IService\IMailService;
use Symfony\Component\HttpFoundation\Response;

class MailService implements IMailService
{
    const FORMAT_DATE = 'd/m/Y H:i:s';

    public static function send($content, string $subject, string $from, string $to, ?string $data = null): array
    {
        $mail = new \SendGrid\Mail\Mail();
        $mail->setFrom($from, "Dendromap");
        $mail->setSubject($subject);
        $mail->addTo($to, "");

        $mail->addContent("text/html", $content);

        $data ? $mail->addCc($data, "Dendromap") : null; // add Cc destination

        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        try {
            $sendgrid->send($mail);
            return [
                "data" => [
                    "message" => "Mail envoyé avec succès"
                ],
                "statusCode" => Response::HTTP_OK
            ];
        } catch (\Exception $e) {
            return [
                "data" => [
                    "message" => "Impossible d'envoyer le mail"
                ],
                "statusCode" => Response::HTTP_BAD_REQUEST
            ];
        }

    }

    public static function sendMailWhenSubscribed(User $user, $discount = ''): array
    {
        // FIX INTERVAL DATE
        $price = ForfaitService::getPriceForfait($discount, $user->getGroupe()->getForfait()->getCodeForfait());

        switch ($user->getGroupe()->getForfait()->getCodeForfait()) {
            case 'GRATUIT' :
                $content = "
                    <h4><b> Bonjour, </b></h4>
                    <p>Merci d’utiliser Dendromap® et d’œuvrer pour la visibilité et sauvegarde du patrimoine arboré !</p>
                    
                    <p>Vous avez souscrit le forfait gratuit Flex au " . $user->getGroupe()->getDateSubscribed()->format('d/m/Y H:i:s') . ". <br>
                        Votre abonnement arrivera à expiration le " . $user->getGroupe()->getDateEcheance()->format('d/m/Y H:i:s') . ". 
                        A l’issue de cette période, votre compte sera supprimé de notre base de données.
                        <br> <br>
                        Si vous souhaitez changer d’abonnement pour <b>exploiter toutes les fonctionnalités de Dendromap®, 
                        retrouvez tous nos forfaits ici :<a href='https://dendromap.fr/#/#tarifs'>https://dendromap.fr/#/#tarifs</a> </b>
                    </p>
                    
                    <p> Nos tutoriels en ligne sont également là pour vous aider : <a href='https://www.youtube.com/channel/UCiN8ZfwNRQN8oiqgrMY1mMg'>https://www.youtube.com/channel/UCiN8ZfwNRQN8oiqgrMY1mMg</a> <br>
                        D’autres questions ? Nos équipes restent bien sûr à votre disposition par mail : <a href='mailto:support@dendromap.fr'>support@dendromap.fr</a> <br> <br>
                        Bon inventaire !</p>
                        
                    <p> La Team Dendromap®</p>
                    ";
                break;
            case '1M_FREE':
                $content = "
                    <h4> <b> Bonjour, </b></h4>
                    <p>
                        Vous avez souhaité bénéficier d’une <b> période d’essai gratuite de 30 jours </b> le " . $user->getGroupe()->getDateSubscribed()->format('d/m/Y H:i:s') . ", 
                        nous avons donc le plaisir de vous offrir un accès premium à <b> toutes les fonctionnalités de Dendromap® </b> jusqu’au " . $user->getGroupe()->getDateEcheance()->format('d/m/Y H:i:s') . ".
                    </p>
                    <p>
                        Il n’y aura <b> aucune facturation </b> pendant la période d’essai, puis vous serez automatiquement facturé de 120 € T.T.C. par mois 
                        renouvelable, <b> sauf si vous résiliez votre forfait avant la date d’échéance </b> ou décidez de changer 
                        d’abonnement. Retrouvez nos offres ici : <a href='https://dendromap.fr/#/#tarifs'>https://dendromap.fr/#/#tarifs</a>
                    </p>
                     <p> 
                        Découvrez nos <b> tutoriels en ligne </b> pour vous aider à utiliser au mieux notre solution métier : 
                      <a href='https://www.youtube.com/channel/UCiN8ZfwNRQN8oiqgrMY1mMg'>https://www.youtube.com/channel/UCiN8ZfwNRQN8oiqgrMY1mMg</a> <br>
                      D’autres questions ? Nos équipes restent bien sûr à votre disposition par mail à <a href='mailto:support@dendromap.fr'>support@dendromap.fr</a>.
                      <br> Bon inventaire !
                    </p>
                        
                    <p> <b> La Team Dendromap® </b></p>
                    ";
                break;
            case '1M' || 'Agile_1MOIS':
                $content = "
                    <h4> <b> Bonjour, </b></h4>
                    <p>Merci d’œuvrer pour la visibilité, la gestion et la protection des arbres ! 
                    <br>Vous avez désormais <b> accès à toutes les fonctionnalités de Dendromap® </b> et bénéficiez <b>gratuitement</b>
                       des dernières mises à jour.
                       <br>Découvrez nos <b>tutoriels en ligne </b> pour vous aider à utiliser au mieux notre solution métier 
                       : <a href='https://www.youtube.com/channel/UCiN8ZfwNRQN8oiqgrMY1mMg'>https://www.youtube.com/channel/UCiN8ZfwNRQN8oiqgrMY1mMg</a> !
                       </p>
                    <p>Pour rappel : vous avez souscrit un <b> forfait Agile - engagement 1 mois </b> le " . $user->getGroupe()->getDateSubscribed()->format('d/m/Y H:i:s') . ". 
                        Vous serez donc facturé de " . $price . " euros T.T.C., puis votre abonnement sera automatiquement renouvelé pour une durée identique; 
                        sauf si vous y mettez un terme avant la date d’échéance le " . $user->getGroupe()->getDateEcheance()->format('d/m/Y H:i:s') . "
                        via votre espace perso, rubrique « Mon Profil »<br> <br>
                        
                       <b> Si vous souhaitez changer d’abonnement, </b> retrouvez tous nos forfaits ici 
                      :<a href='https://dendromap.fr/#/#tarifs'>https://dendromap.fr/#/#tarifs</a> <br>
                      D’autres questions ? Nos équipes restent bien sûr à votre disposition par mail à <a href='mailto:support@dendromap.fr'>support@dendromap.fr</a>.
                      <br> Bon inventaire !
                    </p>
                        
                    <p> <b> La Team Dendromap® </b></p>
                    ";
                break;
            case '6M' || 'Agile_6MOIS':
                $content = "
                    <h4> <b> Bonjour, </b></h4>
                    <p>Merci d’œuvrer pour la visibilité, la gestion et la protection des arbres ! 
                    <br>Vous avez désormais <b> accès à toutes les fonctionnalités de Dendromap® </b> et bénéficiez <b>gratuitement</b>
                       des dernières mises à jour.
                       <br>Découvrez nos <b>tutoriels en ligne </b> pour vous aider à utiliser au mieux notre solution métier 
                       : <a href='https://www.youtube.com/channel/UCiN8ZfwNRQN8oiqgrMY1mMg'>https://www.youtube.com/channel/UCiN8ZfwNRQN8oiqgrMY1mMg</a> !
                       </p>
                    
                    <p>Pour rappel : vous avez souscrit un <b> forfait Agile - engagement 6 mois </b> le " . $user->getGroupe()->getDateSubscribed()->format('d/m/Y H:i:s') . "
                        Vous serez donc facturé de ". $price ." euros T.T.C. par mois pendant 6 mois, puis votre abonnement sera automatiquement renouvelé pour une durée identique ; 
                        sauf si vous y mettez un terme avant la date d’échéance le " . $user->getGroupe()->getDateEcheance()->format('d/m/Y H:i:s') . "
                        via votre espace perso, rubrique « Mon Profil ».
                        <br> <br>
                       
                       <b> Si vous souhaitez changer d’abonnement, </b> retrouvez tous nos forfaits ici 
                      :<a href='https://dendromap.fr/#/#tarifs'>https://dendromap.fr/#/#tarifs</a> <br>
                      D’autres questions ? Nos équipes restent bien sûr à votre disposition par mail à <a href='mailto:support@dendromap.fr'>support@dendromap.fr</a>.
                      <br> Bon inventaire !
                    </p>
                        
                    <p> <b> La Team Dendromap® </b></p>
                    ";
                break;
            case '12M' || 'Agile_12MOIS':
                $content = "
                    <h4><b> Bonjour, </b></h4>
                    <p>Merci d’œuvrer pour la visibilité, la gestion et la protection des arbres ! 
                    <br>Vous avez désormais <b> accès à toutes les fonctionnalités de Dendromap® </b> et bénéficiez <b>gratuitement</b>
                       des dernières mises à jour.
                       <br>Découvrez nos <b>tutoriels en ligne </b> pour vous aider à utiliser au mieux notre solution métier 
                       : <a href='https://www.youtube.com/channel/UCiN8ZfwNRQN8oiqgrMY1mMg'>https://www.youtube.com/channel/UCiN8ZfwNRQN8oiqgrMY1mMg</a> !
                       </p>
                    
                    <p>Pour rappel : vous avez souscrit un <b> forfait Agile - engagement 12 mois </b> le " . $user->getGroupe()->getCreatedAt()->format('d/m/Y H:i:s') . ".
                        Vous serez donc facturé de ". $price . " euros T.T.C. par mois pendant 12 mois, puis votre abonnement sera automatiquement renouvelé pour une durée identique ; 
                        sauf si vous y mettez un terme avant la date d’échéance le " . $user->getGroupe()->getDateEcheance()->format('d/m/Y H:i:s') . "
                         via votre espace perso, rubrique « Mon Profil ».
                        <br> <br>
                        
                       <b> Si vous souhaitez changer d’abonnement, </b> retrouvez tous nos forfaits ici 
                      :<a href='https://dendromap.fr/#/#tarifs'>https://dendromap.fr/#/#tarifs</a> <br>
                      D’autres questions ? Nos équipes restent bien sûr à votre disposition par mail à <a href='mailto:support@dendromap.fr'>support@dendromap.fr</a>.
                      <br> Bon inventaire !
                    </p>
                        
                    <p> <b> La Team Dendromap® </b></p>
                     ";
                break;

        }
        return self::send($content, 'Abonnement Dendromap', 'no-reply@dendromap.com', $user->getEmail());
    }


    static function sendEmailConfirmation(User $user, $token, $password = null): string
    {
        if ($password) {
            $content = "
                <h4>Bonjour " . $user->getUsername() . " et bienvenue sur Dendromap,</h4>
                <p> Pour commencer, vous devez confirmer votre adresse e-mail en cliquant sur le lien dessus :
                </p>
                
                <p>
                    <strong><a href=" . getenv('BASE_URL') . "/confirmation/'" . $token . "'>Activation Email</a></strong>
                </p>
               
                    <p>Une fois votre email confirmé, connectez vous avec ce mot de passe :<strong>$password</strong></p>
                <p>
                    Ce lien n'est active que 1 jour : 
                </p>";
        } else {
            $content = "
                <h4>Bonjour " . $user->getUsername() . " et bienvenue sur Dendromap,</h4>
                <p> Pour commencer, vous devez confirmer votre adresse e-mail en cliquant sur le lien dessus :
                </p>s
                <p>
                    <strong><a href=" . getenv('BASE_URL') . "/confirmation/'" . $token . "'>Activation Email</a></strong>
                </p>
                <p>
                    Ce lien n'est active que 1 jour : 
                </p>";
        }
        return self::send($content, "Confirmer votre email", "no-reply@dendromap.com", $user->getEmail());
    }

    private static function mailChangeForfaitFromPayant(array $infosForfait, User $user): string
    {
        return "  
          <h4> <b> Bonjour, </b></h4>
            <p>Merci de renouveler votre confiance ! 
            <br>Pour rappel : vous avez souscrit un <b>forfait Agile - engagement " . $infosForfait['duration'] . " mois</b> le " . $user->getGroupe()->getUpdatedAt()->format(self::FORMAT_DATE) . ".
             Vous serez donc facturé de " . $infosForfait['price'] . " euros T.T.C. par mois, puis votre abonnement sera automatiquement renouvelé pour une durée identique; 
             sauf si vous y mettez un terme avant la date d’échéance le " . $user->getGroupe()->getDateEcheance()->format(self::FORMAT_DATE) . " via votre espace perso, rubrique « Mon Profil ».
            <br>
            Veuillez noter qu’il ne sera effectif qu’à la fin de votre précédent 
            engagement, à savoir le " . $user->getGroupe()->getDateSubscribed()->format(self::FORMAT_DATE) . ".             
               <br>Découvrez nos <b>tutoriels en ligne </b> pour vous aider à utiliser au mieux notre solution métier 
               : <a href='https://www.youtube.com/channel/UCiN8ZfwNRQN8oiqgrMY1mMg'>https://www.youtube.com/channel/UCiN8ZfwNRQN8oiqgrMY1mMg</a> !
               </p>
            <p>
               <b> Si vous souhaitez changer d’abonnement, </b> retrouvez tous nos forfaits ici 
              :<a href='https://dendromap.fr/#/#tarifs'>https://dendromap.fr/#/#tarifs</a> <br>
              D’autres questions ? Nos équipes restent bien sûr à votre disposition par mail à <a href='mailto:support@dendromap.fr'>support@dendromap.fr</a>.
              <br> Bon inventaire !
            </p>
            
            <p>
                Arboricolement, <br>
                La Team Dendromap®
            </p>";
    }

    private static function mailChangeForfaitfromGratuit($infosForfait, User $user): string
    {
        return "  
          <h4> <b> Bonjour, </b></h4>
            <p>Merci d’œuvrer pour la visibilité, la gestion et la protection des arbres ! 
            <br>Vous avez désormais <b> accès à toutes les fonctionnalités de Dendromap® </b> et bénéficiez <b>gratuitement</b>
               des dernières mises à jour.
               <br>Découvrez nos <b>tutoriels en ligne </b> pour vous aider à utiliser au mieux notre solution métier 
               : <a href='https://www.youtube.com/channel/UCiN8ZfwNRQN8oiqgrMY1mMg'>https://www.youtube.com/channel/UCiN8ZfwNRQN8oiqgrMY1mMg</a> !
               </p>
            
            <p>Pour rappel : vous avez souscrit un <b> forfait Agile - engagement " . $infosForfait['duration'] . " mois </b> le " . $user->getGroupe()->getDateSubscribed()->format(self::FORMAT_DATE) . "
                Vous serez donc facturé de " . $infosForfait['price'] . " euros T.T.C. par mois pendant " . $infosForfait['duration'] . " mois, 
                puis votre abonnement sera automatiquement renouvelé pour une durée identique; sauf si vous y mettez un terme avant 
                la date d’échéance le " . $user->getGroupe()->getDateEcheance()->format(self::FORMAT_DATE) . "
                via votre espace perso, rubrique « Mon Profil ».
                <br> <br>
               <b> Si vous souhaitez changer d’abonnement, </b> retrouvez tous nos forfaits ici 
              :<a href='https://dendromap.fr/#/#tarifs'>https://dendromap.fr/#/#tarifs</a> <br>
              D’autres questions ? Nos équipes restent bien sûr à votre disposition par mail à <a href='mailto:support@dendromap.fr'>support@dendromap.fr</a>.
              <br> Bon inventaire !
            </p>
                
            <p>
                Arboricolement, <br>
                La Team Dendromap®
            </p>";
    }

    private static function mailDownForfait(User $user): string
    {
        return "
            <p>
            Nous sommes désolés de vous voir partir !
           <br>La résiliation sera effective à la fin de votre période d’engagement au " . $user->getGroupe()->getDateEcheance()->format(self::FORMAT_DATE) . ". 
            <br> <br>
            Vous avez des besoins d’inventaires, de diagnostics phytosanitaires à court terme ? 
            Nous avons des forfaits adaptés ici <a href='https://dendromap.fr/#/#tarifs'>https://dendromap.fr/#/#tarifs</a> et espérons vous revoir bientôt.
            <br> <br>
            
            Aboricolement, <br>
            La team Dendromap
            <br><br>
            <i>
                N.B. : Votre accès sera conservé 6 mois mais avec des fonctionnalités limitées, 
                puis votre compte sera supprimé pour des raisons de stockage de données.
            </i>
            </p>
       ";
    }

    private static function renewToPay(User $user): string
    {
        return "
             <p>Bonjour ,</p>
            <p>
            Merci de nous avoir fait confiance et d’utiliser Dendromap® pour la gestion de vos arbres!
           <br>Votre forfait AGILE arrive à expiration le " . $user->getGroupe()->getDateEcheance()->format(self::FORMAT_DATE) . ",
           et sera automatiquement reconduit pour la même période d’engagement sans action de votre part.
           </p>
           <p>
            Si toutefois vous souhaitez modifier ou résilier votre forfait, 
            rendez-vous sur votre espace personnel, rubrique « Mon forfait ».
            </p>
            <p>
                Arboricolement,
                <br>La Team Dendromap®
            </p>
            <p>
               <i>N.B. : En cas de résiliation, votre accès sera conservé 6 mois mais avec des fonctionnalités limitées, 
                puis votre compte sera supprimé pour des raisons de stockage de données. </i>
            </p>
       ";
    }

    public static function sendMailForfait(array $infoForfait, User $user, string $type): array
    {
        // 3 type of Mail
        // *** PAY_TO_PAY, FREE_TO_PAY AND DOWN_TO_PAY
        // GET CONTENT
        switch ($type) {
            case 'PAY_TO_PAY' :
                $content = self::mailChangeForfaitFromPayant($infoForfait, $user);
                $subject = 'Confirmation de changement d’abonnement Dendromap®';
                break;
            case 'FREE_TO_PAY':
                $content = self::mailChangeForfaitfromGratuit($infoForfait, $user);
                $subject = 'Confirmation d’abonnement Dendromap®';
                break;
            case 'DOWN_TO_PAY':
                $content = self::mailDownForfait($user);
                $subject = 'Confirmation de résiliation d’abonnement Dendromap®';
                break;
            case 'RENEW_TO_PAY':
                $content = self::renewToPay($user);
                $subject = 'Renouvellement de votre abonnement Dendromap®';
                break;
        }
        return self::send($content, $subject, 'no-reply@dendromap.com', $user->getEmail(), $type == 'DOWN_TO_PAY' ? "contact@dendromap.fr" : null);
    }

    static function generateUrlConfirmChangePsd(User $user, $token): array
    {
        $content = " 
                    <h4>Bonjour " . $user->getUsername() . " et bienvenue sur Dendromap,</h4>
                    <p> Nous avons récemment reçu une demande de changement du mot de passe de votre compte.</p>
                      
                    <p>Si vous avez bien demandé ce changement de mot de passe, veuillez cliquer sur le lien ci-dessous afin de définir un nouveau mot de passe dans les 2 heures :</p>
                    <br>
                        <strong>
                                <a href=" . getenv('BASE_URL') . '#/password-confirm/' . $token . ">Cliquez ici afin de changer de mot de passe </a>
                        </strong>
                    <p> Si vous ne souhaitez pas changer de mot de passe, contentez-vous d’ignorer ce message.  </p>
                ";

        return self::send($content, 'Changement de mot de passe', 'no-reply@dendromap.com', $user->getEmail(), null);
    }

    public static function sendEmailContact(Contact $objet)
    {
        // SEND EMAIL
        // LE CAS D'UN MAIL DE CONTACT
        $email = new \SendGrid\Mail\Mail();

        $email->setFrom($objet->getEmail(), '');
        $email->setSubject('Contact - ' . $objet->getObjet());
        $email->addTo("contact@dendromap.fr", "DENDROMAP");
        // $email->addContent("text/plain", $objet->getMessage());
        $email->addContent(
            "text/html", "
            <table>
                <thead>
                    <tr>
                        <th>NOM</th>
                        <th>PRENOM</th>
                        <th>FONCTION</th>
                        <th>EMAIL</th>
                        <th>TELEPHONE</th>
                        <th>Je suis un/une</th>
                        <th>OBJET</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <td>" . $objet->getNom() . "</td>
                    <td>" . $objet->getPrenom() . "</td>
                    <td>" . $objet->getFonction() . "</td>
                    <td>" . $objet->getEmail() . "</td>
                    <td>" . $objet->getTel() . "</td>
                    <td>" . $objet->getGroupe() . "</td>
                    <td>" . $objet->getObjet() . "</td>
                    <td>" . $objet->getMessage() . "</td>
                </tbody>
            </table>"
        );
        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        try {
            $sendgrid->send($email);
        } catch (\Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
        }
    }
}
