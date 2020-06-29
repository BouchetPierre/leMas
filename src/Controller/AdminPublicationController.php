<?php

namespace App\Controller;

use App\Entity\AdminPublication;
use App\Form\AdminPublicationType;
use App\Repository\AdminPublicationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AdminPublicationController extends AbstractController
{
    /**
     * Permet de faire une publication
     *
     * @Route("/admin/publication/new", name="admin_publication")
     *
     * @return Response
     */

    public function create(Request $request, EntityManagerInterface $manager){
        $publication = new AdminPublication();

        $form= $this->createForm(AdminPublicationType::class, $publication);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $publication->setAuthor($this->getUser());

            $brochureFile = $form->get('brochure')->getData();

            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME).'.pdf';


                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $originalFilename
                    );
                } catch (FileException $e) {

                }
                $publication->setBrochureFilename($originalFilename);
            }

            $manager->persist($publication);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre publication a bien été crée !'
            );

            return $this->redirectToRoute("admin_dachboard");
        }

        return $this->render('admin/publication/new.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * Permet de voir les publications
     * @Route("/publications/show", name="publication_show")
     * @IsGranted("ROLE_USER")
     */
    public function index(AdminPublicationRepository $repo, UserRepository $user)
    {

        $publications = $repo->findAll2();
        $idAuthor = $user->idAdmin();



        return $this->render('publications/index.html.twig', [
            'publications' => $publications,
            'idLeMas' => $idAuthor
        ]);
    }

    /**
     * @Route("/admin/publications", name="admin_publications_index")
     */
    public function indexAdmin(AdminPublicationRepository $repo)
    {
        $publications = $repo->findAll();

        return $this->render('admin/publication/index.html.twig', [
            'publications' => $publications
        ]);
    }

    /**
     * Permet de supprimer une publication
     * @Route("admin/publications/{id}/delete", name="admin_publications_delete")
     *
     * @return Response
     */

    public function deleteMes(AdminPublication $publication, EntityManagerInterface $manager){
        $manager->remove($publication);
        $manager->flush();

        $this->addFlash(
            'success',
            "La publication a bien été supprimée !"
        );

        return $this->redirectToRoute('admin_publications_index');
    }

    /**
     * Permet d'afficher et de modifier une publication
     * @Route("admin/publication/{id}/edit", name="admin_publications_edit")
     *      *
     * @return Response
     */

    public function edit(AdminPublication $publication, Request $request, EntityManagerInterface $manager){

        $form= $this->createForm(AdminPublicationType::class, $publication);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($publication);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre publication a bien été modifiée !'
            );

            return $this->redirectToRoute('admin_publications_index');
        }
        return $this->render('admin/publication/edit.html.twig',[
            'form' => $form->createView(),
            'publication'=> $publication
        ]);
    }
}
