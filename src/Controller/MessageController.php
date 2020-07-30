<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\AnnonceRepository;
use App\Repository\MessageRepository;
use App\Repository\RepondRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Message;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class MessageController extends AbstractController
{
    /**
     * permet de creer un message
     * @Route("/message/new/{slug}", name="message_create")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function create(Annonce $annonce, Request $request, EntityManagerInterface $manager, \Swift_Mailer $mailer)
    {
        $message = new Message();
        $destinataire = $annonce->getAuthor()->getLastname();

        $form= $this->createForm(MessageType::class, $message);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $mailUser = $annonce->getAuthor()->getEmail();
            $this->notify($mailUser, $mailer);

            $message->setDestinataire($annonce);
            $message->setAuthor($this->getUser());
            $manager->persist($message);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre message a bien été crée !'
            );

            return $this->redirectToRoute("account_index");

        }

        return $this->render('message/index.html.twig', [
            'form' => $form->createView(),
            'destinataire' => $destinataire
        ]);
    }


    /**
     * Permet d'afficher les messages
     * @Route("/message/show", name="message_show")
     * @IsGranted("ROLE_USER")
     */
    public function index(MessageRepository $repo, RepondRepository $repond)
    {
        $user = $this->getUser()->getId();

        $reponsesRecu = $repond->findByUserRecu($user);
        $reponsesSend = $repond->findByUserSend($user);
        $messages = $repo->findByUser($user);
        $messageRecu = $repo->findByDest($user);

      return $this->render('message/show.html.twig', [
            'messages' => $messages,
            'messageRecu' => $messageRecu,
            'reponsesRecu'=> $reponsesRecu,
            'reponsesSend'=> $reponsesSend

        ]);
    }


    /**
     * Permet de supprimer un message
     * @Route("/message/show/{id}/delete", name="message_delete")
     *
     * @return Response
     */

    public function delete(Message $message, EntityManagerInterface $manager){
        $manager->remove($message);
        $manager->flush();

        $this->addFlash(
            'success',
            'Le message a bien été supprimée !'
        );

        return $this->redirectToRoute('message_show');
    }

    private function notify($mailUser, \Swift_Mailer $mailer) //function to send a mail to member for notify cancellation
    {

        $message = (new \Swift_Message("Vous avez reçu un message sur le site de l'ASL Le Mas!!!"))
            ->setFrom('ancienshdo@gmail.com')
            ->setTo($mailUser)
            ->setBody("Allez consulter votre messagerie sur le site de l'ASL Le Mas!!!");

        try {
            $mailer->send($message);
        }
        catch(\Exception $e) {

        }
    }
}
