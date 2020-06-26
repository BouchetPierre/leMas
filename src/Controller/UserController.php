<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{


    /**
     * @Route("/admin/show/user", name="admin_user_index")
     *
     */
    public function users(UserRepository $repo)
    {
        $users = $repo->findAll();
        return $this->render('admin/user.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * Permet de supprimer un user
     * @Route("/admin/user/{id}/delete", name="admin_user_delete")
     *
     * @return Response
     */

    public function delete(User $user, EntityManagerInterface $manager){
        $manager->remove($user);
        $manager->flush();

        $this->addFlash(
            'success',
            "L'utilisateur a bien été supprimé !"
        );

        return $this->redirectToRoute('admin_user_index');
    }

    /**
     * @Route("/user/user/{slug}", name="user_show")
     */
    public function index(User $user)
    {
        return $this->render('user/index.html.twig', [
            'user' => $user
        ]);
    }
}
