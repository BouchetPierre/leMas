<?php

namespace App\Controller;

use App\Entity\Repond;
use App\Entity\User;
use App\Form\RepondType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class RepondController extends AbstractController
{

    /**
     * permet de repondre à un message
     * @Route("/repond/{id}", name="repond")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function create(User $user, Request $request, EntityManagerInterface $manager, MailerInterface $mailerInterface)
    {
        $reponse = new Repond();
        $destinataire = $user->getLastname();

        $form= $this->createForm(RepondType::class, $reponse);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $email = (new Email())
                ->from('asl.lemas34@gmail.com')
                ->to($user->getEmail())
                ->subject("Vous avez reçu un message sur le site de l' ASL Le MAS !")
                ->html("<p>Rendez vous sur votre messagerie du site de l'ASL</p>");
            $mailerInterface->send($email);

            $reponse->setDestinataire($user);
            $reponse->setAuthor($this->getUser());
            $manager->persist($reponse);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre réponse a bien été envoyée !'
            );

            return $this->redirectToRoute("account_index");

        }

        return $this->render('repond/index.html.twig', [
            'form' => $form->createView(),
            'destinataire' => $destinataire
        ]);
    }

    /**
     * Permet de supprimer un message
     * @Route("/repond/show/{id}/delete", name="repond_delete")
     *
     * @return Response
     */

    public function delete(Repond $reponse, EntityManagerInterface $manager){
        $manager->remove($reponse);
        $manager->flush();

        $this->addFlash(
            'success',
            'Le message a bien été supprimée !'
        );

        return $this->redirectToRoute('message_show');
    }

}

