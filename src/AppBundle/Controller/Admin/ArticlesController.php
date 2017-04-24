<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Articles;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Repository\ConsumptionCategoryRepository;

/**
 * Article controller.
 *
 * @Route("admin/articles")
 */
class ArticlesController extends Controller
{
    /**
     * Lists all article entities.
     *
     * @Route("/", name="admin_articles_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $dql   = "SELECT a FROM AppBundle:Articles a";
        $query = $em->createQuery($dql);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            5/*limit per page*/
        );

        $articles = $em->getRepository('AppBundle:Articles')->findAll();

        return $this->render('admin/articles/index.html.twig', array(
            'articles' => $articles,
            'pagination' => $pagination
        ));
    }

    /**
     * Creates a new article entity.
     *
     * @Route("/new", name="admin_articles_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $article = new Articles();
        $form = $this->createForm('AppBundle\Form\ArticlesType', $article);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            try {
                if ($form->isValid()){
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($article);
                    $em->flush();

                    $this->addFlash(
                        'success',
                        'The article was added!'
                    );
                    return $this->redirectToRoute('admin_articles_index');
                }
                if ($form->getErrors()) {
                    $this->addFlash(
                        'danger',
                        $form->getErrors()
                    );
                }
            } catch (\Exception $e) {
                $this->addFlash(
                    'danger',
                    $e->getMessage()
                );
            }
        }
        return $this->render('admin/articles/new.html.twig', array(
            'article' => $article,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing article entity.
     *
     * @Route("/{id}/edit", name="articles_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Articles $article)
    {
        $editForm = $this->createForm('AppBundle\Form\ArticlesType', $article);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            try{
                $this->_setParentCategory($article, $editForm);
                $this->getDoctrine()->getManager()->flush();
            } catch (\Exception $e) {
                $this->addFlash(
                    'danger',
                    $e->getMessage()
                );
            }
            $this->addFlash(
                'success',
                "The category {$article->getName()} has changed."
            );

            return $this->redirectToRoute('admin_articles_index');
        }

        return $this->render('admin/articles/edit.html.twig', array(
            'article' => $article,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a article entity.
     *
     * @Route("/{id}/delete", name="admin_articles_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, Articles $article)
    {
        $name = '';
        try {
            $id = (int)$request->get('id');
            if(!$id){
                $this->addFlash(
                    'danger',
                    "The id is not set."
                );
                return $this->redirectToRoute('admin_articles_index');
            }
            $article =  $this->getDoctrine()->getRepository('AppBundle:Articles')->find($id);
            if (!$article) {
                $this->addFlash(
                    'danger',
                    "The article with this id is not exist."
                );
                return $this->redirectToRoute('admin_articles_index');
            }
            $name = $article->getName();
            $em = $this->getDoctrine()->getManager();
            $em->remove($article);
            $em->flush();}

        catch (\Exception $e) {
                $this->addFlash(
                    'danger',
                    $e->getMessage()
                );
            }
        $this->addFlash(
            'success',
            "The article {$name} has deleted."
        );
        return $this->redirectToRoute('admin_articles_index');
    }

    /**
     * Creates a form to delete a article entity.
     *
     * @param Articles $article The article entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Articles $article)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_articles_delete', array('id' => $article->getId())))
            ->setMethod('GET')
            ->getForm()
        ;
    }
}
